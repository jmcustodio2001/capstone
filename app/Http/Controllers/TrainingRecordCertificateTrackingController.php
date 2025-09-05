<?php

namespace App\Http\Controllers;

use App\Models\TrainingRecordCertificateTracking;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrainingRecordCertificateTrackingController extends Controller
{
    public function index()
    {
        $certificates = TrainingRecordCertificateTracking::with(['employee', 'course'])->paginate(10);
        $employees = \App\Models\Employee::all();
        $courses = \App\Models\CourseManagement::all();
        return view('learning_management.training_record_certificate_tracking', compact('certificates', 'employees', 'courses'));
    }

    public function store(Request $request)
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
            $createdCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            $certificateController = new \App\Http\Controllers\CertificateGenerationController(new \App\Services\AICertificateGeneratorService());

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

                TrainingRecordCertificateTracking::create([
                    'employee_id' => $request->employee_id,
                    'course_id' => $course->course_id,
                    'training_date' => $request->requested_date,
                    'certificate_number' => $certificateNumber,
                    'certificate_expiry' => date('Y-m-d', strtotime($request->requested_date . ' +2 years')),
                    'status' => 'Completed',
                    'remarks' => 'Auto-generated from approved training request: ' . $request->training_title,
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
                    'status' => 'Completed',
                    'remarks' => 'Auto-generated from Customer Service & Sales Skills Training completion',
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
                    'status' => 'Completed',
                    'remarks' => 'Auto-generated from destination knowledge training completion',
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
}
