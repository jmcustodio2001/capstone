<?php

namespace App\Jobs;

use App\Models\DestinationKnowledgeTraining;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoCompleteDestinationTrainingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job to auto-complete destination knowledge training after 1 day.
     */
    public function handle()
    {
        try {
            Log::info('AutoCompleteDestinationTrainingJob: Starting auto-completion check');

            // Find all destination knowledge training records that are "in-progress" 
            // and were accepted (is_active = true) more than 1 day ago
            $trainingsToComplete = DestinationKnowledgeTraining::where('status', 'in-progress')
                ->where('is_active', true)
                ->where('updated_at', '<=', Carbon::now()->subDay())
                ->get();

            $completedCount = 0;

            foreach ($trainingsToComplete as $training) {
                // Check if it's been exactly 1 day or more since acceptance
                $daysSinceAcceptance = Carbon::parse($training->updated_at)->diffInDays(Carbon::now());
                
                if ($daysSinceAcceptance >= 1) {
                    // Update status to completed
                    $training->status = 'completed';
                    $training->progress = 100;
                    $training->date_completed = Carbon::now();
                    $training->save();

                    // Create completed training record for employee
                    $this->createCompletedTrainingRecord($training);

                    // Generate certificate for completed training
                    $this->generateCertificate($training);

                    // Sync with other systems
                    $this->syncWithOtherSystems($training);

                    // Log the auto-completion
                    ActivityLog::create([
                        'user_id' => 1, // System user
                        'action' => 'auto_complete',
                        'module' => 'Destination Knowledge Training',
                        'description' => "Auto-completed destination knowledge training '{$training->destination_name}' for employee {$training->employee_id} after 1 day",
                    ]);

                    $completedCount++;
                    
                    Log::info("Auto-completed training ID {$training->id} for employee {$training->employee_id}");
                }
            }

            Log::info("AutoCompleteDestinationTrainingJob: Completed {$completedCount} trainings");

        } catch (\Exception $e) {
            Log::error('AutoCompleteDestinationTrainingJob failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Sync completion with Employee Training Dashboard and Competency systems
     */
    private function syncWithOtherSystems($training)
    {
        try {
            // Sync with Employee Training Dashboard
            $employeeTraining = \App\Models\EmployeeTrainingDashboard::where('employee_id', $training->employee_id)
                ->whereHas('course', function($q) use ($training) {
                    $q->where('course_title', 'LIKE', '%' . $training->destination_name . '%');
                })
                ->first();

            if ($employeeTraining) {
                $employeeTraining->progress = 100;
                $employeeTraining->status = 'Completed';
                $employeeTraining->last_accessed = Carbon::now();
                $employeeTraining->save();
            }

            // Sync with Employee Competency Profile
            $competencyProfile = \App\Models\EmployeeCompetencyProfile::where('employee_id', $training->employee_id)
                ->whereHas('competency', function($q) use ($training) {
                    $destinationNameClean = str_replace([' Training', 'Training'], '', $training->destination_name);
                    $q->where('competency_name', 'LIKE', '%' . $destinationNameClean . '%');
                })
                ->first();

            if ($competencyProfile) {
                $competencyProfile->proficiency_level = 5; // Expert level
                $competencyProfile->save();
            }

            // Sync with Competency Gap
            $competencyGap = \App\Models\CompetencyGap::where('employee_id', $training->employee_id)
                ->whereHas('competency', function($q) use ($training) {
                    $destinationNameClean = str_replace([' Training', 'Training'], '', $training->destination_name);
                    $q->where('competency_name', 'LIKE', '%' . $destinationNameClean . '%');
                })
                ->first();

            if ($competencyGap) {
                $competencyGap->current_level = $competencyGap->required_level; // Close the gap
                $competencyGap->gap = 0;
                $competencyGap->save();
            }

        } catch (\Exception $e) {
            Log::error('Error syncing auto-completed training with other systems: ' . $e->getMessage());
        }
    }

    /**
     * Create completed training record for employee's completed training section
     */
    private function createCompletedTrainingRecord($training)
    {
        try {
            // Check if completed training record already exists
            $existingRecord = \App\Models\CompletedTraining::where('employee_id', $training->employee_id)
                ->where('training_title', $training->destination_name)
                ->first();

            if (!$existingRecord) {
                \App\Models\CompletedTraining::create([
                    'employee_id' => $training->employee_id,
                    'training_title' => $training->destination_name,
                    'completion_date' => Carbon::now()->format('Y-m-d'),
                    'remarks' => 'Auto-completed destination knowledge training after 1 day acceptance',
                    'status' => 'Verified',
                    'certificate_path' => null // Will be updated after certificate generation
                ]);

                Log::info("Created completed training record for employee {$training->employee_id}: {$training->destination_name}");
            }
        } catch (\Exception $e) {
            Log::error('Error creating completed training record: ' . $e->getMessage());
        }
    }

    /**
     * Generate certificate for completed destination knowledge training
     */
    private function generateCertificate($training)
    {
        try {
            // Find or create course record for certificate generation
            $course = \App\Models\CourseManagement::firstOrCreate(
                ['course_title' => $training->destination_name],
                [
                    'description' => 'Destination Knowledge Training - ' . $training->details,
                    'start_date' => Carbon::now(),
                    'end_date' => Carbon::now()->addMonths(3),
                    'status' => 'Active'
                ]
            );

            // Check if certificate already exists
            $existingCertificate = \App\Models\TrainingRecordCertificateTracking::where('employee_id', $training->employee_id)
                ->where('course_id', $course->course_id)
                ->first();

            if (!$existingCertificate) {
                // Use AI Certificate Generator Service if available
                if (class_exists('\App\Services\AICertificateGeneratorService')) {
                    $certificateService = new \App\Services\AICertificateGeneratorService();
                    
                    // Generate certificate
                    $certificateData = $certificateService->generateCertificate(
                        $training->employee->full_name ?? 'Employee',
                        $training->destination_name,
                        Carbon::now()->format('Y-m-d'),
                        $training->employee_id
                    );

                    // Create certificate tracking record
                    \App\Models\TrainingRecordCertificateTracking::create([
                        'employee_id' => $training->employee_id,
                        'course_id' => $course->course_id,
                        'certificate_number' => $certificateData['certificate_number'] ?? 'CERT-' . time(),
                        'certificate_url' => $certificateData['certificate_url'] ?? null,
                        'issue_date' => Carbon::now(),
                        'status' => 'issued',
                        'issued_by' => 'System Auto-Generation'
                    ]);

                    Log::info("Generated certificate for employee {$training->employee_id}: {$training->destination_name}");
                } else {
                    // Fallback: Create certificate tracking record without actual certificate
                    \App\Models\TrainingRecordCertificateTracking::create([
                        'employee_id' => $training->employee_id,
                        'course_id' => $course->course_id,
                        'certificate_number' => 'DEST-' . $training->employee_id . '-' . time(),
                        'certificate_url' => null,
                        'issue_date' => Carbon::now(),
                        'status' => 'pending_generation',
                        'issued_by' => 'System Auto-Generation'
                    ]);

                    Log::info("Created certificate tracking record (pending generation) for employee {$training->employee_id}: {$training->destination_name}");
                }
            }
        } catch (\Exception $e) {
            Log::error('Error generating certificate for destination training: ' . $e->getMessage());
        }
    }
}
