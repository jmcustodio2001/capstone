<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Schema\Blueprint;
use App\Models\UpcomingTraining;
use App\Models\CompletedTraining;
use App\Models\ActivityLog;
use App\Models\EmployeeTrainingDashboard;
use App\Models\DestinationKnowledgeTraining;
use App\Models\ExamAttempt;
use App\Models\TrainingProgress;
use App\Models\TrainingFeedback;
use App\Models\TrainingNotification;
use App\Models\TrainingRequest;
use App\Models\CompetencyCourseAssignment;
use App\Models\CustomerServiceSalesSkillsTraining;
use App\Models\ExamQuestion;
use App\Models\CourseManagement;
use App\Http\Controllers\CertificateGenerationController;
use App\Models\TrainingReview;
use App\Models\Employee;
use App\Models\EmployeeCompetencyProfile;
use App\Models\CompetencyGap;
use App\Models\SuccessionReadinessRating;
use App\Models\TrainingRecordCertificateTracking;
use App\Services\AIQuestionGeneratorService;
use App\Services\AICertificateGeneratorService;
class MyTrainingController extends Controller
{
    /**
     * Verify employee password
     */
    private function verifyEmployeePassword($password)
    {
        $employee = Auth::user();
        if (!$employee) {
            return false;
        }
        
        return Hash::check($password, $employee->password);
    }

    /**
     * Ensure the training_requests table exists
     */
    private function ensureTrainingRequestsTableExists()
    {
        try {
            if (!Schema::hasTable('training_requests')) {
                Log::info('Creating missing training_requests table...');

                Schema::create('training_requests', function (Blueprint $table) {
                    $table->id('request_id');
                    $table->string('employee_id', 20);
                    $table->unsignedBigInteger('course_id')->nullable();
                    $table->string('training_title', 255);
                    $table->text('reason');
                    $table->string('status')->default('Pending');
                    $table->date('requested_date');
                    $table->timestamps();

                    // Add indexes for better performance
                    $table->index('employee_id');
                    $table->index('course_id');
                    $table->index('status');
                });

                Log::info('training_requests table created successfully');
            }
        } catch (\Exception $e) {
            Log::error('Error creating training_requests table: ' . $e->getMessage());

            // Try direct SQL approach as fallback
            try {
                DB::statement("CREATE TABLE IF NOT EXISTS `training_requests` (
                    `request_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `employee_id` varchar(20) NOT NULL,
                    `course_id` bigint(20) UNSIGNED DEFAULT NULL,
                    `training_title` varchar(255) NOT NULL,
                    `reason` text NOT NULL,
                    `status` varchar(255) NOT NULL DEFAULT 'Pending',
                    `requested_date` date NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`request_id`),
                    KEY `training_requests_employee_id_index` (`employee_id`),
                    KEY `training_requests_course_id_index` (`course_id`),
                    KEY `training_requests_status_index` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                Log::info('training_requests table created using direct SQL');
            } catch (\Exception $sqlError) {
                Log::error('Failed to create training_requests table: ' . $sqlError->getMessage());
                throw $sqlError;
            }
        }
    }

    /**
     * Ensure the employee_training_dashboard table exists
     */
    private function ensureEmployeeTrainingDashboardTableExists()
    {
        try {
            if (!Schema::hasTable('employee_training_dashboard')) {
                Log::info('Creating missing employee_training_dashboard table...');

                Schema::create('employee_training_dashboard', function (Blueprint $table) {
                    $table->id();
                    $table->string('employee_id', 20);
                    $table->unsignedBigInteger('course_id');
                    $table->date('training_date')->nullable();
                    $table->integer('progress')->default(0);
                    $table->string('status')->default('Not Started');
                    $table->text('remarks')->nullable();
                    $table->timestamp('last_accessed')->nullable();
                    $table->unsignedBigInteger('assigned_by')->nullable();
                    $table->timestamp('expired_date')->nullable();
                    $table->timestamps();

                    // Add indexes for better performance
                    $table->index('employee_id');
                    $table->index('course_id');
                    $table->index('status');
                });

                Log::info('employee_training_dashboard table created successfully');
            }
        } catch (\Exception $e) {
            Log::error('Error creating employee_training_dashboard table: ' . $e->getMessage());

            // Try direct SQL approach as fallback
            try {
                DB::statement("CREATE TABLE IF NOT EXISTS `employee_training_dashboard` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `employee_id` varchar(20) NOT NULL,
                    `course_id` bigint(20) UNSIGNED NOT NULL,
                    `training_date` date DEFAULT NULL,
                    `progress` int(11) NOT NULL DEFAULT 0,
                    `status` varchar(255) NOT NULL DEFAULT 'Not Started',
                    `remarks` text DEFAULT NULL,
                    `last_accessed` timestamp NULL DEFAULT NULL,
                    `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
                    `expired_date` timestamp NULL DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `employee_training_dashboard_employee_id_index` (`employee_id`),
                    KEY `employee_training_dashboard_course_id_index` (`course_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                Log::info('employee_training_dashboard table created using direct SQL');
            } catch (\Exception $sqlError) {
                Log::error('Failed to create employee_training_dashboard table: ' . $sqlError->getMessage());
                throw $sqlError;
            }
        }
    }

    /**
     * Ensure the training_progress table exists
     */
    private function ensureTrainingProgressTableExists()
    {
        try {
            if (!Schema::hasTable('training_progress')) {
                Log::info('Creating missing training_progress table...');

                Schema::create('training_progress', function (Blueprint $table) {
                    $table->id('progress_id');
                    $table->string('employee_id', 20);
                    $table->string('training_title');
                    $table->integer('progress_percentage')->default(0);
                    $table->dateTime('last_updated');
                    $table->string('status')->default('In Progress');
                    $table->timestamps();

                    // Add indexes for better performance
                    $table->index('employee_id');
                    $table->index('status');
                });

                Log::info('training_progress table created successfully');
            }
        } catch (\Exception $e) {
            Log::error('Error creating training_progress table: ' . $e->getMessage());

            // Try direct SQL approach as fallback
            try {
                DB::statement("CREATE TABLE IF NOT EXISTS `training_progress` (
                    `progress_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `employee_id` varchar(20) NOT NULL,
                    `training_title` varchar(255) NOT NULL,
                    `progress_percentage` int(11) NOT NULL DEFAULT 0,
                    `last_updated` datetime NOT NULL,
                    `status` varchar(255) NOT NULL DEFAULT 'In Progress',
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`progress_id`),
                    KEY `training_progress_employee_id_index` (`employee_id`),
                    KEY `training_progress_status_index` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                Log::info('training_progress table created using direct SQL');
            } catch (\Exception $sqlError) {
                Log::error('Failed to create training_progress table: ' . $sqlError->getMessage());
                throw $sqlError;
            }
        }
    }

    /**
     * Ensure the training_notifications table exists
     */
    private function ensureTrainingNotificationsTableExists()
    {
        try {
            if (!Schema::hasTable('training_notifications')) {
                Log::info('Creating missing training_notifications table...');

                Schema::create('training_notifications', function (Blueprint $table) {
                    $table->id();
                    $table->string('employee_id', 20);
                    $table->text('message');
                    $table->timestamp('sent_at');
                    $table->boolean('is_read')->default(false);
                    $table->timestamps();

                    $table->index('employee_id');
                    $table->index('is_read');
                });

                Log::info('training_notifications table created successfully');
            }
        } catch (\Exception $e) {
            Log::error('Error creating training_notifications table: ' . $e->getMessage());

            try {
                DB::statement("CREATE TABLE IF NOT EXISTS `training_notifications` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `employee_id` varchar(20) NOT NULL,
                    `message` text NOT NULL,
                    `sent_at` timestamp NOT NULL,
                    `is_read` tinyint(1) NOT NULL DEFAULT 0,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `training_notifications_employee_id_index` (`employee_id`),
                    KEY `training_notifications_is_read_index` (`is_read`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                Log::info('training_notifications table created using direct SQL');
            } catch (\Exception $sqlError) {
                Log::error('Failed to create training_notifications table: ' . $sqlError->getMessage());
                throw $sqlError;
            }
        }
    }

    /**
     * Ensure the training_record_certificate_tracking table exists
     */
    private function ensureTrainingRecordCertificateTrackingTableExists()
    {
        try {
            if (!Schema::hasTable('training_record_certificate_tracking')) {
                Log::info('Creating missing training_record_certificate_tracking table...');

                Schema::create('training_record_certificate_tracking', function (Blueprint $table) {
                    $table->id();
                    $table->string('employee_id', 20);
                    $table->unsignedBigInteger('course_id')->nullable();
                    $table->string('certificate_number')->unique();
                    $table->string('certificate_url')->nullable();
                    $table->date('issue_date');
                    $table->string('status')->default('issued');
                    $table->string('issued_by')->nullable();
                    $table->timestamps();

                    $table->index('employee_id');
                    $table->index('course_id');
                    $table->index('status');
                });

                Log::info('training_record_certificate_tracking table created successfully');
            }
        } catch (\Exception $e) {
            Log::error('Error creating training_record_certificate_tracking table: ' . $e->getMessage());

            try {
                DB::statement("CREATE TABLE IF NOT EXISTS `training_record_certificate_tracking` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `employee_id` varchar(20) NOT NULL,
                    `course_id` bigint(20) UNSIGNED DEFAULT NULL,
                    `certificate_number` varchar(255) NOT NULL,
                    `certificate_url` varchar(255) DEFAULT NULL,
                    `issue_date` date NOT NULL,
                    `status` varchar(255) NOT NULL DEFAULT 'issued',
                    `issued_by` varchar(255) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `training_record_certificate_tracking_certificate_number_unique` (`certificate_number`),
                    KEY `training_record_certificate_tracking_employee_id_index` (`employee_id`),
                    KEY `training_record_certificate_tracking_course_id_index` (`course_id`),
                    KEY `training_record_certificate_tracking_status_index` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                Log::info('training_record_certificate_tracking table created using direct SQL');
            } catch (\Exception $sqlError) {
                Log::error('Failed to create training_record_certificate_tracking table: ' . $sqlError->getMessage());
                throw $sqlError;
            }
        }
    }

    public function index()
    {
        // Ensure required tables exist before proceeding
        $this->ensureTrainingRequestsTableExists();
        $this->ensureEmployeeTrainingDashboardTableExists();
        $this->ensureTrainingProgressTableExists();
        $this->ensureTrainingNotificationsTableExists();
        $this->ensureTrainingRecordCertificateTrackingTableExists();

        // Fix assigned_by_name for any records that still have numeric values
        $this->fixAssignedByNamesOnLoad();

        $employeeId = Auth::user()->employee_id;

        // Get all upcoming trainings including competency gap assignments
        $manualUpcoming = UpcomingTraining::where('employee_id', $employeeId)->get()->map(function($training) use ($employeeId) {
            // Fix expiration date for competency gap trainings
            if ($training->source === 'competency_gap') {
                $competencyGap = CompetencyGap::with('competency')
                    ->where('employee_id', $employeeId)
                    ->whereHas('competency', function($query) use ($training) {
                        $query->where('competency_name', $training->training_title);
                    })
                    ->first();
                
                if ($competencyGap && $competencyGap->expired_date) {
                    $training->end_date = $competencyGap->expired_date;
                    $training->expired_date = $competencyGap->expired_date;
                }
            }
            return $training;
        });

        // Get admin-assigned trainings from EmployeeTrainingDashboard
        $adminAssigned = EmployeeTrainingDashboard::with(['course', 'assignedBy'])
            ->where('employee_id', $employeeId)
            ->whereIn('status', ['Assigned', 'In Progress', 'Not Started'])
            ->whereHas('course') // Only include records that have valid course relationships
            ->get()
            ->map(function($training) use ($employeeId) {
                // Calculate combined exam/quiz progress instead of using raw progress
                $combinedProgress = ExamAttempt::calculateCombinedProgress($employeeId, $training->course_id);

                // Use combined progress if available, otherwise fall back to training progress
                $displayProgress = $combinedProgress > 0 ? $combinedProgress : ($training->progress ?? 0);

                // Get expired date from competency gap instead of training record
                $competencyGapExpiredDate = null;
                if ($training->course && $training->course->course_title) {
                    // Find matching competency gap by course title
                    $competencyName = str_replace([' Training', ' Course', ' Program'], '', $training->course->course_title);
                    $competencyGap = CompetencyGap::whereHas('competency', function($query) use ($competencyName) {
                        $query->where('competency_name', 'LIKE', '%' . $competencyName . '%');
                    })->where('employee_id', $employeeId)->first();

                    if ($competencyGap && $competencyGap->expired_date) {
                        $competencyGapExpiredDate = $competencyGap->expired_date;
                    }
                }

                // Generate proper Training ID with year and sequential number
                $trainingYear = $training->created_at->format('Y');
                $sequentialNumber = str_pad($training->id, 4, '0', STR_PAD_LEFT);
                $properTrainingId = "TR{$trainingYear}{$sequentialNumber}";
                
                // Calculate proper expired date
                $expiredDate = $competencyGapExpiredDate ?? $training->expired_date ?? ($training->course ? $training->course->expired_date : null);
                if (!$expiredDate && $training->course && $training->course->start_date) {
                    $expiredDate = \Carbon\Carbon::parse($training->course->start_date)->addMonths(6);
                } elseif (!$expiredDate) {
                    $expiredDate = $training->created_at->addMonths(6);
                }

                return (object)[
                    'upcoming_id' => $properTrainingId,
                    'training_title' => $training->course->course_title,
                    'start_date' => $training->course->start_date ?? $training->training_date,
                    'end_date' => $training->course->end_date ?? null, // Use course end_date for End Date column
                    'expired_date' => $expiredDate,
                    'status' => $training->status,
                    'source' => 'admin_assigned',
                    'progress' => $displayProgress,
                    'remarks' => $training->remarks ?? 'Assigned by admin',
                    'assigned_by_name' => $training->assignedBy ? $training->assignedBy->name : 'System Admin',
                    'assigned_date' => $training->training_date,
                    'course_id' => $training->course_id
                ];
            });

        // Get competency-based course assignments
        $competencyAssigned = CompetencyCourseAssignment::with(['course', 'assignedBy'])
            ->where('employee_id', $employeeId)
            ->whereIn('status', ['Assigned', 'In Progress', 'Not Started'])
            ->whereHas('course') // Only include records that have valid course relationships
            ->get()
            ->map(function($assignment) use ($employeeId) {
                // Calculate combined exam/quiz progress instead of using raw progress
                $combinedProgress = ExamAttempt::calculateCombinedProgress($employeeId, $assignment->course_id);

                // Use combined progress if available, otherwise fall back to assignment progress
                $displayProgress = $combinedProgress > 0 ? $combinedProgress : ($assignment->progress ?? 0);

                // Generate proper Training ID for competency assignment
                $assignmentYear = $assignment->created_at->format('Y');
                $sequentialNumber = str_pad($assignment->id, 4, '0', STR_PAD_LEFT);
                $properAssignmentId = "CA{$assignmentYear}{$sequentialNumber}";
                
                // Calculate proper expired date for competency assignment
                $expiredDate = $assignment->expired_date;
                if (!$expiredDate && $assignment->course && $assignment->course->start_date) {
                    $expiredDate = \Carbon\Carbon::parse($assignment->course->start_date)->addMonths(6);
                } elseif (!$expiredDate) {
                    $expiredDate = $assignment->created_at->addMonths(6);
                }

                return (object)[
                    'upcoming_id' => $properAssignmentId,
                    'training_title' => $assignment->course->course_title,
                    'start_date' => $assignment->course->start_date ?? $assignment->assigned_date,
                    'end_date' => $assignment->course->end_date ?? null,
                    'expired_date' => $expiredDate,
                    'status' => $assignment->status,
                    'source' => 'competency_assigned',
                    'progress' => $displayProgress,
                    'remarks' => 'Assigned based on competency gap',
                    'assigned_by_name' => $this->getCompetencyAssignedByName($assignment),
                    'assigned_date' => $assignment->assigned_date,
                    'course_id' => $assignment->course_id
                ];
            });

        // Fix expiration dates for destination trainings before retrieving them
        $this->fixDestinationExpirationDates();
        
        // Fix expiration dates for competency gap trainings
        $this->fixCompetencyGapExpirationDates();

        // Get destination knowledge training assignments
        // CRITICAL FIX: Only show destination trainings that DON'T already exist in upcoming_trainings table
        // This prevents duplicates when both controllers try to show the same training
        $existingUpcomingDestinations = UpcomingTraining::where('employee_id', $employeeId)
            ->where('source', 'destination_assigned')
            ->pluck('training_title')
            ->toArray();
            
        $destinationAssigned = DestinationKnowledgeTraining::where('employee_id', $employeeId)
            ->where('admin_approved_for_upcoming', true) // Only show if explicitly approved via Auto-Assign button
            ->whereNotIn('status', ['completed', 'declined']) // Exclude completed and declined
            ->whereNotIn('destination_name', $existingUpcomingDestinations) // Exclude if already in upcoming_trainings table
            ->get();

        // Debug logging to help identify the issue
        Log::info('MyTrainingController debug info:', [
            'employee_id' => $employeeId,
            'manual_upcoming_count' => $manualUpcoming->count(),
            'admin_assigned_count' => $adminAssigned->count(),
            'competency_assigned_count' => $competencyAssigned->count(),
            'total_destination_records' => DestinationKnowledgeTraining::where('employee_id', $employeeId)->count(),
            'approved_for_upcoming_count' => $destinationAssigned->count(),
            'all_destination_records' => DestinationKnowledgeTraining::where('employee_id', $employeeId)->get(['id', 'destination_name', 'admin_approved_for_upcoming', 'is_active', 'status'])->toArray(),
            'employee_training_dashboard_count' => EmployeeTrainingDashboard::where('employee_id', $employeeId)->count(),
            'competency_course_assignments_count' => CompetencyCourseAssignment::where('employee_id', $employeeId)->count()
        ]);

        $destinationAssigned = $destinationAssigned->map(function($training) {
                // Generate proper Training ID for destination training
                $destinationYear = $training->created_at->format('Y');
                $sequentialNumber = str_pad($training->id, 4, '0', STR_PAD_LEFT);
                $properDestinationId = "DT{$destinationYear}{$sequentialNumber}";
                
                // Use proper expired date or calculate one
                $expiredDate = $training->expired_date;
                if (!$expiredDate) {
                    $expiredDate = $training->created_at->addMonths(3);
                }
                
                // Get the actual admin name who assigned this training
                $assignedByName = 'Admin User';
                try {
                    // Try to get from upcoming_trainings table first
                    $upcomingTraining = \App\Models\UpcomingTraining::where('employee_id', $training->employee_id)
                        ->where('destination_training_id', $training->id)
                        ->first();
                    
                    if ($upcomingTraining && !empty($upcomingTraining->assigned_by_name) && $upcomingTraining->assigned_by_name !== '1') {
                        $assignedByName = $upcomingTraining->assigned_by_name;
                    } elseif ($upcomingTraining && $upcomingTraining->assigned_by) {
                        // Try to get user name from assigned_by ID
                        if (is_numeric($upcomingTraining->assigned_by)) {
                            $assignedUser = \App\Models\User::find($upcomingTraining->assigned_by);
                            if ($assignedUser && !empty($assignedUser->name)) {
                                $assignedByName = $assignedUser->name;
                                // Update the record with the found name for future use
                                $upcomingTraining->assigned_by_name = $assignedByName;
                                $upcomingTraining->save();
                            }
                        }
                    }
                    
                    // If still no name found or showing numeric ID, try to fix it
                    if ($upcomingTraining && (empty($upcomingTraining->assigned_by_name) || is_numeric($upcomingTraining->assigned_by_name))) {
                        // Fix the assigned_by_name based on source or user lookup
                        $fixedName = null;
                        
                        if (is_numeric($upcomingTraining->assigned_by)) {
                            $user = \App\Models\User::find($upcomingTraining->assigned_by);
                            if ($user && !empty($user->name)) {
                                $fixedName = $user->name;
                            }
                        }
                        
                        // If no user found, try to get from destination training record itself
                        if (!$fixedName && $training->assigned_by) {
                            if (is_numeric($training->assigned_by)) {
                                $user = \App\Models\User::find($training->assigned_by);
                                if ($user && !empty($user->name)) {
                                    $fixedName = $user->name;
                                }
                            }
                        }
                        
                        // Final fallback
                        if (!$fixedName) {
                            $fixedName = 'Admin User'; // Default for destination training
                        }
                        
                        $upcomingTraining->assigned_by_name = $fixedName;
                        $upcomingTraining->save();
                        $assignedByName = $fixedName;
                        
                        Log::info("Fixed assigned_by_name for upcoming training ID {$upcomingTraining->upcoming_id}: {$fixedName}");
                    }
                    
                    // If no upcoming training record exists, try to get from destination training record
                    if (!$upcomingTraining && $training->assigned_by && is_numeric($training->assigned_by)) {
                        $user = \App\Models\User::find($training->assigned_by);
                        if ($user && !empty($user->name)) {
                            $assignedByName = $user->name;
                        }
                    }
                    
                } catch (\Exception $e) {
                    Log::warning('Error getting assigned by name for destination training: ' . $e->getMessage());
                }
                
                return (object)[
                    'upcoming_id' => $properDestinationId,
                    'training_title' => $training->destination_name,
                    'start_date' => $training->created_at,
                    'end_date' => $expiredDate,
                    'expired_date' => $expiredDate,
                    'status' => $training->is_active ? 'Active' : ($training->status === 'declined' ? 'Declined' : 'Pending Response'),
                    'source' => 'destination_assigned',
                    'progress' => $training->progress ?? 0,
                    'delivery_mode' => $training->delivery_mode,
                    'remarks' => $training->remarks ?? 'Destination knowledge training assignment',
                    'assigned_by_name' => $assignedByName,
                    'assigned_date' => $training->created_at,
                    'destination_training_id' => $training->id,
                    'is_active' => $training->is_active,
                    'needs_response' => !$training->is_active && $training->status !== 'declined'
                ];
            });

        // Calculate readiness rating for each employee to determine exam/quiz visibility
        $employee = Auth::user();
        $competencyProfiles = EmployeeCompetencyProfile::where('employee_id', $employeeId)->get();

        $readinessRating = 0;
        if ($competencyProfiles->count() > 0) {
            $avgProficiency = $competencyProfiles->avg('proficiency_level');
            $leadershipCompetencies = $competencyProfiles->whereIn('competency_name', [
                'LEADERSHIP', 'MANAGEMENT', 'TEAM LEADERSHIP', 'STRATEGIC LEADERSHIP'
            ]);
            $proficiencyScore = ($avgProficiency / 5) * 100;
            $leadershipScore = min($leadershipCompetencies->count() * 20, 100);
            $competencyBreadthScore = min($competencyProfiles->count() * 10, 100);

            // Get training data
            $trainingRecords = EmployeeTrainingDashboard::where('employee_id', $employeeId)->get();
            $avgTrainingProgress = $trainingRecords->avg('progress') ?? 0;
            $totalCoursesAssigned = $trainingRecords->count();
            $completedCourses = $trainingRecords->where('progress', '>=', 100)->count();
            $courseCompletionRate = $totalCoursesAssigned > 0 ? ($completedCourses / $totalCoursesAssigned) * 100 : 0;

            $trainingProgressScore = $avgTrainingProgress;
            $courseCompletionScore = $courseCompletionRate;
            $courseAssignmentScore = min($totalCoursesAssigned * 10, 100);

            $combinedTrainingScore = ($trainingProgressScore * 0.5) +
                                   ($courseCompletionScore * 0.3) +
                                   ($courseAssignmentScore * 0.2);

            $readinessRating = round(($proficiencyScore * 0.30) +
                                   ($leadershipScore * 0.25) +
                                   ($competencyBreadthScore * 0.15) +
                                   ($combinedTrainingScore * 0.30));
        }

        // Debug: Log all training sources before deduplication
        Log::info('DEBUG - Training sources before deduplication:', [
            'employee_id' => $employeeId,
            'manualUpcoming' => $manualUpcoming->map(function($item) {
                return [
                    'id' => $item->id ?? 'N/A',
                    'training_title' => $item->training_title ?? 'N/A',
                    'source' => $item->source ?? 'N/A',
                    'course_id' => $item->course_id ?? 'NULL'
                ];
            })->toArray(),
            'adminAssigned' => $adminAssigned->map(function($item) {
                return [
                    'id' => $item->id ?? 'N/A',
                    'training_title' => $item->training_title ?? 'N/A',
                    'source' => $item->source ?? 'N/A',
                    'course_id' => $item->course_id ?? 'NULL'
                ];
            })->toArray(),
            'competencyAssigned' => $competencyAssigned->map(function($item) {
                return [
                    'id' => $item->id ?? 'N/A',
                    'training_title' => $item->training_title ?? 'N/A',
                    'source' => $item->source ?? 'N/A',
                    'course_id' => $item->course_id ?? 'NULL'
                ];
            })->toArray()
        ]);

        // Combine all upcoming trainings with proper deduplication
        $allTrainings = collect()
            ->merge($manualUpcoming->toArray())
            ->merge($adminAssigned->toArray())
            ->merge($competencyAssigned->toArray())
            ->merge($destinationAssigned->toArray());

        // ENHANCED deduplication with comprehensive title normalization
        $seenTrainings = [];
        $upcoming = $allTrainings->filter(function($item) use (&$seenTrainings, $employeeId) {
            $item = (object) $item;
            
            // Get raw training title
            $rawTitle = $item->training_title ?? '';
            
            // Skip if training title is empty or generic
            if (empty(trim($rawTitle)) || in_array(strtolower(trim($rawTitle)), ['training course', 'unknown course', 'unknown', 'course', 'n/a'])) {
                return false;
            }
            
            // COMPREHENSIVE title normalization for "Communication Skills" duplicates
            $normalizedTitle = strtolower(trim($rawTitle));
            
            // Remove common suffixes/prefixes that cause duplicates
            $normalizedTitle = preg_replace('/\b(training|course|program|skills|knowledge|development|workshop|seminar)\b/i', '', $normalizedTitle);
            $normalizedTitle = preg_replace('/\s+/', ' ', trim($normalizedTitle)); // Clean up spaces
            
            // Create multiple deduplication keys for comprehensive matching
            $courseId = $item->course_id ?? null;
            $source = $item->source ?? '';
            
            $deduplicationKeys = [
                // Primary key: employee + normalized title
                $employeeId . '_title_' . $normalizedTitle,
                // Secondary key: employee + course_id (if available)
                $courseId ? $employeeId . '_course_' . $courseId : null,
                // Tertiary key: employee + raw title (for exact matches)
                $employeeId . '_raw_' . strtolower(trim($rawTitle))
            ];
            
            // Remove null keys
            $deduplicationKeys = array_filter($deduplicationKeys);
            
            // Check if any of these keys already exist
            $isDuplicate = false;
            $existingKey = null;
            $existingItem = null;
            
            foreach ($deduplicationKeys as $checkKey) {
                if (isset($seenTrainings[$checkKey])) {
                    $isDuplicate = true;
                    $existingKey = $checkKey;
                    $existingItem = $seenTrainings[$checkKey];
                    break;
                }
            }
            
            // Additional fuzzy matching for edge cases
            if (!$isDuplicate && strlen($normalizedTitle) > 2) {
                foreach ($seenTrainings as $key => $existingTraining) {
                    $existingNormalized = strtolower(trim($existingTraining->training_title ?? ''));
                    $existingNormalized = preg_replace('/\b(training|course|program|skills|knowledge|development|workshop|seminar)\b/i', '', $existingNormalized);
                    $existingNormalized = preg_replace('/\s+/', ' ', trim($existingNormalized));
                    
                    // Check for very similar titles (like "communication" vs "communication")
                    if ($normalizedTitle === $existingNormalized && strlen($normalizedTitle) > 2) {
                        $isDuplicate = true;
                        $existingKey = $key;
                        $existingItem = $existingTraining;
                        break;
                    }
                }
            }
            
            if ($isDuplicate && $existingItem) {
                // Priority-based replacement
                $existingSource = $existingItem->source ?? '';
                $currentSource = $item->source ?? '';
                
                $sourcePriority = [
                    'admin_assigned' => 5,
                    'competency_assigned' => 4,
                    'competency_gap' => 3,
                    'manual' => 2,
                    'destination_assigned' => 1
                ];
                
                $existingPriority = $sourcePriority[$existingSource] ?? 0;
                $currentPriority = $sourcePriority[$currentSource] ?? 0;
                
                if ($currentPriority > $existingPriority) {
                    // Replace with higher priority item - remove old keys first
                    foreach ($seenTrainings as $key => $training) {
                        if ($training === $existingItem) {
                            unset($seenTrainings[$key]);
                        }
                    }
                    
                    // Add new item with all its keys
                    foreach ($deduplicationKeys as $newKey) {
                        $seenTrainings[$newKey] = $item;
                    }
                    return true;
                } else {
                    // Skip this duplicate (lower or equal priority)
                    return false;
                }
            } else {
                // First time seeing this training - add with all keys
                foreach ($deduplicationKeys as $newKey) {
                    $seenTrainings[$newKey] = $item;
                }
                return true;
            }
        })->map(function($item) {
            return (object) $item;
        })->values(); // Reset array keys

        // Debug: Log final deduplicated results
        Log::info('DEBUG - Final deduplicated trainings:', [
            'employee_id' => $employeeId,
            'total_count' => $upcoming->count(),
            'trainings' => $upcoming->map(function($item) {
                return [
                    'upcoming_id' => $item->upcoming_id ?? 'N/A',
                    'training_title' => $item->training_title ?? 'N/A',
                    'source' => $item->source ?? 'N/A',
                    'course_id' => $item->course_id ?? 'NULL'
                ];
            })->toArray()
        ]);

        // Temporary debug output - remove after fixing
        if (request()->has('debug')) {
            dd([
                'manualUpcoming_count' => $manualUpcoming->count(),
                'adminAssigned_count' => $adminAssigned->count(),
                'competencyAssigned_count' => $competencyAssigned->count(),
                'final_upcoming_count' => $upcoming->count(),
                'all_trainings_before_dedup' => $allTrainings->map(function($item) {
                    $item = (object) $item;
                    return [
                        'training_title' => $item->training_title ?? 'N/A',
                        'source' => $item->source ?? 'N/A',
                        'course_id' => $item->course_id ?? 'NULL'
                    ];
                })->toArray(),
                'final_trainings' => $upcoming->map(function($item) {
                    return [
                        'upcoming_id' => $item->upcoming_id ?? 'N/A',
                        'training_title' => $item->training_title ?? 'N/A',
                        'source' => $item->source ?? 'N/A'
                    ];
                })->toArray()
            ]);
        }

        // Get manually added completed trainings
        $manualCompleted = CompletedTraining::where('employee_id', $employeeId)->get();

        // Get system-completed trainings from EmployeeTrainingDashboard
        $systemCompleted = EmployeeTrainingDashboard::with(['course', 'assignedBy'])
            ->where('employee_id', $employeeId)
            ->where(function($query) {
                $query->where('status', 'Completed')
                      ->orWhere('progress', '>=', 100);
            })
            ->get()
            ->map(function($training) {
                return (object)[
                    'completed_id' => 'system_' . $training->id,
                    'training_title' => $training->course->course_title ?? 'Unknown Course',
                    'completion_date' => $training->progress >= 100 ? $training->updated_at->format('Y-m-d') : $training->training_date,
                    'remarks' => 'Completed via system - Progress: ' . ($training->progress ?? 0) . '%',
                    'status' => 'Verified',
                    'certificate_path' => null,
                    'source' => 'system_completed',
                    'progress' => $training->progress ?? 0,
                    'assigned_by_name' => $training->assignedBy ? $training->assignedBy->name : 'System',
                    'training_date' => $training->training_date
                ];
            });

        // Get completed competency-based course assignments
        $competencyCompleted = CompetencyCourseAssignment::with(['course', 'assignedBy'])
            ->where('employee_id', $employeeId)
            ->where('status', 'Completed')
            ->orWhere(function($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)
                      ->where('progress', '>=', 100);
            })
            ->get()
            ->map(function($assignment) {
                return (object)[
                    'completed_id' => 'comp_completed_' . $assignment->id,
                    'training_title' => $assignment->course->course_title ?? 'Unknown Course',
                    'completion_date' => $assignment->progress >= 100 ? $assignment->updated_at : $assignment->assigned_date,
                    'remarks' => 'Completed competency-based training - Progress: ' . ($assignment->progress ?? 0) . '%',
                    'status' => 'Verified',
                    'certificate_path' => null,
                    'source' => 'competency_completed',
                    'progress' => $assignment->progress ?? 0,
                    'assigned_by_name' => $assignment->assignedBy ? $assignment->assignedBy->name : 'System',
                    'assigned_date' => $assignment->assigned_date
                ];
            });

        // Get completed destination knowledge training - exclude if already in manual
        $destinationCompleted = DestinationKnowledgeTraining::where('employee_id', $employeeId)
            ->where('status', 'completed')
            ->get()
            ->filter(function($training) use ($manualCompleted) {
                // Check if this destination training already exists in manual completed trainings
                $normalizedDestName = strtolower(trim(str_replace(['Training', 'Course', 'Program'], '', $training->destination_name)));

                foreach ($manualCompleted as $manual) {
                    $normalizedManualTitle = strtolower(trim(str_replace(['Training', 'Course', 'Program'], '', $manual->training_title)));
                    if ($normalizedDestName === $normalizedManualTitle) {
                        return false; // Skip this destination training as it's already in manual
                    }
                }
                return true;
            })
            ->map(function($training) {
                return (object)[
                    'completed_id' => 'dest_completed_' . $training->id,
                    'training_title' => $training->destination_name,
                    'completion_date' => $training->date_completed ? $training->date_completed : $training->updated_at,
                    'remarks' => 'Auto-completed destination knowledge training - Progress: 100%',
                    'status' => 'Verified',
                    'certificate_path' => null,
                    'source' => 'destination_completed',
                    'progress' => 100,
                    'course_id' => null
                ];
            });

        // Get completed Customer Service Sales Skills Training
        $customerServiceCompleted = CustomerServiceSalesSkillsTraining::with('training')
            ->where('employee_id', $employeeId)
            ->whereNotNull('date_completed')
            ->where('date_completed', '!=', '1970-01-01')
            ->get()
            ->map(function($training) {
                return (object)[
                    'completed_id' => 'customer_service_' . $training->id,
                    'training_title' => $training->skill_topic,
                    'completion_date' => $training->date_completed,
                    'remarks' => 'Completed customer service sales skills training',
                    'status' => 'Verified',
                    'certificate_path' => null,
                    'source' => 'customer_service_completed',
                    'progress' => 100,
                    'course_id' => $training->training_id
                ];
            });

        // Get completed training requests (100% progress)
        $completedRequests = TrainingRequest::with('course')
            ->where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->get()
            ->filter(function($request) use ($employeeId) {
                // Check if there's a corresponding training dashboard record with 100% progress
                $dashboardRecord = EmployeeTrainingDashboard::where('employee_id', $employeeId)
                    ->where('course_id', $request->course_id)
                    ->first();

                if ($dashboardRecord) {
                    $combinedProgress = ExamAttempt::calculateCombinedProgress($employeeId, $request->course_id);
                    $actualProgress = $combinedProgress > 0 ? $combinedProgress : ($dashboardRecord->progress ?? 0);
                    return $actualProgress >= 100;
                }
                return false;
            })
            ->map(function($request) use ($employeeId) {
                $dashboardRecord = EmployeeTrainingDashboard::where('employee_id', $employeeId)
                    ->where('course_id', $request->course_id)
                    ->first();

                return (object)[
                    'completed_id' => 'request_completed_' . $request->request_id,
                    'training_title' => $request->training_title,
                    'completion_date' => $dashboardRecord ? $dashboardRecord->updated_at->format('Y-m-d') : now()->format('Y-m-d'),
                    'remarks' => 'Completed training request - Progress: 100%',
                    'status' => 'Verified',
                    'certificate_path' => null,
                    'source' => 'request_completed',
                    'progress' => 100,
                    'course_id' => $request->course_id,
                    'request_id' => $request->request_id
                ];
            });

        // Enhanced deduplication with priority for Destination Knowledge training
        $allCompleted = collect();

        // Helper function for better title normalization - prioritize destination knowledge
        $normalizeTitle = function($title) {
            // Remove common training words and normalize
            $normalized = strtolower(trim(preg_replace('/\b(training|course|program|knowledge|destination)\b/i', '', $title)));
            // Remove extra spaces and dashes
            return preg_replace('/[\s\-]+/', '', $normalized);
        };

        // Helper function to check if destination knowledge version exists
        $hasDestinationKnowledgeVersion = function($title, $collection) use ($normalizeTitle) {
            $baseTitle = $normalizeTitle($title);
            foreach ($collection as $item) {
                if (stripos($item->training_title, 'Destination Knowledge') !== false) {
                    $destTitle = $normalizeTitle($item->training_title);
                    if ($destTitle === $baseTitle) {
                        return true;
                    }
                }
            }
            return false;
        };

        // Priority 1: Manual completed trainings (highest priority)
        foreach ($manualCompleted as $manual) {
            $allCompleted->push($manual);
        }

        // Priority 2: Destination Knowledge training (higher priority than Employee Training Dashboard)
        foreach ($destinationCompleted as $destination) {
            $destinationNormalized = $normalizeTitle($destination->training_title);
            $isDuplicate = false;

            foreach ($allCompleted as $existing) {
                $existingNormalized = $normalizeTitle($existing->training_title);
                if ($existingNormalized === $destinationNormalized) {
                    $isDuplicate = true;
                    break;
                }
            }

            if (!$isDuplicate) {
                $allCompleted->push($destination);
            }
        }

        // Priority 3: System completed (Employee Training Dashboard) - skip if destination knowledge exists
        foreach ($systemCompleted as $system) {
            $systemNormalized = $normalizeTitle($system->training_title);
            $isDuplicate = false;

            // Check against all existing records
            foreach ($allCompleted as $existing) {
                $existingNormalized = $normalizeTitle($existing->training_title);
                if ($existingNormalized === $systemNormalized) {
                    $isDuplicate = true;
                    break;
                }
            }

            // SPECIAL CHECK: Skip if destination knowledge version exists
            // This prevents "BAESA" from being added when "Destination Knowledge - BAESA" exists
            if (!$isDuplicate && $hasDestinationKnowledgeVersion($system->training_title, $destinationCompleted)) {
                $isDuplicate = true; // Skip this system training as destination knowledge version exists
            }

            if (!$isDuplicate) {
                $allCompleted->push($system);
            }
        }

        // Priority 4: Competency completed - skip if destination knowledge exists
        foreach ($competencyCompleted as $competency) {
            $competencyNormalized = $normalizeTitle($competency->training_title);
            $isDuplicate = false;

            foreach ($allCompleted as $existing) {
                $existingNormalized = $normalizeTitle($existing->training_title);
                if ($existingNormalized === $competencyNormalized) {
                    $isDuplicate = true;
                    break;
                }
            }

            // Skip if destination knowledge version exists
            if (!$isDuplicate && $hasDestinationKnowledgeVersion($competency->training_title, $destinationCompleted)) {
                $isDuplicate = true;
            }

            if (!$isDuplicate) {
                $allCompleted->push($competency);
            }
        }

        // Priority 5: Customer service completed - skip if destination knowledge exists
        foreach ($customerServiceCompleted as $customerService) {
            $customerServiceNormalized = $normalizeTitle($customerService->training_title);
            $isDuplicate = false;

            foreach ($allCompleted as $existing) {
                $existingNormalized = $normalizeTitle($existing->training_title);
                if ($existingNormalized === $customerServiceNormalized) {
                    $isDuplicate = true;
                    break;
                }
            }

            // Skip if destination knowledge version exists
            if (!$isDuplicate && $hasDestinationKnowledgeVersion($customerService->training_title, $destinationCompleted)) {
                $isDuplicate = true;
            }

            if (!$isDuplicate) {
                $allCompleted->push($customerService);
            }
        }

        // Priority 6: Training requests - lowest priority, skip if destination knowledge exists
        foreach ($completedRequests as $completedRequest) {
            $requestNormalized = $normalizeTitle($completedRequest->training_title);
            $isDuplicate = false;

            foreach ($allCompleted as $existing) {
                $existingNormalized = $normalizeTitle($existing->training_title);
                if ($existingNormalized === $requestNormalized) {
                    $isDuplicate = true;
                    break;
                }
            }

            // Skip if destination knowledge version exists
            if (!$isDuplicate && $hasDestinationKnowledgeVersion($completedRequest->training_title, $destinationCompleted)) {
                $isDuplicate = true;
            }

            if (!$isDuplicate) {
                $allCompleted->push($completedRequest);
            }
        }

        // Final deduplication pass using Laravel collections
        $completed = $allCompleted->unique(function($item) use ($normalizeTitle) {
            return $normalizeTitle($item->training_title);
        })->sortByDesc('completion_date')->values();

        // COMPREHENSIVE DEBUG LOGGING FOR COMPLETED TRAININGS
        Log::info('=== COMPLETED TRAININGS DEBUG ===', [
            'employee_id' => $employeeId,
            'manual_completed_count' => $manualCompleted->count(),
            'system_completed_count' => $systemCompleted->count(),
            'destination_completed_count' => $destinationCompleted->count(),
            'customer_service_completed_count' => $customerServiceCompleted->count(),
            'completed_requests_count' => $completedRequests->count(),
            'all_completed_count' => $allCompleted->count(),
            'final_completed_count' => $completed->count()
        ]);

        // Log details of each source
        if ($manualCompleted->count() > 0) {
            Log::info('Manual Completed Trainings:', $manualCompleted->map(function($item) {
                return [
                    'id' => $item->completed_id ?? 'N/A',
                    'title' => $item->training_title ?? 'N/A',
                    'date' => $item->completion_date ?? 'N/A',
                    'status' => $item->status ?? 'N/A'
                ];
            })->toArray());
        }

        if ($systemCompleted->count() > 0) {
            Log::info('System Completed Trainings:', $systemCompleted->map(function($item) {
                return [
                    'title' => $item->training_title ?? 'N/A',
                    'progress' => $item->progress ?? 'N/A',
                    'date' => $item->completion_date ?? 'N/A'
                ];
            })->toArray());
        }

        if ($destinationCompleted->count() > 0) {
            Log::info('Destination Completed Trainings:', $destinationCompleted->map(function($item) {
                return [
                    'title' => $item->training_title ?? 'N/A',
                    'date' => $item->completion_date ?? 'N/A'
                ];
            })->toArray());
        }

        if ($completedRequests->count() > 0) {
            Log::info('Completed Training Requests:', $completedRequests->map(function($item) {
                return [
                    'title' => $item->training_title ?? 'N/A',
                    'progress' => $item->progress ?? 'N/A',
                    'date' => $item->completion_date ?? 'N/A'
                ];
            })->toArray());
        }

        if ($completed->count() > 0) {
            Log::info('Final Completed Trainings for Feedback:', $completed->map(function($item) {
                return [
                    'title' => $item->training_title ?? 'N/A',
                    'source' => $item->source ?? 'N/A',
                    'date' => $item->completion_date ?? 'N/A'
                ];
            })->toArray());
        } else {
            Log::warning('NO COMPLETED TRAININGS FOUND FOR FEEDBACK FORM', [
                'employee_id' => $employeeId,
                'manual_count' => $manualCompleted->count(),
                'system_count' => $systemCompleted->count(),
                'destination_count' => $destinationCompleted->count(),
                'requests_count' => $completedRequests->count()
            ]);
        }

        // Debug logging for ITALY duplicates (remove after fixing)
        $italyRecords = $completed->filter(function($item) {
            return stripos($item->training_title, 'italy') !== false;
        });
        if ($italyRecords->count() > 1) {
            Log::info('ITALY duplicates found in completed trainings:', [
                'employee_id' => $employeeId,
                'count' => $italyRecords->count(),
                'records' => $italyRecords->map(function($item) {
                    return [
                        'title' => $item->training_title,
                        'source' => $item->source,
                        'date' => $item->completion_date
                    ];
                })->toArray()
            ]);
        }

        // Get dashboard training progress
        $dashboardProgress = EmployeeTrainingDashboard::where('employee_id', $employeeId)
            ->whereIn('status', ['In Progress', 'Not Started'])
            ->where('progress', '<', 100)
            ->get()
            ->map(function($training) use ($employeeId) {
                $combinedProgress = ExamAttempt::calculateCombinedProgress($employeeId, $training->course_id);
                $displayProgress = $combinedProgress > 0 ? $combinedProgress : ($training->progress ?? 0);

                return (object)[
                    'progress_id' => 'dashboard_' . $training->id,
                    'training_title' => $training->course->course_title ?? 'Unknown Course',
                    'progress_percentage' => $displayProgress,
                    'last_updated' => $training->updated_at->format('Y-m-d H:i'),
                    'status' => $displayProgress >= 100 ? 'Completed' : ($displayProgress > 0 ? 'In Progress' : 'Not Started'),
                    'source' => 'dashboard_progress',
                    'course_id' => $training->course_id
                ];
            });

        // Get manual training progress (legacy)
        $manualProgress = TrainingProgress::where('employee_id', $employeeId)->get();

        // Get approved training requests that should appear in progress
        $approvedRequests = TrainingRequest::with('course')
            ->where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->get()
            ->map(function($request) use ($employeeId) {
                // Check if there's a corresponding training dashboard record for this course
                $dashboardRecord = EmployeeTrainingDashboard::where('employee_id', $employeeId)
                    ->where('course_id', $request->course_id)
                    ->first();

                // If dashboard record exists, use its progress; otherwise default to 0
                $actualProgress = 0;
                $lastUpdated = $request->updated_at->format('Y-m-d');
                $canStartExam = true;

                if ($dashboardRecord) {
                    // Calculate combined exam/quiz progress instead of using raw progress
                    $combinedProgress = ExamAttempt::calculateCombinedProgress($employeeId, $request->course_id);

                    // Use combined progress if available, otherwise fall back to dashboard progress
                    $actualProgress = $combinedProgress > 0 ? $combinedProgress : ($dashboardRecord->progress ?? 0);
                    $lastUpdated = $dashboardRecord->updated_at->format('Y-m-d H:i');
                    $canStartExam = $actualProgress >= 75;
                }

                // Calculate expired date for approved requests
                $expiredDate = null;
                if ($request->course && $request->course->expired_date) {
                    $expiredDate = $request->course->expired_date;
                } else {
                    // Set default expiration (90 days from request date)
                    $expiredDate = \Carbon\Carbon::parse($request->requested_date)->addDays(90)->format('Y-m-d H:i:s');
                }

                return (object)[
                    'progress_id' => 'request_' . $request->request_id,
                    'training_title' => $request->training_title,
                    'progress_percentage' => $actualProgress,
                    'last_updated' => $lastUpdated,
                    'status' => $actualProgress >= 100 ? 'Completed' : ($actualProgress > 0 ? 'In Progress' : 'Ready to Start'),
                    'source' => 'approved_request',
                    'course_id' => $request->course_id,
                    'request_id' => $request->request_id,
                    'can_start_exam' => $canStartExam,
                    'exam_quiz_scores' => $dashboardRecord ? ExamAttempt::getBestScores($employeeId, $request->course_id) : null,
                    'expired_date' => $expiredDate
                ];
            });

        // Combine all progress sources and deduplicate
        $allProgress = $dashboardProgress->concat($manualProgress)->concat($approvedRequests);

        // Deduplicate based on course_id and training title to prevent duplicates
        $progress = $allProgress->unique(function ($item) {
            // Create unique key based on course_id (if available) or normalized training title
            if (isset($item->course_id) && $item->course_id) {
                return 'course_' . $item->course_id . '_' . $item->source;
            }
            // Fallback to normalized training title for manual entries
            $normalizedTitle = strtolower(trim(str_replace(['Training', 'Course', 'Program'], '', $item->training_title)));
            return 'title_' . $normalizedTitle . '_' . $item->source;
        })
        ->groupBy(function($item) {
            // Group by course_id or normalized title to merge duplicates
            if (isset($item->course_id) && $item->course_id) {
                return 'course_' . $item->course_id;
            }
            $normalizedTitle = strtolower(trim(str_replace(['Training', 'Course', 'Program'], '', $item->training_title)));
            return 'title_' . $normalizedTitle;
        })
        ->map(function($group) {
            // For each group, return the record with highest progress
            return $group->sortByDesc('progress_percentage')->first();
        })
        ->values();
        $feedback = TrainingFeedback::where('employee_id', $employeeId)->get();
        $notifications = TrainingNotification::where('employee_id', $employeeId)->get();

        // Get training requests for the employee with course relationship
        $trainingRequests = TrainingRequest::with('course')->where('employee_id', $employeeId)->get();

        // Get readiness rating for the employee
        $readinessRatingRecord = SuccessionReadinessRating::where('employee_id', $employeeId)->first();
        $readinessRating = $readinessRatingRecord ? $readinessRatingRecord->readiness_score : 0;

        // Get available courses for training requests
        $availableCourses = CourseManagement::where('status', 'Active')->get();

        // Get employee's competency gaps for course recommendations
        $competencyGaps = CompetencyGap::with('competency')
            ->where('employee_id', $employeeId)
            ->where('gap', '>', 0)
            ->get();

        // Get recommended courses based on competency gaps
        $recommendedCourses = collect();
        foreach ($competencyGaps as $gap) {
            $relatedCourses = CourseManagement::where('course_title', 'LIKE', '%' . $gap->competency->competency_name . '%')
                ->where('status', 'Active')
                ->get();
            $recommendedCourses = $recommendedCourses->merge($relatedCourses);
        }

        // Remove duplicates and already assigned/requested courses
        $recommendedCourses = $recommendedCourses->unique('course_id')->filter(function($course) use ($employeeId) {
            $alreadyAssigned = EmployeeTrainingDashboard::where('employee_id', $employeeId)
                ->where('course_id', $course->course_id)
                ->exists();
            $alreadyRequested = TrainingRequest::where('employee_id', $employeeId)
                ->where('course_id', $course->course_id)
                ->exists();
            return !$alreadyAssigned && !$alreadyRequested;
        });

        // Prepare completed trainings for feedback form
        $completedTrainings = $completed->map(function($training) {
            return (object)[
                'id' => $training->completed_id ?? $training->id ?? 'unknown',
                'course_title' => $training->training_title,
                'training_title' => $training->training_title, // Add this for consistency
                'progress' => $training->progress ?? 100,
                'completion_date' => $training->completion_date ?? now(),
                'course_id' => $training->course_id ?? null
            ];
        });

        // FALLBACK: If no completed trainings found, check for 100% progress trainings
        if ($completedTrainings->count() === 0) {
            Log::info('No completed trainings found, checking for 100% progress trainings...');
            
            // Check training dashboard for 100% progress
            $progressCompleted = EmployeeTrainingDashboard::with('course')
                ->where('employee_id', $employeeId)
                ->where('progress', '>=', 100)
                ->get()
                ->map(function($training) {
                    return (object)[
                        'id' => 'progress_' . $training->id,
                        'course_title' => $training->course->course_title ?? 'Unknown Course',
                        'training_title' => $training->course->course_title ?? 'Unknown Course',
                        'progress' => $training->progress ?? 100,
                        'completion_date' => $training->updated_at ?? now(),
                        'course_id' => $training->course_id
                    ];
                });
            
            // Check for any training requests with 100% exam progress
            $examCompleted = TrainingRequest::with('course')
                ->where('employee_id', $employeeId)
                ->where('status', 'Approved')
                ->get()
                ->filter(function($request) use ($employeeId) {
                    $combinedProgress = ExamAttempt::calculateCombinedProgress($employeeId, $request->course_id);
                    return $combinedProgress >= 100;
                })
                ->map(function($request) use ($employeeId) {
                    return (object)[
                        'id' => 'exam_' . $request->request_id,
                        'course_title' => $request->training_title,
                        'training_title' => $request->training_title,
                        'progress' => 100,
                        'completion_date' => now(),
                        'course_id' => $request->course_id
                    ];
                });
            
            $completedTrainings = $progressCompleted->concat($examCompleted);
            
            Log::info('FALLBACK COMPLETED TRAININGS FOUND:', [
                'employee_id' => $employeeId,
                'progress_completed_count' => $progressCompleted->count(),
                'exam_completed_count' => $examCompleted->count(),
                'total_fallback_count' => $completedTrainings->count()
            ]);
        }

        // DEBUG: Log the final completedTrainings data for feedback form
        Log::info('COMPLETED TRAININGS FOR FEEDBACK FORM:', [
            'employee_id' => $employeeId,
            'count' => $completedTrainings->count(),
            'trainings' => $completedTrainings->map(function($training) {
                return [
                    'id' => $training->id,
                    'course_title' => $training->course_title,
                    'training_title' => $training->training_title ?? 'N/A',
                    'progress' => $training->progress,
                    'completion_date' => $training->completion_date,
                    'course_id' => $training->course_id
                ];
            })->toArray()
        ]);

        return view('employee_ess_modules.my_trainings.index', compact(
            'upcoming', 'completed', 'progress', 'feedback', 'notifications',
            'trainingRequests', 'readinessRating', 'availableCourses', 'recommendedCourses',
            'completedTrainings'
        ))->with('upcomingTrainings', $upcoming);
    }

    public function store(Request $request)
    {
        // Ensure required tables exist before storing
        $this->ensureTrainingRequestsTableExists();
        $this->ensureTrainingProgressTableExists();
        $this->ensureTrainingNotificationsTableExists();

        $employeeId = Auth::user()->employee_id;

        // Log incoming request for debugging
        Log::info('MyTrainingController store method called', [
            'employee_id' => $employeeId,
            'request_data' => $request->all()
        ]);

        // Determine what type of data is being submitted based on the presence of specific fields
        if ($request->has('reason')) {
            // Training Request submission - reason field is unique to training requests
            try {
                $request->validate([
                    'training_title' => 'required|string|max:255',
                    'reason' => 'required|string|max:1000',
                    'requested_date' => 'required|date',
                    'course_id' => 'nullable', // Allow both string and integer, we'll handle conversion below
                    'password' => 'required|string|min:3'
                ]);

                // Verify employee password
                if (!$this->verifyEmployeePassword($request->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid password. Please enter your correct password.'
                    ], 401);
                }

                // Handle course_id conversion - convert numeric strings to integers, non-numeric to null
                $courseId = null;
                if ($request->course_id) {
                    if (is_numeric($request->course_id)) {
                        $courseId = (int) $request->course_id;
                    } else {
                        // If course_id is a string (like training title), set to null
                        $courseId = null;
                    }
                }

                $trainingRequest = TrainingRequest::create([
                    'employee_id' => $employeeId,
                    'course_id' => $courseId,
                    'training_title' => $request->training_title,
                    'reason' => $request->reason,
                    'status' => 'Pending',
                    'requested_date' => $request->requested_date
                ]);

                // Log activity
                ActivityLog::createLog([
                    'module' => 'Training Management',
                    'action' => 'Training Request Submitted',
                    'description' => "Submitted training request for: {$request->training_title}",
                    'model_type' => 'TrainingRequest',
                    'model_id' => $trainingRequest->request_id
                ]);

                Log::info('Training request created successfully', [
                    'request_id' => $trainingRequest->request_id,
                    'employee_id' => $employeeId,
                    'training_title' => $request->training_title
                ]);

                // Return JSON for AJAX requests, redirect for regular form submissions
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Training request submitted successfully!'
                    ]);
                }

                return redirect()->back()->with('success', 'Training request submitted successfully!');

            } catch (\Exception $e) {
                Log::error('Training request submission failed', [
                    'employee_id' => $employeeId,
                    'error' => $e->getMessage(),
                    'request_data' => $request->all()
                ]);

                // Return JSON for AJAX requests, redirect for regular form submissions
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to submit training request: ' . $e->getMessage()
                    ], 500);
                }

                return redirect()->back()->with('error', 'Failed to submit training request: ' . $e->getMessage());
            }
        }

        elseif ($request->has('completion_date')) {
            // Completed Training submission
            $request->validate([
                'training_title' => 'required|string|max:255',
                'completion_date' => 'required|date',
                'remarks' => 'nullable|string|max:500'
            ]);

            $completedTraining = CompletedTraining::create([
                'employee_id' => $employeeId,
                'training_title' => $request->training_title,
                'completion_date' => $request->completion_date,
                'remarks' => $request->remarks ?? 'Self-reported completed training',
                'status' => 'Pending Verification'
            ]);

            // Log activity
            ActivityLog::createLog([
                'module' => 'Training Management',
                'action' => 'Completed Training Added',
                'description' => "Added completed training: {$request->training_title}",
                'model_type' => 'CompletedTraining',
                'model_id' => $completedTraining->completed_id
            ]);

            return redirect()->back()->with('success', 'Completed training added successfully!');
        }

        elseif ($request->has('progress_percentage')) {
            // Training Progress submission
            $request->validate([
                'training_title' => 'required|string|max:255',
                'progress_percentage' => 'required|integer|min:0|max:100',
                'last_updated' => 'required|date'
            ]);

            $trainingProgress = TrainingProgress::create([
                'employee_id' => $employeeId,
                'training_title' => $request->training_title,
                'progress_percentage' => $request->progress_percentage,
                'last_updated' => $request->last_updated,
                'status' => $request->progress_percentage >= 100 ? 'Completed' : 'In Progress'
            ]);

            // Log activity
            ActivityLog::createLog([
                'module' => 'Training Management',
                'action' => 'Training Progress Updated',
                'description' => "Updated progress for {$request->training_title}: {$request->progress_percentage}%",
                'model_type' => 'TrainingProgress',
                'model_id' => $trainingProgress->progress_id
            ]);

            return redirect()->back()->with('success', 'Training progress added successfully!');
        }

        else {
            // Invalid submission - missing required fields to determine type
            return redirect()->back()->with('error', 'Invalid form submission. Please try again.');
        }
    }

    public function update(Request $request, $id)
    {
        // Ensure required tables exist before updating
        $this->ensureTrainingRequestsTableExists();

        $employeeId = Auth::user()->employee_id;

        try {
            $request->validate([
                'training_title' => 'required|string|max:255',
                'reason' => 'required|string|max:1000',
                'requested_date' => 'required|date',
                'status' => 'required|string|in:Pending,Approved,Rejected',
                'password' => 'required|string|min:3'
            ]);

            // Verify employee password
            if (!$this->verifyEmployeePassword($request->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password. Please enter your correct password.'
                ], 401);
            }

            // Find the training request
            $trainingRequest = TrainingRequest::where('request_id', $id)
                ->where('employee_id', $employeeId)->first();

            if (!$trainingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Training request not found or you do not have permission to edit it.'
                ], 404);
            }

            // Update the training request
            $trainingRequest->update([
                'training_title' => $request->training_title,
                'reason' => $request->reason,
                'status' => $request->status,
                'requested_date' => $request->requested_date
            ]);

            // Log activity
            ActivityLog::createLog([
                'module' => 'Training Management',
                'action' => 'Training Request Updated',
                'description' => "Employee {$employeeId} updated training request: {$request->training_title}",
                'employee_id' => $employeeId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Training request updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating training request', [
                'employee_id' => $employeeId,
                'request_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update training request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        // Ensure required tables exist before deleting
        $this->ensureTrainingRequestsTableExists();
        $this->ensureTrainingProgressTableExists();

        $employeeId = Auth::user()->employee_id;
        $isAjax = request()->ajax();

        // Verify password for security
        $requestData = json_decode(request()->getContent(), true);
        if (!isset($requestData['password'])) {
            return response()->json([
                'success' => false,
                'message' => 'Password is required for this action.'
            ], 400);
        }

        if (!$this->verifyEmployeePassword($requestData['password'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password. Please enter your correct password.'
            ], 401);
        }

        // 1. Dashboard training records (direct dashboard_id or format: dashboard_X)
        if (is_numeric($id) || str_starts_with($id, 'dashboard_')) {
            $dashboardId = str_starts_with($id, 'dashboard_') ? str_replace('dashboard_', '', $id) : $id;
            $dashboardRecord = EmployeeTrainingDashboard::where('id', $dashboardId)
                ->where('employee_id', $employeeId)->first();
            if ($dashboardRecord) {
                $dashboardRecord->delete();
                if ($isAjax) {
                    return response()->json(['success' => true, 'message' => 'Training progress deleted successfully!']);
                }
                return redirect()->back()->with('success', 'Training progress deleted successfully!');
            }
        }

        // 2. Training requests (direct request_id)
        $trainingRequest = TrainingRequest::where('request_id', $id)
            ->where('employee_id', $employeeId)->first();
        if ($trainingRequest) {
            // Check if this is an approved request deletion
            $deleteApproved = isset($requestData['delete_approved']) && $requestData['delete_approved'];
            
            if ($trainingRequest->status == 'Approved' && $deleteApproved) {
                // Enhanced deletion for approved requests - remove related records
                try {
                    // Find course_id for this training request
                    $courseId = $trainingRequest->course_id;
                    if (!$courseId && $trainingRequest->training_title) {
                        $course = \App\Models\CourseManagement::where('course_title', $trainingRequest->training_title)->first();
                        $courseId = $course ? $course->course_id : null;
                    }
                    
                    if ($courseId) {
                        // Delete related dashboard records
                        EmployeeTrainingDashboard::where('employee_id', $employeeId)
                            ->where('course_id', $courseId)
                            ->where('source', 'approved_request')
                            ->delete();
                        
                        // Delete related exam attempts
                        \App\Models\ExamAttempt::where('employee_id', $employeeId)
                            ->where('course_id', $courseId)
                            ->delete();
                        
                        Log::info('Deleted approved training request with related records', [
                            'request_id' => $id,
                            'employee_id' => $employeeId,
                            'course_id' => $courseId,
                            'training_title' => $trainingRequest->training_title
                        ]);
                    }
                    
                    // Delete the training request
                    $trainingRequest->delete();
                    
                    // Log activity
                    ActivityLog::createLog([
                        'module' => 'Training Management',
                        'action' => 'Delete Approved Training Request',
                        'description' => "Deleted approved training request: {$trainingRequest->training_title} (ID: {$id}) with all related records",
                        'model_type' => 'TrainingRequest',
                        'model_id' => $id
                    ]);
                    
                    if ($isAjax) {
                        return response()->json([
                            'success' => true, 
                            'message' => 'Approved training request and all related records deleted successfully!'
                        ]);
                    }
                    return redirect()->back()->with('success', 'Approved training request and all related records deleted successfully!');
                    
                } catch (\Exception $e) {
                    Log::error('Error deleting approved training request', [
                        'request_id' => $id,
                        'employee_id' => $employeeId,
                        'error' => $e->getMessage()
                    ]);
                    
                    if ($isAjax) {
                        return response()->json([
                            'success' => false, 
                            'message' => 'Error deleting approved training request: ' . $e->getMessage()
                        ], 500);
                    }
                    return redirect()->back()->with('error', 'Error deleting approved training request: ' . $e->getMessage());
                }
            } else {
                // Regular deletion for pending/rejected requests
                $trainingRequest->delete();
                
                // Log activity
                ActivityLog::createLog([
                    'module' => 'Training Management',
                    'action' => 'Delete Training Request',
                    'description' => "Deleted training request: {$trainingRequest->training_title} (ID: {$id})",
                    'model_type' => 'TrainingRequest',
                    'model_id' => $id
                ]);
                
                if ($isAjax) {
                    return response()->json(['success' => true, 'message' => 'Training request deleted successfully!']);
                }
                return redirect()->back()->with('success', 'Training request deleted successfully!');
            }
        }

        // 3. Request-based progress records (format: request_X)
        elseif (str_starts_with($id, 'request_')) {
            $requestId = str_replace('request_', '', $id);
            $record = TrainingRequest::where('request_id', $requestId)
                ->where('employee_id', $employeeId)->first();
            if ($record) {
                $record->delete();
                if ($isAjax) {
                    return response()->json(['success' => true, 'message' => 'Training request deleted successfully!']);
                }
                return redirect()->back()->with('success', 'Training request deleted successfully!');
            }
        }

        // 4. Manual progress records (numeric IDs)
        else {
            $record = TrainingProgress::where('progress_id', $id)
                ->where('employee_id', $employeeId)->first();
            if ($record) {
                $record->delete();
                if ($isAjax) {
                    return response()->json(['success' => true, 'message' => 'Training progress deleted successfully!']);
                }
                return redirect()->back()->with('success', 'Training progress deleted successfully!');
            }
        }

        // 4. Try completed trainings
        $completed = CompletedTraining::where('completed_id', $id)->where('employee_id', $employeeId)->first();
        if ($completed) {
            $completed->delete();
            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'Training record deleted successfully!']);
            }
            return redirect()->back()->with('success', 'Training record deleted successfully!');
        }

        // 5. Try upcoming trainings
        $upcoming = UpcomingTraining::where('upcoming_id', $id)->where('employee_id', $employeeId)->first();
        if ($upcoming) {
            $upcoming->delete();
            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'Upcoming training deleted successfully!']);
            }
            return redirect()->back()->with('success', 'Upcoming training deleted successfully!');
        }

        if ($isAjax) {
            return response()->json(['success' => false, 'error' => 'Record not found!']);
        }
        return redirect()->back()->with('error', 'Record not found!');
    }

    /**
     * Get reviewer content based on exam questions
     */
    public function getReviewerContent($courseId)
    {
        try {
            Log::info("Getting reviewer content for course ID: {$courseId}");

            // First check if any questions exist at all
            $allQuestions = ExamQuestion::all();
            Log::info("Total questions in database: " . $allQuestions->count());

            // Get all exam questions for this specific course
            $examQuestions = ExamQuestion::where('course_id', $courseId)
                ->where('is_active', true)
                ->get();

            Log::info("Questions found for course {$courseId}: " . $examQuestions->count());

            // If no questions exist, try to generate them
            if ($examQuestions->isEmpty()) {
                Log::info("No questions found, attempting to generate...");

                // Try to generate questions using AI service
                if (class_exists('App\Services\AIQuestionGeneratorService')) {
                    $aiService = new AIQuestionGeneratorService();
                } else {
                    Log::warning('AIQuestionGeneratorService not found');
                    return response()->json([
                        'success' => false,
                        'error' => 'AI Question Generator Service not available',
                        'course_id' => $courseId
                    ]);
                }
                $course = CourseManagement::find($courseId);

                if ($course && isset($aiService)) {
                    Log::info("Course found: " . $course->course_title);

                    try {
                        // Generate exam questions
                        $examResult = $aiService->generateQuestionsForCourse($courseId, 'exam', 10);
                        Log::info("Exam generation result: " . json_encode($examResult));

                        // Generate quiz questions
                        $quizResult = $aiService->generateQuestionsForCourse($courseId, 'quiz', 5);
                        Log::info("Quiz generation result: " . json_encode($quizResult));

                        // Fetch the newly generated questions
                        $examQuestions = ExamQuestion::where('course_id', $courseId)
                            ->where('is_active', true)
                            ->get();

                        Log::info("Questions after generation: " . $examQuestions->count());
                    } catch (\Exception $e) {
                        Log::error("Error generating questions: " . $e->getMessage());
                    }
                } else {
                    Log::error("Course not found with ID: {$courseId} or AI service unavailable");
                }
            }

            // Separate exam and quiz questions for display
            $examQuestionsOnly = $examQuestions->where('type', 'exam');
            $quizQuestionsOnly = $examQuestions->where('type', 'quiz');

            // Prepare actual questions for review
            $reviewQuestions = $examQuestions->map(function($question) {
                return [
                    'id' => $question->id,
                    'type' => $question->type ?? 'exam',
                    'question' => $question->question,
                    'options' => $question->options ?? [],
                    'correct_answer' => $question->correct_answer,
                    'explanation' => $question->explanation ?? 'No explanation provided',
                    'points' => $question->points ?? 1
                ];
            });

            $response = [
                'success' => true,
                'course_id' => $courseId,
                'exam_questions' => $examQuestionsOnly->values(),
                'quiz_questions' => $quizQuestionsOnly->values(),
                'review_questions' => $reviewQuestions->values(),
                'total_questions' => $examQuestions->count(),
                'exam_count' => $examQuestionsOnly->count(),
                'quiz_count' => $quizQuestionsOnly->count(),
                'debug_info' => [
                    'total_db_questions' => $allQuestions->count(),
                    'course_questions' => $examQuestions->count(),
                    'course_title' => $course->course_title ?? 'Unknown'
                ]
            ];

            Log::info("Reviewer response: " . json_encode($response));
            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Reviewer content error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'course_id' => $courseId,
                'stack_trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Generate study materials from exam questions
     */
    private function generateStudyMaterials($examQuestions, $quizQuestions)
    {
        $allQuestions = $examQuestions->merge($quizQuestions);

        $keyLearningPoints = [];
        $studyTips = [];
        $practiceAreas = [];

        foreach ($allQuestions as $question) {
            // Extract key concepts from questions
            $questionText = $question->question;

            // Generate learning points based on question content
            if (stripos($questionText, 'communication') !== false) {
                $keyLearningPoints[] = 'Effective communication principles and techniques';
                $studyTips[] = 'Practice active listening and clear verbal communication';
            }

            if (stripos($questionText, 'leadership') !== false) {
                $keyLearningPoints[] = 'Leadership styles and management approaches';
                $studyTips[] = 'Study different leadership theories and their applications';
            }

            if (stripos($questionText, 'customer') !== false || stripos($questionText, 'service') !== false) {
                $keyLearningPoints[] = 'Customer service excellence and relationship management';
                $studyTips[] = 'Focus on customer satisfaction strategies and conflict resolution';
            }

            if (stripos($questionText, 'technical') !== false || stripos($questionText, 'system') !== false) {
                $keyLearningPoints[] = 'Technical skills and system operations';
                $studyTips[] = 'Review technical documentation and practice hands-on exercises';
            }

            // Extract practice areas from correct answers
            $practiceAreas[] = [
                'topic' => $this->extractTopicFromQuestion($questionText),
                'explanation' => $question->explanation ?? 'Review this concept thoroughly'
            ];
        }

        return [
            'key_learning_points' => array_unique($keyLearningPoints),
            'study_tips' => array_unique($studyTips),
            'practice_areas' => array_slice($practiceAreas, 0, 10), // Limit to 10
            'sample_questions' => $allQuestions->take(5)->map(function($q) {
                return [
                    'question' => $q->question,
                    'explanation' => $q->explanation
                ];
            })
        ];
    }

    /**
     * Extract topic from question text
     */
    private function extractTopicFromQuestion($questionText)
    {
        // Simple topic extraction logic
        if (stripos($questionText, 'communication') !== false) return 'Communication Skills';
        if (stripos($questionText, 'leadership') !== false) return 'Leadership & Management';
        if (stripos($questionText, 'customer') !== false) return 'Customer Service';
        if (stripos($questionText, 'technical') !== false) return 'Technical Knowledge';
        if (stripos($questionText, 'safety') !== false) return 'Safety Procedures';

        return 'General Knowledge';
    }

    /**
     * Mark training as reviewed
     */
    public function markAsReviewed(Request $request)
    {
        try {
            $employeeId = Auth::user()->employee_id;
            $courseId = $request->input('course_id');
            $trainingTitle = $request->input('training_title');

            // Create or update training review record
            $review = TrainingReview::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'course_id' => $courseId
                ],
                [
                    'training_title' => $trainingTitle,
                    'reviewed_at' => now(),
                    'review_status' => 'completed'
                ]
            );

            // Log activity
            ActivityLog::create([
                'employee_id' => $employeeId,
                'action' => 'Training Review Completed',
                'description' => "Completed review for training: {$trainingTitle}",
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Training marked as reviewed successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function exportPdf()
    {
        $employeeId = Auth::user()->employee_id;
        $completed = CompletedTraining::where('employee_id', $employeeId)->get();

        // Simple PDF generation (you can enhance this with a proper PDF library)
        $html = '<h1>Training Records</h1><table border="1"><tr><th>Training Title</th><th>Completion Date</th><th>Status</th></tr>';
        foreach ($completed as $training) {
            $html .= '<tr><td>' . $training->training_title . '</td><td>' . $training->completion_date . '</td><td>' . $training->status . '</td></tr>';
        }
        $html .= '</table>';

        return response($html)->header('Content-Type', 'text/html');
    }

    public function exportExcel()
    {
        $employeeId = Auth::user()->employee_id;
        $completed = CompletedTraining::where('employee_id', $employeeId)->get();

        $csv = "Training Title,Completion Date,Status\n";
        foreach ($completed as $training) {
            $csv .= '"' . $training->training_title . '","' . $training->completion_date . '","' . $training->status . '"' . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="training_records.csv"');
    }

    public function downloadCertificate($id)
    {
        $employeeId = Auth::user()->employee_id;
        $completed = CompletedTraining::where('completed_id', $id)->where('employee_id', $employeeId)->first();

        if ($completed && $completed->certificate_path) {
            $filePath = storage_path('app/public/' . $completed->certificate_path);
            if (file_exists($filePath)) {
                return response()->download($filePath);
            }
        }

        return redirect()->back()->with('error', 'Certificate not found!');
    }

    /**
     * Accept destination knowledge training assignment
     */
    public function acceptDestinationTraining(Request $request)
    {
        try {
            $request->validate([
                'training_id' => 'required|integer|exists:destination_knowledge_trainings,id'
            ]);

            $employeeId = Auth::user()->employee_id;
            $training = DestinationKnowledgeTraining::findOrFail($request->training_id);

            // Verify this training belongs to the authenticated employee
            if ($training->employee_id !== $employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to training record.'
                ], 403);
            }

            // Check delivery mode for auto-completion logic
            $isOnlineTraining = $training->delivery_mode === 'Online Training';

            if ($isOnlineTraining) {
                // For Online Training: Set as in-progress (requires progress tracking)
                $training->is_active = true;
                $training->status = 'in-progress';
                $training->progress = 0; // Start with 0% progress
                $message = 'Training accepted successfully! You can now begin your online training.';

                Log::info("Online training accepted - requires progress tracking", [
                    'employee_id' => $employeeId,
                    'training_id' => $training->id,
                    'delivery_mode' => $training->delivery_mode
                ]);
            } else {
                // For non-Online Training: Auto-complete immediately
                $training->is_active = true;
                $training->status = 'completed';
                $training->progress = 100; // Set to 100% complete
                $training->date_completed = now();
                $message = 'Training accepted and completed successfully! Since this is a ' . $training->delivery_mode . ', it has been automatically marked as completed.';

                // Create completed training record for employee's completed section
                $this->createCompletedTrainingRecord($training);

                // Sync with other systems (Employee Training Dashboard, Competency Profile)
                $this->syncCompletedTrainingWithSystems($training);

                // Generate certificate for completed training
                $this->generateCertificateForTraining($training);

                Log::info("Non-online training auto-completed upon acceptance", [
                    'employee_id' => $employeeId,
                    'training_id' => $training->id,
                    'delivery_mode' => $training->delivery_mode,
                    'auto_completed' => true
                ]);
            }

            $training->save();

            // Update the upcoming training status to "Completed to Assign"
            $upcomingTraining = \App\Models\UpcomingTraining::where('employee_id', $employeeId)
                ->where('destination_training_id', $training->id)
                ->first();

            if ($upcomingTraining) {
                $upcomingTraining->status = 'Completed to Assign';
                $upcomingTraining->needs_response = false; // No longer needs response since it's accepted
                $upcomingTraining->save();

                Log::info("Updated upcoming training status to 'Completed to Assign'", [
                    'upcoming_training_id' => $upcomingTraining->upcoming_id,
                    'employee_id' => $employeeId,
                    'training_id' => $training->id
                ]);
            }

            // Create notification for admin
            TrainingNotification::create([
                'employee_id' => 'ADMIN',
                'message' => "Employee {$employeeId} has accepted the destination training: {$training->destination_name}" .
                           ($isOnlineTraining ? "" : " (Auto-completed - {$training->delivery_mode})"),
                'sent_at' => now()
            ]);

            // Log activity
            ActivityLog::createLog([
                'action' => $isOnlineTraining ? 'accept' : 'accept_and_complete',
                'module' => 'Employee Training Response',
                'description' => "Employee {$employeeId} accepted destination training: {$training->destination_name}" .
                               ($isOnlineTraining ? "" : " and was auto-completed due to {$training->delivery_mode} delivery mode"),
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'auto_completed' => !$isOnlineTraining
            ]);

        } catch (\Exception $e) {
            Log::error('Error accepting destination training: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error accepting training: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create completed training record for employee's completed training section
     */
    private function createCompletedTrainingRecord($training)
    {
        try {
            // Check if completed training record already exists
            $existingRecord = CompletedTraining::where('employee_id', $training->employee_id)
                ->where('training_title', $training->destination_name)
                ->first();

            if (!$existingRecord) {
                CompletedTraining::create([
                    'employee_id' => $training->employee_id,
                    'training_title' => $training->destination_name,
                    'completion_date' => now(),
                    'remarks' => 'Completed destination knowledge training upon acceptance',
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
    private function generateCertificateForTraining($training)
    {
        try {
            // Find or create course record for certificate generation
            $course = CourseManagement::firstOrCreate(
                ['course_title' => $training->destination_name],
                [
                    'description' => 'Destination Knowledge Training - ' . $training->details,
                    'start_date' => now(),
                    'end_date' => now()->addMonths(3),
                    'status' => 'Active'
                ]
            );

            // Check if certificate already exists
            $existingCertificate = TrainingRecordCertificateTracking::where('employee_id', $training->employee_id)
                ->where('course_id', $course->course_id)
                ->first();

            if (!$existingCertificate) {
                // Use AI Certificate Generator Service if available
                if (class_exists('App\Services\AICertificateGeneratorService')) {
                    $certificateService = new AICertificateGeneratorService();

                    // Get employee name
                    $employee = Employee::where('employee_id', $training->employee_id)->first();
                    $employeeName = $employee ? $employee->first_name . ' ' . $employee->last_name : 'Employee';

                    // Generate certificate with correct parameters
                    $certificateData = $certificateService->generateCertificate(
                        $employeeName,
                        $training->destination_name,
                        now(),
                        $training->employee_id
                    );

                    // Create certificate tracking record
                    TrainingRecordCertificateTracking::create([
                        'employee_id' => $training->employee_id,
                        'course_id' => $course->course_id,
                        'certificate_number' => $certificateData['certificate_number'] ?? 'CERT-' . time(),
                        'certificate_url' => $certificateData['certificate_url'] ?? null,
                        'issue_date' => now(),
                        'status' => 'issued',
                        'issued_by' => 'System Auto-Generation'
                    ]);

                    Log::info("Generated certificate for employee {$training->employee_id}: {$training->destination_name}");
                } else {
                    // Fallback: Create certificate tracking record without actual certificate
                    TrainingRecordCertificateTracking::create([
                        'employee_id' => $training->employee_id,
                        'course_id' => $course->course_id,
                        'certificate_number' => 'DEST-' . $training->employee_id . '-' . time(),
                        'certificate_url' => null,
                        'issue_date' => now(),
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

    /**
     * Sync completion with Employee Training Dashboard and Competency systems
     */
    private function syncCompletedTrainingWithSystems($training)
    {
        try {
            // Sync with Employee Training Dashboard
            $employeeTraining = EmployeeTrainingDashboard::where('employee_id', $training->employee_id)
                ->whereHas('course', function($q) use ($training) {
                    $q->where('course_title', 'LIKE', '%' . $training->destination_name . '%');
                })
                ->first();

            if ($employeeTraining) {
                $employeeTraining->progress = 100;
                $employeeTraining->status = 'Completed';
                $employeeTraining->last_accessed = now();
                $employeeTraining->save();
            }

            // Sync with Employee Competency Profile
            $competencyProfile = EmployeeCompetencyProfile::where('employee_id', $training->employee_id)
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
            $competencyGap = CompetencyGap::where('employee_id', $training->employee_id)                ->whereHas('competency', function($q) use ($training) {
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
            Log::error('Error syncing completed training with other systems: ' . $e->getMessage());
        }
    }

    /**
     * Decline destination knowledge training assignment
     */
    public function declineDestinationTraining(Request $request)
    {
        try {
            $request->validate([
                'training_id' => 'required|integer|exists:destination_knowledge_trainings,id',
                'reason' => 'nullable|string|max:500'
            ]);

            $employeeId = Auth::user()->employee_id;
            $training = DestinationKnowledgeTraining::findOrFail($request->training_id);

            // Verify this training belongs to the authenticated employee
            if ($training->employee_id !== $employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to training record.'
                ], 403);
            }

            // Update training status to declined
            $training->status = 'declined';
            $training->is_active = false;
            if ($request->reason) {
                $training->remarks = ($training->remarks ? $training->remarks . ' | ' : '') . 'Declined by employee: ' . $request->reason;
            }
            $training->save();

            // Create notification for admin
            $declineMessage = "Employee {$employeeId} has declined the destination training: {$training->destination_name}";
            if ($request->reason) {
                $declineMessage .= " | Reason: {$request->reason}";
            }

            TrainingNotification::create([
                'employee_id' => 'ADMIN',
                'message' => $declineMessage,
                'sent_at' => now()
            ]);

            // Log activity
            ActivityLog::createLog([
                'action' => 'decline',
                'module' => 'Employee Training Response',
                'description' => "Employee {$employeeId} declined destination training: {$training->destination_name}" . ($request->reason ? " | Reason: {$request->reason}" : ''),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Training declined successfully. Admin has been notified.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error declining destination training: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error declining training: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get destination training details for viewing
     */
    public function getDestinationTrainingDetails($trainingId)
    {
        try {
            $employeeId = Auth::user()->employee_id;
            $training = DestinationKnowledgeTraining::with('employee')->findOrFail($trainingId);

            // Verify this training belongs to the authenticated employee
            if ($training->employee_id !== $employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to training record.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'training' => [
                    'id' => $training->id,
                    'destination_name' => $training->destination_name,
                    'details' => $training->details,
                    'delivery_mode' => $training->delivery_mode,
                    'status' => $training->status,
                    'progress' => $training->progress,
                    'remarks' => $training->remarks,
                    'created_at' => $training->created_at,
                    'date_completed' => $training->date_completed,
                    'expired_date' => $training->expired_date,
                    'is_active' => $training->is_active
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching destination training details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching training details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk transfer existing 100% completed trainings to Completed Trainings table
     */
    public function transferCompletedTrainings()
    {
        $transferCount = 0;

        // 1. Transfer Employee Training Dashboard records with 100% progress
        $dashboardTrainings = EmployeeTrainingDashboard::with(['course', 'employee'])
            ->where('progress', '>=', 100)
            ->orWhere('status', 'Completed')
            ->get();

        foreach ($dashboardTrainings as $training) {
            if (!$training->course) continue;

            $exists = CompletedTraining::where('employee_id', $training->employee_id)
                ->where(function($query) use ($training) {
                    $query->where('course_id', $training->course_id)
                          ->orWhere('training_title', $training->course->course_title);
                })
                ->exists();

            if (!$exists) {
                CompletedTraining::create([
                    'employee_id' => $training->employee_id,
                    'course_id' => $training->course_id,
                    'training_title' => $training->course->course_title,
                    'completion_date' => now()->format('Y-m-d'),
                    'remarks' => 'Auto-transferred from training dashboard (100% completion)',
                    'status' => 'Verified'
                ]);
                $transferCount++;
            }
        }

        // 2. Transfer Destination Knowledge Training records with 100% progress
        $destTrainings = DestinationKnowledgeTraining::with('employee')
            ->where('progress', '>=', 100)
            ->orWhere('status', 'completed')
            ->get();

        foreach ($destTrainings as $training) {
            $exists = CompletedTraining::where('employee_id', $training->employee_id)
                ->where('training_title', $training->destination_name)
                ->exists();

            if (!$exists) {
                CompletedTraining::create([
                    'employee_id' => $training->employee_id,
                    'training_title' => $training->destination_name,
                    'completion_date' => $training->date_completed ?? now()->format('Y-m-d'),
                    'remarks' => 'Auto-transferred from destination knowledge training (100% completion)',
                    'status' => 'Verified'
                ]);
                $transferCount++;
            }
        }

        // 3. Transfer passed exams (score >= 80%)
        $passedExams = ExamAttempt::with(['course', 'employee'])
            ->where('score', '>=', 80)
            ->where('status', 'completed')
            ->get();

        foreach ($passedExams as $exam) {
            if (!$exam->course) continue;

            $exists = CompletedTraining::where('employee_id', $exam->employee_id)
                ->where(function($query) use ($exam) {
                    $query->where('course_id', $exam->course_id)
                          ->orWhere('training_title', $exam->course->course_title);
                })
                ->exists();

            if (!$exists) {
                CompletedTraining::create([
                    'employee_id' => $exam->employee_id,
                    'course_id' => $exam->course_id,
                    'training_title' => $exam->course->course_title,
                    'completion_date' => $exam->completed_at ? $exam->completed_at->format('Y-m-d') : now()->format('Y-m-d'),
                    'remarks' => "Auto-transferred from exam completion (Score: {$exam->score}%)",
                    'status' => 'Verified'
                ]);
                $transferCount++;
            }
        }

        // 4. Transfer completed training requests (100% progress)
        $completedRequests = TrainingRequest::with(['course', 'employee'])
            ->where('status', 'Approved')
            ->get()
            ->filter(function($request) {
                // Check if there's a corresponding training dashboard record with 100% progress
                $dashboardRecord = EmployeeTrainingDashboard::where('employee_id', $request->employee_id)
                    ->where('course_id', $request->course_id)
                    ->first();

                if ($dashboardRecord) {
                    $combinedProgress = ExamAttempt::calculateCombinedProgress($request->employee_id, $request->course_id);
                    $actualProgress = $combinedProgress > 0 ? $combinedProgress : ($dashboardRecord->progress ?? 0);
                    return $actualProgress >= 100;
                }
                return false;
            });

        foreach ($completedRequests as $request) {
            if (!$request->course) continue;

            $exists = CompletedTraining::where('employee_id', $request->employee_id)
                ->where(function($query) use ($request) {
                    $query->where('course_id', $request->course_id)
                          ->orWhere('training_title', $request->training_title);
                })
                ->exists();

            if (!$exists) {
                $dashboardRecord = EmployeeTrainingDashboard::where('employee_id', $request->employee_id)
                    ->where('course_id', $request->course_id)
                    ->first();

                CompletedTraining::create([
                    'employee_id' => $request->employee_id,
                    'course_id' => $request->course_id,
                    'training_title' => $request->training_title,
                    'completion_date' => $dashboardRecord ? $dashboardRecord->updated_at->format('Y-m-d') : now()->format('Y-m-d'),
                    'remarks' => 'Auto-transferred from completed training request (100% progress)',
                    'status' => 'Verified'
                ]);
                $transferCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully transferred {$transferCount} completed trainings",
            'transferred_count' => $transferCount
        ]);
    }

    /**
     * Fix expiration dates for destination training records
     */
    private function fixDestinationExpirationDates()
    {
        try {
            $updated = 0;
            
            // Get all destination training records without proper expiration dates
            $records = DestinationKnowledgeTraining::destinationTrainings()
                ->where(function($query) {
                    $query->whereNull('expired_date')
                          ->orWhere('expired_date', '0000-00-00 00:00:00')
                          ->orWhere('expired_date', '');
                })
                ->get();

            foreach ($records as $record) {
                // Set expiration date to 3 months from creation date
                $expirationDate = $record->created_at->addMonths(3);
                
                $record->expired_date = $expirationDate;
                
                // Ensure the record is properly marked for upcoming if active
                if ($record->is_active && !$record->admin_approved_for_upcoming) {
                    $record->admin_approved_for_upcoming = true;
                }
                
                $record->save();
                $updated++;
            }

            if ($updated > 0) {
                Log::info("Fixed expiration dates for {$updated} destination training records in MyTrainingController");
            }

        } catch (\Exception $e) {
            Log::error('Error fixing destination expiration dates in MyTrainingController: ' . $e->getMessage());
        }
    }

    /**
     * Fix expiration dates for competency gap trainings
     */
    private function fixCompetencyGapExpirationDates()
    {
        try {
            $updated = 0;
            
            // Get all competency gaps without proper expiration dates
            $competencyGaps = \App\Models\CompetencyGap::where(function($query) {
                $query->whereNull('expired_date')
                      ->orWhere('expired_date', '0000-00-00 00:00:00')
                      ->orWhere('expired_date', '');
            })->get();

            foreach ($competencyGaps as $gap) {
                // Set expiration date to 6 months from creation date for competency gaps
                $expirationDate = $gap->created_at->addMonths(6);
                
                $gap->expired_date = $expirationDate;
                $gap->save();
                $updated++;
            }

            // Also fix upcoming trainings that are competency gap assigned
            $upcomingTrainings = UpcomingTraining::where('source', 'competency_assigned')
                ->where(function($query) {
                    $query->whereNull('expired_date')
                          ->orWhere('expired_date', '0000-00-00 00:00:00')
                          ->orWhere('expired_date', '');
                })
                ->get();

            foreach ($upcomingTrainings as $training) {
                // Try to find matching competency gap
                $competencyName = str_replace([' Training', ' Course', ' Program'], '', $training->training_title);
                $competencyGap = \App\Models\CompetencyGap::whereHas('competency', function($query) use ($competencyName) {
                    $query->where('competency_name', 'LIKE', '%' . $competencyName . '%');
                })->where('employee_id', $training->employee_id)->first();

                if ($competencyGap && $competencyGap->expired_date) {
                    $training->expired_date = $competencyGap->expired_date;
                } else {
                    // Fallback: set to 6 months from creation
                    $training->expired_date = $training->created_at->addMonths(6);
                }
                
                $training->save();
                $updated++;
            }

            if ($updated > 0) {
                Log::info("Fixed expiration dates for {$updated} competency gap training records in MyTrainingController");
            }

        } catch (\Exception $e) {
            Log::error('Error fixing competency gap expiration dates in MyTrainingController: ' . $e->getMessage());
        }
    }

    /**
     * Debug duplicate trainings issue
     */
    public function debugDuplicateTrainings()
    {
        try {
            $employeeId = 'EMP001'; // Debug for specific employee
            
            $debugData = [
                'employee_id' => $employeeId,
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'sources' => []
            ];

            // 1. Check upcoming_trainings table
            $upcomingTrainings = \App\Models\UpcomingTraining::where('employee_id', $employeeId)
                ->where('training_title', 'LIKE', '%Communication Skills%')
                ->get();
            
            $debugData['sources']['upcoming_trainings'] = [
                'count' => $upcomingTrainings->count(),
                'records' => $upcomingTrainings->map(function($training) {
                    return [
                        'upcoming_id' => $training->upcoming_id,
                        'training_title' => $training->training_title,
                        'source' => $training->source,
                        'status' => $training->status,
                        'assigned_by' => $training->assigned_by,
                        'assigned_date' => $training->assigned_date,
                        'created_at' => $training->created_at,
                        'needs_response' => $training->needs_response
                    ];
                })->toArray()
            ];

            // 2. Check employee_training_dashboard table
            $dashboardTrainings = \App\Models\EmployeeTrainingDashboard::with('course')
                ->where('employee_id', $employeeId)
                ->whereHas('course', function($query) {
                    $query->where('course_title', 'LIKE', '%Communication Skills%');
                })
                ->whereIn('status', ['Assigned', 'In Progress', 'Not Started'])
                ->get();

            $debugData['sources']['employee_training_dashboard'] = [
                'count' => $dashboardTrainings->count(),
                'records' => $dashboardTrainings->map(function($training) {
                    return [
                        'id' => $training->id,
                        'course_id' => $training->course_id,
                        'course_title' => $training->course ? $training->course->course_title : 'No Course',
                        'training_title' => $training->training_title,
                        'status' => $training->status,
                        'progress' => $training->progress,
                        'training_date' => $training->training_date,
                        'created_at' => $training->created_at
                    ];
                })->toArray()
            ];

            // 3. Check competency_course_assignments table
            $competencyAssignments = \App\Models\CompetencyCourseAssignment::with('course')
                ->where('employee_id', $employeeId)
                ->whereHas('course', function($query) {
                    $query->where('course_title', 'LIKE', '%Communication Skills%');
                })
                ->whereIn('status', ['Assigned', 'In Progress', 'Not Started'])
                ->get();

            $debugData['sources']['competency_course_assignments'] = [
                'count' => $competencyAssignments->count(),
                'records' => $competencyAssignments->map(function($assignment) {
                    return [
                        'id' => $assignment->id,
                        'course_id' => $assignment->course_id,
                        'course_title' => $assignment->course ? $assignment->course->course_title : 'No Course',
                        'status' => $assignment->status,
                        'progress' => $assignment->progress,
                        'created_at' => $assignment->created_at
                    ];
                })->toArray()
            ];

            // 4. Check destination_knowledge_trainings table
            $destinationTrainings = \App\Models\DestinationKnowledgeTraining::where('employee_id', $employeeId)
                ->where('destination_name', 'LIKE', '%Communication Skills%')
                ->where('admin_approved_for_upcoming', true)
                ->whereNotIn('status', ['completed', 'declined'])
                ->get();

            $debugData['sources']['destination_knowledge_trainings'] = [
                'count' => $destinationTrainings->count(),
                'records' => $destinationTrainings->map(function($training) {
                    return [
                        'id' => $training->id,
                        'destination_name' => $training->destination_name,
                        'status' => $training->status,
                        'admin_approved_for_upcoming' => $training->admin_approved_for_upcoming,
                        'created_at' => $training->created_at
                    ];
                })->toArray()
            ];

            // 5. Check competency_gaps table
            $competencyGaps = \App\Models\CompetencyGap::with('competency')
                ->where('employee_id', $employeeId)
                ->whereHas('competency', function($query) {
                    $query->where('competency_name', 'LIKE', '%Communication Skills%');
                })
                ->get();

            $debugData['sources']['competency_gaps'] = [
                'count' => $competencyGaps->count(),
                'records' => $competencyGaps->map(function($gap) {
                    return [
                        'id' => $gap->id,
                        'competency_name' => $gap->competency ? $gap->competency->competency_name : 'No Competency',
                        'required_level' => $gap->required_level,
                        'current_level' => $gap->current_level,
                        'assigned_to_training' => $gap->assigned_to_training,
                        'expired_date' => $gap->expired_date,
                        'created_at' => $gap->created_at
                    ];
                })->toArray()
            ];

            // Summary
            $debugData['summary'] = [
                'total_sources_with_data' => collect($debugData['sources'])->filter(function($source) {
                    return $source['count'] > 0;
                })->count(),
                'potential_duplicates' => collect($debugData['sources'])->sum('count'),
                'deduplication_needed' => collect($debugData['sources'])->sum('count') > 1
            ];

            return response()->json($debugData, 200, [], JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Debug failed: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Get the assigned by name for competency course assignments
     */
    private function getCompetencyAssignedByName($assignment)
    {
        try {
            Log::info('Getting competency assigned by name for assignment', [
                'employee_id' => $assignment->employee_id,
                'course_id' => $assignment->course_id,
                'assigned_by' => $assignment->assigned_by ?? 'null'
            ]);
            
            // Try to get from the assignment's assignedBy relationship first
            if ($assignment->assignedBy && !empty($assignment->assignedBy->name)) {
                Log::info('Found admin from assignedBy relationship: ' . $assignment->assignedBy->name);
                return $assignment->assignedBy->name;
            }
            
            // If assigned_by is numeric, try to find the user directly
            if (isset($assignment->assigned_by) && is_numeric($assignment->assigned_by)) {
                $user = \App\Models\User::find($assignment->assigned_by);
                if ($user && !empty($user->name)) {
                    Log::info('Found admin by ID lookup: ' . $user->name);
                    return $user->name;
                }
            }
            
            // If assigned_by is already a name (string), use it
            if (isset($assignment->assigned_by) && !is_numeric($assignment->assigned_by) && !empty($assignment->assigned_by)) {
                Log::info('Using string assigned_by: ' . $assignment->assigned_by);
                return $assignment->assigned_by;
            }
            
            // Check upcoming training record for this assignment
            $upcomingTraining = \App\Models\UpcomingTraining::where('employee_id', $assignment->employee_id)
                ->where('course_id', $assignment->course_id)
                ->where('source', 'competency_gap')
                ->first();
            
            if ($upcomingTraining) {
                Log::info('Found upcoming training record', [
                    'assigned_by' => $upcomingTraining->assigned_by,
                    'assigned_by_name' => $upcomingTraining->assigned_by_name
                ]);
                
                if (!empty($upcomingTraining->assigned_by_name) && !is_numeric($upcomingTraining->assigned_by_name) && $upcomingTraining->assigned_by_name !== 'Competency System') {
                    return $upcomingTraining->assigned_by_name;
                }
                
                if ($upcomingTraining->assigned_by && is_numeric($upcomingTraining->assigned_by)) {
                    $user = \App\Models\User::find($upcomingTraining->assigned_by);
                    if ($user && !empty($user->name)) {
                        Log::info('Found admin from upcoming training: ' . $user->name);
                        return $user->name;
                    }
                }
            }
            
            // Check if we can get it from the competency gap that created this assignment
            if ($assignment->course && $assignment->course->course_title) {
                $competencyGap = \App\Models\CompetencyGap::where('employee_id', $assignment->employee_id)
                    ->where('assigned_to_training', true)
                    ->whereHas('competency', function($query) use ($assignment) {
                        $courseTitle = $assignment->course->course_title;
                        $query->where('competency_name', 'LIKE', '%' . $courseTitle . '%')
                              ->orWhere('competency_name', 'LIKE', '%' . str_replace(' Training', '', $courseTitle) . '%');
                    })
                    ->first();
                
                if ($competencyGap && $competencyGap->assigned_by) {
                    Log::info('Found competency gap record', [
                        'assigned_by' => $competencyGap->assigned_by
                    ]);
                    
                    if (is_numeric($competencyGap->assigned_by)) {
                        $user = \App\Models\User::find($competencyGap->assigned_by);
                        if ($user && !empty($user->name)) {
                            Log::info('Found admin from competency gap: ' . $user->name);
                            return $user->name;
                        }
                    } else {
                        return $competencyGap->assigned_by;
                    }
                }
            }
            
            // Final fallback - try to get any admin user
            $adminUser = \App\Models\User::where('role', 'admin')->first();
            if ($adminUser) {
                Log::info('Using fallback admin: ' . $adminUser->name);
                return $adminUser->name;
            }
            
            Log::warning('No admin found, using system fallback');
            return 'Admin User';
            
        } catch (\Exception $e) {
            Log::warning('Error getting competency assigned by name: ' . $e->getMessage());
            return 'Admin User';
        }
    }

    /**
     * Fix competency assignment assigned_by_name values
     */
    public function fixCompetencyAssignedByNames()
    {
        try {
            $updatedCount = 0;
            
            // Fix upcoming training records with competency source
            $competencyUpcomingTrainings = \App\Models\UpcomingTraining::where('source', 'competency_gap')
                ->where(function($query) {
                    $query->where('assigned_by_name', 'Competency System')
                          ->orWhere('assigned_by_name', 'System Admin')
                          ->orWhereNull('assigned_by_name')
                          ->orWhere('assigned_by_name', '');
                })
                ->get();
            
            foreach ($competencyUpcomingTrainings as $training) {
                $assignedByName = null;
                
                // Try to get the admin name from the assigned_by ID
                if (is_numeric($training->assigned_by)) {
                    $user = \App\Models\User::find($training->assigned_by);
                    if ($user && !empty($user->name)) {
                        $assignedByName = $user->name;
                    }
                }
                
                // If no user found, try to get from competency gap record
                if (!$assignedByName) {
                    $competencyGap = \App\Models\CompetencyGap::where('employee_id', $training->employee_id)
                        ->where('assigned_to_training', true)
                        ->whereHas('competency', function($query) use ($training) {
                            $trainingTitle = $training->training_title;
                            $query->where('competency_name', 'LIKE', '%' . $trainingTitle . '%')
                                  ->orWhere('competency_name', 'LIKE', '%' . str_replace(' Training', '', $trainingTitle) . '%');
                        })
                        ->first();
                    
                    if ($competencyGap && $competencyGap->assigned_by) {
                        if (is_numeric($competencyGap->assigned_by)) {
                            $user = \App\Models\User::find($competencyGap->assigned_by);
                            if ($user && !empty($user->name)) {
                                $assignedByName = $user->name;
                            }
                        } else {
                            $assignedByName = $competencyGap->assigned_by;
                        }
                    }
                }
                
                // Final fallback
                if (!$assignedByName) {
                    $assignedByName = 'Competency System';
                }
                
                // Update the record
                $training->assigned_by_name = $assignedByName;
                $training->save();
                $updatedCount++;
                
                Log::info("Updated competency training assigned_by_name for ID {$training->upcoming_id}: {$assignedByName}");
            }
            
            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updatedCount} competency training records with assigned_by_name",
                'updated_count' => $updatedCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fixing competency assigned_by_name: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fixing competency assigned_by_name: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix assigned_by_name for records on page load (private method)
     */
    private function fixAssignedByNamesOnLoad()
    {
        try {
            // Get upcoming training records that need fixing (limit to 10 to avoid performance issues)
            $upcomingTrainings = \App\Models\UpcomingTraining::whereNotNull('assigned_by')
                ->where(function($query) {
                    $query->whereNull('assigned_by_name')
                          ->orWhere('assigned_by_name', '')
                          ->orWhere('assigned_by_name', 'LIKE', '%competency_auto_assigned%')
                          ->orWhere('assigned_by_name', '1')
                          ->orWhere('assigned_by_name', '2')
                          ->orWhere('assigned_by_name', '3')
                          ->orWhere('assigned_by_name', '4')
                          ->orWhere('assigned_by_name', '5')
                          ->orWhere('assigned_by_name', 'Competency System') // Fix competency system records
                          ->orWhere('assigned_by_name', 'System Admin')
                          ->orWhereRaw('assigned_by_name REGEXP \'^[0-9]+$\''); // Match any numeric value
                })
                ->limit(15) // Increased limit to handle more records
                ->get();

            foreach ($upcomingTrainings as $training) {
                $assignedByName = null;
                
                // Try to get the admin name from the assigned_by ID
                if (is_numeric($training->assigned_by)) {
                    $user = \App\Models\User::find($training->assigned_by);
                    if ($user && !empty($user->name)) {
                        $assignedByName = $user->name;
                    }
                }
                
                // For competency gap records, try harder to find the actual admin
                if (!$assignedByName && $training->source === 'competency_gap') {
                    // Try to find from competency gap record
                    $competencyGap = \App\Models\CompetencyGap::where('employee_id', $training->employee_id)
                        ->where('assigned_to_training', true)
                        ->whereHas('competency', function($query) use ($training) {
                            $trainingTitle = $training->training_title;
                            $query->where('competency_name', 'LIKE', '%' . $trainingTitle . '%')
                                  ->orWhere('competency_name', 'LIKE', '%' . str_replace(' Training', '', $trainingTitle) . '%');
                        })
                        ->first();
                    
                    if ($competencyGap && $competencyGap->assigned_by) {
                        if (is_numeric($competencyGap->assigned_by)) {
                            $user = \App\Models\User::find($competencyGap->assigned_by);
                            if ($user && !empty($user->name)) {
                                $assignedByName = $user->name;
                            }
                        } else {
                            $assignedByName = $competencyGap->assigned_by;
                        }
                    }
                }
                
                // If still no user found, use source-based names
                if (!$assignedByName) {
                    switch ($training->source) {
                        case 'competency_gap':
                        case 'competency_assigned':
                        case 'competency_auto_assign':
                            // Try to get any admin user as fallback
                            $adminUser = \App\Models\User::where('role', 'admin')->first();
                            $assignedByName = $adminUser ? $adminUser->name : 'Admin User';
                            break;
                        case 'destination_assigned':
                            $assignedByName = 'Admin User';
                            break;
                        case 'admin_assigned':
                            $assignedByName = 'System Admin';
                            break;
                        default:
                            $assignedByName = 'System';
                            break;
                    }
                }
                
                // Update the record
                $training->assigned_by_name = $assignedByName;
                $training->save();
                
                Log::info("Auto-fixed assigned_by_name for training ID {$training->upcoming_id}: {$assignedByName}");
            }
            
        } catch (\Exception $e) {
            Log::warning('Error auto-fixing assigned_by_name on load: ' . $e->getMessage());
        }
    }

    /**
     * Fix existing upcoming training records that don't have assigned_by_name populated
     */
    public function fixAssignedByNames()
    {
        try {
            $updatedCount = 0;
            
            // Get all upcoming training records that have assigned_by but no assigned_by_name
            $upcomingTrainings = \App\Models\UpcomingTraining::whereNotNull('assigned_by')
                ->where(function($query) {
                    $query->whereNull('assigned_by_name')
                          ->orWhere('assigned_by_name', '')
                          ->orWhere('assigned_by_name', 'LIKE', '%competency_auto_assigned%')
                          ->orWhereRaw('assigned_by_name REGEXP \'^[0-9]+$\''); // Match any numeric value
                })
                ->get();

            foreach ($upcomingTrainings as $training) {
                $assignedByName = null;
                
                // Try to get the admin name from the assigned_by ID
                if (is_numeric($training->assigned_by)) {
                    $user = \App\Models\User::find($training->assigned_by);
                    if ($user) {
                        $assignedByName = $user->name;
                    }
                }
                
                // If no user found or assigned_by is not numeric, use source-based names
                if (!$assignedByName) {
                    switch ($training->source) {
                        case 'competency_gap':
                        case 'competency_assigned':
                        case 'competency_auto_assign':
                            $assignedByName = 'Competency System';
                            break;
                        case 'destination_assigned':
                            $assignedByName = 'Admin User';
                            break;
                        case 'admin_assigned':
                            $assignedByName = 'System Admin';
                            break;
                        default:
                            $assignedByName = 'System';
                            break;
                    }
                }
                
                // Update the record
                $training->assigned_by_name = $assignedByName;
                $training->save();
                $updatedCount++;
                
                Log::info("Updated assigned_by_name for training ID {$training->upcoming_id}: {$assignedByName}");
            }
            
            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updatedCount} upcoming training records with assigned_by_name",
                'updated_count' => $updatedCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fixing assigned_by_name: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fix assigned_by_name: ' . $e->getMessage()
            ], 500);
        }
    }
}
