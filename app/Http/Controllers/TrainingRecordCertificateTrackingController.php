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
            
            // Fetch certificates with relationships - enhanced error handling
            $certificates = TrainingRecordCertificateTracking::with(['employee', 'course'])
                ->orderBy('created_at', 'desc')
                ->get();
            
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
            Log::info('Certificate tracking - Records found: ' . $certificates->count());
            Log::info('Certificate tracking - Employees available: ' . $employees->count());
            Log::info('Certificate tracking - Courses available: ' . $courses->count());
            
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
            foreach ($completedTrainingsFromTable as $training) {
                if (!$training->employee || !$training->course) {
                    $errorCount++;
                    Log::warning("Skipping completed training - missing employee or course data: Employee ID {$training->employee_id}, Course ID {$training->course_id}");
                    continue;
                }

                // Check if certificate tracking already exists
                $existingCert = TrainingRecordCertificateTracking::where('employee_id', $training->employee_id)
                    ->where('course_id', $training->course_id)
                    ->first();

                if ($existingCert) {
                    $skippedCount++;
                    continue;
                }

                // Generate certificate using the CertificateGenerationController
                $certificateResult = $certificateController->generateCertificateOnCompletion(
                    $training->employee_id,
                    $training->course_id,
                    $training->completion_date
                );

                if ($certificateResult) {
                    $this->syncWithCompetencyProfile($training->employee_id, $training->course->course_title, $training->completion_date);
                    $createdCount++;
                    Log::info("SUCCESS: Created certificate for {$training->employee->first_name} {$training->employee->last_name} ({$training->employee_id}) - {$training->course->course_title}");
                } else {
                    $errorCount++;
                    Log::error("FAILED: Certificate generation for {$training->employee_id} - {$training->course->course_title}");
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
                'description' => "Auto-generated certificate tracking for ALL EMPLOYEES: Created {$createdCount}, Skipped {$skippedCount}, Errors {$errorCount}. Sources: Employee Training Dashboard, Customer Service Training, Destination Knowledge Training.",
            ]);

            // Force sync all existing certificates with competency profiles
            $this->syncAllExistingCertificatesWithCompetency();

            $successMessage = "Successfully processed ALL EMPLOYEES' completed training! ";
            $successMessage .= "Created: {$createdCount} new certificates, ";
            $successMessage .= "Skipped: {$skippedCount} existing records, ";
            $successMessage .= "Errors: {$errorCount}. ";
            $successMessage .= "All training sources checked: Employee Training Dashboard, Customer Service Training, Destination Knowledge Training.";

            return redirect()->route('training_record_certificate_tracking.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            return redirect()->route('training_record_certificate_tracking.index')
                ->with('error', 'Error generating certificates: ' . $e->getMessage());
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

            // Get all existing columns
            $existingColumns = DB::select("SHOW COLUMNS FROM training_record_certificate_tracking");
            $columnNames = array_column($existingColumns, 'Field');
            
            // Required columns with their definitions
            $requiredColumns = [
                'training_date' => "ADD COLUMN `training_date` date NOT NULL AFTER `course_id`",
                'certificate_number' => "ADD COLUMN `certificate_number` varchar(255) DEFAULT NULL AFTER `training_date`",
                'certificate_expiry' => "ADD COLUMN `certificate_expiry` date DEFAULT NULL AFTER `certificate_number`",
                'certificate_url' => "ADD COLUMN `certificate_url` varchar(255) DEFAULT NULL AFTER `certificate_expiry`",
                'issue_date' => "ADD COLUMN `issue_date` date DEFAULT NULL AFTER `certificate_url`",
                'status' => "ADD COLUMN `status` varchar(255) NOT NULL DEFAULT 'Active' AFTER `issue_date`",
                'remarks' => "ADD COLUMN `remarks` text DEFAULT NULL AFTER `status`"
            ];

            $columnsAdded = [];
            
            // Check and add missing columns
            foreach ($requiredColumns as $columnName => $alterStatement) {
                if (!in_array($columnName, $columnNames)) {
                    try {
                        DB::statement("ALTER TABLE `training_record_certificate_tracking` " . $alterStatement);
                        $columnsAdded[] = $columnName;
                        Log::info("Added missing column: {$columnName}");
                    } catch (\Exception $e) {
                        Log::error("Failed to add column {$columnName}: " . $e->getMessage());
                    }
                }
            }

            // Update existing records with default values if columns were added
            if (!empty($columnsAdded)) {
                if (in_array('training_date', $columnsAdded)) {
                    DB::statement("UPDATE `training_record_certificate_tracking` SET `training_date` = COALESCE(DATE(`created_at`), CURDATE()) WHERE `training_date` IS NULL OR `training_date` = '0000-00-00'");
                }
                if (in_array('issue_date', $columnsAdded)) {
                    DB::statement("UPDATE `training_record_certificate_tracking` SET `issue_date` = COALESCE(DATE(`created_at`), CURDATE()) WHERE `issue_date` IS NULL OR `issue_date` = '0000-00-00'");
                }
                if (in_array('status', $columnsAdded)) {
                    DB::statement("UPDATE `training_record_certificate_tracking` SET `status` = 'Active' WHERE `status` IS NULL OR `status` = ''");
                }
                
                Log::info('Updated existing records with default values for columns: ' . implode(', ', $columnsAdded));
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Error ensuring table structure: ' . $e->getMessage());
            
            // If all else fails, recreate the table completely
            try {
                Log::info('Attempting to recreate table due to structure issues...');
                $this->executeTableCreation();
                return true;
            } catch (\Exception $e2) {
                Log::error('Failed to recreate table: ' . $e2->getMessage());
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
}
