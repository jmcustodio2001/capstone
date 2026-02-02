<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompetencyGap;
use App\Models\Employee;
use App\Models\CompetencyLibrary;
use App\Models\EmployeeCompetencyProfile;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Schema\Blueprint;

class CompetencyGapAnalysisController extends Controller
{
    // AJAX: Recalculate readiness scores and return updated gap data
    public function refreshScore(Request $request)
    {
        $gaps = \App\Models\CompetencyGap::with(['employee', 'competency'])->get();
        $gapData = $gaps->map(function($gap) {
            return [
                'id' => $gap->id,
                'employee_id' => $gap->employee_id,
                'competency_id' => $gap->competency_id,
                'competency_rate' => $gap->competency ? $gap->competency->rate : 0,
                'required_level' => $gap->required_level,
                'current_level' => $gap->current_level,
                'gap' => $gap->gap,
            ];
        });
        return response()->json(['gaps' => $gapData]);
    }
    public function index()
    {
        // Ensure table exists before proceeding
        $this->ensureTableExists();

        // Fix missing categories for existing competencies
        $this->fixMissingCategories();

        // Auto-fix expired dates (NULL or > 180 days)
        $this->fixExpiredDates();

        // Fetch employees from API endpoint (Consistent with Profile Controller)
        $employeeMap = [];
        try {
            $response = \Illuminate\Support\Facades\Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
            $apiData = $response->successful() ? $response->json() : [];
            $apiEmployees = (isset($apiData['data']) && is_array($apiData['data'])) ? $apiData['data'] : $apiData;

            if (is_array($apiEmployees)) {
                foreach ($apiEmployees as $emp) {
                    // Match the ID mapping logic from EmployeeCompetencyProfileController
                    $empId = is_array($emp) 
                        ? ($emp['external_employee_id'] ?? $emp['employee_id'] ?? $emp['id'] ?? null)
                        : ($emp->external_employee_id ?? $emp->employee_id ?? $emp->id ?? null);
                    
                    if ($empId) {
                        $employeeMap[$empId] = $emp;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch employees from API in CompetencyGap: ' . $e->getMessage());
        }

        // Eager load related employee and competency
        $gaps = CompetencyGap::with(['employee', 'competency'])
            ->whereHas('competency', function($query) {
                $query->where('competency_name', 'NOT LIKE', '%BESTLINK%')
                    ->where('competency_name', 'NOT LIKE', '%ITALY%');
            })
            ->accessible()
            ->orderBy('employee_id')
            ->paginate(12);

        // Fetch expired gaps for display
        $expiredGaps = CompetencyGap::with(['employee', 'competency'])
            ->whereHas('competency', function($query) {
                $query->where('competency_name', 'NOT LIKE', '%BESTLINK%')
                    ->where('competency_name', 'NOT LIKE', '%ITALY%');
            })
            ->expired()
            ->get();

        // Map API data to active gaps for real name display
        foreach ($gaps as $gap) {
            if (isset($employeeMap[$gap->employee_id])) {
                $apiEmp = $employeeMap[$gap->employee_id];
                
                // Get local picture from the existing relation BEFORE overwriting
                $localPic = $gap->employee ? $gap->employee->profile_picture : null;
                
                $employeeData = new \stdClass();
                $employeeData->employee_id = $gap->employee_id;
                $employeeData->first_name = is_array($apiEmp) ? ($apiEmp['first_name'] ?? 'Unknown') : ($apiEmp->first_name ?? 'Unknown');
                $employeeData->last_name = is_array($apiEmp) ? ($apiEmp['last_name'] ?? 'Employee') : ($apiEmp->last_name ?? 'Employee');
                
                $apiPic = is_array($apiEmp) ? ($apiEmp['profile_picture'] ?? null) : ($apiEmp->profile_picture ?? null);
                // Prefer API pic, fallback to local pic
                $employeeData->profile_picture = $apiPic ?: $localPic;
                
                $gap->setRelation('employee', $employeeData);
            }
        }

        // Map API data to expired gaps
        foreach ($expiredGaps as $gap) {
            if (isset($employeeMap[$gap->employee_id])) {
                $apiEmp = $employeeMap[$gap->employee_id];
                
                // Get local picture from the existing relation BEFORE overwriting
                $localPic = $gap->employee ? $gap->employee->profile_picture : null;
                
                $employeeData = new \stdClass();
                $employeeData->employee_id = $gap->employee_id;
                $employeeData->first_name = is_array($apiEmp) ? ($apiEmp['first_name'] ?? 'Unknown') : ($apiEmp->first_name ?? 'Unknown');
                $employeeData->last_name = is_array($apiEmp) ? ($apiEmp['last_name'] ?? 'Employee') : ($apiEmp->last_name ?? 'Employee');
                
                $apiPic = is_array($apiEmp) ? ($apiEmp['profile_picture'] ?? null) : ($apiEmp->profile_picture ?? null);
                // Prefer API pic, fallback to local pic
                $employeeData->profile_picture = $apiPic ?: $localPic;
                
                $gap->setRelation('employee', $employeeData);
            }
        }

        // Prepare employees list for the "Add Gap" dropdown
        $localEmployees = Employee::all();
        $employees = [];
        
        foreach ($employeeMap as $id => $data) {
            $employees[] = [
                'id' => $id,
                'name' => (is_array($data) ? ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '') : ($data->first_name ?? '') . ' ' . ($data->last_name ?? ''))
            ];
        }

        foreach ($localEmployees as $emp) {
            if (!isset($employeeMap[$emp->employee_id])) {
                $employees[] = [
                    'id' => $emp->employee_id,
                    'name' => $emp->first_name . ' ' . $emp->last_name
                ];
            }
        }

        $competencies = CompetencyLibrary::select('id', 'competency_name', 'description', 'category', 'rate')
            ->where('competency_name', 'NOT LIKE', '%BESTLINK%')
            ->where('competency_name', 'NOT LIKE', '%ITALY%')
            ->where('category', '!=', 'Destination Knowledge')
            ->where('description', '!=', 'Auto-imported skill from employee profile')
            ->paginate(10, ['*'], 'competencies_page');

        // Training assignments check
        $employeeTrainingAssignments = [];
        foreach ($gaps as $gap) {
            $employeeId = $gap->employee_id;
            $competencyId = $gap->competency_id;
            $competencyName = $gap->competency?->competency_name ?? '';

            $hasSpecificTraining = \App\Models\EmployeeTrainingDashboard::where('employee_training_dashboards.employee_id', $employeeId)
                ->join('course_management', 'employee_training_dashboards.course_id', '=', 'course_management.course_id')
                ->where(function($query) use ($competencyName) {
                    $cleanCompetency = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $competencyName);
                    $query->where('course_management.course_title', 'LIKE', '%' . $cleanCompetency . '%')
                          ->orWhere('course_management.course_title', 'LIKE', '%' . $competencyName . '%');
                })
                ->exists();

            $employeeTrainingAssignments[$employeeId . '_' . $competencyId] = $hasSpecificTraining;
        }

        return view('competency_management.competency_gap', compact('gaps', 'employees', 'competencies', 'employeeTrainingAssignments', 'expiredGaps'));
    }

    public function store(Request $request)
    {
        try {
            // Enhanced debug logging
            Log::info('=== CompetencyGap Store Request START ===');
            Log::info('Request Method: ' . $request->method());
            Log::info('Request URL: ' . $request->fullUrl());
            Log::info('Request Headers: ', $request->headers->all());
            Log::info('Request Data: ', $request->all());
            Log::info('Is AJAX: ' . ($request->ajax() ? 'YES' : 'NO'));
            Log::info('Wants JSON: ' . ($request->wantsJson() ? 'YES' : 'NO'));

            // Ensure table exists before proceeding
            $this->ensureTableExists();

            Log::info('Table exists, proceeding with validation...');

            $maxDate = now()->addDays(30)->endOfDay()->toDateTimeString();
            $validated = $request->validate([
                'employee_id'    => 'required|exists:employees,employee_id',
                'competency_id'  => 'required|exists:competency_library,id',
                'required_level' => 'required|integer|min:1|max:5',
                'current_level'  => 'required|integer|min:0|max:5',
                'gap'            => 'required|integer',
                'gap_description'=> 'nullable|string|max:1000',
                'expired_date'   => 'nullable|date|before_or_equal:' . $maxDate,
            ]);

            Log::info('Validation passed. Validated Data: ', $validated);

            // Set default expired date if not provided
            if (!isset($validated['expired_date']) || empty($validated['expired_date'])) {
                $validated['expired_date'] = now()->addMonth();
                Log::info('Set default expired_date: ' . $validated['expired_date']->format('Y-m-d H:i:s'));
            } else {
                // Convert string date to Carbon object if it exists
                $validated['expired_date'] = \Carbon\Carbon::parse($validated['expired_date']);
                Log::info('Converted expired_date: ' . $validated['expired_date']->format('Y-m-d H:i:s'));
            }

            // Create the CompetencyGap record with proper data handling
            $gapData = $validated;
            // Ensure expired_date is a Carbon object for the model (Laravel casting expects Carbon objects)
            if (isset($gapData['expired_date'])) {
                // Always ensure it's a Carbon object, even if it already is one
                $gapData['expired_date'] = \Carbon\Carbon::parse($gapData['expired_date']);
            }

            Log::info('Attempting to create CompetencyGap record...');

            $gap = CompetencyGap::create($gapData);

            Log::info('CompetencyGap created successfully with ID: ' . $gap->id);

            // Log creation with expiration info
            try {
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'module' => 'Competency Management',
                    'action' => 'create',
                    'description' => 'Added competency gap for employee ID: ' . $gap->employee_id . ' (Expires: ' . ($gap->expired_date ? $gap->expired_date->format('Y-m-d g:i A') : 'No expiry') . ')',
                    'model_type' => CompetencyGap::class,
                    'model_id' => $gap->id,
                ]);
                Log::info('Activity log created successfully');
            } catch (\Exception $logError) {
                Log::warning('Activity log creation failed: ' . $logError->getMessage());
                // Don't fail the main operation if logging fails
            }

            Log::info('=== CompetencyGap Store Request SUCCESS ===');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gap record added successfully!',
                    'gap' => $gap->load(['employee', 'competency'])
                ]);
            }
            return redirect()->back()->with('success', 'Gap record added!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error: ', $e->errors());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('=== CompetencyGap Store Request FAILED ===');
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('Error File: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack Trace: ' . $e->getTraceAsString());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                    'debug' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]
                ], 500);
            }
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $gap = CompetencyGap::findOrFail($id);
            
            // Check if gap is already assigned to training
            if ($gap->assigned_to_training) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot update this competency gap as it has already been assigned to training.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Cannot update this competency gap as it has already been assigned to training.');
            }
            $maxDate = now()->addDays(30)->endOfDay()->toDateTimeString();
            $validated = $request->validate([
                'employee_id'    => 'required|exists:employees,employee_id',
                'competency_id'  => 'required|exists:competency_library,id',
                'required_level' => 'required|integer|min:1|max:5',
                'current_level'  => 'required|integer|min:0|max:5',
                'gap'            => 'required|integer',
                'gap_description'=> 'nullable|string|max:1000',
                'expired_date'   => 'nullable|date|before_or_equal:' . $maxDate,
            ]);
            $gap->update($validated);

            // SYNC WITH TRAINING RECORD: Update expired dates in both systems
            $this->syncExpiredDateWithTraining($gap);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'update',
                'description' => 'Updated competency gap for employee ID: ' . $gap->employee_id,
                'model_type' => CompetencyGap::class,
                'model_id' => $gap->id,
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gap record updated successfully!',
                    'gap' => $gap->load(['employee', 'competency'])
                ]);
            }
            return redirect()->back()->with('success', 'Gap record updated!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $gap = CompetencyGap::findOrFail($id);
            
            // Check if gap is already assigned to training
            if ($gap->assigned_to_training) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete this competency gap as it has already been assigned to training.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Cannot delete this competency gap as it has already been assigned to training.');
            }
            $employeeId = $gap->employee_id;
            $gap->delete();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'delete',
                'description' => 'Deleted competency gap for employee ID: ' . $employeeId,
                'model_type' => CompetencyGap::class,
                'model_id' => $id,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gap record deleted successfully!'
                ]);
            }

            return redirect()->back()->with('success', 'Gap record deleted!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            throw $e;
        }
    }

    // Export gaps to CSV
    public function export()
    {
        $gaps = CompetencyGap::with(['employee', 'competency'])->get();

        $filename = 'competency_gaps_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $columns = [
            'ID', 'Employee', 'Competency', 'Rate', 'Required Level', 'Current Level', 'Gap'
        ];

        $callback = function() use ($gaps, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($gaps as $gap) {
                fputcsv($file, [
                    $gap->id,
                    $gap->employee ? ($gap->employee->first_name . ' ' . $gap->employee->last_name) : 'N/A',
                    $gap->competency ? $gap->competency->competency_name : 'N/A',
                    $gap->competency ? $gap->competency->rate : 'N/A',
                    $gap->required_level,
                    $gap->current_level,
                    $gap->gap,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function ajaxGapTable()
    {
        $customerServiceCompetencies = \App\Models\CompetencyLibrary::where('category', 'Customer Service & Sales')->pluck('id');
        $gaps = \App\Models\CompetencyGap::whereIn('competency_id', $customerServiceCompetencies)
            ->where('gap', '>', 0)
            ->with(['employee', 'competency'])
            ->get()
            ->map(function($gap) {
                $recommendedTraining = \App\Models\EmployeeTrainingDashboard::whereHas('course', function($q) use ($gap) {
                    $q->where('course_title', 'LIKE', '%' . $gap->competency->competency_name . '%');
                })->first();
                return (object) [
                    'employee' => $gap->employee,
                    'competency' => $gap->competency,
                    'required_level' => $gap->required_level,
                    'current_level' => $gap->current_level,
                    'gap' => $gap->gap,
                    'recommended_training' => $recommendedTraining ? $recommendedTraining->course : null,
                ];
            });
        $gapTableHtml = view('partials.gap_table', ['gaps' => $gaps])->render();
        return response($gapTableHtml);
    }

    /**
     * Approve and activate destination knowledge training from competency gap management
     */
    public function approveDestinationKnowledge(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|string|exists:employees,employee_id',
                'destination_name' => 'required|string'
            ]);

            // Find the destination knowledge training record
            $destinationTraining = \App\Models\DestinationKnowledgeTraining::where('employee_id', $request->employee_id)
                ->where('destination_name', $request->destination_name)
                ->first();

            if (!$destinationTraining) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destination knowledge training record not found.'
                ], 404);
            }

            // Approve and activate the training
            $destinationTraining->update([
                'admin_approved_for_upcoming' => true,
                'is_active' => true,
                'status' => 'approved'
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'approve_destination_knowledge',
                'description' => "Approved destination knowledge training for {$request->employee_id}: {$request->destination_name}",
                'model_type' => \App\Models\DestinationKnowledgeTraining::class,
                'model_id' => $destinationTraining->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Destination knowledge training approved and activated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving destination knowledge training: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove all destination training competency gap records
     */
    public function removeDestinationTrainingGaps()
    {
        // Check if user is admin
        if (!Auth::guard('admin')->check() || strtoupper(Auth::guard('admin')->user()->role) !== 'ADMIN') {
            abort(403, 'Access denied. Admin privileges required.');
        }

        try {
            // Find all competency gap records for destination training competencies
            $destinationGaps = CompetencyGap::whereHas('competency', function($query) {
                $query->where('category', 'Destination Knowledge')
                    ->orWhere('category', 'General')
                    ->orWhere('competency_name', 'LIKE', '%BESTLINK%')
                    ->orWhere('competency_name', 'LIKE', '%ITALY%')
                    ->orWhere('competency_name', 'LIKE', '%destination%')
                    ->orWhere('description', 'LIKE', '%Auto-created from destination knowledge training%');
            })->get();

            $deletedCount = $destinationGaps->count();

            // Delete the gap records
            foreach ($destinationGaps as $gap) {
                $gap->delete();
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'cleanup',
                'description' => "Removed {$deletedCount} destination training competency gap records. These should only exist in the Destination Knowledge Training system.",
                'model_type' => CompetencyGap::class,
            ]);

            return redirect()->route('admin.competency_gap.index')
                ->with('success', "Successfully removed {$deletedCount} destination training competency gap records. These should only exist in the Destination Knowledge Training system.");

        } catch (\Exception $e) {
            return redirect()->route('admin.competency_gap.index')
                ->with('error', 'Error during removal: ' . $e->getMessage());
        }
    }

    /**
     * Assign competency gap training to upcoming training with accurate data flow
     */
    public function assignToUpcomingTraining(Request $request)
    {
        try {
            $request->validate([
                'gap_id' => 'required|integer|exists:competency_gaps,id'
            ]);

            // Find the competency gap record
            $competencyGap = CompetencyGap::with(['employee', 'competency'])->findOrFail($request->gap_id);

            // Get current user for assigned_by field
            $assignedBy = 'Admin';
            try {
                if (Auth::check() && Auth::user()) {
                    $assignedBy = Auth::user()->name ?? Auth::user()->username ?? 'Admin';
                }
            } catch (\Exception $authException) {
                Log::warning('Auth issue in assignToUpcomingTraining: ' . $authException->getMessage());
                $assignedBy = 'Admin';
            }

            // Ensure upcoming_trainings table exists
            if (!Schema::hasTable('upcoming_trainings')) {
                Schema::create('upcoming_trainings', function (Blueprint $table) {
                    $table->id('upcoming_id');
                    $table->string('employee_id');
                    $table->string('training_title');
                    $table->date('start_date');
                    $table->date('end_date')->nullable();
                    $table->string('status')->default('Assigned');
                    $table->string('source')->nullable();
                    $table->string('assigned_by')->nullable();
                    $table->timestamp('assigned_date')->nullable();
                    $table->unsignedBigInteger('destination_training_id')->nullable();
                    $table->boolean('needs_response')->default(false);
                    $table->timestamps();
                    
                    $table->index('employee_id');
                    $table->index('destination_training_id');
                });
            }

            // Check if training already exists in upcoming trainings
            $existingUpcoming = \App\Models\UpcomingTraining::where('employee_id', $competencyGap->employee_id)
                ->where('training_title', $competencyGap->competency->competency_name)
                ->first();

            // Find matching course ID for this competency
            $courseRecord = \App\Models\CourseManagement::where('course_title', $competencyGap->competency->competency_name)
                ->orWhere('course_title', 'LIKE', '%' . $competencyGap->competency->competency_name . '%')
                ->first();
            $courseId = $courseRecord ? $courseRecord->course_id : null;

            // Prepare accurate data from competency gap
            $upcomingData = [
                'employee_id' => $competencyGap->employee_id,
                'course_id' => $courseId,
                'training_title' => $competencyGap->competency->competency_name,
                'start_date' => now(), // Start immediately when assigned
                'end_date' => $competencyGap->expired_date ?: now()->addMonths(3), // Use gap expiration or 3 months
                'status' => 'Assigned',
                'source' => 'competency_gap',
                'assigned_by' => Auth::id(), // Store admin user ID
                'assigned_by_name' => $assignedBy, // Store admin name for display
                'assigned_date' => now(), // Accurate assignment timestamp
                'needs_response' => true
            ];

            if ($existingUpcoming) {
                // Update existing record with accurate data
                $existingUpcoming->update($upcomingData);
                $message = "Updated existing training assignment for {$competencyGap->competency->competency_name}";
            } else {
                // Create new record with accurate data
                \App\Models\UpcomingTraining::create($upcomingData);
                $message = "Successfully assigned {$competencyGap->competency->competency_name} to upcoming trainings";
            }

            // Log the activity with accurate information
            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'assign_training',
                'description' => "Assigned {$competencyGap->competency->competency_name} training to {$competencyGap->employee->first_name} {$competencyGap->employee->last_name} from competency gap analysis. Start date: " . $upcomingData['start_date'] . ", Expiration: " . $upcomingData['end_date'] . ", Assigned by: " . $assignedBy,
                'model_type' => CompetencyGap::class,
                'model_id' => $competencyGap->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message . " with accurate start date, expiration, and assigned by information."
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning competency gap to upcoming training: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error assigning training: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get competency profile data for an employee and competency
     */
    public function getCompetencyData(Request $request)
    {
        try {
            // Handle both GET and POST requests
            if ($request->isMethod('get')) {
                $request->validate([
                    'employee_id' => 'required|string|exists:employees,employee_id',
                    'competency_id' => 'required|integer|exists:competency_library,id'
                ]);
            } else {
                $request->validate([
                    'employee_id' => 'required|string|exists:employees,employee_id',
                    'competency_id' => 'required|integer|exists:competency_library,id'
                ]);
            }

            // Find existing competency profile
            $competencyProfile = EmployeeCompetencyProfile::where('employee_id', $request->employee_id)
                ->where('competency_id', $request->competency_id)
                ->first();

            // Debug logging
            Log::info('getCompetencyData debug', [
                'employee_id' => $request->employee_id,
                'competency_id' => $request->competency_id,
                'profile_found' => $competencyProfile ? true : false,
                'profile_level' => $competencyProfile ? $competencyProfile->proficiency_level : null
            ]);

            // Also check if we can calculate current level even without a profile
            $competency = CompetencyLibrary::find($request->competency_id);
            $competencyName = $competency->competency_name;

            $actualProgress = 0;
            $progressSource = 'none';
            $currentLevel = 0;

            // Check if this is a destination knowledge competency
            $isDestinationCompetency = stripos($competencyName, 'Destination Knowledge') !== false;

            if ($isDestinationCompetency) {
                // For destination competencies, check training data even without profile
                $locationName = str_replace(['Destination Knowledge - ', 'Destination Knowledge'], '', $competencyName);
                $locationName = trim($locationName);

                if (!empty($locationName)) {
                    // Find matching destination knowledge training record
                    $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $request->employee_id)
                        ->where('destination_name', 'LIKE', '%' . $locationName . '%')
                        ->first();

                    if ($destinationRecord) {
                        // Use EXACT same progress calculation as destination knowledge training view
                        $destinationNameClean = str_replace([' Training', 'Training'], '', $destinationRecord->destination_name);

                        // Find matching course ID for this destination
                        $matchingCourse = \App\Models\CourseManagement::where('course_title', 'LIKE', '%' . $destinationNameClean . '%')->first();
                        $courseId = $matchingCourse ? $matchingCourse->course_id : null;

                        // Get exam progress
                        $combinedProgress = 0;
                        if ($courseId) {
                            $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($destinationRecord->employee_id, $courseId);
                        }

                        // Fall back to training dashboard progress if no exam data
                        if ($combinedProgress == 0) {
                            $trainingProgress = \App\Models\EmployeeTrainingDashboard::where('employee_id', $destinationRecord->employee_id)
                                ->where('course_id', $courseId)
                                ->value('progress');
                            $combinedProgress = $trainingProgress ?? $destinationRecord->progress ?? 0;
                        }

                        $actualProgress = min(100, round($combinedProgress));
                        $progressSource = 'destination';
                    }
                }
            } else {
                // For non-destination competencies, check training dashboard
                $trainingRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $request->employee_id)->get();

                foreach ($trainingRecords as $record) {
                    $courseTitle = $record->training_title ?? '';

                    // General competency matching
                    $cleanCompetency = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $competencyName);
                    $cleanCourse = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $courseTitle);

                    if (stripos($cleanCourse, $cleanCompetency) !== false || stripos($cleanCompetency, $cleanCourse) !== false) {
                        // Get progress from this training record
                        $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($request->employee_id, $record->course_id);
                        $trainingProgress = $record->progress ?? 0;

                        // Priority: Exam progress > Training record progress
                        $actualProgress = $examProgress > 0 ? $examProgress : $trainingProgress;
                        $progressSource = 'training';
                        break;
                    }
                }
            }

            // If we have training progress, use it regardless of profile existence
            if ($actualProgress > 0) {
                $displayProgress = $actualProgress;
            } else if ($competencyProfile) {
                // ALWAYS use stored proficiency if profile exists - don't check if manually set
                $storedProficiency = ($competencyProfile->proficiency_level / 5) * 100;
                $displayProgress = $storedProficiency;
                $progressSource = 'profile';
            } else {
                $displayProgress = 0;
                $progressSource = 'none';
            }

            // Convert percentage to level (1-5) - Fixed logic for proficiency levels
            if ($displayProgress >= 80) $currentLevel = 5;
            elseif ($displayProgress >= 60) $currentLevel = 4;
            elseif ($displayProgress >= 40) $currentLevel = 3;
            elseif ($displayProgress >= 20) $currentLevel = 2;
            elseif ($displayProgress > 0) $currentLevel = 1;
            else $currentLevel = 0;

            // For competency profiles, use direct proficiency level if available
            if ($competencyProfile && $progressSource === 'profile') {
                $currentLevel = $competencyProfile->proficiency_level;
            }

            if ($currentLevel > 0) {
                $sourceLabel = [
                    'destination' => 'destination training',
                    'training' => 'employee training',
                    'profile' => 'competency profile'
                ][$progressSource] ?? 'training data';

                return response()->json([
                    'success' => true,
                    'current_level' => $currentLevel,
                    'current_percentage' => round($displayProgress),
                    'progress_source' => $progressSource,
                    'has_profile' => $competencyProfile ? true : false,
                    'message' => "Auto-populated level {$currentLevel} from {$sourceLabel} ({$displayProgress}%)"
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'current_level' => 0,
                    'has_profile' => $competencyProfile ? true : false,
                    'message' => $competencyProfile ?
                        'Competency profile found but no training progress detected. Please enter current level manually.' :
                        'No existing competency profile found. Please enter current level manually.'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching competency data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk sync all employees' competency profiles from training data
     */
    public function bulkSyncCompetencyProfiles()
    {
        try {
            $createdProfiles = 0;
            $updatedProfiles = 0;
            $errors = [];

            // Get all employees
            $employees = \App\Models\Employee::all();

            // Get all competencies
            $competencies = CompetencyLibrary::all();

            foreach ($employees as $employee) {
                foreach ($competencies as $competency) {
                    try {
                        $competencyName = $competency->competency_name;
                        $actualProgress = 0;
                        $progressSource = 'none';

                        // Check if this is a destination knowledge competency
                        $isDestinationCompetency = stripos($competencyName, 'Destination Knowledge') !== false;

                        if ($isDestinationCompetency) {
                            // For destination competencies
                            $locationName = str_replace(['Destination Knowledge - ', 'Destination Knowledge'], '', $competencyName);
                            $locationName = trim($locationName);

                            if (!empty($locationName)) {
                                $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employee->employee_id)
                                    ->where('destination_name', 'LIKE', '%' . $locationName . '%')
                                    ->first();

                                if ($destinationRecord) {
                                    $destinationNameClean = str_replace([' Training', 'Training'], '', $destinationRecord->destination_name);
                                    $matchingCourse = \App\Models\CourseManagement::where('course_title', 'LIKE', '%' . $destinationNameClean . '%')->first();
                                    $courseId = $matchingCourse ? $matchingCourse->course_id : null;

                                    $combinedProgress = 0;
                                    if ($courseId) {
                                        $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($destinationRecord->employee_id, $courseId);
                                    }

                                    if ($combinedProgress == 0) {
                                        $trainingProgress = \App\Models\EmployeeTrainingDashboard::where('employee_id', $destinationRecord->employee_id)
                                            ->where('course_id', $courseId)
                                            ->value('progress');
                                        $combinedProgress = $trainingProgress ?? $destinationRecord->progress ?? 0;
                                    }

                                    $actualProgress = min(100, round($combinedProgress));
                                    $progressSource = 'destination';
                                }
                            }
                        } else {
                            // For non-destination competencies
                            $trainingRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employee->employee_id)->get();

                            foreach ($trainingRecords as $record) {
                                $courseTitle = $record->training_title ?? '';
                                $cleanCompetency = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $competencyName);
                                $cleanCourse = str_replace([' Training', 'Training', ' Course', 'Course', ' Program', 'Program'], '', $courseTitle);

                                if (stripos($cleanCourse, $cleanCompetency) !== false || stripos($cleanCompetency, $cleanCourse) !== false) {
                                    $examProgress = \App\Models\ExamAttempt::calculateCombinedProgress($employee->employee_id, $record->course_id);
                                    $trainingProgress = $record->progress ?? 0;
                                    $actualProgress = $examProgress > 0 ? $examProgress : $trainingProgress;
                                    $progressSource = 'training';
                                    break;
                                }
                            }
                        }

                        // Only create/update if we have actual progress
                        if ($actualProgress > 0) {
                            // Convert percentage to level (1-5)
                            $currentLevel = 1;
                            if ($actualProgress >= 90) $currentLevel = 5;
                            elseif ($actualProgress >= 70) $currentLevel = 4;
                            elseif ($actualProgress >= 50) $currentLevel = 3;
                            elseif ($actualProgress >= 30) $currentLevel = 2;
                            elseif ($actualProgress > 0) $currentLevel = 1;

                            // Check if profile already exists
                            $existingProfile = EmployeeCompetencyProfile::where('employee_id', $employee->employee_id)
                                ->where('competency_id', $competency->id)
                                ->first();

                            if ($existingProfile) {
                                // Only update if the new level is higher or if it's from training data
                                if ($currentLevel > $existingProfile->proficiency_level || $progressSource !== 'none') {
                                    $existingProfile->update([
                                        'proficiency_level' => $currentLevel,
                                        'assessment_date' => now(),
                                        'notes' => "Auto-updated from {$progressSource} data ({$actualProgress}%)"
                                    ]);
                                    $updatedProfiles++;
                                }
                            } else {
                                // Create new profile
                                EmployeeCompetencyProfile::create([
                                    'employee_id' => $employee->employee_id,
                                    'competency_id' => $competency->id,
                                    'proficiency_level' => $currentLevel,
                                    'assessment_date' => now(),
                                    'notes' => "Auto-created from {$progressSource} data ({$actualProgress}%)"
                                ]);
                                $createdProfiles++;
                            }
                        }

                    } catch (\Exception $e) {
                        $errors[] = "Error processing {$employee->first_name} {$employee->last_name} - {$competency->competency_name}: " . $e->getMessage();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Bulk sync completed successfully!",
                'created_profiles' => $createdProfiles,
                'updated_profiles' => $updatedProfiles,
                'total_processed' => $createdProfiles + $updatedProfiles,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during bulk sync: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix missing categories for existing competencies
     */
    private function fixMissingCategories()
    {
        // Only fix competencies that have NULL or empty categories
        $competencies = CompetencyLibrary::whereNull('category')
            ->orWhere('category', '')
            ->get();

        foreach ($competencies as $comp) {
            $name = strtolower($comp->competency_name);
            $description = strtolower($comp->description ?? '');
            $category = 'General'; // Default

            // Comprehensive destination knowledge detection
            $destinationKeywords = [
                'destination', 'location', 'place', 'city', 'terminal', 'station', 'route', 'area',
                'baguio', 'baesa', 'quezon', 'cubao', 'manila', 'cebu', 'davao', 'boracay',
                'pasay', 'makati', 'taguig', 'ortigas', 'alabang', 'fairview', 'novaliches',
                'geography', 'travel', 'transport', 'bus', 'terminal', 'depot'
            ];

            $isDestination = false;
            foreach ($destinationKeywords as $keyword) {
                if (strpos($name, $keyword) !== false || strpos($description, $keyword) !== false) {
                    $isDestination = true;
                    break;
                }
            }

            if ($isDestination) {
                $category = 'Destination Knowledge';
            } elseif (strpos($name, 'customer') !== false || strpos($name, 'service') !== false) {
                $category = 'Customer Service';
            } elseif (strpos($name, 'communication') !== false) {
                $category = 'Communication';
            } elseif (strpos($name, 'leadership') !== false || strpos($name, 'management') !== false) {
                $category = 'Leadership';
            } elseif (strpos($name, 'technical') !== false || strpos($name, 'system') !== false) {
                $category = 'Technical';
            }

            // Update the category for competencies that don't have one
            $comp->category = $category;
            $comp->save();
        }
    }

    /**
     * Extend expiration date for a competency gap assignment
     */
    public function extendExpiration(Request $request, $id)
    {
        try {
            $gap = CompetencyGap::findOrFail($id);

            $request->validate([
                'extension_days' => 'required|integer|min:1|max:60'
            ]);

            $extensionDays = (int) $request->extension_days;

            // Determine a proper base date for extension:
            // - If current expiry is in the future, add from that date
            // - If absent or in the past, add from now
            $baseDate = now();
            if ($gap->expired_date) {
                try {
                    $currentExpiry = \Carbon\Carbon::parse($gap->expired_date);
                    $baseDate = $currentExpiry->isFuture() ? $currentExpiry : now();
                } catch (\Exception $e) {
                    // Fallback to now if parsing fails
                    $baseDate = now();
                }
            }

            $newExpiry = $baseDate->copy()->addDays($extensionDays);
            $gap->expired_date = $newExpiry;
            $gap->is_active = true; // Reactivate if it was expired
            $gap->save();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'extend',
                'description' => "Extended competency gap expiration by {$extensionDays} days for employee ID: {$gap->employee_id} (New expiry: " . $gap->expired_date->format('Y-m-d g:i A') . ")",
                'model_type' => CompetencyGap::class,
                'model_id' => $gap->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Expiration extended by {$extensionDays} days",
                'new_expiry' => $gap->expired_date->format('Y-m-d g:i A')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error extending expiration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if employee can access a specific competency gap
     */
    public function checkAccess(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|string|exists:employees,employee_id',
                'competency_id' => 'required|integer|exists:competency_library,id'
            ]);

            $gap = CompetencyGap::where('employee_id', $request->employee_id)
                ->where('competency_id', $request->competency_id)
                ->first();

            if (!$gap) {
                return response()->json([
                    'success' => false,
                    'message' => 'No competency gap assignment found.'
                ], 404);
            }

            $isAccessible = $gap->isAccessible();
            $isExpired = $gap->isExpired();

            return response()->json([
                'success' => true,
                'accessible' => $isAccessible,
                'expired' => $isExpired,
                'expiry_date' => $gap->expired_date ? $gap->expired_date->format('Y-m-d g:i A') : null,
                'message' => $isExpired ? 'This competency gap assignment has expired and is no longer accessible.' : 'Access granted.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking access: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix existing competency gap records that don't have expiration dates
     */
    public function fixExpiredDates()
    {
        try {
            $updated = 0;
            $limitDate = now()->addDays(30);

            // 1. Fix NULL expired_date (Set to 1 month from now)
            $gapsWithoutExpiredDate = CompetencyGap::whereNull('expired_date')->get();
            foreach ($gapsWithoutExpiredDate as $gap) {
                $gap->expired_date = now()->addMonth();
                $gap->is_active = true;
                $gap->save();
                $updated++;
            }

            // 2. Fix expired_date > 180 days (Cap at 30 days from now)
            // Targeting the 180-day bug while allowing reasonable extensions
            $bugThresholdDate = now()->addDays(180);
            $longExpirationGaps = CompetencyGap::where('expired_date', '>', $bugThresholdDate)->get();
            foreach ($longExpirationGaps as $gap) {
                $gap->expired_date = now()->addMonth();
                $gap->save();
                $updated++;
            }

            // 3. Fix "Feb 25" records (approx 30 days) to "Feb 26" (1 month)
            // This corrects the records we just set to 30 days if the user prefers 1 month (Jan 26 -> Feb 26)
            $thirtyDayDate = now()->addDays(30)->startOfDay();
            $thirtyDayGaps = CompetencyGap::whereDate('expired_date', $thirtyDayDate)->get();
            foreach ($thirtyDayGaps as $gap) {
                $gap->expired_date = now()->addMonth();
                $gap->save();
                $updated++;
            }

            if ($updated > 0) {
                // Log the fix
                ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'bulk_fix',
                    'module' => 'Competency Gap Management',
                    'description' => "Fixed expiration dates for {$updated} competency gap records (NULL, >180 days, or adjusted to 1 month).",
                    'model_type' => null,
                    'model_id' => null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updated} competency gap records with expiration dates.",
                'updated_count' => $updated
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fixing expiration dates: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Sync expired dates between competency gap and training records
     */
    private function syncExpiredDateWithTraining($gap)
    {
        try {
            // Find matching training record
            $competency = $gap->competency;
            if (!$competency) return;

            $courseTitle = str_replace([' Training', ' Course', ' Program'], '', $competency->competency_name);

            $trainingRecord = \App\Models\EmployeeTrainingDashboard::where('employee_id', $gap->employee_id)
                ->whereHas('course', function($q) use ($courseTitle, $competency) {
                    $q->where('course_title', 'LIKE', '%' . $courseTitle . '%')
                      ->orWhere('course_title', 'LIKE', '%' . $competency->competency_name . '%');
                })
                ->first();

            if ($trainingRecord) {
                // Sync expired dates - Competency Gap is the source of truth
                if ($gap->expired_date) {
                    // Always propagate gap's expired date to training record
                    $trainingRecord->expired_date = $gap->expired_date;
                    $trainingRecord->save();

                    ActivityLog::create([
                        'user_id' => Auth::id() ?? 1,
                        'action' => 'sync',
                        'module' => 'Expired Date Sync',
                        'description' => "Updated training record expired date from competency gap for {$gap->employee->first_name} {$gap->employee->last_name} - {$competency->competency_name}. Training now uses gap date: " . $gap->expired_date->format('Y-m-d g:i:s A'),
                    ]);
                } elseif (!$gap->expired_date && $trainingRecord->expired_date) {
                    // Only if gap has no date, use training date for gap (fallback)
                    $gap->expired_date = $trainingRecord->expired_date;
                    $gap->save();

                    ActivityLog::create([
                        'user_id' => Auth::id() ?? 1,
                        'action' => 'sync',
                        'module' => 'Expired Date Sync',
                        'description' => "Set competency gap expired date from training record for {$gap->employee->first_name} {$gap->employee->last_name} - {$competency->competency_name}. Gap now uses: " . $gap->expired_date->format('Y-m-d g:i:s A'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error syncing expired dates: ' . $e->getMessage());
        }
    }

    /**
     * Ensure the competency_gaps table exists with proper structure
     */
    private function ensureTableExists()
    {
        try {
            if (!Schema::hasTable('competency_gaps')) {
                Log::info('Creating competency_gaps table...');

                Schema::create('competency_gaps', function (Blueprint $table) {
                    $table->id();
                    $table->string('employee_id', 20);
                    $table->unsignedBigInteger('competency_id');
                    $table->integer('required_level');
                    $table->integer('current_level');
                    $table->integer('gap');
                    $table->text('gap_description')->nullable();
                    $table->timestamp('expired_date')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->boolean('assigned_to_training')->default(false);
                    $table->timestamps();

                    // Add indexes for better performance
                    $table->index(['employee_id', 'competency_id']);
                    $table->index('is_active');
                    $table->index('expired_date');
                });

                // Add foreign key constraints separately with error handling
                try {
                    if (Schema::hasTable('employees')) {
                        Schema::table('competency_gaps', function (Blueprint $table) {
                            $table->foreign('employee_id')
                                ->references('employee_id')
                                ->on('employees')
                                ->onDelete('cascade');
                        });
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not create employee_id foreign key: ' . $e->getMessage());
                }

                try {
                    if (Schema::hasTable('competency_library')) {
                        Schema::table('competency_gaps', function (Blueprint $table) {
                            $table->foreign('competency_id')
                                ->references('id')
                                ->on('competency_library')
                                ->onDelete('cascade');
                        });
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not create competency_id foreign key: ' . $e->getMessage());
                }

                Log::info('competency_gaps table created successfully');
            } else {
                // Check if required columns exist and add them if missing
                if (!Schema::hasColumn('competency_gaps', 'expired_date')) {
                    Schema::table('competency_gaps', function (Blueprint $table) {
                        $table->timestamp('expired_date')->nullable();
                    });
                    Log::info('Added expired_date column to competency_gaps table');
                }

                if (!Schema::hasColumn('competency_gaps', 'is_active')) {
                    Schema::table('competency_gaps', function (Blueprint $table) {
                        $table->boolean('is_active')->default(true);
                    });
                    Log::info('Added is_active column to competency_gaps table');
                }

                if (!Schema::hasColumn('competency_gaps', 'assigned_to_training')) {
                    Schema::table('competency_gaps', function (Blueprint $table) {
                        $table->boolean('assigned_to_training')->default(false);
                    });
                    Log::info('Added assigned_to_training column to competency_gaps table');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error ensuring competency_gaps table exists: ' . $e->getMessage());
            // Continue execution - table might already exist
        }
    }

    /**
     * Assign competency gap to employee's upcoming training
     */
    public function assignToTraining(Request $request)
    {
        try {
            $request->validate([
                'gap_id' => 'required|integer|exists:competency_gaps,id',
                // Relaxed validation: rely on the gap record to identify the employee
                'employee_id' => 'nullable', 
                'competency' => 'required|string',
                'expired_date' => 'nullable|string'
            ]);

            // Find the competency gap record
            $competencyGap = CompetencyGap::with(['employee', 'competency'])->findOrFail($request->gap_id);

            // Get the employee record directly from the relationship
            $employee = $competencyGap->employee;
            $employeeName = 'Unknown Employee';
            $employeeDatabaseId = trim($competencyGap->employee_id);

            if ($employee) {
                $employeeName = $employee->first_name . ' ' . $employee->last_name;
                $employeeDatabaseId = $employee->employee_id;
            } else {
                // Fallback: Try to find manually using the ID from the gap record
                // 1. Direct match on employee_id (trimming whitespace)
                $employeeId = trim($competencyGap->employee_id);
                $employee = Employee::where('employee_id', $employeeId)->first();
                
                // 2. If numeric, try padded versions (common issue with imported data)
                if (!$employee && is_numeric($employeeId)) {
                    $paddings = [3, 4, 5, 6]; // Try common zero-padding lengths
                    foreach ($paddings as $length) {
                        $paddedId = str_pad($employeeId, $length, '0', STR_PAD_LEFT);
                        $employee = Employee::where('employee_id', $paddedId)->first();
                        if ($employee) break;
                    }
                }

                // 3. Fallback: Check 'id' column if it exists (in case of legacy ID usage)
                if (!$employee && Schema::hasColumn('employees', 'id')) {
                    $employee = Employee::where('id', $employeeId)->first();
                }

                // 4. Fallback: Search by name if provided (Handles corrupted/mismatched IDs)
                if (!$employee && $request->has('employee_name')) {
                    $nameParts = explode(' ', $request->employee_name);
                    // Try exact match on First + Last name if only 2 parts, or search first/last
                    if (count($nameParts) >= 2) {
                        $firstName = $nameParts[0];
                        $lastName = end($nameParts); // Last part as last name
                        
                        $employee = Employee::where('first_name', 'LIKE', "%{$firstName}%")
                                          ->where('last_name', 'LIKE', "%{$lastName}%")
                                          ->first();
                        
                        if ($employee) {
                            Log::info("AssignTraining: Found employee by name '{$request->employee_name}' (ID: {$employee->employee_id}) after ID lookup failed.");
                        }
                    }
                }

                // If still not found, we will proceed with the raw ID and let the DB FK constraint handle validity
                // This handles cases where Eloquent fails but the data is valid in DB
                if ($employee) {
                    $employeeName = $employee->first_name . ' ' . $employee->last_name;
                    $employeeDatabaseId = $employee->employee_id;
                } else {
                     Log::warning("AssignTraining: Employee model not found for Gap ID {$competencyGap->id}, using raw ID: {$employeeId}");
                     // We continue execution instead of returning 404
                }
            }
            
            // The employees table uses string employee_id, but upcoming_trainings expects integer
            // We need to modify the upcoming_trainings table to use string employee_id to match
            
            // Ensure upcoming_trainings table exists with correct structure
            if (!Schema::hasTable('upcoming_trainings')) {
                Schema::create('upcoming_trainings', function (Blueprint $table) {
                    $table->id('upcoming_id');
                    $table->string('employee_id', 20); // Change to string to match employees table
                    $table->string('training_title');
                    $table->timestamp('start_date')->nullable();
                    $table->timestamp('end_date')->nullable();
                    $table->string('status')->default('Assigned');
                    $table->string('source')->nullable();
                    $table->string('assigned_by')->nullable();
                    $table->timestamp('assigned_date')->nullable();
                    $table->unsignedBigInteger('destination_training_id')->nullable();
                    $table->boolean('needs_response')->default(true);
                    $table->timestamps();
                    
                    $table->index('employee_id');
                    // Removed foreign key constraint to allow external employees
                });
            } else {
                // Check if employee_id column needs to be modified and drop foreign key
                try {
                    // First try to drop the foreign key if it exists to allow external employees
                    try {
                        Schema::table('upcoming_trainings', function (Blueprint $table) {
                            $table->dropForeign(['employee_id']);
                        });
                    } catch (\Exception $e) {
                        // Foreign key might not exist or has different name, ignore
                    }

                    // Attempt to modify the column type if it exists as integer
                    Schema::table('upcoming_trainings', function (Blueprint $table) {
                        $table->string('employee_id', 20)->change();
                    });
                } catch (\Exception $e) {
                    // Column might already be string, or other issue - log but continue
                    Log::info('Could not modify employee_id column in upcoming_trainings: ' . $e->getMessage());
                }
            }
            
            // $employeeDatabaseId is already set above


            // Get current user for assigned_by field
            $assignedBy = 'Admin User';
            $assignedById = null;
            try {
                if (Auth::check() && Auth::user()) {
                    $assignedBy = Auth::user()->name ?? Auth::user()->username ?? 'Admin User';
                    $assignedById = Auth::id();
                }
            } catch (\Exception $authException) {
                Log::warning('Auth issue in assignToTraining: ' . $authException->getMessage());
                $assignedBy = 'Admin User';
            }

            // Check if training already exists in upcoming trainings
            $existingUpcoming = \App\Models\UpcomingTraining::where('employee_id', $employeeDatabaseId)
                ->where('training_title', $request->competency)
                ->first();

            // Use the competency gap's exact expiration date
            $expirationDate = $competencyGap->expired_date;
            
            // If expired_date is provided in request, validate it matches the competency gap
            if ($request->expired_date) {
                $requestDate = \Carbon\Carbon::parse($request->expired_date);
                $gapDate = \Carbon\Carbon::parse($competencyGap->expired_date);
                
                // Use the competency gap date to ensure consistency
                $expirationDate = $gapDate;
                
                Log::info("Expiration date comparison - Request: {$requestDate}, Gap: {$gapDate}, Using: {$expirationDate}");
            }

            // Find matching course ID for this competency
            $courseRecord = \App\Models\CourseManagement::where('course_title', $request->competency)
                ->orWhere('course_title', 'LIKE', '%' . $request->competency . '%')
                ->first();
            $courseId = $courseRecord ? $courseRecord->course_id : null;

            // Prepare training data using the database ID
            $trainingData = [
                'employee_id' => $employeeDatabaseId, // Use database ID, not employee_id string
                'course_id' => $courseId,
                'training_title' => $request->competency,
                'start_date' => now(),
                'end_date' => $expirationDate, // Use the exact expiration date from competency gap
                'status' => 'Assigned',
                'source' => 'competency_gap',
                'assigned_by' => $assignedById, // Store admin user ID
                'assigned_by_name' => $assignedBy, // Store admin name for display
                'assigned_date' => now(),
                'needs_response' => true
            ];

            if ($existingUpcoming) {
                // Update existing record
                $existingUpcoming->update($trainingData);
                $message = "Updated existing training assignment for {$request->competency}";
            } else {
                // Create new record
                \App\Models\UpcomingTraining::create($trainingData);
                $message = "Successfully assigned {$request->competency} to upcoming trainings";
            }

            // Mark the competency gap as assigned to training
            $competencyGap->assigned_to_training = true;
            $competencyGap->save();

            // Log the activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'assign_training',
                'description' => "Assigned '{$request->competency}' training to {$employeeName} from competency gap analysis. Assigned by: {$assignedBy}",
                'model_type' => CompetencyGap::class,
                'model_id' => $competencyGap->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning competency gap to training: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error assigning training: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix existing expiration dates to match competency gaps
     */
    public function fixExpirationDates()
    {
        try {
            // Direct fix for Communication Skills
            $updated = \DB::table('upcoming_trainings')
                ->where('training_title', 'Communication Skills')
                ->where('source', 'competency_gap')
                ->update(['end_date' => '2025-09-30 19:15:00']);
            
            Log::info("Direct fix: Updated {$updated} Communication Skills records to Sep 30, 2025");
            
            // Also run the general fix method
            $updatedCount = \App\Models\UpcomingTraining::fixExpirationDates();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully fixed {$updatedCount} expiration dates. Direct fix updated {$updated} Communication Skills records.",
                'updated_count' => $updatedCount,
                'direct_fix' => $updated
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fixing expiration dates: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fixing expiration dates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unassign competency gap from training (admin override)
     */
    public function unassignFromTraining(Request $request, $id)
    {
        try {
            $gap = CompetencyGap::findOrFail($id);
            
            // Mark as not assigned to training
            $gap->assigned_to_training = false;
            $gap->save();

            // Remove related training records from upcoming trainings
            // Find and remove upcoming training records that match this competency gap
            $competencyName = $gap->competency->competency_name ?? '';
            $employeeId = $gap->employee_id;

            if ($competencyName && $employeeId) {
                // Remove from UpcomingTraining table if it exists
                try {
                    $upcomingTrainings = \App\Models\UpcomingTraining::where('employee_id', $employeeId)
                        ->where(function($query) use ($competencyName) {
                            $query->where('training_title', 'LIKE', '%' . $competencyName . '%')
                                  ->orWhere('training_title', 'LIKE', '%' . str_replace(' Training', '', $competencyName) . '%')
                                  ->orWhere('training_title', 'LIKE', '%' . str_replace(' Course', '', $competencyName) . '%')
                                  ->orWhere('training_title', 'LIKE', '%' . str_replace(' Program', '', $competencyName) . '%');
                        })
                        ->where(function($query) {
                            $query->where('source', 'competency_gap')
                                  ->orWhere('source', 'competency_assigned')
                                  ->orWhere('source', 'admin_assigned');
                        })
                        ->get();

                    foreach ($upcomingTrainings as $training) {
                        $training->delete();
                    }

                    $removedCount = $upcomingTrainings->count();
                } catch (\Exception $e) {
                    // If UpcomingTraining model doesn't exist, continue
                    $removedCount = 0;
                }

                // Also remove from EmployeeTrainingDashboard if assigned through competency gap
                try {
                    $dashboardTrainings = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
                        ->where(function($query) use ($competencyName) {
                            $query->where('training_title', 'LIKE', '%' . $competencyName . '%')
                                  ->orWhere('training_title', 'LIKE', '%' . str_replace(' Training', '', $competencyName) . '%')
                                  ->orWhere('training_title', 'LIKE', '%' . str_replace(' Course', '', $competencyName) . '%')
                                  ->orWhere('training_title', 'LIKE', '%' . str_replace(' Program', '', $competencyName) . '%');
                        })
                        ->where('source', 'competency_assigned')
                        ->get();

                    foreach ($dashboardTrainings as $training) {
                        $training->delete();
                    }

                    $removedCount += $dashboardTrainings->count();
                } catch (\Exception $e) {
                    // Continue if model doesn't exist
                }

                // Remove from TrainingRequest table (for _requests.blade.php) - Enhanced matching
                try {
                    // More aggressive matching for training requests
                    $trainingRequests = \App\Models\TrainingRequest::where('employee_id', $employeeId)
                        ->where(function($query) use ($competencyName) {
                            // Clean competency name for better matching
                            $cleanCompetencyName = str_replace([' Training', ' Course', ' Program', ' Skills'], '', $competencyName);
                            
                            $query->where('training_title', 'LIKE', '%' . $competencyName . '%')
                                  ->orWhere('training_title', 'LIKE', '%' . $cleanCompetencyName . '%')
                                  ->orWhere('training_title', $competencyName)
                                  ->orWhere('training_title', $cleanCompetencyName);
                        })
                        ->where(function($query) {
                            $query->where('reason', 'LIKE', '%competency%')
                                  ->orWhere('reason', 'LIKE', '%gap%')
                                  ->orWhere('reason', 'LIKE', '%admin%')
                                  ->orWhere('reason', 'LIKE', '%Automatically enrolled%')
                                  ->orWhere('status', 'Approved'); // Include auto-approved from competency gaps
                        })
                        ->get();

                    foreach ($trainingRequests as $request) {
                        Log::info("Removing training request: {$request->training_title} (ID: {$request->request_id})");
                        $request->delete();
                    }

                    $removedCount += $trainingRequests->count();
                    Log::info("Removed {$trainingRequests->count()} training requests for competency: {$competencyName}");
                } catch (\Exception $e) {
                    Log::warning("Error removing training requests: " . $e->getMessage());
                }

                // Remove from TrainingProgress table (for _progress.blade.php) - Enhanced matching
                try {
                    // More aggressive matching for training progress
                    $cleanCompetencyName = str_replace([' Training', ' Course', ' Program', ' Skills'], '', $competencyName);
                    
                    $trainingProgress = \App\Models\TrainingProgress::where('employee_id', $employeeId)
                        ->where(function($query) use ($competencyName, $cleanCompetencyName) {
                            $query->where('training_title', 'LIKE', '%' . $competencyName . '%')
                                  ->orWhere('training_title', 'LIKE', '%' . $cleanCompetencyName . '%')
                                  ->orWhere('training_title', $competencyName)
                                  ->orWhere('training_title', $cleanCompetencyName);
                        })
                        ->where(function($query) {
                            $query->where('source', 'approved_request')
                                  ->orWhere('source', 'auto_approved_request')
                                  ->orWhere('source', 'competency_assigned')
                                  ->orWhereNull('source'); // Include records without source
                        })
                        ->get();

                    foreach ($trainingProgress as $progress) {
                        Log::info("Removing training progress: {$progress->training_title} (ID: {$progress->progress_id})");
                        $progress->delete();
                    }

                    $removedCount += $trainingProgress->count();
                    Log::info("Removed {$trainingProgress->count()} training progress records for competency: {$competencyName}");
                } catch (\Exception $e) {
                    Log::warning("Error removing training progress: " . $e->getMessage());
                }
            }

            // Log the activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => 'Competency Management',
                'action' => 'unassign_training',
                'description' => "Unassigned competency gap from training for employee ID: {$gap->employee_id} - {$gap->competency->competency_name}. Removed {$removedCount} related training records.",
                'model_type' => CompetencyGap::class,
                'model_id' => $gap->id,
            ]);

            // Clear any cached data that might affect counts
            $this->clearTrainingCaches($employeeId);

            return response()->json([
                'success' => true,
                'message' => "Competency gap unassigned from training successfully. Removed {$removedCount} related records from upcoming trainings, training requests, and training progress. You can now edit or delete this record.",
                'removed_count' => $removedCount,
                'refresh_required' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unassigning from training: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create competency_gaps table directly using raw SQL
     */
    public function createCompetencyGapsTable()
    {
        try {
            // Check if table already exists
            if (Schema::hasTable('competency_gaps')) {
                return response()->json([
                    'success' => true,
                    'message' => 'competency_gaps table already exists'
                ]);
            }

            // Create table using raw SQL for better control
            $sql = "
                CREATE TABLE competency_gaps (
                    id bigint unsigned NOT NULL AUTO_INCREMENT,
                    employee_id varchar(20) NOT NULL,
                    competency_id bigint unsigned NOT NULL,
                    required_level int NOT NULL,
                    current_level int NOT NULL,
                    gap int NOT NULL,
                    gap_description text,
                    expired_date timestamp NULL DEFAULT NULL,
                    is_active tinyint(1) NOT NULL DEFAULT '1',
                    assigned_to_training tinyint(1) NOT NULL DEFAULT '0',
                    created_at timestamp NULL DEFAULT NULL,
                    updated_at timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (id),
                    KEY competency_gaps_employee_id_competency_id_index (employee_id, competency_id),
                    KEY competency_gaps_is_active_index (is_active),
                    KEY competency_gaps_expired_date_index (expired_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";

            DB::statement($sql);

            // Add foreign key constraints if tables exist
            try {
                if (Schema::hasTable('employees')) {
                    DB::statement("
                        ALTER TABLE competency_gaps 
                        ADD CONSTRAINT competency_gaps_employee_id_foreign 
                        FOREIGN KEY (employee_id) REFERENCES employees (employee_id) ON DELETE CASCADE
                    ");
                }
            } catch (\Exception $e) {
                Log::warning('Could not create employee_id foreign key: ' . $e->getMessage());
            }

            try {
                if (Schema::hasTable('competency_library')) {
                    DB::statement("
                        ALTER TABLE competency_gaps 
                        ADD CONSTRAINT competency_gaps_competency_id_foreign 
                        FOREIGN KEY (competency_id) REFERENCES competency_library (id) ON DELETE CASCADE
                    ");
                }
            } catch (\Exception $e) {
                Log::warning('Could not create competency_id foreign key: ' . $e->getMessage());
            }

            Log::info('competency_gaps table created successfully via direct SQL');

            return response()->json([
                'success' => true,
                'message' => 'competency_gaps table created successfully with all columns including assigned_to_training'
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating competency_gaps table: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating table: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Automatically detect and create competency gaps for all employees
     * This checks all employee competency profiles and creates gap records
     * when current level is below required level
     */
    public function autoDetectGaps()
    {
        try {
            $createdGaps = 0;
            $updatedGaps = 0;
            $skippedGaps = 0;
            $errors = [];

            // 1. Fetch employees from API with consistent ID mapping
            $employeeMap = [];
            try {
                $response = \Illuminate\Support\Facades\Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
                $apiData = $response->successful() ? $response->json() : [];
                $apiEmployees = (isset($apiData['data']) && is_array($apiData['data'])) ? $apiData['data'] : $apiData;

                if (is_array($apiEmployees)) {
                    foreach ($apiEmployees as $emp) {
                        $empId = is_array($emp) 
                            ? ($emp['external_employee_id'] ?? $emp['employee_id'] ?? $emp['id'] ?? null)
                            : ($emp->external_employee_id ?? $emp->employee_id ?? $emp->id ?? null);
                        
                        if ($empId) {
                            $employeeMap[$empId] = $emp;
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('API fetch failed in autoDetectGaps: ' . $e->getMessage());
            }

            // 2. Clear old auto-detected gaps to ensure a fresh, accurate sync
            // This fixes the "30 gaps" issue where old records were sticking around
            CompetencyGap::where('gap_description', 'LIKE', 'Auto-detected:%')->delete();

            // 3. Process ONLY existing profiles for a 1:1 sync
            $profiles = EmployeeCompetencyProfile::all();

            if ($profiles->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No profiles found to synchronize. Please assess employees first.'
                ]);
            }

            foreach ($profiles as $profile) {
                try {
                    $employeeId = $profile->employee_id;
                    $competencyId = $profile->competency_id;

                    $competency = CompetencyLibrary::find($competencyId);
                    if (!$competency) continue;

                    // Exclude restricted categories
                    if (stripos($competency->competency_name, 'BESTLINK') !== false || 
                        stripos($competency->competency_name, 'ITALY') !== false) {
                        continue;
                    }

                    // 4. Resolve name accurately
                    $firstName = 'Employee';
                    $lastName = $employeeId;
                    
                    if (isset($employeeMap[$employeeId])) {
                        $apiEmp = $employeeMap[$employeeId];
                        $firstName = is_array($apiEmp) ? ($apiEmp['first_name'] ?? 'Employee') : ($apiEmp->first_name ?? 'Employee');
                        $lastName = is_array($apiEmp) ? ($apiEmp['last_name'] ?? $employeeId) : ($apiEmp->last_name ?? $employeeId);
                    } else {
                        $localEmp = Employee::where('employee_id', $employeeId)->first();
                        if ($localEmp) {
                            $firstName = $localEmp->first_name;
                            $lastName = $localEmp->last_name;
                        }
                    }

                    $requiredRate = $competency->rate ?? 5;
                    $currentLevel = $profile->proficiency_level ?? 0;

                    // Only create gap if level is actually below requirement
                    if ($currentLevel < $requiredRate) {
                        $gap = $requiredRate - $currentLevel;
                        $gapDescription = "Auto-detected: {$firstName} {$lastName} needs improvement in {$competency->competency_name}";

                        CompetencyGap::create([
                            'employee_id' => $employeeId,
                            'competency_id' => $competencyId,
                            'required_level' => $requiredRate,
                            'current_level' => $currentLevel,
                            'gap' => $gap,
                            'gap_description' => $gapDescription,
                            'is_active' => 1,
                            'expired_date' => now()->addMonths(6)->format('Y-m-d')
                        ]);
                        $createdGaps++;
                    } else {
                        $skippedGaps++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error processing Profile ID {$profile->id}: " . $e->getMessage();
                }
            }

            // Log activity with accurate summary
            try {
                ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'Auto-Detect Competency Gaps',
                    'module' => 'Competency Gap Analysis',
                    'description' => "Accurate sync: Created {$createdGaps} gaps from " . $profiles->count() . " profiles.",
                    'ip_address' => request()->ip()
                ]);
            } catch (\Exception $e) {
                Log::warning('Could not log activity: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Gaps accurately synchronized with profiles.',
                'created' => $createdGaps,
                'skipped' => $skippedGaps,
                'total_profiles' => $profiles->count(),
                'errors' => count($errors) > 0 ? $errors : null
            ]);

        } catch (\Exception $e) {
            Log::error('Error in auto-detect gaps: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error detecting gaps: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear training-related caches and force data refresh
     */
    private function clearTrainingCaches($employeeId)
    {
        try {
            // Clear any Laravel cache that might be storing training counts
            Cache::forget("employee_training_counts_{$employeeId}");
            Cache::forget("upcoming_trainings_{$employeeId}");
            Cache::forget("training_requests_{$employeeId}");
            Cache::forget("training_progress_{$employeeId}");
            
            // Force refresh of any cached dashboard data
            Cache::forget("employee_dashboard_{$employeeId}");
            
            Log::info("Cleared training caches for employee: {$employeeId}");
        } catch (\Exception $e) {
            Log::warning("Error clearing training caches: " . $e->getMessage());
        }
    }

}