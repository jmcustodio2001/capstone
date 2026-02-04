<?php

namespace App\Http\Controllers;

use App\Models\TrainingRecordCertificateTracking;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TrainingRecordCertificateTrackingController extends Controller
{
    public function index()
    {
        try {
            // Check if table exists first
            $tableExists = DB::select("SHOW TABLES LIKE 'training_record_certificate_tracking'");

            if (count($tableExists) == 0) {
                // Table doesn't exist, create it
                Log::warning('training_record_certificate_tracking table does not exist, creating it...');
                $this->ensureTableStructure();
            }

            // FIXED: Show ALL certificate tracking records instead of filtering by completed trainings
            // This ensures that all auto-generated certificates appear in the table
            $certificates = TrainingRecordCertificateTracking::with(['employee', 'course'])
                ->orderBy('created_at', 'desc')
                ->paginate(5);

            // Fetch employees from API for fallback (Fix for Unknown Employee)
            $apiEmployees = $this->getEmployeesFromAPI();

            // Attach API employees to certificates where local employee is missing
            foreach ($certificates as $certificate) {
                if (!$certificate->employee && $certificate->employee_id) {
                    $apiEmp = $apiEmployees->firstWhere('employee_id', $certificate->employee_id);
                    if ($apiEmp) {
                         $certificate->setRelation('employee', $apiEmp);
                    }
                }
            }

            // Get all completed trainings from various sources for debugging purposes
            $completedTrainingEmployees = $this->getCompletedTrainingEmployees();

            // Safely get employees and courses with error handling
            $employees = collect();
            $courses = collect();

            try {
                $employees = \App\Models\Employee::all();
            } catch (\Exception $e) {
                Log::error('Error fetching employees: ' . $e->getMessage());
            }

            try {
                $courses = \App\Models\CourseManagement::all();
            } catch (\Exception $e) {
                Log::error('Error fetching courses: ' . $e->getMessage());
            }

            // Debug logging with safe counts
            Log::info('Certificate tracking - Completed training employees: ' . $completedTrainingEmployees->count());
            Log::info('Certificate tracking - All certificates displayed: ' . $certificates->count());
            Log::info('Certificate tracking - Employees available: ' . $employees->count());
            Log::info('Certificate tracking - Courses available: ' . $courses->count());

            // Debug: Log details of completed trainings for each employee
            foreach ($completedTrainingEmployees as $employeeId => $completedTrainings) {
                Log::info("Employee {$employeeId} completed trainings:", $completedTrainings->map(function($training) {
                    return [
                        'title' => $training->training_title,
                        'course_id' => $training->course_id,
                        'source' => $training->source
                    ];
                })->toArray());
            }

            // Debug: Log all certificate records being displayed
            Log::info('All certificate records: ' . $certificates->pluck('id')->implode(', '));

            // Check for orphaned records (certificates without valid employee/course relationships)
            $orphanedCount = 0;
            foreach ($certificates as $certificate) {
                if (!$certificate->employee || !$certificate->course) {
                    $orphanedCount++;
                    Log::warning('Orphaned certificate record found', [
                        'certificate_id' => $certificate->id,
                        'employee_id' => $certificate->employee_id,
                        'course_id' => $certificate->course_id,
                        'has_employee' => $certificate->employee ? 'yes' : 'no',
                        'has_course' => $certificate->course ? 'yes' : 'no'
                    ]);
                }
            }

            if ($orphanedCount > 0) {
                Log::warning("Found {$orphanedCount} certificate records with missing employee or course relationships");
            }

            return view('learning_management.training_record_certificate_tracking', compact('certificates', 'employees', 'courses'));

        } catch (\Exception $e) {
            Log::error('Error in TrainingRecordCertificateTracking index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // If there's an error, try to ensure table structure and return empty data
            $this->ensureTableStructure();

            $certificates = collect();
            $employees = collect();
            $courses = collect();

            return view('learning_management.training_record_certificate_tracking', compact('certificates', 'employees', 'courses'))
                ->with('error', 'Database error detected. Table structure has been verified. Please refresh the page. Error: ' . $e->getMessage());
        }
    }

    /**
     * Show individual certificate record for editing
     */
    public function show($id)
    {
        try {
            $certificate = TrainingRecordCertificateTracking::with(['employee', 'course'])->findOrFail($id);

            // Safe employee data extraction
            $employee = $certificate->employee;
            $employeeName = 'Unknown Employee';
            if ($employee) {
                $firstName = $employee->first_name ?? '';
                $lastName = $employee->last_name ?? '';
                $employeeName = trim($firstName . ' ' . $lastName);
                if (empty($employeeName)) {
                    $employeeName = 'Employee ID: ' . $certificate->employee_id;
                }
            }

            // Safe course data extraction
            $course = $certificate->course;
            $courseTitle = 'Unknown Course';
            if ($course && $course->course_title) {
                $courseTitle = $course->course_title;
            } elseif ($certificate->course_id) {
                $courseTitle = 'Course ID: ' . $certificate->course_id;
            }

            // Format dates for display
            $formattedDate = 'Unknown Date';
            $issuedDate = 'Unknown Date';
            if ($certificate->training_date) {
                try {
                    $trainingDate = \Carbon\Carbon::parse($certificate->training_date);
                    $formattedDate = $trainingDate->format('F j, Y');
                    $issuedDate = $trainingDate->format('M j, Y');
                } catch (\Exception $e) {
                    Log::warning('Failed to parse training date', ['training_date' => $certificate->training_date]);
                }
            }

            // Format the data for JSON response with safe null handling
            $data = [
                'id' => $certificate->id,
                'employee_id' => $certificate->employee_id ?? '',
                'course_id' => $certificate->course_id ?? '',
                'employee_name' => $employeeName,
                'course_name' => $courseTitle, // Also provide as course_name for consistency
                'course_title' => $courseTitle,
                'certificate_number' => $certificate->certificate_number ?? '',
                'training_date' => $certificate->training_date ?? '',
                'certificate_expiry' => $certificate->certificate_expiry ?? '',
                'status' => $certificate->status ?? 'Pending',
                'remarks' => $certificate->remarks ?? '',
                'certificate_url' => $certificate->certificate_url ?? '',
                'formatted_date' => $formattedDate,
                'issued_date' => $issuedDate,
                'has_employee_record' => $employee ? true : false,
                'has_course_record' => $course ? true : false
            ];

            Log::info('Certificate data retrieved successfully', [
                'certificate_id' => $id,
                'employee_found' => $employee ? 'yes' : 'no',
                'course_found' => $course ? 'yes' : 'no'
            ]);

            return response()->json($data);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Certificate not found: ' . $id);
            return response()->json(['error' => 'Certificate record not found. It may have been deleted.'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching certificate for editing', [
                'certificate_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Server error occurred while fetching certificate data. Please try again.'], 500);
        }
    }

    public function store(Request $request)
    {
        // EMERGENCY FIX: Ensure table structure is correct before proceeding
        if (!$this->ensureTableStructure()) {
            return redirect()->route('training_record_certificate_tracking.index')
                ->with('error', 'Failed to ensure proper table structure. Please contact administrator.');
        }

        $request->validate([
            'employee_id' => 'required|string|exists:employees,employee_id',
            'course_id' => 'required|integer|exists:course_management,course_id',
            'training_date' => 'required|date',
            'certificate_number' => 'required|string',
            'certificate_expiry' => 'required|date',
            'status' => 'required|string',
            'remarks' => 'required|string',
            'certificate_file' => 'nullable|file|mimes:pdf,png,jpg,jpeg,doc,docx',
        ]);

        $data = $request->except('certificate_file');
        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $path = $file->store('certificates', 'public');
            $data['certificate_url'] = '/storage/' . $path;
        }
        $record = TrainingRecordCertificateTracking::create($data);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'module' => 'Training Record Certificate Tracking',
            'description' => 'Added training record certificate (ID: ' . $record->id . ')',
        ]);

        // Integration: Update simulation with training completion
        $this->updateSimulationWithTrainingCompletion($record->employee_id);

        return redirect()->route('training_record_certificate_tracking.index')->with('success', 'Record added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|string|exists:employees,employee_id',
            'course_id' => 'required|integer|exists:course_management,course_id',
            'training_date' => 'required|date',
            'certificate_number' => 'required|string',
            'certificate_expiry' => 'required|date',
            'status' => 'required|string',
            'remarks' => 'required|string',
            'certificate_file' => 'nullable|file|mimes:pdf,png,jpg,jpeg,doc,docx',
        ]);
        $record = TrainingRecordCertificateTracking::findOrFail($id);
        $data = $request->except('certificate_file');
        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $path = $file->store('certificates', 'public');
            $data['certificate_url'] = '/storage/' . $path;
        }
        $record->update($data);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'module' => 'Training Record Certificate Tracking',
            'description' => 'Updated training record certificate (ID: ' . $record->id . ')',
        ]);

        // Integration: Update simulation with training completion
        $this->updateSimulationWithTrainingCompletion($record->employee_id);

        return redirect()->route('training_record_certificate_tracking.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Integration: Update simulation tool with latest training completion for the employee
     */
    private function updateSimulationWithTrainingCompletion($employeeId)
    {
        // Get the latest certificate/training completion for the employee
        $latestCert = \App\Models\TrainingRecordCertificateTracking::where('employee_id', $employeeId)
            ->orderByDesc('training_date')->first();
        if ($latestCert) {
            // Update or create a simulation entry for this employee
            $sim = \App\Models\SuccessionSimulation::updateOrCreate(
                ['employee_id' => $employeeId],
                [
                    'simulation_result' => $latestCert->status === 'Completed' ? 'Certified' : $latestCert->status,
                    'created_at' => $latestCert->training_date,
                ]
            );
        }
    }
    public function destroy($id)
    {
        $record = TrainingRecordCertificateTracking::findOrFail($id);
        $record->delete();
        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'module' => 'Training Record Certificate Tracking',
            'description' => 'Deleted training record certificate (ID: ' . $record->id . ')',
        ]);
        return redirect()->route('training_record_certificate_tracking.index')->with('success', 'Record deleted successfully.');
    }

    public function autoGenerateMissingCertificates()
    {
        try {
            // Ensure table exists first
            if (!$this->ensureTableStructure()) {
                return redirect()->back()->with('error', 'Failed to verify table structure.');
            }

            $employees = \App\Models\Employee::all();
            $totalCreated = 0;
            $totalEmployees = $employees->count();

            Log::info("Starting comprehensive certificate sync for {$totalEmployees} employees");

            foreach ($employees as $employee) {
                try {
                    $createdForEmployee = $this->syncEmployeeCompletedTrainings($employee->employee_id);
                    $totalCreated += $createdForEmployee;
                } catch (\Exception $e) {
                    Log::error("Failed to sync certificates for employee {$employee->employee_id}: " . $e->getMessage());
                }
            }

            return redirect()->back()->with('success', "Generation Complete! Certificate records have been synchronized for all employees. Created {$totalCreated} new records.");

        } catch (\Exception $e) {
            Log::error('Auto-generate certificates error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating certificates: ' . $e->getMessage());
        }
    }

    /**
     * Sync completed trainings for a single employee using logic from MyTrainingController
     * Returns number of new certificates created
     */
    private function syncEmployeeCompletedTrainings($employeeId)
    {
        $createdCount = 0;

        // 1. REPLICATE LOGIC FROM MyTrainingController to get COMPLETE list

        // Get manually added completed trainings
        $manualCompleted = \App\Models\CompletedTraining::where('employee_id', $employeeId)->get();

        // Get system-completed trainings
        $systemCompleted = \App\Models\EmployeeTrainingDashboard::with(['course'])
            ->where('employee_id', $employeeId)
            ->where(function($query) {
                $query->where('status', 'Completed')
                      ->orWhere('progress', '>=', 100);
            })->get();

        // Get completed destination knowledge trainings
        $destinationTrainings = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeId)
              ->where(function($q) {
                  $q->where('progress', '>=', 100)->orWhere('status', 'completed');
              })
              ->get();

        // Get completed customer service trainings
        $customerServiceTrainings = collect();
        try {
             $customerServiceTrainings = \App\Models\CustomerServiceSalesSkillsTraining::where('employee_id', $employeeId)
                  ->whereNotNull('date_completed')
                  ->where('date_completed', '!=', '1970-01-01')
                  ->get();
        } catch (\Exception $e) {}

        // Get competency course assignments
        $competencyCompleted = \App\Models\CompetencyCourseAssignment::with(['course'])
            ->where('employee_id', $employeeId)
            ->where(function($q) {
                $q->where('status', 'Completed')->orWhere('progress', '>=', 100);
            })->get();

        // 2. PROCESS EACH SOURCE

        // Manual
        foreach ($manualCompleted as $training) {
             if($this->processSingleTraining($employeeId, $training->training_title, $training->completion_date, $training->certificate_path, 'Manual')) {
                 $createdCount++;
             }
        }

        // System (Dashboard)
        foreach ($systemCompleted as $training) {
             $title = $training->course ? $training->course->course_title : 'Unknown System Training';
             if($this->processSingleTraining($employeeId, $title, $training->updated_at, null, 'Dashboard')) {
                 $createdCount++;
             }
        }

        // Destination
        foreach ($destinationTrainings as $training) {
             if($this->processSingleTraining($employeeId, $training->destination_name, $training->updated_at, null, 'Destination Knowledge')) {
                 $createdCount++;
             }
        }

        // Customer Service
        foreach ($customerServiceTrainings as $training) {
             $completionDate = $training->date_completed ?? $training->updated_at;
             $title = $training->skill_topic ?? 'Customer Service & Sales Skills Training';
             if($this->processSingleTraining($employeeId, $title, $completionDate, null, 'Customer Service')) {
                 $createdCount++;
             }
        }

        // Competency
        foreach ($competencyCompleted as $comp) {
            $title = $comp->course ? $comp->course->course_title : 'Competency Training';
            if($this->processSingleTraining($employeeId, $title, $comp->updated_at, null, 'Competency Assignment')) {
                $createdCount++;
            }
        }

        // Upcoming Training (Completed)
        $upcoming = \App\Models\UpcomingTraining::where('employee_id', $employeeId)
            ->where('status', 'Completed')
            ->get();

        foreach ($upcoming as $train) {
            $date = $train->end_date ?? $train->updated_at;
            if($this->processSingleTraining($employeeId, $train->training_title, $date, null, 'Upcoming Training')) {
                $createdCount++;
            }
        }

        return $createdCount;
    }

    /**
     * Process a single training record and create certificate if missing
     */
    private function processSingleTraining($employeeId, $title, $date, $existingCertPath = null, $source = 'Unknown')
    {
        if (empty($title)) return false;

        $title = trim($title);
        $date = $date instanceof \Carbon\Carbon ? $date : (\Carbon\Carbon::tryParse($date) ?? now());

        // 1. Find or Create Course
        $course = \App\Models\CourseManagement::where('course_title', $title)->first();

        // Fuzzy match for Customer Service
        if (!$course && stripos($title, 'Customer Service') !== false) {
             $course = \App\Models\CourseManagement::where('course_title', 'LIKE', '%Customer Service%')->first();
        }

        if (!$course) {
            // Create course if missing
            try {
                $course = \App\Models\CourseManagement::create([
                    'course_title' => $title,
                    'course_description' => "Auto-created from {$source} training",
                    'status' => 'Active',
                    'duration_hours' => 8,
                    'delivery_mode' => 'Mixed',
                    'start_date' => $date
                ]);
            } catch (\Exception $e) {
                // Fallback to Uncategorized if creation fails
               $course = \App\Models\CourseManagement::firstOrCreate(['course_title' => 'Uncategorized Training'], ['status'=>'Active']);
            }
        }

        if (!$course) return false;

        // 2. Check if certificate exists (by Course ID or Title Match)
        $exists = TrainingRecordCertificateTracking::where('employee_id', $employeeId)
            ->where(function($q) use ($course, $title) {
                $q->where('course_id', $course->course_id)
                  ->orWhereHas('course', function($sq) use ($title) {
                      $sq->where('course_title', 'LIKE', $title);
                  });
            })->exists();

        if ($exists) return false;

        // 3. Create Certificate Record
        try {
            // Generate standard certificate number
            $certNum = 'CERT-' . strtoupper(substr($employeeId, 0, 3)) . '-' . $date->format('Ymd') . '-' . rand(1000, 9999);

            // Format existing path if present
            $certUrl = null;
            if ($existingCertPath) {
                $certUrl = $existingCertPath;
                if (!str_starts_with($certUrl, '/storage/') && !str_starts_with($certUrl, '/')) {
                     $certUrl = '/storage/' . $certUrl;
                }
                $certUrl = preg_replace('#/+#', '/', $certUrl);
            }

            TrainingRecordCertificateTracking::create([
                'employee_id' => $employeeId,
                'course_id' => $course->course_id,
                'training_date' => $date,
                'certificate_number' => $certNum,
                'certificate_expiry' => $date->copy()->addYears(2),
                'issue_date' => $date,
                'status' => 'Verified',
                'remarks' => "Auto-synced from {$source} Records",
                'certificate_url' => $certUrl
            ]);

            // Sync competency
            $this->syncWithCompetencyProfile($employeeId, $title, $date);

            Log::info("Synced certificate for {$employeeId}: {$title}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to create certificate for {$employeeId} - {$title}: " . $e->getMessage());
            return false;
        }
    }











    private function cleanupDuplicateCertificates()
    {
        try {
            Log::info("Starting duplicate certificate cleanup...");

            // Find duplicate certificates (same employee_id and course_id)
            $duplicates = TrainingRecordCertificateTracking::select('employee_id', 'course_id', DB::raw('COUNT(*) as count'))
                ->groupBy('employee_id', 'course_id')
                ->having('count', '>', 1)
                ->get();

            $deletedCount = 0;

            foreach ($duplicates as $duplicate) {
                // Get all certificates for this employee-course combination
                $certificates = TrainingRecordCertificateTracking::where('employee_id', $duplicate->employee_id)
                    ->where('course_id', $duplicate->course_id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                // Keep the latest one, delete the rest
                $latestCertificate = $certificates->first();
                $certificatesToDelete = $certificates->skip(1);

                foreach ($certificatesToDelete as $certToDelete) {
                    Log::info("Deleting duplicate certificate: ID {$certToDelete->id} for employee {$certToDelete->employee_id}, course {$certToDelete->course_id}");
                    $certToDelete->delete();
                    $deletedCount++;
                }
            }

            Log::info("Duplicate cleanup completed. Deleted {$deletedCount} duplicate certificate records.");

            // Log activity for cleanup
            if ($deletedCount > 0) {
                ActivityLog::create([
                    'user_id' => Auth::id() ?: 1,
                    'action' => 'cleanup',
                    'module' => 'Training Record Certificate Tracking',
                    'description' => "Cleaned up {$deletedCount} duplicate certificate records during auto-generation process.",
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Error during duplicate certificate cleanup: " . $e->getMessage());
        }
    }

    private function syncAllExistingCertificatesWithCompetency()
    {
        try {
            $certificates = TrainingRecordCertificateTracking::with(['employee', 'course'])->get();

            foreach ($certificates as $cert) {
                if ($cert->course) {
                    $this->syncWithCompetencyProfile($cert->employee_id, $cert->course->course_title, $cert->training_date);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error syncing existing certificates: " . $e->getMessage());
        }
    }

    /**
     * Standalone method to clean up duplicate certificates (can be called via route)
     */
    public function cleanupDuplicates()
    {
        try {
            $this->cleanupDuplicateCertificates();

            return redirect()->route('training_record_certificate_tracking.index')
                ->with('success', 'Duplicate certificate cleanup completed successfully.');

        } catch (\Exception $e) {
            return redirect()->route('training_record_certificate_tracking.index')
                ->with('error', 'Error during cleanup: ' . $e->getMessage());
        }
    }

    private function syncWithCompetencyProfile($employeeId, $trainingTitle, $completionDate)
    {
        try {
            // Debug logging
            Log::info("Syncing competency profile for Employee: {$employeeId}, Training: {$trainingTitle}");

            // Extract competency name from training title
            $competencyName = $this->extractCompetencyName($trainingTitle);
            Log::info("Extracted competency name: {$competencyName}");

            // Skip auto-creation of competency library entries for destination knowledge training
            // These should only exist in destination knowledge training system, not competency library
            if ($this->isDestinationKnowledgeTraining($trainingTitle)) {
                Log::info("Skipping competency library creation for destination knowledge training: {$trainingTitle}");
                return true; // Return success but don't create competency library entry
            }

            // Check if competency already exists in the library (ONLY use existing competencies)
            $competency = \App\Models\CompetencyLibrary::where('competency_name', $competencyName)->first();

            // If competency doesn't exist in the library, skip the sync process
            // This prevents auto-generation of competencies when trainings are completed
            if (!$competency) {
                Log::info("Competency '{$competencyName}' not found in competency library. Skipping auto-generation to prevent duplicate entries.");
                return true; // Return success but don't create anything
            }

            Log::info("Using existing competency: {$competencyName} (ID: {$competency->competency_id})");

            // Create or update employee competency profile with level 5 (Expert) for 100% completed training
            $profile = \App\Models\EmployeeCompetencyProfile::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'competency_id' => $competency->competency_id
                ],
                [
                    'proficiency_level' => 5, // Expert level (100%) for completed training - BOOSTS READINESS RATING
                    'assessment_date' => $completionDate,
                    'notes' => "Auto-generated from 100% completed training - Certificate eligible",
                    'updated_at' => now()
                ]
            );
            Log::info("Employee competency profile created/updated with ID: {$profile->id} - Level 5 (Expert) for readiness rating");

            // Update or create competency gap (no gap since training completed at 100%)
            $gap = \App\Models\CompetencyGap::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'competency_id' => $competency->competency_id
                ],
                [
                    'required_level' => 100, // 100% required
                    'current_level' => 100,  // 100% achieved through training completion
                    'gap' => 0, // No gap since training completed at 100%
                    'updated_at' => now()
                ]
            );
            Log::info("Competency gap created/updated with ID: {$gap->id} - 100% current level");

            // Log activity for readiness rating impact
            ActivityLog::create([
                'user_id' => Auth::id() ?: 1,
                'action' => 'sync',
                'module' => 'Employee Competency Profile',
                'description' => "Auto-synced competency profile for {$employeeId} - {$competencyName} (Level 5 Expert) from 100% training completion. This will boost readiness rating and potential successor identification scores.",
            ]);

            return true;

        } catch (\Exception $e) {
            // Log error but don't break the certificate generation process
            Log::error("Error syncing competency profile for {$employeeId}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    private function extractCompetencyName($trainingTitle)
    {
        // Remove common training suffixes
        $competencyName = preg_replace('/\s*(Training|Course|Program|Certification)$/i', '', $trainingTitle);

        // Special handling for destination knowledge
        if (stripos($trainingTitle, 'BAESA') !== false || stripos($trainingTitle, 'QUEZON') !== false) {
            return "Destination Knowledge - " . ucwords(strtolower($competencyName));
        }

        return ucwords(strtolower($competencyName));
    }

    private function categorizeCompetency($competencyName)
    {
        $name = strtolower($competencyName);

        if (strpos($name, 'destination') !== false || strpos($name, 'baesa') !== false || strpos($name, 'quezon') !== false) {
            return 'Destination Knowledge';
        } elseif (strpos($name, 'service') !== false || strpos($name, 'customer') !== false) {
            return 'Customer Service';
        } elseif (strpos($name, 'leadership') !== false || strpos($name, 'management') !== false) {
            return 'Leadership';
        } elseif (strpos($name, 'communication') !== false || strpos($name, 'speaking') !== false) {
            return 'Communication';
        } elseif (strpos($name, 'technical') !== false || strpos($name, 'system') !== false) {
            return 'Technical';
        }

        return 'General';
    }

    private function isDestinationKnowledgeTraining($trainingTitle)
    {
        $title = strtolower($trainingTitle);
        $destinationKeywords = [
            'destination', 'location', 'place', 'city', 'terminal', 'station',
            'baesa', 'quezon', 'cubao', 'baguio', 'boracay', 'cebu', 'davao',
            'manila', 'geography', 'route', 'travel', 'area knowledge',
            'bestlink', 'college', 'philippines'
        ];

        foreach ($destinationKeywords as $keyword) {
            if (strpos($title, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    public function forceSyncAllCertificates()
    {
        try {
            $certificates = TrainingRecordCertificateTracking::with(['employee', 'course'])->get();
            $syncedCount = 0;

            foreach ($certificates as $cert) {
                if ($cert->course && $cert->employee) {
                    $result = $this->syncWithCompetencyProfile($cert->employee_id, $cert->course->course_title, $cert->training_date);
                    if ($result) {
                        $syncedCount++;
                    }
                }
            }

            return redirect()->route('training_record_certificate_tracking.index')
                ->with('success', "Successfully synced {$syncedCount} certificates with Employee Competency Profiles.");

        } catch (\Exception $e) {
            return redirect()->route('training_record_certificate_tracking.index')
                ->with('error', 'Error syncing certificates: ' . $e->getMessage());
        }
    }

    /**
     * Create the missing training_record_certificate_tracking table
     * Access via: /admin/training-certificate-tracking/create-table
     */
    public function createMissingTable()
    {
        try {
            // Create the table using Laravel Schema Builder
            if (!\Illuminate\Support\Facades\Schema::hasTable('training_record_certificate_tracking')) {
                \Illuminate\Support\Facades\Schema::create('training_record_certificate_tracking', function ($table) {
                    $table->id();
                    $table->string('employee_id', 50)->index();
                    $table->unsignedBigInteger('course_id')->index();
                    $table->date('training_date');
                    $table->string('certificate_number')->nullable();
                    $table->date('certificate_expiry')->nullable();
                    $table->string('certificate_url')->nullable();
                    $table->string('status')->default('Active');
                    $table->text('remarks')->nullable();
                    $table->timestamps();
                });

                // Log activity
                ActivityLog::create([
                    'user_id' => Auth::id() ?: 1,
                    'action' => 'create',
                    'module' => 'Training Record Certificate Tracking',
                    'description' => 'Created missing training_record_certificate_tracking table with proper structure',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully created training_record_certificate_tracking table',
                    'action' => 'created'
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Table training_record_certificate_tracking already exists',
                    'action' => 'none'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error creating training_record_certificate_tracking table: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error creating table: ' . $e->getMessage(),
                'action' => 'error'
            ], 500);
        }
    }

    /**
     * Force create table immediately - for emergency use
     */
    public function forceCreateTable()
    {
        try {
            // Execute the table creation directly using raw SQL
            $createTableSQL = "
                DROP TABLE IF EXISTS `training_record_certificate_tracking`;

                CREATE TABLE `training_record_certificate_tracking` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `employee_id` varchar(50) NOT NULL,
                    `course_id` bigint(20) unsigned NOT NULL,
                    `training_date` date NOT NULL,
                    `certificate_number` varchar(255) DEFAULT NULL,
                    `certificate_expiry` date DEFAULT NULL,
                    `certificate_url` varchar(255) DEFAULT NULL,
                    `status` varchar(255) NOT NULL DEFAULT 'Active',
                    `remarks` text DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_employee_id` (`employee_id`),
                    KEY `idx_course_id` (`course_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";

            // Execute each statement separately
            DB::statement("DROP TABLE IF EXISTS `training_record_certificate_tracking`");

            DB::statement("
                CREATE TABLE `training_record_certificate_tracking` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `employee_id` varchar(50) NOT NULL,
                    `course_id` bigint(20) unsigned NOT NULL,
                    `training_date` date NOT NULL,
                    `certificate_number` varchar(255) DEFAULT NULL,
                    `certificate_expiry` date DEFAULT NULL,
                    `certificate_url` varchar(255) DEFAULT NULL,
                    `status` varchar(255) NOT NULL DEFAULT 'Active',
                    `remarks` text DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_employee_id` (`employee_id`),
                    KEY `idx_course_id` (`course_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Verify table was created
            $tableExists = DB::select("SHOW TABLES LIKE 'training_record_certificate_tracking'");

            if (count($tableExists) > 0) {
                // Log activity
                ActivityLog::create([
                    'user_id' => Auth::id() ?: 1,
                    'action' => 'create',
                    'module' => 'Training Record Certificate Tracking',
                    'description' => 'Force created training_record_certificate_tracking table using raw SQL',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully force created training_record_certificate_tracking table using raw SQL',
                    'action' => 'force_created'
                ]);
            } else {
                throw new \Exception('Table creation failed - table does not exist after creation attempt');
            }

        } catch (\Exception $e) {
            Log::error('Error force creating training_record_certificate_tracking table: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error force creating table: ' . $e->getMessage(),
                'action' => 'error'
            ], 500);
        }
    }

    /**
     * Execute table creation immediately - call this method directly
     */
    public function executeTableCreation()
    {
        try {
            // Execute the table creation directly
            DB::statement("DROP TABLE IF EXISTS `training_record_certificate_tracking`");

            DB::statement("
                CREATE TABLE `training_record_certificate_tracking` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `employee_id` varchar(50) NOT NULL,
                    `course_id` bigint(20) unsigned NOT NULL,
                    `training_date` date NOT NULL,
                    `certificate_number` varchar(255) DEFAULT NULL,
                    `certificate_expiry` date DEFAULT NULL,
                    `certificate_url` varchar(255) DEFAULT NULL,
                    `status` varchar(255) NOT NULL DEFAULT 'Active',
                    `remarks` text DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_employee_id` (`employee_id`),
                    KEY `idx_course_id` (`course_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Verify table exists
            $result = DB::select("SHOW TABLES LIKE 'training_record_certificate_tracking'");

            if (count($result) > 0) {
                Log::info('training_record_certificate_tracking table created successfully');
                return true;
            } else {
                Log::error('Failed to create training_record_certificate_tracking table');
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Error creating training_record_certificate_tracking table: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Emergency fix - executes immediately when autoGenerateMissingCertificates is called
     */
    private function ensureTableStructure()
    {
        try {
            // Check if table exists
            $tableExists = DB::select("SHOW TABLES LIKE 'training_record_certificate_tracking'");

            if (count($tableExists) == 0) {
                // Table doesn't exist, create it
                $this->executeTableCreation();
                return true;
            }

            // Check if all required columns exist
            if (!TrainingRecordCertificateTracking::hasAllRequiredColumns()) {
                Log::info('Missing columns detected, attempting to fix table structure');

                // Get missing columns for logging
                $missingColumns = TrainingRecordCertificateTracking::getMissingColumns();
                Log::info('Missing columns: ' . implode(', ', $missingColumns));

                // Use the model's comprehensive fix method
                $result = TrainingRecordCertificateTracking::fixMissingColumns();

                if ($result['success']) {
                    Log::info('Successfully fixed table structure: ' . $result['message']);
                    return true;
                } else {
                    Log::error('Failed to fix table structure: ' . $result['message']);
                    return false;
                }
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Error ensuring table structure: ' . $e->getMessage());

            // If all else fails, try to use the model's fix method
            try {
                Log::info('Attempting to fix table structure using model method...');
                $result = TrainingRecordCertificateTracking::fixMissingColumns();

                if ($result['success']) {
                    Log::info('Successfully fixed table structure using model method');
                    return true;
                } else {
                    Log::error('Failed to fix table structure using model method: ' . $result['message']);
                    return false;
                }
            } catch (\Exception $e2) {
                Log::error('Failed to fix table structure: ' . $e2->getMessage());
                return false;
            }
        }
    }

    /**
     * Fix missing training_date column in existing table
     * Access via: /admin/training-certificate-tracking/fix-training-date-column
     */
    public function fixTrainingDateColumn()
    {
        try {
            // Check if table exists
            $tableExists = DB::select("SHOW TABLES LIKE 'training_record_certificate_tracking'");

            if (count($tableExists) == 0) {
                // Table doesn't exist, create it completely
                return $this->executeTableCreation();
            }

            // Check if training_date column exists
            $columns = DB::select("SHOW COLUMNS FROM training_record_certificate_tracking LIKE 'training_date'");

            if (count($columns) == 0) {
                // Column doesn't exist, add it
                DB::statement("ALTER TABLE `training_record_certificate_tracking` ADD COLUMN `training_date` date NOT NULL AFTER `course_id`");

                // Update existing records with a default date if they don't have one
                DB::statement("UPDATE `training_record_certificate_tracking` SET `training_date` = COALESCE(`created_at`, NOW()) WHERE `training_date` IS NULL OR `training_date` = '0000-00-00'");

                Log::info('Added missing training_date column to training_record_certificate_tracking table');

                // Log activity
                ActivityLog::create([
                    'user_id' => Auth::id() ?: 1,
                    'action' => 'alter',
                    'module' => 'Training Record Certificate Tracking',
                    'description' => 'Added missing training_date column to training_record_certificate_tracking table',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully added missing training_date column to training_record_certificate_tracking table',
                    'action' => 'column_added'
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'training_date column already exists in training_record_certificate_tracking table',
                    'action' => 'none'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error fixing training_date column: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fixing training_date column: ' . $e->getMessage(),
                'action' => 'error'
            ], 500);
        }
    }

    /**
     * Comprehensive table structure fix - recreates table with all required columns
     * Access via: /admin/training-certificate-tracking/fix-table-structure
     */
    public function fixTableStructure()
    {
        try {
            // Drop and recreate table with complete structure
            DB::statement("DROP TABLE IF EXISTS `training_record_certificate_tracking`");

            DB::statement("
                CREATE TABLE `training_record_certificate_tracking` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `employee_id` varchar(50) NOT NULL,
                    `course_id` bigint(20) unsigned NOT NULL,
                    `training_date` date NOT NULL,
                    `certificate_number` varchar(255) DEFAULT NULL,
                    `certificate_expiry` date DEFAULT NULL,
                    `certificate_url` varchar(255) DEFAULT NULL,
                    `status` varchar(255) NOT NULL DEFAULT 'Active',
                    `remarks` text DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_employee_id` (`employee_id`),
                    KEY `idx_course_id` (`course_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Verify table was created with correct structure
            $columns = DB::select("DESCRIBE training_record_certificate_tracking");
            $hasTrainingDate = false;

            foreach ($columns as $column) {
                if ($column->Field === 'training_date') {
                    $hasTrainingDate = true;
                    break;
                }
            }

            if ($hasTrainingDate) {
                // Log activity
                ActivityLog::create([
                    'user_id' => Auth::id() ?: 1,
                    'action' => 'recreate',
                    'module' => 'Training Record Certificate Tracking',
                    'description' => 'Recreated training_record_certificate_tracking table with complete structure including training_date column',
                ]);

                Log::info('Successfully recreated training_record_certificate_tracking table with complete structure');

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully recreated training_record_certificate_tracking table with complete structure including training_date column',
                    'action' => 'table_recreated',
                    'columns' => $columns
                ]);
            } else {
                throw new \Exception('Table recreated but training_date column is still missing');
            }

        } catch (\Exception $e) {
            Log::error('Error fixing table structure: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fixing table structure: ' . $e->getMessage(),
                'action' => 'error'
            ], 500);
        }
    }

    /**
     * Quick fix for missing training_date column using model method
     * Access via: /admin/training-certificate-tracking/quick-fix-column
     */
    public function quickFixTrainingDateColumn()
    {
        try {
            $result = TrainingRecordCertificateTracking::fixMissingTrainingDateColumn();

            // Log the result for debugging
            Log::info('Quick fix training_date column result: ', $result);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 500);
            }

        } catch (\Exception $e) {
            Log::error('Error in quickFixTrainingDateColumn: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error calling model fix method: ' . $e->getMessage(),
                'action' => 'error'
            ], 500);
        }
    }

    /**
     * Execute fix immediately and redirect back with message
     * Access via: /admin/training-certificate-tracking/execute-fix-now
     */
    public function executeFixNow()
    {
        try {
            $result = TrainingRecordCertificateTracking::fixMissingTrainingDateColumn();

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error executing fix: ' . $e->getMessage());
        }
    }

    /**
     * Simple fix for missing training_date column - direct access
     * Access via: /admin/fix-training-date-column-now
     */
    public function fixColumnNow()
    {
        try {
            // Check if column exists first
            if (\Illuminate\Support\Facades\Schema::hasColumn('training_record_certificate_tracking', 'training_date')) {
                return response()->json([
                    'success' => true,
                    'message' => 'training_date column already exists in the table.',
                    'action' => 'no_action_needed'
                ]);
            }

            $result = TrainingRecordCertificateTracking::fixMissingTrainingDateColumn();

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fixing training_date column: ' . $e->getMessage(),
                'action' => 'error'
            ], 500);
        }
    }

    /**
     * Get all employees who have completed trainings from various sources
     * This method replicates the logic from MyTrainingController to identify completed trainings
     */
    private function getCompletedTrainingEmployees()
    {
        $completedEmployees = collect();

        try {
            // Get all employees
            $allEmployees = \App\Models\Employee::all();

            foreach ($allEmployees as $employee) {
                $employeeId = $employee->employee_id;
                $employeeCompletedTrainings = collect();

                // 1. Get manually added completed trainings
                $manualCompleted = \App\Models\CompletedTraining::where('employee_id', $employeeId)->get();
                foreach ($manualCompleted as $completed) {
                    $employeeCompletedTrainings->push((object)[
                        'training_title' => $completed->training_title,
                        'course_id' => $completed->course_id,
                        'completion_date' => $completed->completion_date,
                        'source' => 'manual_completed'
                    ]);
                }

                // 2. Get system-completed trainings from EmployeeTrainingDashboard (100% progress)
                $systemCompleted = \App\Models\EmployeeTrainingDashboard::with('course')
                    ->where('employee_id', $employeeId)
                    ->where(function($query) {
                        $query->where('status', 'Completed')
                              ->orWhere('progress', '>=', 100);
                    })
                    ->get();

                foreach ($systemCompleted as $completed) {
                    $employeeCompletedTrainings->push((object)[
                        'training_title' => $completed->course->course_title ?? 'Unknown Course',
                        'course_id' => $completed->course_id,
                        'completion_date' => $completed->updated_at,
                        'source' => 'system_completed'
                    ]);
                }

                // 3. Get completed competency-based course assignments
                $competencyCompleted = \App\Models\CompetencyCourseAssignment::with('course')
                    ->where('employee_id', $employeeId)
                    ->where(function($query) {
                        $query->where('status', 'Completed')
                              ->orWhere('progress', '>=', 100);
                    })
                    ->get();

                foreach ($competencyCompleted as $completed) {
                    $employeeCompletedTrainings->push((object)[
                        'training_title' => $completed->course->course_title ?? 'Unknown Course',
                        'course_id' => $completed->course_id,
                        'completion_date' => $completed->updated_at,
                        'source' => 'competency_completed'
                    ]);
                }

                // 4. Get completed destination knowledge training
                $destinationCompleted = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeId)
                    ->where('status', 'completed')
                    ->get();

                foreach ($destinationCompleted as $completed) {
                    $employeeCompletedTrainings->push((object)[
                        'training_title' => $completed->destination_name,
                        'course_id' => null, // Destination trainings may not have course_id
                        'completion_date' => $completed->date_completed ?: $completed->updated_at,
                        'source' => 'destination_completed'
                    ]);
                }

                // 5. Get completed Customer Service Sales Skills Training
                try {
                    $customerServiceCompleted = \App\Models\CustomerServiceSalesSkillsTraining::where('employee_id', $employeeId)
                        ->whereNotNull('date_completed')
                        ->where('date_completed', '!=', '1970-01-01')
                        ->get();

                    foreach ($customerServiceCompleted as $completed) {
                        $employeeCompletedTrainings->push((object)[
                            'training_title' => $completed->skill_topic,
                            'course_id' => $completed->training_id,
                            'completion_date' => $completed->date_completed,
                            'source' => 'customer_service_completed'
                        ]);
                    }
                } catch (\Exception $e) {
                    // Table may not exist, skip
                    Log::info("Customer Service table not found for employee {$employeeId}: " . $e->getMessage());
                }

                // 6. Get completed training requests (approved with 100% progress)
                $completedRequests = \App\Models\TrainingRequest::where('employee_id', $employeeId)
                    ->where('status', 'Approved')
                    ->get()
                    ->filter(function($request) use ($employeeId) {
                        // Check if there's a corresponding training dashboard record with 100% progress
                        $dashboardRecord = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
                            ->where('course_id', $request->course_id)
                            ->first();

                        if ($dashboardRecord) {
                            try {
                                $combinedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($employeeId, $request->course_id);
                                $actualProgress = $combinedProgress > 0 ? $combinedProgress : ($dashboardRecord->progress ?? 0);
                                return $actualProgress >= 100;
                            } catch (\Exception $e) {
                                // If ExamAttempt calculation fails, fall back to dashboard progress
                                return ($dashboardRecord->progress ?? 0) >= 100;
                            }
                        }
                        return false;
                    });

                foreach ($completedRequests as $completed) {
                    $employeeCompletedTrainings->push((object)[
                        'training_title' => $completed->training_title,
                        'course_id' => $completed->course_id,
                        'completion_date' => now(),
                        'source' => 'request_completed'
                    ]);
                }

                // Only add employee to the list if they have completed trainings
                if ($employeeCompletedTrainings->isNotEmpty()) {
                    $completedEmployees->put($employeeId, $employeeCompletedTrainings);
                }
            }

            Log::info('Certificate tracking - Found employees with completed trainings: ' . $completedEmployees->count());

            return $completedEmployees;

        } catch (\Exception $e) {
            Log::error('Error getting completed training employees: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get employees from API with error handling
     */
    private function getEmployeesFromAPI()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
            $apiEmployees = $response->successful() ? $response->json() : [];

            if (isset($apiEmployees['data']) && is_array($apiEmployees['data'])) {
                $apiEmployees = $apiEmployees['data'];
            }

            if (is_array($apiEmployees) && !empty($apiEmployees)) {
                return collect($apiEmployees)->map(function($emp) {
                    // Normalize profile picture URL
                    $profilePic = $emp['profile_picture'] ?? null;
                    if ($profilePic && !Str::startsWith($profilePic, 'http')) {
                         $profilePic = 'http://hr4.jetlougetravels-ph.com/storage/' . ltrim($profilePic, '/');
                    }

                    // Create a pseudo-model object that behaves like Employee model
                    $empObj = new \App\Models\Employee();
                    $empObj->forceFill([
                        'employee_id' => $emp['employee_id'] ?? $emp['id'] ?? $emp['external_employee_id'] ?? 'N/A',
                        'first_name' => $emp['first_name'] ?? 'Unknown',
                        'last_name' => $emp['last_name'] ?? 'Employee',
                        'profile_picture' => $profilePic,
                        // Add other fields if necessary
                    ]);

                    // We need to make sure the attributes are accessible as properties
                    return $empObj;
                });
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch employees from API in TrainingRecordCertificateTracking: ' . $e->getMessage());
        }
        return collect();
    }

    /**
     * Fix certificate_expiry field and all missing columns in the table
     */
    public function fixCertificateExpiryField()
    {
        try {
            Log::info('Attempting to fix training_record_certificate_tracking table structure');

            // Use the model's comprehensive fix method
            $result = TrainingRecordCertificateTracking::fixMissingColumns();

            if ($result['success']) {
                Log::info('Successfully fixed table structure', $result);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'changes' => $result['changes'] ?? []
                ]);
            } else {
                Log::error('Failed to fix table structure', $result);

                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error fixing table structure: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fix table structure: ' . $e->getMessage()
            ], 500);
        }
    }
}
