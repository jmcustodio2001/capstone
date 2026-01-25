<?php

namespace App\Http\Controllers;

use App\Models\CourseManagement;
use App\Models\EmployeeTrainingDashboard;
use App\Models\ActivityLog;
use App\Models\CourseManagementNotification;
use App\Models\UpcomingTraining;
use App\Models\CompetencyLibrary;
use App\Models\DestinationKnowledgeTraining;
use App\Models\DestinationMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CourseManagementController extends Controller
{
    public function index()
    {
        try {
            // Get existing courses from course_management table
            // Exclude destination-based courses (they appear in Accredited Training Centers section)
            $courses = CourseManagement::where(function($query) {
                    $query->whereNull('source_type')
                          ->orWhere('source_type', '!=', 'destination_master');
                })
                ->get();
            
            // Auto-sync competencies from competency library as courses
            $this->syncCompetenciesToCourses();
            
            // Refresh courses after sync (exclude destination-based courses)
            $courses = CourseManagement::where(function($query) {
                    $query->whereNull('source_type')
                          ->orWhere('source_type', '!=', 'destination_master');
                })
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching courses: ' . $e->getMessage());
            $courses = collect();
        }

        // Get both regular training assignments and competency-based assignments
        try {
            $assignedTrainings = EmployeeTrainingDashboard::with(['employee', 'course'])
                ->orderByDesc('created_at')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching assigned trainings: ' . $e->getMessage());
            $assignedTrainings = collect();
        }

        try {
            $competencyAssignments = \App\Models\CompetencyCourseAssignment::with(['employee', 'course'])
                ->orderByDesc('created_at')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching competency assignments: ' . $e->getMessage());
            $competencyAssignments = collect();
        }

        // Get training requests from employees - with enhanced error handling
        try {
            // First ensure table exists
            if (!Schema::hasTable('training_requests')) {
                Log::warning('training_requests table does not exist. Creating it now...');

                Schema::create('training_requests', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id('request_id');
                    $table->string('employee_id', 20);
                    $table->unsignedBigInteger('course_id')->nullable();
                    $table->string('training_title');
                    $table->text('reason');
                    $table->string('status')->default('Pending');
                    $table->date('requested_date');
                    $table->timestamps();

                    // Add foreign key constraint if course_management table exists
                    if (Schema::hasTable('course_management')) {
                        $table->foreign('course_id')->references('course_id')->on('course_management')->onDelete('set null');
                    }

                    // Add foreign key constraint if employees table exists
                    if (Schema::hasTable('employees')) {
                        $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
                    }
                });

                Log::info('training_requests table created successfully');
            }

            // Load training requests with proper error handling
            $trainingRequests = \App\Models\TrainingRequest::with(['employee', 'course'])
                ->orderByDesc('created_at')
                ->get();

            Log::info('Loaded ' . $trainingRequests->count() . ' training requests');

        } catch (\Exception $e) {
            Log::error('Error fetching training requests: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            $trainingRequests = collect();
        }

        // Get pending course activation requests
        try {
            $pendingActivationRequests = CourseManagement::where('status', 'Pending Approval')
                ->orderByDesc('created_at')
                ->get();

            $pendingActivationRequests = $pendingActivationRequests->map(function($course) {
                return (object)[
                    'course_id' => $course->course_id,
                    'request_id' => $course->course_id,
                    'course_title' => $course->course_title,
                    'description' => $course->description ?: 'Course activation request',
                    'requestedBy' => null,
                    'requested_at' => $course->created_at,
                    'status' => 'Pending Approval'
                ];
            });

        } catch (\Exception $e) {
            Log::error('Error fetching pending activation requests: ' . $e->getMessage());
            $pendingActivationRequests = collect();
        }

        // Get competency notifications for course management
        try {
            // First check if the table exists
            if (Schema::hasTable('course_management_notifications')) {
                $competencyNotifications = CourseManagementNotification::with('competency')
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get();
            } else {
                Log::info('course_management_notifications table does not exist, creating it...');
                $this->ensureCourseManagementNotificationsTableExists();
                $competencyNotifications = collect();
            }
        } catch (\Exception $e) {
            Log::error('Error fetching competency notifications: ' . $e->getMessage());
            $competencyNotifications = collect();
        }

        // Get destination master data (possible training destinations)
        try {
            $destinationMasters = DestinationMaster::where('is_active', true)
                ->orderBy('id', 'asc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching destination masters: ' . $e->getMessage());
            $destinationMasters = collect();
        }

        return view('learning_management.course_management', compact('courses', 'assignedTrainings', 'competencyAssignments', 'trainingRequests', 'pendingActivationRequests', 'competencyNotifications', 'destinationMasters'));
    }

    public function create()
    {
        return view('learning_management.course_management');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'course_title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'status' => 'required|string',
                'password' => 'required|string'
            ]);

            // Check for duplicate course titles
            $existingCourse = CourseManagement::where('course_title', $request->course_title)->first();
            if ($existingCourse) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A course with the title "' . $request->course_title . '" already exists. Please use a different title.',
                        'errors' => ['course_title' => ['Course title already exists']]
                    ], 422);
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'A course with the title "' . $request->course_title . '" already exists. Please use a different title.');
            }

            // Validate admin password (simple check - you may want to enhance this)
            $adminUser = Auth::guard('admin')->user();
            if (!$adminUser || !Hash::check($request->password, $adminUser->password)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid admin password. Please enter your correct password.',
                        'errors' => ['password' => ['Invalid password']]
                    ], 422);
                }
                return redirect()->back()->withInput()->with('error', 'Invalid admin password.');
            }

            $course = CourseManagement::create([
                'course_title' => $request->course_title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'status' => $request->status
            ]);
            
            ActivityLog::createLog([
                'module' => 'Learning Management',
                'action' => 'create',
                'description' => 'Created course: ' . $course->course_title,
                'model_type' => CourseManagement::class,
                'model_id' => $course->id,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Course created successfully!',
                    'course' => $course
                ]);
            }
            
            return redirect()->route('admin.course_management.index')->with('success', 'Course created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error creating course: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the course: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the course.');
        }
    }

    public function show($id)
    {
        $course = CourseManagement::findOrFail($id);
        return view('learning_management.course_management', compact('course'));
    }

    public function edit($id)
    {
        $course = CourseManagement::findOrFail($id);
        $courses = CourseManagement::all();
        return view('learning_management.course_management', compact('course', 'courses'));
    }

    public function update(Request $request, $id)
    {
        try {
            // Handle AJAX requests for status-only updates
            if ($request->expectsJson() && $request->has('status') && count($request->all()) === 1) {
                $course = CourseManagement::findOrFail($id);
                $course->status = $request->status;
                $course->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Course status updated successfully'
                ]);
            }

            // Handle full course updates with password validation
            $request->validate([
                'course_title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'status' => 'required|string',
                'password' => 'required|string'
            ]);

            // Validate admin password
            $adminUser = Auth::guard('admin')->user();
            if (!$adminUser || !Hash::check($request->password, $adminUser->password)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid admin password. Please enter your correct password.',
                        'errors' => ['password' => ['Invalid password']]
                    ], 422);
                }
                return redirect()->back()->withInput()->with('error', 'Invalid admin password.');
            }

            $course = CourseManagement::findOrFail($id);
            $course->update([
                'course_title' => $request->course_title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'status' => $request->status
            ]);

            ActivityLog::createLog([
                'module' => 'Learning Management',
                'action' => 'update',
                'description' => 'Updated course: ' . $course->course_title,
                'model_type' => CourseManagement::class,
                'model_id' => $course->id,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Course updated successfully!',
                    'course' => $course
                ]);
            }
            
            return redirect()->route('admin.course_management.index')->with('success', 'Course updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating course: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the course: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->withInput()->with('error', 'An error occurred while updating the course.');
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            // Validate password for deletion
            $request->validate([
                'password' => 'required|string'
            ]);

            // Validate admin password
            $adminUser = Auth::guard('admin')->user();
            if (!$adminUser || !Hash::check($request->password, $adminUser->password)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid admin password. Please enter your correct password.',
                        'errors' => ['password' => ['Invalid password']]
                    ], 422);
                }
                return redirect()->back()->with('error', 'Invalid admin password.');
            }

            $course = CourseManagement::findOrFail($id);
            $courseTitle = $course->course_title;
            $course->delete();
            
            ActivityLog::createLog([
                'module' => 'Learning Management',
                'action' => 'delete',
                'description' => 'Deleted course: ' . $courseTitle,
                'model_type' => CourseManagement::class,
                'model_id' => $id,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Course deleted successfully!'
                ]);
            }
            
            return redirect()->route('admin.course_management.index')->with('success', 'Course deleted successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error deleting course: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the course: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while deleting the course.');
        }
    }

    /**
     * Approve a course request from destination knowledge training
     */
    public function approveCourseRequest(Request $request, $courseId)
    {
        try {
            $course = CourseManagement::find($courseId);

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course not found with ID: ' . $courseId
                ], 404);
            }

            // Update course status to Active
            $course->status = 'Active';
            $course->save();

            // Log the approval
            ActivityLog::createLog([
                'module' => 'Course Management',
                'action' => 'approve',
                'description' => 'Approved course request: ' . $course->course_title,
                'model_type' => 'App\\Models\\CourseManagement',
                'model_id' => $course->course_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Course request approved successfully! Auto-assign is now available.',
                'course_status' => 'Active'
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving course request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving course request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a course request
     */
    public function rejectCourseRequest(Request $request, $courseId)
    {
        try {
            $course = CourseManagement::find($courseId);

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course not found with ID: ' . $courseId
                ], 404);
            }

            $reason = $request->input('reason', 'No reason provided');

            // Update course status to Rejected
            $course->status = 'Rejected';
            $course->save();

            // Log the rejection
            ActivityLog::createLog([
                'module' => 'Course Management',
                'action' => 'reject',
                'description' => 'Rejected course request: ' . $course->course_title . '. Reason: ' . $reason,
                'model_type' => 'App\\Models\\CourseManagement',
                'model_id' => $course->course_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Course request rejected.',
                'course_status' => 'Rejected'
            ]);

        } catch (\Exception $e) {
            Log::error('Error rejecting course request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting course request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-assign courses to an employee based on competency gaps
     * Now creates direct assignments to prevent duplication
     */
    public function autoAssignCourses(Request $request, $employeeId)
    {
        try {
            Log::info('Auto-assign started for employee ID: ' . $employeeId);

            // Ensure competency_course_assignments table exists
            $this->ensureCompetencyCourseAssignmentsTableExists();

            // Find the employee
            $employee = \App\Models\Employee::find($employeeId);

            if (!$employee) {
                Log::error('Auto-assign failed - Employee not found with ID: ' . $employeeId);
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found with ID: ' . $employeeId
                ], 404);
            }

            Log::info('Auto-assign - Found employee: ' . $employee->first_name . ' ' . $employee->last_name);

            // Check if specific competency ID is provided
            $specificCompetencyId = $request->input('specific_competency_id');

            // Get competency gaps for this employee
            $competencyGapsQuery = \App\Models\CompetencyGap::where('employee_id', $employeeId)
                ->where('is_active', 1)
                ->with('competency');

            if ($specificCompetencyId) {
                // Filter by specific competency if provided
                $competencyGapsQuery->where('competency_id', $specificCompetencyId);
                Log::info('Auto-assign - Filtering by specific competency ID: ' . $specificCompetencyId);
            }

            $competencyGaps = $competencyGapsQuery->get();

            Log::info('Auto-assign debug - Found ' . $competencyGaps->count() . ' competency gaps for employee: ' . $employeeId);

            // Also return debug info in the response for immediate visibility
            $debugInfo = [
                'competency_gaps_count' => $competencyGaps->count(),
                'competency_gaps' => $competencyGaps->map(function($gap) {
                    return [
                        'id' => $gap->id,
                        'competency_name' => $gap->competency ? $gap->competency->competency_name : 'No competency linked',
                        'competency_id' => $gap->competency_id
                    ];
                })
            ];

            if ($competencyGaps->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active competency gaps found for this employee.'
                ]);
            }

            $assignedCourses = [];
            $errors = [];

            foreach ($competencyGaps as $gap) {
                try {
                    $competencyName = $gap->competency ? $gap->competency->competency_name : '';
                    Log::info('Auto-assign debug - Processing competency: ' . $competencyName . ' for gap ID: ' . $gap->id);

                    // Find courses related to this competency
                    $courses = CourseManagement::where('status', 'Active')
                        ->where(function($query) use ($gap) {
                            $competencyName = $gap->competency ? $gap->competency->competency_name : '';
                            $query->where('course_title', 'LIKE', '%' . $competencyName . '%')
                                  ->orWhere('description', 'LIKE', '%' . $competencyName . '%');
                        })
                        ->get();

                    Log::info('Auto-assign debug - Found ' . $courses->count() . ' courses matching competency: ' . $competencyName);

                    foreach ($courses as $course) {
                        // Check if EmployeeTrainingDashboard record already exists (primary check)
                        $existingTrainingRecord = EmployeeTrainingDashboard::where('employee_id', $employeeId)
                            ->where('course_id', $course->course_id)
                            ->first();

                        if (!$existingTrainingRecord) {
                            Log::info('Auto-assign debug - Assigning course: ' . $course->course_title . ' to employee: ' . $employeeId);

                            // Use gap's expired_date if available, otherwise default to 90 days
                            $expiredDate = $gap->expired_date ? Carbon::parse($gap->expired_date) : Carbon::now()->addDays(90);

                            // First, create entry in upcoming_trainings table so employee can see it in their upcoming training section
                            $this->ensureUpcomingTrainingsTableExists();
                            
                            // Check if upcoming training entry already exists
                            $existingUpcomingTraining = \App\Models\UpcomingTraining::where('employee_id', $employeeId)
                                ->where('training_title', $course->course_title)
                                ->first();
                                
                            if (!$existingUpcomingTraining) {
                                $upcomingTraining = \App\Models\UpcomingTraining::create([
                                    'employee_id' => $employeeId,
                                    'training_title' => $course->course_title,
                                    'start_date' => Carbon::now(),
                                    'end_date' => $expiredDate,
                                    'status' => 'Scheduled',
                                    'source' => 'competency_auto_assign',
                                    'assigned_by' => Auth::id(),
                                    'assigned_by_name' => Auth::user()->name ?? 'System Admin',
                                    'assigned_date' => Carbon::now(),
                                    'needs_response' => true // Employee needs to accept/decline
                                ]);
                                
                                Log::info('Auto-assign debug - Created upcoming training record with ID: ' . $upcomingTraining->upcoming_id);
                            }

                            // Also create EmployeeTrainingDashboard record (this shows in the dashboard)
                            $trainingRecord = EmployeeTrainingDashboard::create([
                                'employee_id' => $employeeId,
                                'course_id' => $course->course_id,
                                'training_title' => $course->course_title,
                                'status' => 'Assigned',
                                'progress' => 0,
                                'training_date' => Carbon::now(), // Start date
                                'assigned_by' => Auth::id(),
                                'expired_date' => $expiredDate, // Use gap's expiration date
                                'source' => 'competency_assigned'
                            ]);

                            Log::info('Auto-assign debug - Created training dashboard record with ID: ' . $trainingRecord->id);

                            // Try to create competency course assignment (optional - for tracking purposes)
                            try {
                                $existingAssignment = \App\Models\CompetencyCourseAssignment::where('employee_id', $employeeId)
                                    ->where('course_id', $course->course_id)
                                    ->first();

                                if (!$existingAssignment) {
                                    $assignment = \App\Models\CompetencyCourseAssignment::create([
                                        'employee_id' => $employeeId,
                                        'course_id' => $course->course_id,
                                        'assigned_date' => now(),
                                        'assigned_by' => Auth::id(),
                                        'status' => 'Assigned',
                                        'is_destination_knowledge' => false
                                    ]);
                                    Log::info('Auto-assign debug - Created competency course assignment with ID: ' . $assignment->id);
                                }
                            } catch (\Exception $assignmentError) {
                                Log::warning('Auto-assign - Could not create CompetencyCourseAssignment record: ' . $assignmentError->getMessage());
                                // Continue anyway since the main EmployeeTrainingDashboard record was created
                            }

                            $assignedCourses[] = [
                                'course_title' => $course->course_title,
                                'competency' => $gap->competency ? $gap->competency->competency_name : 'Unknown'
                            ];
                        } else {
                            Log::info('Auto-assign debug - Training dashboard record already exists for course: ' . $course->course_title);
                        }
                    }
                } catch (\Exception $e) {
                    $competencyName = $gap->competency ? $gap->competency->competency_name : 'Unknown';
                    $errors[] = 'Error processing competency "' . $competencyName . '": ' . $e->getMessage();
                    Log::error('Auto-assign error for competency ' . $competencyName . ': ' . $e->getMessage());
                }
            }

            // Log the auto-assignment
            ActivityLog::createLog([
                'module' => 'Course Management',
                'action' => 'auto_assign',
                'description' => 'Auto-assigned ' . count($assignedCourses) . ' courses to employee: ' . $employee->first_name . ' ' . $employee->last_name . ' (ID: ' . $employeeId . ') based on competency gaps',
                'model_type' => 'App\\Models\\EmployeeTrainingDashboard',
                'model_id' => null
            ]);

            $message = count($assignedCourses) > 0
                ? 'Successfully auto-assigned ' . count($assignedCourses) . ' courses based on competency gaps. Courses have been added to both Upcoming Training (for employee acceptance) and Employee Training Dashboard.'
                : 'No new courses were assigned. This could be because: 1) The employee already has all relevant courses assigned, or 2) No suitable courses exist in the system for the competency gaps. Please check if courses have been created for the required competencies.';

            if (!empty($errors)) {
                $message .= ' Some errors occurred: ' . implode(', ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'assigned_courses' => $assignedCourses,
                'errors' => $errors,
                'debug_info' => $debugInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Error auto-assigning courses: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error auto-assigning courses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ensure the competency_course_assignments table exists
     */
    private function ensureCompetencyCourseAssignmentsTableExists()
    {
        if (!Schema::hasTable('competency_course_assignments')) {
            Log::info('Creating competency_course_assignments table...');
            
            Schema::create('competency_course_assignments', function (Blueprint $table) {
                $table->id();
                $table->string('employee_id');
                $table->string('course_id');
                $table->date('assigned_date');
                $table->string('status')->default('Not Started');
                $table->integer('progress')->default(0);
                $table->boolean('is_destination_knowledge')->default(false);
                $table->timestamps();

                $table->unique(['employee_id', 'course_id']);
                
                // Add foreign key constraints if tables exist
                if (Schema::hasTable('employees')) {
                    $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
                }
                if (Schema::hasTable('course_management')) {
                    $table->foreign('course_id')->references('course_id')->on('course_management')->onDelete('cascade');
                }
            });
            
            Log::info('competency_course_assignments table created successfully');
        }
    }

    /**
     * Ensure the upcoming_trainings table exists
     */
    private function ensureUpcomingTrainingsTableExists()
    {
        if (!Schema::hasTable('upcoming_trainings')) {
            Log::info('Creating upcoming_trainings table...');
            
            Schema::create('upcoming_trainings', function (Blueprint $table) {
                $table->id('upcoming_id');
                $table->string('employee_id', 20); // Match employees table employee_id column type
                $table->string('training_title');
                $table->timestamp('start_date'); // Use timestamp instead of date for consistency
                $table->timestamp('end_date'); // Use timestamp instead of date for consistency
                $table->string('status')->default('Scheduled');
                $table->string('source')->nullable();
                $table->string('assigned_by')->nullable();
                $table->timestamp('assigned_date')->nullable();
                $table->unsignedBigInteger('destination_training_id')->nullable();
                $table->boolean('needs_response')->default(false);
                $table->timestamps();

                $table->index('employee_id');
                $table->index('destination_training_id');
                
                // Add foreign key constraints if tables exist
                if (Schema::hasTable('employees')) {
                    $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
                }
                if (Schema::hasTable('destination_knowledge_trainings')) {
                    $table->foreign('destination_training_id')->references('id')->on('destination_knowledge_trainings')->onDelete('set null');
                }
            });
            
            Log::info('upcoming_trainings table created successfully');
        } else {
            // Check if table exists but has wrong column types and fix them
            try {
                // Check current column type for employee_id
                $columns = Schema::getColumnListing('upcoming_trainings');
                if (in_array('employee_id', $columns)) {
                    // Modify column type if it exists but is wrong type
                    Schema::table('upcoming_trainings', function (Blueprint $table) {
                        $table->string('employee_id', 20)->change();
                    });
                    Log::info('Updated upcoming_trainings employee_id column type to string(20)');
                }
            } catch (\Exception $e) {
                Log::warning('Could not modify upcoming_trainings column types: ' . $e->getMessage());
            }
        }
    }

    /**
     * Ensure the course_management_notifications table exists
     */
    private function ensureCourseManagementNotificationsTableExists()
    {
        if (!Schema::hasTable('course_management_notifications')) {
            Log::info('Creating course_management_notifications table...');
            
            Schema::create('course_management_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('competency_id')->nullable();
                $table->string('competency_name');
                $table->text('message');
                $table->string('notification_type')->default('competency_update');
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index('competency_id');
                $table->index('is_read');
                $table->index('created_by');
                
                // Add foreign key constraints if tables exist
                if (Schema::hasTable('competency_library')) {
                    $table->foreign('competency_id')->references('id')->on('competency_library')->onDelete('set null');
                }
                if (Schema::hasTable('users')) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                }
            });
            
            Log::info('course_management_notifications table created successfully');
        }
    }

    /**
     * Auto-assign courses to ALL employees based on their competency gaps
     */
    public function autoAssignCoursesToAll(Request $request)
    {
        try {
            Log::info('Global auto-assign started for all employees');

            // Ensure competency_course_assignments table exists
            $this->ensureCompetencyCourseAssignmentsTableExists();

            // Get all active competency gaps with their employees
            $competencyGaps = \App\Models\CompetencyGap::where('is_active', 1)
                ->with(['competency', 'employee'])
                ->get();

            Log::info('Global auto-assign - Found ' . $competencyGaps->count() . ' total active competency gaps');

            if ($competencyGaps->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active competency gaps found for any employees.'
                ]);
            }

            // Group gaps by employee
            $gapsByEmployee = $competencyGaps->groupBy('employee_id');
            Log::info('Global auto-assign - Processing ' . $gapsByEmployee->count() . ' employees with gaps');

            $totalAssignedCourses = 0;
            $employeesProcessed = 0;
            $errors = [];
            $employeeResults = [];

            foreach ($gapsByEmployee as $employeeId => $employeeGaps) {
                try {
                    $employee = $employeeGaps->first()->employee;

                    if (!$employee) {
                        Log::warning('Global auto-assign - Employee not found for ID: ' . $employeeId);
                        continue;
                    }

                    Log::info('Global auto-assign - Processing employee: ' . $employee->first_name . ' ' . $employee->last_name . ' (' . $employeeGaps->count() . ' gaps)');

                    $employeeAssignedCourses = [];
                    $employeeErrors = [];

                    foreach ($employeeGaps as $gap) {
                        try {
                            $competencyName = $gap->competency ? $gap->competency->competency_name : '';
                            Log::info('Global auto-assign - Processing competency: ' . $competencyName . ' for employee: ' . $employeeId);

                            // Find courses related to this competency
                            $courses = CourseManagement::where('status', 'Active')
                                ->where(function($query) use ($gap) {
                                    $competencyName = $gap->competency ? $gap->competency->competency_name : '';
                                    $query->where('course_title', 'LIKE', '%' . $competencyName . '%')
                                          ->orWhere('description', 'LIKE', '%' . $competencyName . '%');
                                })
                                ->get();

                            Log::info('Global auto-assign - Found ' . $courses->count() . ' courses matching competency: ' . $competencyName);

                            foreach ($courses as $course) {
                                // Check if already assigned
                                $existingAssignment = \App\Models\CompetencyCourseAssignment::where('employee_id', $employeeId)
                                    ->where('course_id', $course->course_id)
                                    ->first();

                                if (!$existingAssignment) {
                                    Log::info('Global auto-assign - Assigning course: ' . $course->course_title . ' to employee: ' . $employeeId);

                                    // Check if EmployeeTrainingDashboard record already exists
                                    $existingTrainingRecord = EmployeeTrainingDashboard::where('employee_id', $employeeId)
                                        ->where('course_id', $course->course_id)
                                        ->first();

                                    if (!$existingTrainingRecord) {
                                        // Use gap's expired_date if available, otherwise default to 90 days
                                        $expiredDate = $gap->expired_date ? Carbon::parse($gap->expired_date) : Carbon::now()->addDays(90);

                                        // First, create entry in upcoming_trainings table so employee can see it in their upcoming training section
                                        $this->ensureUpcomingTrainingsTableExists();
                                        
                                        // Check if upcoming training entry already exists
                                        $existingUpcomingTraining = \App\Models\UpcomingTraining::where('employee_id', $employeeId)
                                            ->where('training_title', $course->course_title)
                                            ->first();
                                            
                                        if (!$existingUpcomingTraining) {
                                            $upcomingTraining = \App\Models\UpcomingTraining::create([
                                                'employee_id' => $employeeId,
                                                'training_title' => $course->course_title,
                                                'start_date' => Carbon::now(),
                                                'end_date' => $expiredDate,
                                                'status' => 'Scheduled',
                                                'source' => 'competency_auto_assign',
                                                'assigned_by' => Auth::id(),
                                                'assigned_date' => Carbon::now(),
                                                'needs_response' => true // Employee needs to accept/decline
                                            ]);
                                            
                                            Log::info('Global auto-assign - Created upcoming training record with ID: ' . $upcomingTraining->upcoming_id);
                                        }

                                        // Create competency course assignment
                                        $assignment = \App\Models\CompetencyCourseAssignment::create([
                                            'employee_id' => $employeeId,
                                            'course_id' => $course->course_id,
                                            'assigned_date' => now(),
                                            'assigned_by' => Auth::id(),
                                            'status' => 'Assigned',
                                            'is_destination_knowledge' => false
                                        ]);

                                        // Also create EmployeeTrainingDashboard record for the dashboard display
                                        $trainingRecord = EmployeeTrainingDashboard::create([
                                            'employee_id' => $employeeId,
                                            'course_id' => $course->course_id,
                                            'status' => 'Assigned',
                                            'progress' => 0,
                                            'training_date' => Carbon::now(), // Start date
                                            'assigned_by' => Auth::id(),
                                            'expired_date' => $expiredDate, // Use gap's expiration date
                                            'source' => 'competency_assigned'
                                        ]);

                                        Log::info('Global auto-assign - Created training dashboard record with ID: ' . $trainingRecord->id);
                                    } else {
                                        Log::info('Global auto-assign - Training dashboard record already exists for course: ' . $course->course_title);
                                    }

                                    $employeeAssignedCourses[] = [
                                        'course_title' => $course->course_title,
                                        'competency' => $gap->competency ? $gap->competency->competency_name : 'Unknown'
                                    ];
                                    $totalAssignedCourses++;
                                } else {
                                    Log::info('Global auto-assign - Course already assigned: ' . $course->course_title . ' for employee: ' . $employeeId);
                                }
                            }
                        } catch (\Exception $e) {
                            $competencyName = $gap->competency ? $gap->competency->competency_name : 'Unknown';
                            $errorMsg = 'Error processing competency "' . $competencyName . '": ' . $e->getMessage();
                            $employeeErrors[] = $errorMsg;
                            $errors[] = $errorMsg;
                            Log::error('Global auto-assign error for employee ' . $employeeId . ': ' . $errorMsg);
                        }
                    }

                    $employeesProcessed++;
                    $employeeResults[] = [
                        'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                        'employee_id' => $employeeId,
                        'gaps_count' => $employeeGaps->count(),
                        'courses_assigned' => count($employeeAssignedCourses),
                        'assigned_courses' => $employeeAssignedCourses,
                        'errors' => $employeeErrors
                    ];

                } catch (\Exception $e) {
                    $errorMsg = 'Error processing employee ID ' . $employeeId . ': ' . $e->getMessage();
                    $errors[] = $errorMsg;
                    Log::error('Global auto-assign error: ' . $errorMsg);
                }
            }

            // Log the global auto-assignment
            ActivityLog::createLog([
                'module' => 'Course Management',
                'action' => 'global_auto_assign',
                'description' => 'Global auto-assigned ' . $totalAssignedCourses . ' courses to ' . $employeesProcessed . ' employees based on competency gaps',
                'model_type' => 'App\\Models\\CompetencyCourseAssignment',
                'model_id' => null
            ]);

            $message = $totalAssignedCourses > 0
                ? 'Successfully auto-assigned ' . $totalAssignedCourses . ' courses to ' . $employeesProcessed . ' employees based on competency gaps.'
                : 'No new courses were assigned. This could be because: 1) All employees already have all relevant courses assigned, or 2) No suitable courses exist in the system for the competency gaps. Please check if courses have been created for the required competencies.';

            if (!empty($errors)) {
                $message .= ' Some errors occurred during processing.';
            }

            Log::info('Global auto-assign completed - Total courses assigned: ' . $totalAssignedCourses . ', Employees processed: ' . $employeesProcessed);

            return response()->json([
                'success' => true,
                'message' => $message,
                'total_assigned_courses' => $totalAssignedCourses,
                'employees_processed' => $employeesProcessed,
                'employee_results' => $employeeResults,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Error in global auto-assign: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error in global auto-assign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign destination-specific course to an employee
     */
    public function assignDestinationCourse(Request $request, $employeeId)
    {
        try {
            $employee = \App\Models\Employee::find($employeeId);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found with ID: ' . $employeeId
                ], 404);
            }

            $courseId = $request->input('course_id');
            $course = CourseManagement::find($courseId);

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course not found with ID: ' . $courseId
                ], 404);
            }

            // Check if already assigned
            $existingAssignment = EmployeeTrainingDashboard::where('employee_id', $employeeId)
                ->where('course_id', $courseId)
                ->first();

            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course is already assigned to this employee.'
                ]);
            }

            // Create training assignment
            $assignment = EmployeeTrainingDashboard::create([
                'employee_id' => $employeeId,
                'course_id' => $courseId,
                'training_title' => $course->course_title,
                'training_type' => 'Destination Knowledge',
                'status' => 'Assigned',
                'assigned_date' => now(),
                'assigned_by' => Auth::id()
            ]);

            // Log the assignment
            ActivityLog::createLog([
                'module' => 'Course Management',
                'action' => 'assign_destination',
                'description' => 'Assigned destination course "' . $course->course_title . '" to employee: ' . $employee->first_name . ' ' . $employee->last_name,
                'model_type' => 'App\\Models\\EmployeeTrainingDashboard',
                'model_id' => $assignment->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Destination course assigned successfully.',
                'assignment' => $assignment
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning destination course: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error assigning destination course: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recommended courses for an employee
     */
    public function getRecommendedCourses($employeeId)
    {
        try {
            $employee = \App\Models\Employee::find($employeeId);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found with ID: ' . $employeeId
                ], 404);
            }

            // Get competency gaps
            $competencyGaps = \App\Models\CompetencyGap::where('employee_id', $employeeId)
                ->where('is_active', 1)
                ->with('competency')
                ->get();

            $recommendedCourses = [];

            foreach ($competencyGaps as $gap) {
                $courses = CourseManagement::where('status', 'Active')
                    ->where(function($query) use ($gap) {
                        $competencyName = $gap->competency ? $gap->competency->competency_name : '';
                        $query->where('course_title', 'LIKE', '%' . $competencyName . '%')
                              ->orWhere('description', 'LIKE', '%' . $competencyName . '%');
                    })
                    ->get();

                foreach ($courses as $course) {
                    $recommendedCourses[] = [
                        'course_id' => $course->course_id,
                        'course_title' => $course->course_title,
                        'description' => $course->description,
                        'competency' => $gap->competency ? $gap->competency->competency_name : 'Unknown',
                        'gap_level' => $gap->gap_level
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'recommended_courses' => $recommendedCourses
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting recommended courses: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting recommended courses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync training with competency data
     */
    public function syncTrainingCompetency(Request $request)
    {
        try {
            $syncedCount = 0;
            $errors = [];

            // Get all active training assignments
            $assignments = EmployeeTrainingDashboard::where('status', 'Assigned')->get();

            foreach ($assignments as $assignment) {
                try {
                    // Check if there's a matching competency gap
                    $competencyGap = \App\Models\CompetencyGap::where('employee_id', $assignment->employee_id)
                        ->where('is_active', 1)
                        ->whereRaw('LOWER(competency_name) LIKE LOWER(?)', ['%' . $assignment->training_title . '%'])
                        ->first();

                    if ($competencyGap) {
                        // Create or update competency course assignment
                        \App\Models\CompetencyCourseAssignment::updateOrCreate([
                            'employee_id' => $assignment->employee_id,
                            'course_id' => $assignment->course_id,
                        ], [
                            'assignment_date' => $assignment->assigned_date ?? now(),
                            'status' => 'Assigned',
                            'is_destination_knowledge' => false
                        ]);

                        $syncedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = 'Error syncing assignment ID ' . $assignment->id . ': ' . $e->getMessage();
                }
            }

            // Log the sync operation
            ActivityLog::createLog([
                'module' => 'Course Management',
                'action' => 'sync_training_competency',
                'description' => 'Synced ' . $syncedCount . ' training assignments with competency data',
                'model_type' => 'App\\Models\\CompetencyCourseAssignment',
                'model_id' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully synced ' . $syncedCount . ' training assignments with competency data.',
                'synced_count' => $syncedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Error syncing training competency: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error syncing training competency: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a competency notification as read
     */
    public function markNotificationAsRead(Request $request, $id)
    {
        try {
            $notification = CourseManagementNotification::findOrFail($id);
            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error marking notification as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a competency notification
     */
    public function deleteNotification(Request $request, $id)
    {
        try {
            $notification = CourseManagementNotification::findOrFail($id);
            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept competency notification and create course automatically
     */
    public function acceptNotificationAndCreateCourse(Request $request, $id)
    {
        try {
            $notification = CourseManagementNotification::with('competency')->findOrFail($id);

            // Get the actual competency data
            $competency = $notification->competency;
            if (!$competency) {
                return response()->json([
                    'success' => false,
                    'message' => 'Competency data not found for this notification'
                ], 400);
            }

            // Check if course already exists for this competency
            $existingCourse = CourseManagement::where('course_title', 'LIKE', '%' . $competency->competency_name . '%')
                ->orWhere('description', 'LIKE', '%' . $competency->competency_name . '%')
                ->first();

            if ($existingCourse) {
                return response()->json([
                    'success' => false,
                    'message' => 'A course already exists for this competency: ' . $existingCourse->course_title
                ], 400);
            }

            // Create new course automatically using actual competency data
            $course = CourseManagement::create([
                'course_title' => $competency->competency_name,
                'description' => $competency->description ?: 'Course created from competency library notification for: ' . $competency->competency_name,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays(90), // 90 days duration
                'status' => 'Active', // Automatically set to ACTIVE as requested
                'source_type' => 'competency_library',
                'source_id' => $notification->competency_id,
                'requested_at' => Carbon::now(),
                'requested_by' => Auth::guard('admin')->id()
            ]);

            // Delete notification completely
            $notification->delete();

            // Log the course creation
            ActivityLog::createLog([
                'module' => 'Course Management',
                'action' => 'auto_create_from_notification',
                'description' => 'Auto-created course "' . $course->course_title . '" from competency notification for: ' . $notification->competency_name,
                'model_type' => CourseManagement::class,
                'model_id' => $course->course_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Course created successfully and set to ACTIVE status!',
                'course' => [
                    'id' => $course->course_id,
                    'title' => $course->course_title,
                    'status' => $course->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error accepting notification and creating course: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating course: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-sync competencies from competency library to course management
     */
    private function syncCompetenciesToCourses()
    {
        try {
            // Get all competencies from competency library (include all competencies)
            $competencies = CompetencyLibrary::all();
            
            $synced = 0;
            $skipped = 0;
            
            foreach ($competencies as $competency) {
                // Check if course already exists for this competency
                $existingCourse = CourseManagement::where('course_title', $competency->competency_name)->first();
                
                if (!$existingCourse) {
                    // Create new course from competency
                    $course = CourseManagement::create([
                        'course_title' => $competency->competency_name,
                        'description' => $competency->description ?? 'Auto-synced from Competency Library',
                        'start_date' => now(),
                        'status' => 'Active'
                    ]);
                    
                    $synced++;
                    
                    Log::info("Auto-synced competency '{$competency->competency_name}' to course management");
                } else {
                    $skipped++;
                }
            }
            
            if ($synced > 0) {
                Log::info("Competency sync completed: {$synced} new courses created, {$skipped} already existed");
            }
            
        } catch (\Exception $e) {
            Log::error('Error syncing competencies to courses: ' . $e->getMessage());
        }
    }
}
