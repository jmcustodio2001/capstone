<?php

namespace App\Http\Controllers;

use App\Models\TrainingRecordCertificateTracking;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                ->get();
            
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
            // EMERGENCY FIX: Ensure table structure is correct before proceeding
            if (!$this->ensureTableStructure()) {
                return redirect()->route('training_record_certificate_tracking.index')
                    ->with('error', 'Failed to ensure proper table structure. Please contact administrator.');
            }

            $createdCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            $certificateController = new \App\Http\Controllers\CertificateGenerationController();

            // 1. Get completed trainings from the main completed_trainings table
            $completedTrainingsFromTable = \App\Models\CompletedTraining::with(['employee', 'course'])
                ->get();

            // 2. Get completed trainings from Employee Training Dashboard (100% progress)
            $completedTrainings = \App\Models\EmployeeTrainingDashboard::with(['employee', 'course'])
                ->where('progress', '>=', 100)
                ->get();

            // 3. Get completed destination knowledge trainings
            $destinationTrainings = \App\Models\DestinationKnowledgeTraining::with('employee')
                ->where('progress', '>=', 100)
                ->get();

            // 4. Get completed customer service trainings (if table exists)
            $customerServiceTrainings = collect();
            try {
                $customerServiceTrainings = \App\Models\CustomerServiceSalesSkillsTraining::with('employee')
                    ->where('progress', '>=', 100)
                    ->get();
            } catch (\Exception $e) {
                Log::info("Customer Service table not found, skipping: " . $e->getMessage());
            }

            // 5. Get completed training requests (approved status indicates completion)
            $completedTrainingRequests = \App\Models\TrainingRequest::with(['employee', 'course'])
                ->where('status', 'approved')
                ->get();

            // 4. Get all employees to ensure we don't miss anyone
            $allEmployees = \App\Models\Employee::all();

            Log::info("=== CERTIFICATE TRACKING - ALL EMPLOYEES ANALYSIS ===");
            Log::info("Total Employees: " . $allEmployees->count());
            Log::info("Completed Trainings Table: " . $completedTrainingsFromTable->count());
            Log::info("Employee Training Dashboard (100%): " . $completedTrainings->count());
            Log::info("Destination Trainings (100%): " . $destinationTrainings->count());
            Log::info("Customer Service Trainings (100%): " . $customerServiceTrainings->count());
            Log::info("Training Requests (approved): " . $completedTrainingRequests->count());
            Log::info("Existing Certificates: " . \App\Models\TrainingRecordCertificateTracking::count());

            // === PROCESS 1: Main Completed Trainings Table ===
            Log::info("Processing main completed trainings table...");
            Log::info("Found " . $completedTrainingsFromTable->count() . " completed training records to process");
            
            foreach ($completedTrainingsFromTable as $training) {
                Log::info("Processing completed training: {$training->training_title} for employee {$training->employee_id}");
                // Always try to get or create course by title if missing
                $course = $training->course;
                if (!$course && !empty($training->training_title)) {
                    // Enhanced course matching - try multiple strategies
                    $normalizedTitle = trim($training->training_title);
                    
                    // First try exact match
                    $course = \App\Models\CourseManagement::where('course_title', $normalizedTitle)->first();
                    
                    // Then try partial match
                    if (!$course) {
                        $course = \App\Models\CourseManagement::where('course_title', 'LIKE', '%' . $normalizedTitle . '%')->first();
                    }
                    
                    // Then try reverse partial match
                    if (!$course) {
                        $course = \App\Models\CourseManagement::whereRaw('? LIKE CONCAT("%", course_title, "%")', [$normalizedTitle])->first();
                    }
                    
                    // Create if still not found
                    if (!$course) {
                        $course = \App\Models\CourseManagement::create([
                            'course_title' => $normalizedTitle,
                            'course_description' => 'Auto-created from completed training: ' . $normalizedTitle,
                            'start_date' => $training->completion_date ?? now(),
                            'status' => 'Active',
                            'duration_hours' => 8,
                            'delivery_mode' => 'Mixed'
                        ]);
                        Log::info("Created new course for completed training: {$normalizedTitle}");
                    }
                    
                    // Update the training record with course_id
                    $training->course_id = $course->course_id;
                    $training->save();
                }
                
                // Fallback for missing employee: use employee_id as string
                $employeeId = $training->employee_id;
                if (empty($employeeId)) {
                    $errorCount++;
                    Log::warning("Skipping completed training - missing employee_id for training: {$training->completed_id}");
                    continue;
                }
                
                // Enhanced certificate tracking check - try multiple matching strategies
                $existingCert = TrainingRecordCertificateTracking::where('employee_id', $employeeId)
                    ->where(function($query) use ($training, $course) {
                        if ($training->course_id) {
                            $query->where('course_id', $training->course_id);
                        } elseif ($course) {
                            $query->where('course_id', $course->course_id);
                        } else {
                            // Match by training title if no course_id
                            $query->whereHas('course', function($subQ) use ($training) {
                                $normalizedTitle = trim(str_replace(['Training', 'Course', 'Program'], '', $training->training_title));
                                $subQ->where('course_title', 'LIKE', '%' . $normalizedTitle . '%');
                            });
                        }
                    })
                    ->first();
                    
                if ($existingCert) {
                    // Update existing certificate if it doesn't have a certificate file
                    if (!$existingCert->certificate_url) {
                        $certificateResult = $certificateController->generateCertificateOnCompletion(
                            $employeeId,
                            $course ? $course->course_id : $training->course_id,
                            $training->completion_date
                        );
                        if ($certificateResult) {
                            Log::info("Generated certificate file for existing tracking record: {$existingCert->id}");
                        }
                    }
                    $skippedCount++;
                    continue;
                }
                
                // Generate certificate using the CertificateGenerationController
                $certificateResult = $certificateController->generateCertificateOnCompletion(
                    $employeeId,
                    $course ? $course->course_id : $training->course_id,
                    $training->completion_date
                );
                
                if ($certificateResult) {
                    $this->syncWithCompetencyProfile($employeeId, $course ? $course->course_title : $training->training_title, $training->completion_date);
                    $createdCount++;
                    Log::info("SUCCESS: Created certificate for {$employeeId} - {$training->training_title}");
                } else {
                    $errorCount++;
                    Log::error("FAILED: Certificate generation for {$employeeId} - {$training->training_title}");
                }
            }

            // === PROCESS 2: Training Requests (approved = completed) ===
            Log::info("Processing approved training requests...");
            foreach ($completedTrainingRequests as $request) {
                if (!$request->employee) {
                    $errorCount++;
                    Log::warning("Skipping training request - missing employee data: Employee ID {$request->employee_id}");
                    continue;
                }

                // Find or create course based on training title
                $course = null;
                if ($request->course_id) {
                    $course = $request->course;
                } else {
                    // Find course by training title
                    $course = \App\Models\CourseManagement::where('course_title', 'LIKE', '%' . $request->training_title . '%')->first();

                    if (!$course) {
                        // Create course if it doesn't exist
                        $course = \App\Models\CourseManagement::create([
                            'course_title' => $request->training_title,
                            'course_description' => 'Auto-created from training request: ' . $request->training_title,
                            'duration_hours' => 8,
                            'delivery_mode' => 'Mixed',
                            'status' => 'Active'
                        ]);
                    }
                }

                if (!$course) {
                    $errorCount++;
                    Log::warning("Skipping training request - could not find or create course: {$request->training_title}");
                    continue;
                }

                // Check if certificate tracking already exists
                $existingCert = TrainingRecordCertificateTracking::where('employee_id', $request->employee_id)
                    ->where('course_id', $course->course_id)
                    ->first();

                if ($existingCert) {
                    $skippedCount++;
                    continue;
                }

                // Create certificate tracking record directly (since training requests don't use CertificateGenerationController)
                $certificateNumber = 'REQ-' . strtoupper(substr($request->employee_id, 0, 3)) . '-' . date('Y') . '-' . str_pad($createdCount + 1, 4, '0', STR_PAD_LEFT);

                // Ensure table structure before creating record
                $this->ensureTableStructure();
                
                TrainingRecordCertificateTracking::create([
                    'employee_id' => $request->employee_id,
                    'course_id' => $course->course_id,
                    'training_date' => $request->requested_date,
                    'certificate_number' => $certificateNumber,
                    'certificate_expiry' => date('Y-m-d', strtotime($request->requested_date . ' +2 years')),
                    'issue_date' => $request->requested_date,
                    'status' => 'Pending Examination',
                    'remarks' => 'Training completed - awaiting examination for certification',
                    'certificate_url' => null
                ]);

                $this->syncWithCompetencyProfile($request->employee_id, $course->course_title, $request->requested_date);
                $createdCount++;
                Log::info("SUCCESS: Created certificate for {$request->employee->first_name} {$request->employee->last_name} ({$request->employee_id}) - {$course->course_title}");
            }

            // === PROCESS 3: Employee Training Dashboard completed trainings ===
            Log::info("Processing Employee Training Dashboard completed trainings...");
            foreach ($completedTrainings as $training) {
                if (!$training->employee || !$training->course) {
                    $errorCount++;
                    Log::warning("Skipping training - missing employee or course data: Employee ID {$training->employee_id}, Course ID {$training->course_id}");
                    continue;
                }

                // Check if certificate tracking already exists for this training
                $existingCert = TrainingRecordCertificateTracking::where('employee_id', $training->employee_id)
                    ->where('course_id', $training->course_id)
                    ->first();

                if ($existingCert) {
                    // Update existing certificate if it doesn't have a certificate file
                    if (!$existingCert->certificate_url) {
                        $certificateResult = $certificateController->generateCertificateOnCompletion(
                            $training->employee_id,
                            $training->course_id,
                            $training->updated_at->format('Y-m-d')
                        );

                        if ($certificateResult) {
                            Log::info("Generated certificate file for existing tracking record: {$existingCert->id}");
                        }
                    }
                    $skippedCount++;
                    continue; // Skip if already exists
                }

                // Generate certificate using the CertificateGenerationController
                $certificateResult = $certificateController->generateCertificateOnCompletion(
                    $training->employee_id,
                    $training->course_id,
                    $training->updated_at->format('Y-m-d')
                );

                if ($certificateResult) {
                    // Sync with Employee Competency Profile - CRITICAL for readiness rating
                    $this->syncWithCompetencyProfile($training->employee_id, $training->course->course_title, $training->updated_at->format('Y-m-d'));
                    $createdCount++;

                    Log::info("SUCCESS: Created certificate for {$training->employee->first_name} {$training->employee->last_name} ({$training->employee_id}) - {$training->course->course_title}");
                } else {
                    $errorCount++;
                    Log::error("FAILED: Certificate generation for {$training->employee_id} - {$training->course->course_title}");
                }
            }

            // === PROCESS 2: Customer Service Sales Skills Training ===
            Log::info("Processing Customer Service Sales Skills Training completed trainings...");
            foreach ($customerServiceTrainings as $training) {
                if (!$training->employee) {
                    $errorCount++;
                    Log::warning("Skipping customer service training - missing employee data: Employee ID {$training->employee_id}");
                    continue;
                }

                // Find or create course for Customer Service Training
                $course = \App\Models\CourseManagement::firstOrCreate(
                    ['course_title' => 'Customer Service & Sales Skills Training'],
                    [
                        'course_description' => 'Comprehensive customer service and sales skills development program',
                        'duration_hours' => 16,
                        'delivery_mode' => 'Blended Learning',
                        'status' => 'Active'
                    ]
                );

                // Check if certificate tracking already exists
                $existingCert = TrainingRecordCertificateTracking::where('employee_id', $training->employee_id)
                    ->where('course_id', $course->course_id)
                    ->first();

                if ($existingCert) {
                    $skippedCount++;
                    continue;
                }

                // Create certificate tracking record
                $certificateNumber = 'CS-' . strtoupper(substr($training->employee_id, 0, 3)) . '-' . date('Y') . '-' . str_pad($createdCount + 1, 4, '0', STR_PAD_LEFT);

                TrainingRecordCertificateTracking::create([
                    'employee_id' => $training->employee_id,
                    'course_id' => $course->course_id,
                    'training_date' => $training->updated_at->format('Y-m-d'),
                    'certificate_number' => $certificateNumber,
                    'certificate_expiry' => date('Y-m-d', strtotime('+2 years')),
                    'issue_date' => $training->updated_at->format('Y-m-d'),
                    'status' => 'Pending Examination',
                    'remarks' => 'Training completed - awaiting examination for certification',
                    'certificate_url' => null
                ]);

                // Sync with Employee Competency Profile
                $this->syncWithCompetencyProfile($training->employee_id, 'Customer Service & Sales Skills Training', $training->updated_at->format('Y-m-d'));
                $createdCount++;

                Log::info("SUCCESS: Created Customer Service certificate for {$training->employee->first_name} {$training->employee->last_name} ({$training->employee_id})");
            }

            // === PROCESS 3: Destination Knowledge Training ===
            Log::info("Processing Destination Knowledge Training completed trainings...");
            foreach ($destinationTrainings as $training) {
                if (!$training->employee) {
                    $errorCount++;
                    Log::warning("Skipping destination training - missing employee data: Employee ID {$training->employee_id}");
                    continue;
                }

                // Check if certificate tracking already exists for this destination training
                $existingCert = TrainingRecordCertificateTracking::whereHas('course', function($query) use ($training) {
                    $query->where('course_title', 'LIKE', '%' . $training->destination_name . '%');
                })->where('employee_id', $training->employee_id)->first();

                if ($existingCert) {
                    $skippedCount++;
                    continue; // Skip if already exists
                }

                // Find or create course
                $course = \App\Models\CourseManagement::where('course_title', 'LIKE', '%' . $training->destination_name . '%')->first();

                if (!$course) {
                    $course = \App\Models\CourseManagement::create([
                        'course_title' => $training->destination_name . ' - Destination Knowledge',
                        'course_description' => 'Destination Knowledge Training for ' . $training->destination_name,
                        'duration_hours' => 8,
                        'delivery_mode' => 'Online',
                        'status' => 'Active'
                    ]);
                }

                $certificateNumber = 'DEST-' . strtoupper(substr($training->employee_id, 0, 3)) . '-' . date('Y') . '-' . str_pad($createdCount + 1, 4, '0', STR_PAD_LEFT);

                TrainingRecordCertificateTracking::create([
                    'employee_id' => $training->employee_id,
                    'course_id' => $course->course_id,
                    'training_date' => $training->date_completed ?: $training->updated_at->format('Y-m-d'),
                    'certificate_number' => $certificateNumber,
                    'certificate_expiry' => date('Y-m-d', strtotime('+2 years')),
                    'issue_date' => $training->date_completed ?: $training->updated_at->format('Y-m-d'),
                    'status' => 'Pending Examination',
                    'remarks' => 'Training completed - awaiting examination for certification',
                    'certificate_url' => null
                ]);

                // Sync with Employee Competency Profile
                $this->syncWithCompetencyProfile($training->employee_id, $training->destination_name, $training->date_completed ?: $training->updated_at->format('Y-m-d'));
                $createdCount++;

                Log::info("SUCCESS: Created Destination certificate for {$training->employee->first_name} {$training->employee->last_name} ({$training->employee_id}) - {$training->destination_name}");
            }

            // === PROCESS 6: ALL COURSES FROM COURSE MANAGEMENT ===
            Log::info("Processing ALL courses from Course Management to ensure comprehensive coverage...");
            $allCourses = \App\Models\CourseManagement::where('status', 'Active')->get();
            Log::info("Found " . $allCourses->count() . " active courses in Course Management");
            
            foreach ($allCourses as $course) {
                Log::info("Processing course: {$course->course_title} (ID: {$course->course_id})");
                
                // For each course, find employees who have completed it in any form
                $employeesWithCompletedTraining = collect();
                
                // Check CompletedTraining table
                $completedFromTable = \App\Models\CompletedTraining::where('course_id', $course->course_id)
                    ->orWhere('training_title', 'LIKE', '%' . $course->course_title . '%')
                    ->get();
                foreach ($completedFromTable as $completed) {
                    $employeesWithCompletedTraining->push([
                        'employee_id' => $completed->employee_id,
                        'completion_date' => $completed->completion_date,
                        'source' => 'completed_training_table'
                    ]);
                }
                
                // Check EmployeeTrainingDashboard with 100% progress
                $completedFromDashboard = \App\Models\EmployeeTrainingDashboard::where('course_id', $course->course_id)
                    ->where('progress', '>=', 100)
                    ->get();
                foreach ($completedFromDashboard as $completed) {
                    $employeesWithCompletedTraining->push([
                        'employee_id' => $completed->employee_id,
                        'completion_date' => $completed->updated_at->format('Y-m-d'),
                        'source' => 'employee_training_dashboard'
                    ]);
                }
                
                // Check CompetencyCourseAssignment with 100% progress
                $completedFromCompetency = \App\Models\CompetencyCourseAssignment::where('course_id', $course->course_id)
                    ->where('progress', '>=', 100)
                    ->get();
                foreach ($completedFromCompetency as $completed) {
                    $employeesWithCompletedTraining->push([
                        'employee_id' => $completed->employee_id,
                        'completion_date' => $completed->updated_at->format('Y-m-d'),
                        'source' => 'competency_course_assignment'
                    ]);
                }
                
                // Remove duplicates by employee_id
                $uniqueEmployees = $employeesWithCompletedTraining->unique('employee_id');
                
                Log::info("Found " . $uniqueEmployees->count() . " employees with completed training for course: {$course->course_title}");
                
                // Generate certificates for each employee who completed this course
                foreach ($uniqueEmployees as $employeeData) {
                    $employeeId = $employeeData['employee_id'];
                    $completionDate = $employeeData['completion_date'];
                    
                    // Check if certificate tracking already exists
                    $existingCert = TrainingRecordCertificateTracking::where('employee_id', $employeeId)
                        ->where('course_id', $course->course_id)
                        ->first();
                    
                    if ($existingCert) {
                        // Update existing certificate if it doesn't have a certificate file
                        if (!$existingCert->certificate_url) {
                            $certificateResult = $certificateController->generateCertificateOnCompletion(
                                $employeeId,
                                $course->course_id,
                                $completionDate
                            );
                            if ($certificateResult) {
                                Log::info("Generated certificate file for existing tracking record: {$existingCert->id}");
                            }
                        }
                        $skippedCount++;
                        continue;
                    }
                    
                    // Generate new certificate
                    $certificateResult = $certificateController->generateCertificateOnCompletion(
                        $employeeId,
                        $course->course_id,
                        $completionDate
                    );
                    
                    if ($certificateResult) {
                        $this->syncWithCompetencyProfile($employeeId, $course->course_title, $completionDate);
                        $createdCount++;
                        Log::info("SUCCESS: Created certificate for {$employeeId} - {$course->course_title} (from Course Management scan)");
                    } else {
                        $errorCount++;
                        Log::error("FAILED: Certificate generation for {$employeeId} - {$course->course_title} (from Course Management scan)");
                    }
                }
            }

            // === FINAL SUMMARY ===
            Log::info("=== CERTIFICATE TRACKING SUMMARY ===");
            Log::info("Total employees processed: " . $allEmployees->count());
            Log::info("Certificates created: {$createdCount}");
            Log::info("Records skipped (already exist): {$skippedCount}");
            Log::info("Errors encountered: {$errorCount}");

            // Log activity
            ActivityLog::create([
                'user_id' => Auth::id() ?: 1,
                'action' => 'bulk_create',
                'module' => 'Training Record Certificate Tracking',
                'description' => "Auto-generated certificate tracking for ALL EMPLOYEES and ALL COURSES: Created {$createdCount}, Skipped {$skippedCount}, Errors {$errorCount}. Sources: Completed Training Table, Training Requests, Employee Training Dashboard, Customer Service Training, Destination Knowledge Training, Course Management Scan.",
            ]);

            // Clean up duplicate certificates before syncing
            $this->cleanupDuplicateCertificates();
            
            // Force sync all existing certificates with competency profiles
            $this->syncAllExistingCertificatesWithCompetency();

            $successMessage = "Successfully processed ALL EMPLOYEES and ALL COURSES! ";
            $successMessage .= "Created: {$createdCount} new certificates, ";
            $successMessage .= "Skipped: {$skippedCount} existing records, ";
            $successMessage .= "Errors: {$errorCount}. ";
            $successMessage .= "Comprehensive scan completed: Completed Training Table, Training Requests, Employee Training Dashboard, Customer Service Training, Destination Knowledge Training, and ALL Course Management entries.";

            return redirect()->route('training_record_certificate_tracking.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            return redirect()->route('training_record_certificate_tracking.index')
                ->with('error', 'Error generating certificates: ' . $e->getMessage());
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
