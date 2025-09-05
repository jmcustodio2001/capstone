<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\CompetencyLibraryController;
use App\Http\Controllers\EmployeeCompetencyProfileController;
use App\Http\Controllers\CompetencyGapAnalysisController;
use App\Http\Controllers\CourseManagementController;
use App\Http\Controllers\CustomerServiceSalesSkillsTrainingController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\SuccessionReadinessRatingController;

Route::get('/', function () {
    return view('landingpage');
})->name('welcome');

Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome.page');

// General login route - redirect to employee login by default
Route::get('/login', function () {
    return redirect()->route('employee.login');
})->name('login');

// Admin login routes
Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::post('/admin/verify-password', [AdminController::class, 'verifyPassword'])->name('admin.verify_password')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/check-password-verification', [AdminController::class, 'checkPasswordVerification'])->name('admin.check_password_verification')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard')->middleware('auth:admin');

// Employee login form
Route::get('/employee/login', [EmployeeController::class, 'showLoginForm'])->name('employee.login');
// Employee login POST
Route::post('/employee/login', [EmployeeController::class, 'login'])->name('employee.login.submit');
// Employee dashboard (protected route)
Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard')->middleware('auth:employee');
// Employee logout
Route::post('/employee/logout', [EmployeeController::class, 'logout'])->name('employee.logout');

// Employee competency training response route
Route::post('/employee/competency-training/respond', [EmployeeController::class, 'respondToCompetencyTraining'])->name('employee.competency_training.respond')->middleware('auth:employee');

// Admin Routes - Competency Management
Route::get('/admin/competency-library', [CompetencyLibraryController::class, 'index'])->name('admin.competency_library.index')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-library', [CompetencyLibraryController::class, 'store'])->name('admin.competency_library.store')->middleware(['auth:admin', 'admin.auth']);
Route::put('/admin/competency-library/{id}', [CompetencyLibraryController::class, 'update'])->name('admin.competency_library.update')->middleware(['auth:admin', 'admin.auth']);
Route::delete('/admin/competency-library/{id}', [CompetencyLibraryController::class, 'destroy'])->name('admin.competency_library.destroy')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-library/sync-destination-knowledge', [CompetencyLibraryController::class, 'syncDestinationKnowledgeTraining'])->name('admin.competency_library.sync_destination_knowledge')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-library/fix-destination-competencies', [CompetencyLibraryController::class, 'fixDestinationCompetencies'])->name('admin.competency_library.fix_destination_competencies')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-library/cleanup-destination-duplicates', [CompetencyLibraryController::class, 'cleanupDestinationDuplicates'])->name('admin.competency_library.cleanup_destination_duplicates')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-library/{id}/notify-course-management', [CompetencyLibraryController::class, 'notifyCourseManagement'])->name('admin.competency_library.notify_course_management')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/course-management/notifications/{id}/mark-read', [CourseManagementController::class, 'markNotificationAsRead'])->name('admin.course_management.mark_notification_read')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/course-management/notifications/{id}/accept-create-course', [CourseManagementController::class, 'acceptNotificationAndCreateCourse'])->name('admin.course_management.accept_create_course')->middleware(['auth:admin', 'admin.auth']);
Route::delete('/admin/course-management/notifications/{id}', [CourseManagementController::class, 'deleteNotification'])->name('admin.course_management.delete_notification')->middleware(['auth:admin', 'admin.auth']);

Route::get('/admin/employee-competency-profiles', [EmployeeCompetencyProfileController::class, 'index'])->name('employee_competency_profiles.index')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/employee-competency-profiles', [EmployeeCompetencyProfileController::class, 'store'])->name('employee_competency_profiles.store')->middleware(['auth:admin', 'admin.auth']);
Route::put('/admin/employee-competency-profiles/{id}', [EmployeeCompetencyProfileController::class, 'update'])->name('employee_competency_profiles.update')->middleware(['auth:admin', 'admin.auth']);
Route::delete('/admin/employee-competency-profiles/{id}', [EmployeeCompetencyProfileController::class, 'destroy'])->name('employee_competency_profiles.destroy')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/employee-competency-profiles/sync-training', [EmployeeCompetencyProfileController::class, 'syncWithTrainingProgress'])->name('employee_competency_profiles.sync_training')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/employee-competency-profiles/get-training-progress', [EmployeeCompetencyProfileController::class, 'getTrainingProgress'])->name('employee_competency_profiles.get_training_progress')->middleware(['auth:admin', 'admin.auth']);
Route::get('/api/employee-competency-profile/{employeeId}/{competencyId}', [EmployeeCompetencyProfileController::class, 'getCurrentLevel'])->name('api.employee_competency_profile.current_level')->middleware('auth:admin');

Route::get('/admin/competency-gap-analysis', [CompetencyGapAnalysisController::class, 'index'])->name('competency_gap_analysis.index')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-gap-analysis', [CompetencyGapAnalysisController::class, 'store'])->name('competency_gap_analysis.store')->middleware(['auth:admin', 'admin.auth']);
Route::put('/admin/competency-gap-analysis/{id}', [CompetencyGapAnalysisController::class, 'update'])->name('competency_gap_analysis.update')->middleware(['auth:admin', 'admin.auth']);
Route::delete('/admin/competency-gap-analysis/{id}', [CompetencyGapAnalysisController::class, 'destroy'])->name('competency_gap_analysis.destroy')->middleware(['auth:admin', 'admin.auth']);
Route::match(['GET', 'POST'], '/admin/competency-gap-analysis/export', [CompetencyGapAnalysisController::class, 'export'])->name('competency_gap_analysis.export')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-gap-analysis/approve-destination-knowledge', [CompetencyGapAnalysisController::class, 'approveDestinationKnowledge'])->name('competency_gap_analysis.approve_destination_knowledge')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/competency-gap-analysis/get-competency-data', [CompetencyGapAnalysisController::class, 'getCompetencyData'])->name('competency_gap_analysis.get_competency_data')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-gap-analysis/get-competency-data', [CompetencyGapAnalysisController::class, 'getCompetencyData'])->name('competency_gap_analysis.get_competency_data_post')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-gap-analysis/{id}/extend-expiration', [CompetencyGapAnalysisController::class, 'extendExpiration'])->name('competency_gap_analysis.extend_expiration')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-gap-analysis/{id}/extend-expiration', [CompetencyGapAnalysisController::class, 'extendExpiration'])->name('competency_gap_analysis.extend_expiration_alt')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/competency-gap-analysis/check-access', [CompetencyGapAnalysisController::class, 'checkAccess'])->name('competency_gap_analysis.check_access')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-gap-analysis/fix-expired-dates', [CompetencyGapAnalysisController::class, 'fixExpiredDates'])->name('competency_gap_analysis.fix_expired_dates')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-gap-analysis/bulk-sync-profiles', [CompetencyGapAnalysisController::class, 'bulkSyncCompetencyProfiles'])->name('competency_gap_analysis.bulk_sync_profiles')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-gap-analysis/assign-to-upcoming', [CompetencyGapAnalysisController::class, 'assignToUpcomingTraining'])->name('competency_gap_analysis.assign_to_upcoming')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-gap-analysis/assign-to-training', [CompetencyGapAnalysisController::class, 'assignToTraining'])->name('competency_gap_analysis.assign_to_training')->middleware(['auth:admin', 'admin.auth']);

// Admin Routes - Learning Management
Route::get('/admin/course-management', [CourseManagementController::class, 'index'])->name('admin.course_management.index')->middleware('auth:admin');
Route::post('/admin/course-management', [CourseManagementController::class, 'store'])->name('admin.course_management.store')->middleware('auth:admin');
Route::get('/admin/course-management/create', [CourseManagementController::class, 'create'])->name('admin.course_management.create')->middleware('auth:admin');
Route::get('/admin/course-management/{id}', [CourseManagementController::class, 'show'])->name('admin.course_management.show')->middleware('auth:admin');
Route::get('/admin/course-management/{id}/edit', [CourseManagementController::class, 'edit'])->name('admin.course_management.edit')->middleware('auth:admin');
Route::put('/admin/course-management/{id}', [CourseManagementController::class, 'update'])->name('admin.course_management.update')->middleware('auth:admin');
Route::delete('/admin/course-management/{id}', [CourseManagementController::class, 'destroy'])->name('admin.course_management.destroy')->middleware('auth:admin');
Route::post('/admin/course-management/auto-assign/{employeeId}', [CourseManagementController::class, 'autoAssignCourses'])->name('admin.course_management.auto_assign')->middleware('auth:admin');
Route::post('/admin/course-management/auto-assign-all', [CourseManagementController::class, 'autoAssignCoursesToAll'])->name('admin.course_management.auto_assign_all')->middleware('auth:admin');
Route::post('/admin/course-management/assign-destination/{employeeId}', [CourseManagementController::class, 'assignDestinationCourse'])->name('admin.course_management.assign_destination')->middleware('auth:admin');
Route::get('/admin/course-management/recommended/{employeeId}', [CourseManagementController::class, 'getRecommendedCourses'])->name('admin.course_management.recommended')->middleware('auth:admin');
Route::post('/admin/course-management/sync-training-competency', [CourseManagementController::class, 'syncTrainingCompetency'])->name('admin.course_management.sync_training_competency')->middleware('auth:admin');
Route::get('/admin/employee-trainings-dashboard', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'index'])->name('admin.employee_trainings_dashboard.index')->middleware('auth:admin');
Route::post('/admin/employee-trainings-dashboard', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'store'])->name('admin.employee_trainings_dashboard.store')->middleware('auth:admin');
Route::get('/admin/employee-trainings-dashboard/create', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'create'])->name('admin.employee_trainings_dashboard.create')->middleware('auth:admin');
Route::put('/admin/employee-trainings-dashboard/{id}', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'update'])->name('admin.employee_trainings_dashboard.update')->middleware('auth:admin');
Route::delete('/admin/employee-trainings-dashboard/{id}', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'destroy'])->name('admin.employee_trainings_dashboard.destroy')->middleware('auth:admin');
Route::post('/admin/employee-trainings-dashboard/sync-records', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'syncExistingRecords'])->name('admin.employee_trainings_dashboard.sync_records')->middleware('auth:admin');
Route::post('/admin/employee-trainings-dashboard/sync-existing', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'syncExisting'])->name('admin.employee_trainings_dashboard.sync_existing')->middleware('auth:admin');
Route::post('/admin/employee-trainings-dashboard/fix-expired-dates', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'fixExpiredDates'])->name('admin.employee_trainings_dashboard.fix_expired_dates')->middleware('auth:admin');
Route::get('/admin/training-record-certificate-tracking', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'index'])->name('training_record_certificate_tracking.index')->middleware('auth:admin');
Route::post('/admin/training-record-certificate-tracking', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'store'])->name('training_record_certificate_tracking.store')->middleware('auth:admin');
Route::put('/admin/training-record-certificate-tracking/{id}', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'update'])->name('training_record_certificate_tracking.update')->middleware('auth:admin');
Route::delete('/admin/training-record-certificate-tracking/{id}', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'destroy'])->name('training_record_certificate_tracking.destroy')->middleware('auth:admin');
Route::post('/admin/training-record-certificate-tracking/auto-generate', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'autoGenerateMissingCertificates'])->name('training_record_certificate_tracking.auto_generate')->middleware('auth:admin');
Route::get('/certificates/view/{id}', [App\Http\Controllers\CertificateGenerationController::class, 'viewCertificate'])->name('certificates.view');
Route::get('/certificates/download/{id}', [App\Http\Controllers\CertificateGenerationController::class, 'downloadCertificate'])->name('certificates.download');

// Admin Routes - Training Management
Route::get('/admin/destination-knowledge-training', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'index'])->name('admin.destination-knowledge-training.index')->middleware('auth:admin');
Route::post('/admin/destination-knowledge-training', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'store'])->name('admin.destination-knowledge-training.store')->middleware('auth:admin');
Route::put('/admin/destination-knowledge-training/{id}', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'update'])->name('admin.destination-knowledge-training.update')->middleware('auth:admin');
Route::post('/admin/destination-knowledge-training/assign-to-upcoming', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'assignToUpcomingTraining'])->name('admin.destination-knowledge-training.assign-to-upcoming')->middleware('auth:admin');
Route::get('/csrf-refresh', function() {
    return response()->json(['csrf_token' => csrf_token()]);
})->middleware('auth:admin');

// CSRF token endpoint for AJAX requests
Route::get('/csrf-token', function() {
    return response()->json(['token' => csrf_token(), 'csrf_token' => csrf_token()]);
})->middleware('auth:admin');
Route::post('/admin/destination-knowledge-training/sync-existing', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'syncExisting'])->name('admin.destination-knowledge-training.sync-existing')->middleware('auth:admin');
Route::post('/admin/destination-knowledge-training/sync-all-records', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'syncAllRecords'])->name('admin.destination-knowledge-training.sync-all-records')->middleware('auth:admin');
Route::post('/admin/destination-knowledge-training/store-possible', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'storePossibleDestination'])->name('admin.destination-knowledge-training.store-possible')->middleware('auth:admin');
Route::delete('/admin/destination-knowledge-training/{id}', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'destroy'])->name('admin.destination-knowledge-training.destroy')->middleware('auth:admin');
Route::delete('/admin/destination-knowledge-training/destroy-possible/{id}', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'destroyPossible'])->name('admin.destination-knowledge-training.destroy-possible')->middleware('auth:admin');
Route::post('/admin/destination-knowledge-training/{id}/request-activation', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'requestActivation'])->name('admin.destination-knowledge-training.request-activation')->middleware('auth:admin');

// Course Management approval routes
Route::post('/admin/course-management/{courseId}/approve', [App\Http\Controllers\CourseManagementController::class, 'approveCourseRequest'])->name('admin.course-management.approve')->middleware('auth:admin');
Route::post('/admin/course-management/{courseId}/reject', [App\Http\Controllers\CourseManagementController::class, 'rejectCourseRequest'])->name('admin.course-management.reject')->middleware('auth:admin');

// Training Request approval routes
Route::post('/admin/training-requests/{requestId}/approve', [App\Http\Controllers\TrainingRequestController::class, 'approve'])->name('admin.training-requests.approve')->middleware('auth:admin');
Route::post('/admin/training-requests/{requestId}/reject', [App\Http\Controllers\TrainingRequestController::class, 'reject'])->name('admin.training-requests.reject')->middleware('auth:admin');
Route::get('/admin/customer-service-sales-skills-training', [App\Http\Controllers\CustomerServiceSalesSkillsTrainingController::class, 'index'])->name('customer_service_sales_skills_training.index')->middleware('auth:admin');
Route::post('/admin/customer-service-sales-skills-training', [App\Http\Controllers\CustomerServiceSalesSkillsTrainingController::class, 'store'])->name('customer_service_sales_skills_training.store')->middleware('auth:admin');
Route::put('/admin/customer-service-sales-skills-training/{id}', [App\Http\Controllers\CustomerServiceSalesSkillsTrainingController::class, 'update'])->name('customer_service_sales_skills_training.update')->middleware('auth:admin');
Route::delete('/admin/customer-service-sales-skills-training/{id}', [App\Http\Controllers\CustomerServiceSalesSkillsTrainingController::class, 'destroy'])->name('customer_service_sales_skills_training.destroy')->middleware('auth:admin');

// Admin Routes - Settings
Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings')->middleware('auth:admin');
Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.updateSettings')->middleware('auth:admin');

Route::get('/admin/users/create', [AdminController::class, 'create'])->name('admin.create')->middleware('auth:admin');

Route::get('/admin/users/{id}/edit', [AdminController::class, 'edit'])->name('admin.edit')->middleware('auth:admin');

Route::delete('/admin/users/{id}', [AdminController::class, 'destroy'])->name('admin.destroy')->middleware('auth:admin');

// Admin Routes - Employee Self-Service
Route::get('/admin/profile-update-of-employees', [App\Http\Controllers\ProfileUpdateOfEmployeeController::class, 'index'])->name('profile_update_of_employees.index')->middleware('auth:admin');
Route::post('/admin/profile-updates/{id}/approve', [App\Http\Controllers\ProfileUpdateOfEmployeeController::class, 'approve'])->name('profile_updates.approve')->middleware('auth:admin');
Route::post('/admin/profile-updates/{id}/reject', [App\Http\Controllers\ProfileUpdateOfEmployeeController::class, 'reject'])->name('profile_updates.reject')->middleware('auth:admin');
Route::post('/admin/profile-updates/fix-old-values', [App\Http\Controllers\ProfileUpdateOfEmployeeController::class, 'fixOldValues'])->name('profile_updates.fix_old_values')->middleware('auth:admin');
Route::get('/admin/employee-request-forms', [App\Http\Controllers\EmployeeRequestFormController::class, 'index'])->name('employee_request_forms.index')->middleware('auth:admin');
Route::post('/admin/employee-request-forms/{id}/update-status', [App\Http\Controllers\EmployeeRequestFormController::class, 'updateStatus'])->name('employee_request_forms.update_status')->middleware('auth:admin');
Route::put('/admin/employee-request-forms/{id}', [App\Http\Controllers\EmployeeRequestFormController::class, 'update'])->name('employee_request_forms.update')->middleware('auth:admin');
Route::delete('/admin/employee-request-forms/{id}', [App\Http\Controllers\EmployeeRequestFormController::class, 'destroy'])->name('employee_request_forms.destroy')->middleware('auth:admin');

// Add alias route for backward compatibility
Route::put('/admin/requests/{id}', [App\Http\Controllers\EmployeeRequestFormController::class, 'update'])->name('admin.requests.update')->middleware('auth:admin');
Route::get('/admin/employee-list', [EmployeeController::class, 'index'])->name('employee.list')->middleware('auth:admin');
Route::post('/admin/employees', [EmployeeController::class, 'store'])->name('employees.store')->middleware('auth:admin');
Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show')->middleware('auth:admin');
Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update')->middleware('auth:admin');
Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy')->middleware('auth:admin');
Route::get('/admin/training-feedback', function() { return view('Employee_Self_Service.employee_feedback'); })->name('admin.training_feedback.index')->middleware('auth:admin');

// Admin Routes - Succession Planning
Route::get('/admin/potential-successors', [App\Http\Controllers\PotentialSuccessorController::class, 'index'])->name('potential_successors.index')->middleware('auth:admin');
Route::post('/admin/potential-successors', [App\Http\Controllers\PotentialSuccessorController::class, 'store'])->name('potential_successors.store')->middleware('auth:admin');
Route::get('/admin/potential-successors/{id}', [App\Http\Controllers\PotentialSuccessorController::class, 'show'])->name('potential_successors.show')->middleware('auth:admin');
Route::get('/admin/potential-successors/{id}/edit', [App\Http\Controllers\PotentialSuccessorController::class, 'edit'])->name('potential_successors.edit')->middleware('auth:admin');
Route::put('/admin/potential-successors/{id}', [App\Http\Controllers\PotentialSuccessorController::class, 'update'])->name('potential_successors.update')->middleware('auth:admin');
Route::delete('/admin/potential-successors/{id}', [App\Http\Controllers\PotentialSuccessorController::class, 'destroy'])->name('potential_successors.destroy')->middleware('auth:admin');
Route::post('/potential_successors/ai_suggestions', [App\Http\Controllers\PotentialSuccessorController::class, 'getAISuccessorSuggestions'])->name('potential_successors.ai_suggestions');
Route::post('/potential_successors/predictive_analytics', [App\Http\Controllers\PotentialSuccessorController::class, 'getPredictiveAnalytics'])->name('potential_successors.predictive_analytics');
Route::post('/potential_successors/development_paths', [App\Http\Controllers\PotentialSuccessorController::class, 'getDevelopmentPaths'])->name('potential_successors.development_paths');
Route::get('/admin/succession-readiness-ratings', [App\Http\Controllers\SuccessionReadinessRatingController::class, 'index'])->name('succession_readiness_ratings.index')->middleware('auth:admin');
Route::post('/admin/succession-readiness-ratings', [App\Http\Controllers\SuccessionReadinessRatingController::class, 'store'])->name('succession_readiness_ratings.store')->middleware('auth:admin');
Route::get('/admin/succession-readiness-ratings/{id}', [App\Http\Controllers\SuccessionReadinessRatingController::class, 'show'])->name('succession_readiness_ratings.show')->middleware('auth:admin');
Route::get('/admin/succession-readiness-ratings/{id}/edit', [App\Http\Controllers\SuccessionReadinessRatingController::class, 'edit'])->name('succession_readiness_ratings.edit')->middleware('auth:admin');
Route::put('/admin/succession-readiness-ratings/{id}', [App\Http\Controllers\SuccessionReadinessRatingController::class, 'update'])->name('succession_readiness_ratings.update')->middleware('auth:admin');
Route::delete('/admin/succession-readiness-ratings/{id}', [App\Http\Controllers\SuccessionReadinessRatingController::class, 'destroy'])->name('succession_readiness_ratings.destroy')->middleware('auth:admin');
Route::post('/succession_readiness_ratings/batch_analysis', [App\Http\Controllers\SuccessionReadinessRatingController::class, 'getBatchAnalysis'])->name('succession_readiness_ratings.batch_analysis');
Route::post('/succession_readiness_ratings/ai_insights', [App\Http\Controllers\SuccessionReadinessRatingController::class, 'getAIInsights'])->name('succession_readiness_ratings.ai_insights');
Route::post('/admin/succession-readiness-ratings/refresh', [App\Http\Controllers\SuccessionReadinessRatingController::class, 'refresh'])->name('succession_readiness_ratings.refresh')->middleware('auth:admin');
Route::get('/admin/succession-readiness-ratings/employee-data/{employeeId}', [App\Http\Controllers\SuccessionReadinessRatingController::class, 'getEmployeeCompetencyData'])->name('succession_readiness_ratings.employee_data')->middleware('auth:admin');
Route::get('/succession_readiness_ratings/competency-data/{employeeId}', [App\Http\Controllers\SuccessionReadinessRatingController::class, 'getCompetencyData'])->name('succession_readiness_ratings.competency_data');
Route::get('/employee_training_dashboard/readiness-score/{employeeId}', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'getReadinessScore'])->name('employee_training_dashboard.readiness_score');
Route::get('/admin/succession-simulations', [App\Http\Controllers\SuccessionSimulationController::class, 'index'])->name('succession_simulations.index')->middleware('auth:admin');
Route::post('/admin/succession-simulations', [App\Http\Controllers\SuccessionSimulationController::class, 'store'])->name('succession_simulations.store')->middleware('auth:admin');
Route::put('/admin/succession-simulations/{id}', [App\Http\Controllers\SuccessionSimulationController::class, 'update'])->name('succession_simulations.update')->middleware('auth:admin');
Route::delete('/admin/succession-simulations/{id}', [App\Http\Controllers\SuccessionSimulationController::class, 'destroy'])->name('succession_simulations.destroy')->middleware('auth:admin');

// Employee Routes - Self Service
Route::get('/employee/leave-applications', [App\Http\Controllers\LeaveApplicationController::class, 'index'])->name('employee.leave_applications.index')->middleware('auth:employee');
Route::post('/employee/leave-applications', [App\Http\Controllers\LeaveApplicationController::class, 'store'])->name('employee.leave_applications.store')->middleware('auth:employee');
Route::get('/employee/leave-applications/{id}', [App\Http\Controllers\LeaveApplicationController::class, 'show'])->name('employee.leave_applications.show')->middleware('auth:employee');
Route::put('/employee/leave-applications/{id}', [App\Http\Controllers\LeaveApplicationController::class, 'update'])->name('employee.leave_applications.update')->middleware('auth:employee');
Route::delete('/employee/leave-applications/{id}', [App\Http\Controllers\LeaveApplicationController::class, 'cancel'])->name('employee.leave_applications.cancel')->middleware('auth:employee');
Route::get('/employee/attendance-logs', [App\Http\Controllers\AttendanceTimeLogController::class, 'index'])->name('employee.attendance_logs.index')->middleware('auth:employee');
Route::post('/employee/attendance/time-in', [App\Http\Controllers\AttendanceTimeLogController::class, 'timeIn'])->name('employee.attendance.time_in')->middleware('auth:employee');
Route::post('/employee/attendance/time-out', [App\Http\Controllers\AttendanceTimeLogController::class, 'timeOut'])->name('employee.attendance.time_out')->middleware('auth:employee');
Route::get('/employee/attendance/status', [App\Http\Controllers\AttendanceTimeLogController::class, 'getCurrentStatus'])->name('employee.attendance.status')->middleware('auth:employee');
Route::get('/employee/attendance/{logId}/details', [App\Http\Controllers\AttendanceTimeLogController::class, 'getDetails'])->name('employee.attendance.details')->middleware('auth:employee');
Route::post('/employee/attendance/correction-request', [App\Http\Controllers\AttendanceTimeLogController::class, 'submitCorrectionRequest'])->name('employee.attendance.correction_request')->middleware('auth:employee');

// Payslip Resource Routes
Route::resource('payslips', App\Http\Controllers\PayslipController::class)->middleware('auth:admin');
Route::get('/payslips/download-all', [App\Http\Controllers\PayslipController::class, 'downloadAll'])->name('payslips.download_all')->middleware('auth:admin');
Route::get('/payslips/{id}/download', [App\Http\Controllers\PayslipController::class, 'download'])->name('payslips.download')->middleware('auth:admin');

Route::get('/employee/claim-reimbursements', [App\Http\Controllers\ClaimReimbursementController::class, 'index'])->name('employee.claim_reimbursements.index')->middleware('auth:employee');
Route::post('/employee/claim-reimbursements', [App\Http\Controllers\ClaimReimbursementController::class, 'store'])->name('employee.claim_reimbursements.store')->middleware('auth:employee');
Route::get('/employee/claim-reimbursements/{id}', [App\Http\Controllers\ClaimReimbursementController::class, 'show'])->name('employee.claim_reimbursements.show')->middleware('auth:employee');
Route::put('/employee/claim-reimbursements/{id}', [App\Http\Controllers\ClaimReimbursementController::class, 'update'])->name('employee.claim_reimbursements.update')->middleware('auth:employee');
Route::delete('/employee/claim-reimbursements/{id}/cancel', [App\Http\Controllers\ClaimReimbursementController::class, 'cancel'])->name('employee.claim_reimbursements.cancel')->middleware('auth:employee');
Route::get('/employee/claim-reimbursements/{id}/download-receipt', [App\Http\Controllers\ClaimReimbursementController::class, 'downloadReceipt'])->name('employee.claim_reimbursements.download_receipt')->middleware('auth:employee');
Route::get('/employee/requests', [App\Http\Controllers\RequestFormController::class, 'index'])->name('employee.requests.index')->middleware('auth:employee');
Route::post('/employee/requests', [App\Http\Controllers\RequestFormController::class, 'store'])->name('employee.requests.store')->middleware('auth:employee');
// Employee profile update route
Route::post('/employee/profile/update', [EmployeeController::class, 'updateProfile'])->name('employee.profile.update')->middleware('auth:employee');

// Employee Settings routes
Route::get('/employee/settings', [EmployeeController::class, 'settings'])->name('employee.settings')->middleware('auth:employee');
Route::put('/employee/settings', [EmployeeController::class, 'updateSettings'])->name('employee.updateSettings')->middleware('auth:employee');
Route::post('/employee/verify-password', [EmployeeController::class, 'verifyPassword'])->name('employee.verify_password')->middleware('auth:employee');

// Employee password verification for claim reimbursement
Route::post('/employee/verify-password', [EmployeeDashboardController::class, 'verifyPassword'])->name('employee.dashboard.verify_password')->middleware('auth:employee');

Route::get('/employee/my-trainings', [App\Http\Controllers\MyTrainingController::class, 'index'])->name('employee.my_trainings.index')->middleware('auth:employee');

// Training export routes
Route::get('/employee/trainings/export/pdf', [App\Http\Controllers\MyTrainingController::class, 'exportPdf'])->name('employee.trainings.export.pdf')->middleware('auth:employee');
Route::get('/employee/trainings/export/excel', [App\Http\Controllers\MyTrainingController::class, 'exportExcel'])->name('employee.trainings.export.excel')->middleware('auth:employee');

// Training CRUD routes
Route::post('/employee/my-trainings', [App\Http\Controllers\MyTrainingController::class, 'store'])->name('employee.my_trainings.store')->middleware('auth:employee');
Route::put('/employee/my-trainings/{id}', [App\Http\Controllers\MyTrainingController::class, 'update'])->name('employee.my_trainings.update')->middleware('auth:employee');
Route::delete('/employee/my-trainings/{id}', [App\Http\Controllers\MyTrainingController::class, 'destroy'])->name('employee.my_trainings.destroy')->middleware('auth:employee');

// Certificate download route
Route::get('/employee/certificate/download/{id}', function($id) {
    // Sample certificate download - replace with actual file download
    return response()->json(['message' => 'Certificate download functionality not yet implemented']);
})->name('employee.certificate.download')->middleware('auth:employee');

// Certificate tracking routes
Route::get('/certificates/view/{id}', [App\Http\Controllers\CertificateGenerationController::class, 'viewCertificate'])->name('certificates.view')->middleware('auth:admin');

Route::get('/certificates/download/{id}', [App\Http\Controllers\CertificateGenerationController::class, 'downloadCertificate'])->name('certificates.download')->middleware('auth:admin');

// Certificate generation routes
Route::get('/certificates/preview', [App\Http\Controllers\CertificateGenerationController::class, 'previewTemplate'])->name('certificates.preview')->middleware('auth:admin');
Route::post('/certificates/generate', [App\Http\Controllers\CertificateGenerationController::class, 'generateManualCertificate'])->name('certificates.generate')->middleware('auth:admin');
Route::post('/certificates/bulk-generate', [App\Http\Controllers\CertificateGenerationController::class, 'bulkGenerateCertificates'])->name('certificates.bulk_generate')->middleware('auth:admin');

// Exam routes
Route::get('/employee/exam/start/{courseId}', [ExamController::class, 'startExam'])->name('employee.exam.start')->middleware('auth:employee');
Route::get('/employee/exam/take/{attemptId}', [ExamController::class, 'take'])->name('employee.exam.take')->middleware('auth:employee');
Route::post('/employee/exam/submit/{attemptId}', [ExamController::class, 'submitAjax'])->name('employee.exam.submit')->middleware('auth:employee');
Route::post('/employee/exam/submit-ajax/{attemptId}', [ExamController::class, 'submitAjax'])->name('employee.exam.submit_ajax')->middleware('auth:employee');
Route::get('/employee/exam/result/{attemptId}', [ExamController::class, 'result'])->name('employee.exam.result')->middleware('auth:employee');
Route::get('/employee/exam/simple-result/{attemptId}', [ExamController::class, 'simpleResult'])->name('employee.exam.simple_result')->middleware('auth:employee');

// Training feedback routes
Route::get('/employee/training-feedback', [App\Http\Controllers\TrainingFeedbackController::class, 'index'])->name('employee.training_feedback.index')->middleware('auth:employee');
Route::post('/employee/training-feedback', [App\Http\Controllers\TrainingFeedbackController::class, 'store'])->name('employee.training_feedback.store')->middleware('auth:employee');
Route::get('/employee/training-feedback/{id}', [App\Http\Controllers\TrainingFeedbackController::class, 'show'])->name('employee.training_feedback.show')->middleware('auth:employee');
Route::put('/employee/training-feedback/{id}', [App\Http\Controllers\TrainingFeedbackController::class, 'update'])->name('employee.training_feedback.update')->middleware('auth:employee');
Route::delete('/employee/training-feedback/{id}', [App\Http\Controllers\TrainingFeedbackController::class, 'destroy'])->name('employee.training_feedback.destroy')->middleware('auth:employee');
Route::get('/employee/competency-profile', [App\Http\Controllers\Employee\CompetencyProfileController::class, 'index'])->name('employee.competency_profile.index')->middleware('auth:employee');

// Competency Profile AJAX routes
Route::get('/employee/competency-profile/progress-data', [App\Http\Controllers\Employee\CompetencyProfileController::class, 'getProgressData'])->name('employee.competency_profile.progress_data')->middleware('auth:employee');

Route::get('/employee/competency-profile/{id}', [App\Http\Controllers\Employee\CompetencyProfileController::class, 'show'])->name('employee.competency_profile.show')->middleware('auth:employee');

Route::post('/employee/competency-profile/{id}/request-feedback', [App\Http\Controllers\Employee\CompetencyProfileController::class, 'requestFeedback'])->name('employee.competency_profile.request_feedback')->middleware('auth:employee');

Route::post('/employee/competency-profile/{id}/update-progress', [App\Http\Controllers\Employee\CompetencyProfileController::class, 'updateProgress'])->name('employee.competency_profile.update_progress')->middleware('auth:employee');

Route::post('/employee/competency-profile/{id}/start-training', [App\Http\Controllers\Employee\CompetencyProfileController::class, 'startTraining'])->name('employee.competency_profile.start_training')->middleware('auth:employee');

Route::get('/employee/profile-updates', [App\Http\Controllers\ProfileUpdateController::class, 'index'])->name('employee.profile_updates.index')->middleware('auth:employee');
Route::post('/employee/profile-updates', [App\Http\Controllers\ProfileUpdateController::class, 'store'])->name('employee.profile_updates.store')->middleware('auth:employee');
Route::get('/employee/profile-updates/{profileUpdate}', [App\Http\Controllers\ProfileUpdateController::class, 'show'])->name('employee.profile_updates.show')->middleware('auth:employee');
Route::get('/employee/profile-updates/{profileUpdate}/edit', [App\Http\Controllers\ProfileUpdateController::class, 'edit'])->name('employee.profile_updates.edit')->middleware('auth:employee');
Route::put('/employee/profile-updates/{profileUpdate}', [App\Http\Controllers\ProfileUpdateController::class, 'update'])->name('employee.profile_updates.update')->middleware('auth:employee');
Route::delete('/employee/profile-updates/{profileUpdate}', [App\Http\Controllers\ProfileUpdateController::class, 'destroy'])->name('employee.profile_updates.destroy')->middleware('auth:employee');
Route::get('/employee/profile-updates/{profileUpdate}/details', [App\Http\Controllers\ProfileUpdateController::class, 'details'])->name('employee.profile_updates.details')->middleware('auth:employee');
Route::post('/employee/verify-password', [App\Http\Controllers\ProfileUpdateController::class, 'verifyPassword'])->name('employee.verify_password')->middleware('auth:employee');

// Employee destination training response routes
Route::post('/employee/destination-training/accept', [App\Http\Controllers\MyTrainingController::class, 'acceptDestinationTraining'])->name('employee.destination_training.accept')->middleware('auth:employee');
Route::post('/employee/destination-training/decline', [App\Http\Controllers\MyTrainingController::class, 'declineDestinationTraining'])->name('employee.destination_training.decline')->middleware('auth:employee');
Route::get('/employee/destination-training/details/{id}', [App\Http\Controllers\MyTrainingController::class, 'getDestinationTrainingDetails'])->name('employee.destination_training.details')->middleware('auth:employee');

// Activity Logs Route
Route::get('/admin/activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity_logs.index')->middleware('auth:admin');
