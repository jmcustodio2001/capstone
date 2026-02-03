<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
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
use App\Http\Controllers\EmployeeCertificationController;
use App\Http\Controllers\EmployeeAwardController;
use App\Http\Controllers\TrainingProgressUpdateController;
use App\Http\Controllers\ReportsController;

Route::get('/', function () {
    return view('landingpage');
})->name('welcome');

Route::get('/about', function () {
    return view('aboutpage');
})->name('about');

Route::get('/contact', function () {
    return view('contactpage');
})->name('contact');

Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome.page');

// Test DomPDF functionality
Route::get('/test-pdf', function () {
    try {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML('<h1>Test PDF</h1><p>DomPDF is working!</p>');
        return $pdf->download('test.pdf');
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'DomPDF Error: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'dompdf_available' => class_exists('\Barryvdh\DomPDF\Facade\Pdf')
        ], 500);
    }
});

// Test Email Configuration
Route::get('/test-email', function () {
    try {
        $testEmail = 'jm.custodio092001@gmail.com'; // Test recipient

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = env('MAIL_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->SMTPSecure = env('MAIL_ENCRYPTION') === 'tls' ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = env('MAIL_PORT');

        // Enable debug output
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'html';

        // Recipients
        $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $mail->addAddress($testEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Test Email Configuration';
        $mail->Body = '<h1>Email Test Successful!</h1><p>Your email configuration is working properly.</p>';

        $result = $mail->send();

        return response()->json([
            'success' => true,
            'message' => 'Email sent successfully!',
            'config' => [
                'host' => env('MAIL_HOST'),
                'port' => env('MAIL_PORT'),
                'username' => env('MAIL_USERNAME'),
                'from_address' => env('MAIL_FROM_ADDRESS'),
                'encryption' => env('MAIL_ENCRYPTION')
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'config' => [
                'host' => env('MAIL_HOST'),
                'port' => env('MAIL_PORT'),
                'username' => env('MAIL_USERNAME'),
                'from_address' => env('MAIL_FROM_ADDRESS'),
                'encryption' => env('MAIL_ENCRYPTION')
            ]
        ], 500);
    }
});

// Check current email configuration
Route::get('/check-email-config', function () {
    return response()->json([
        'mail_config' => [
            'MAIL_MAILER' => env('MAIL_MAILER'),
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_USERNAME' => env('MAIL_USERNAME') ? 'SET' : 'NOT SET',
            'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? 'SET' : 'NOT SET',
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME')
        ],
        'phpmailer_available' => class_exists('PHPMailer\PHPMailer\PHPMailer'),
        'recommendations' => [
            'For Gmail SMTP' => [
                'MAIL_MAILER=smtp',
                'MAIL_HOST=smtp.gmail.com',
                'MAIL_PORT=587',
                'MAIL_USERNAME=your-email@gmail.com',
                'MAIL_PASSWORD=your-app-password',
                'MAIL_ENCRYPTION=tls',
                'MAIL_FROM_ADDRESS=your-email@gmail.com',
                'MAIL_FROM_NAME="HR2ESS System"'
            ],
            'For Mailtrap (Testing)' => [
                'MAIL_MAILER=smtp',
                'MAIL_HOST=smtp.mailtrap.io',
                'MAIL_PORT=2525',
                'MAIL_USERNAME=your-mailtrap-username',
                'MAIL_PASSWORD=your-mailtrap-password',
                'MAIL_ENCRYPTION=tls',
                'MAIL_FROM_ADDRESS=test@example.com',
                'MAIL_FROM_NAME="HR2ESS System"'
            ]
        ]
    ]);
});

// General login route - redirect to employee login by default
Route::get('/login', function () {
    return redirect()->route('employee.login');
})->name('login');

// Admin login routes
Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/verify-otp', [AdminController::class, 'verifyOTP'])->name('admin.verify_otp');
Route::post('/admin/resend-otp', [AdminController::class, 'resendOTP'])->name('admin.resend_otp');
Route::get('/admin/csrf-token', [AdminController::class, 'refreshCSRF'])->name('admin.csrf_token');
Route::get('/admin/test-email', function() {
    try {
        // Check what admin users exist in the database
        $adminUsers = \App\Models\User::whereIn('role', ['admin', 'superadmin'])->get();

        Log::info("Found admin users:", $adminUsers->toArray());

        if ($adminUsers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No admin users found in database',
                'admin_users' => []
            ]);
        }

        // Use the first admin user found
        $adminUser = $adminUsers->first();

        $otpService = new \App\Services\OTPService();

        // Test admin user object
        $testAdmin = (object) [
            'email' => $adminUser->email,
            'first_name' => $adminUser->name,
            'last_name' => '',
            'employee_id' => 'ADMIN_' . $adminUser->id
        ];

        $testOTP = '123456';

        Log::info("Testing admin email sending to: {$testAdmin->email}");

        $result = $otpService->sendAdminOTP($testAdmin, $testOTP);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'test_email' => $testAdmin->email,
            'test_otp' => $testOTP,
            'admin_users_in_db' => $adminUsers->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ];
            })
        ]);

    } catch (\Exception $e) {
        Log::error('Test email error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
})->name('admin.test_email');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::post('/admin/verify-password', [AdminController::class, 'verifyPassword'])->name('admin.verify_password')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/check-password-verification', [AdminController::class, 'checkPasswordVerification'])->name('admin.check_password_verification')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard')->middleware('auth:admin');
Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update')->middleware(['auth:admin', 'admin.auth']);

// Admin Topbar API Routes
Route::get('/admin/system-status', [AdminController::class, 'getSystemStatus'])->name('admin.system_status')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/notifications', [AdminController::class, 'getNotifications'])->name('admin.notifications')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/notifications/count', [AdminController::class, 'getNotificationCount'])->name('admin.notifications.count')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/notifications/mark-all-read', [AdminController::class, 'markAllNotificationsRead'])->name('admin.notifications.mark_all_read')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/employees', [AdminController::class, 'employeeList'])->name('admin.employees.index')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/employees', [AdminController::class, 'createEmployee'])->name('admin.employees.create')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/reports/generate/{type}', [AdminController::class, 'generateReport'])->name('admin.reports.generate')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/reports', [ReportsController::class, 'index'])->name('admin.reports')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/reports/export', [ReportsController::class, 'export'])->name('admin.reports.export')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/system/backup', [AdminController::class, 'createSystemBackup'])->name('admin.system.backup')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/system/clear-cache', [AdminController::class, 'clearSystemCache'])->name('admin.system.clear_cache')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/user-activity', [AdminController::class, 'getUserActivity'])->name('admin.user_activity')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/system-logs', [AdminController::class, 'getSystemLogs'])->name('admin.system_logs')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/database-status', [AdminController::class, 'getDatabaseStatus'])->name('admin.database_status')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/maintenance-mode/enable', [AdminController::class, 'enableMaintenanceMode'])->name('admin.maintenance_mode.enable')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/change-password', [AdminController::class, 'changePassword'])->name('admin.change_password')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/refresh-csrf', [AdminController::class, 'refreshCSRF'])->name('admin.refresh_csrf')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/reset-uptime', [AdminController::class, 'resetSystemUptime'])->name('admin.reset_uptime')->middleware(['auth:admin', 'admin.auth']);

// Security Settings Routes
Route::get('/admin/security-settings', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'getSettings'])->name('admin.security_settings.get')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/security-settings', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'updateSettings'])->name('admin.security_settings.update')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/security/verify-password', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'verifyPassword'])->name('admin.security.verify_password')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/audit-logs', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'getAuditLogs'])->name('admin.audit_logs')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/timeout-settings', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'getTimeoutSettings'])->name('admin.timeout_settings')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/maintenance-mode/toggle', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'toggleMaintenanceMode'])->name('admin.maintenance_mode.toggle')->middleware(['auth:admin', 'admin.auth']);

// Security Alerts Routes
Route::get('/admin/security-alerts', [App\Http\Controllers\Admin\SecurityAlertsController::class, 'getAlerts'])->name('admin.security_alerts')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/security-alerts/{id}/read', [App\Http\Controllers\Admin\SecurityAlertsController::class, 'markAsRead'])->name('admin.security_alerts.read')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/security-alerts/mark-all-read', [App\Http\Controllers\Admin\SecurityAlertsController::class, 'markAllAsRead'])->name('admin.security_alerts.mark_all_read')->middleware(['auth:admin', 'admin.auth']);

// Test route to manually reset uptime (remove in production)
Route::get('/admin/test-reset-uptime', function() {
    try {
        $appStartFile = storage_path('framework/cache/app_start_time');
        if (file_exists($appStartFile)) {
            unlink($appStartFile);
        }
        file_put_contents($appStartFile, time());
        session(['admin_session_start' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Uptime reset manually',
            'file_path' => $appStartFile,
            'new_timestamp' => time(),
            'session_start' => session('admin_session_start')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
})->middleware(['auth:admin', 'admin.auth']);

// Cleanup phantom training records
Route::post('/admin/cleanup-phantom-records', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'cleanupPhantomRecords'])->name('admin.cleanup_phantom_records')->middleware('auth:admin');

// Employee Authentication Routes
// CSRF Token endpoint (accessible without authentication)
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

Route::group(['prefix' => 'employee', 'as' => 'employee.'], function () {
    Route::get('/login', [EmployeeController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [EmployeeController::class, 'login'])->name('login.submit');
    Route::post('/verify-otp', [EmployeeController::class, 'verifyOTP'])->name('verify_otp');
    Route::post('/resend-otp', [EmployeeController::class, 'resendOTP'])->name('resend_otp');
    Route::post('/logout', [EmployeeController::class, 'logout'])->name('logout');

    // Simple password verification route (always returns success since we removed password validation)
    Route::post('/verify-password', function() {
        return response()->json(['success' => true, 'message' => 'Password verified']);
    })->name('verify_password');

    // Forgot Password Routes
    Route::get('/forgot-password', [EmployeeController::class, 'showForgotPasswordForm'])->name('forgot_password');
    Route::post('/forgot-password/send-code', [EmployeeController::class, 'sendForgotPasswordCode'])->name('forgot_password.send_code');
    Route::post('/forgot-password/verify-otp', [EmployeeController::class, 'verifyForgotPasswordOTP'])->name('forgot_password.verify_otp');
    Route::post('/forgot-password/resend-code', [EmployeeController::class, 'resendForgotPasswordCode'])->name('forgot_password.resend_code');
    Route::post('/forgot-password/reset', [EmployeeController::class, 'resetPassword'])->name('forgot_password.reset');
});

// Test OTP system (remove in production)
Route::get('/test-otp-system', function() {
    try {
        // Check if PHPMailer is available
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return response()->json([
                'status' => 'error',
                'message' => 'PHPMailer not installed. Run: composer install',
                'phpmailer_available' => false
            ]);
        }

        // Check if we have employees
        $employee = \App\Models\Employee::first();
        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'No employees found in database',
                'employee_count' => 0
            ]);
        }

        // Test OTP service
        $otpService = new \App\Services\OTPService();

        // Check email configuration
        $emailConfig = [
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_USERNAME' => env('MAIL_USERNAME'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION')
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'OTP system ready for testing',
            'phpmailer_available' => true,
            'employee_count' => \App\Models\Employee::count(),
            'test_employee' => [
                'id' => $employee->employee_id,
                'email' => $employee->email,
                'name' => $employee->first_name . ' ' . $employee->last_name
            ],
            'email_config' => $emailConfig,
            'otp_fields_added' => \Illuminate\Support\Facades\Schema::hasColumns('employees', [
                'otp_code', 'otp_expires_at', 'otp_attempts', 'last_otp_sent_at', 'otp_verified', 'email_verified_at'
            ]),
            'next_steps' => [
                '1. Configure email settings in .env file',
                '2. Run: composer install (to install PHPMailer)',
                '3. Test login at /employee/login',
                '4. Remove this test route in production'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error testing OTP system: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('test.otp.system');
// Employee dashboard (protected route)
Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard')->middleware('employee.auth');

// Employee dashboard additional routes
Route::middleware('employee.auth')->group(function () {
    Route::post('/employee/verify-password', [EmployeeDashboardController::class, 'verifyPassword'])->name('employee.verify-password');
    Route::get('/employee/announcements/{id}', [EmployeeDashboardController::class, 'getAnnouncementDetails'])->name('employee.announcements.details');
    Route::post('/employee/profile/update', [EmployeeDashboardController::class, 'updateProfile'])->name('employee.profile.update');
    Route::post('/employee/leave-application', [EmployeeDashboardController::class, 'submitLeaveApplication'])->name('employee.leave-application.submit');
    Route::post('/employee/attendance/log', [EmployeeDashboardController::class, 'logAttendance'])->name('employee.attendance.log');
    Route::get('/employee/dashboard/get-counts', [EmployeeDashboardController::class, 'getDashboardCounts'])->name('employee.dashboard.get_counts');
    Route::get('/employee/fetch-rewards', [EmployeeDashboardController::class, 'fetchGivenRewards'])->name('employee.fetch_rewards');

    // Employee Training Progress - create or update
    Route::post('/employee/training-progress/create-or-update', [TrainingProgressUpdateController::class, 'store'])->name('employee.training_progress.create_or_update');
    Route::post('/employee/training/refresh-progress', [TrainingProgressUpdateController::class, 'refreshProgress'])->name('employee.training.refresh_progress');

    // Employee Training Data Refresh
    Route::get('/employee/my-trainings/refresh-data', [App\Http\Controllers\MyTrainingController::class, 'refreshData'])->name('employee.my_trainings.refresh_data');
    Route::get('/employee/my-trainings/get-counts', [App\Http\Controllers\MyTrainingController::class, 'getTrainingCounts'])->name('employee.my_trainings.get_counts');
    Route::post('/employee/my-trainings/auto-create-requests', [App\Http\Controllers\MyTrainingController::class, 'autoCreateRequests'])->name('employee.my_trainings.auto_create_requests');
});
// Test OTP Route (for debugging)
Route::get('/test-otp/{email}', function($email) {
    try {
        $employee = \App\Models\Employee::where('email', $email)->first();
        if (!$employee) {
            return response()->json(['error' => 'Employee not found with email: ' . $email]);
        }

        $otpService = new \App\Services\OTPService();
        $result = $otpService->sendOTP($employee);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'result' => $result,
            'smtp_config' => [
                'MAIL_MAILER' => env('MAIL_MAILER'),
                'MAIL_HOST' => env('MAIL_HOST'),
                'MAIL_PORT' => env('MAIL_PORT'),
                'MAIL_USERNAME' => env('MAIL_USERNAME') ? 'SET' : 'NOT SET',
                'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? 'SET' : 'NOT SET',
                'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
                'OTP_BYPASS_EMAIL' => env('OTP_BYPASS_EMAIL', false)
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('test.otp.debug');

// Employee competency training response route
Route::post('/employee/competency-training/respond', [EmployeeController::class, 'respondToCompetencyTraining'])->name('employee.competency_training.respond')->middleware('employee.auth');

// Admin Routes - Competency Management
Route::get('/admin/competency-library', [CompetencyLibraryController::class, 'index'])->name('admin.competency_library.index')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-library', [CompetencyLibraryController::class, 'store'])->name('admin.competency_library.store')->middleware(['auth:admin', 'admin.auth']);
Route::put('/admin/competency-library/{id}', [CompetencyLibraryController::class, 'update'])->name('admin.competency_library.update')->middleware(['auth:admin', 'admin.auth']);
Route::delete('/admin/competency-library/{id}', [CompetencyLibraryController::class, 'destroy'])->name('admin.competency_library.destroy')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-library/sync-destination-knowledge', [CompetencyLibraryController::class, 'syncDestinationKnowledgeTraining'])->name('admin.competency_library.sync_destination_knowledge')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-library/fix-destination-competencies', [CompetencyLibraryController::class, 'fixDestinationCompetencies'])->name('admin.competency_library.fix_destination_competencies')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-library/cleanup-destination-duplicates', [CompetencyLibraryController::class, 'cleanupDestinationDuplicates'])->name('admin.competency_library.cleanup_destination_duplicates')->middleware(['auth:admin', 'admin.auth']);

// Remove destination training competencies from competency library
Route::get('/admin/competency-library/remove-destination-training', [CompetencyLibraryController::class, 'removeDestinationTrainingCompetencies'])
    ->name('admin.competency_library.remove_destination_training');

// Remove destination training competency gaps
Route::get('/admin/competency-gap/remove-destination-training-gaps', [CompetencyGapAnalysisController::class, 'removeDestinationTrainingGaps'])
    ->name('admin.competency_gap.remove_destination_training_gaps');

Route::post('/admin/competency-library/{id}/notify-course-management', [CompetencyLibraryController::class, 'notifyCourseManagement'])->name('admin.competency_library.notify_course_management')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/course-management/notifications/{id}/mark-read', [CourseManagementController::class, 'markNotificationAsRead'])->name('admin.course_management.mark_notification_read')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/course-management/notifications/{id}/accept-create-course', [CourseManagementController::class, 'acceptNotificationAndCreateCourse'])->name('admin.course_management.accept_create_course')->middleware(['auth:admin', 'admin.auth']);
Route::delete('/admin/course-management/notifications/{id}', [CourseManagementController::class, 'deleteNotification'])->name('admin.course_management.delete_notification')->middleware(['auth:admin', 'admin.auth']);

// Test route to check if API is working
Route::get('/admin/test-employee-api', function() {
    $employees = \App\Models\Employee::take(5)->get(['employee_id', 'first_name', 'last_name']);
    return response()->json([
        'message' => 'API is working',
        'sample_employees' => $employees,
        'total_count' => \App\Models\Employee::count()
    ]);
})->middleware(['auth:admin', 'admin.auth']);

Route::get('/admin/employee-profile/{employeeId}', [EmployeeController::class, 'getEmployeeProfile'])->name('admin.employee_profile')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/employee-competency-profiles', [EmployeeCompetencyProfileController::class, 'index'])->name('employee_competency_profiles.index')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/employee-competency-profiles', [EmployeeCompetencyProfileController::class, 'store'])->name('employee_competency_profiles.store')->middleware(['auth:admin', 'admin.auth']);
Route::put('/admin/employee-competency-profiles/{id}', [EmployeeCompetencyProfileController::class, 'update'])->name('employee_competency_profiles.update')->middleware(['auth:admin', 'admin.auth']);
Route::delete('/admin/employee-competency-profiles/{id}', [EmployeeCompetencyProfileController::class, 'destroy'])->name('employee_competency_profiles.destroy')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/employee-competency-profiles/{id}/notify-course-management', [EmployeeCompetencyProfileController::class, 'notifyCourseManagement'])->name('employee_competency_profiles.notify_course_management')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/employee-competency-profiles/sync-training', [EmployeeCompetencyProfileController::class, 'syncWithTrainingProgress'])->name('employee_competency_profiles.sync_training')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/employee-competency-profiles/get-training-progress', [EmployeeCompetencyProfileController::class, 'getTrainingProgress'])->name('employee_competency_profiles.get_training_progress')->middleware(['auth:admin', 'admin.auth']);
Route::get('/api/employee-competency-profile/{employeeId}/{competencyId}', [EmployeeCompetencyProfileController::class, 'getCurrentLevel'])->name('api.employee_competency_profile.current_level')->middleware('auth:admin');

// Employee Competency Profile skill gap detection
Route::get('/admin/employee-competency-profiles/detect-skill-gaps/{employeeId}', [EmployeeCompetencyProfileController::class, 'detectSkillGaps'])
    ->name('employee_competency_profiles.detect_skill_gaps')->middleware(['auth:admin', 'admin.auth']);

// Get employee's existing skills
Route::get('/admin/employee-competency-profiles/get-employee-skills/{employeeId}', [EmployeeCompetencyProfileController::class, 'getEmployeeSkills'])
    ->name('employee_competency_profiles.get_employee_skills')->middleware(['auth:admin', 'admin.auth']);

// Initialize basic skills for employee
Route::post('/admin/employee-competency-profiles/initialize-basic-skills/{employeeId}', [EmployeeCompetencyProfileController::class, 'initializeBasicSkills'])
    ->name('employee_competency_profiles.initialize_basic_skills')->middleware(['auth:admin', 'admin.auth']);

// Employee Certification Routes
Route::get('/admin/employee-certifications', [EmployeeCertificationController::class, 'index'])->name('employee_certifications.index')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/employee-certifications', [EmployeeCertificationController::class, 'store'])->name('employee_certifications.store')->middleware(['auth:admin', 'admin.auth']);
Route::put('/admin/employee-certifications/{certification}', [EmployeeCertificationController::class, 'update'])->name('employee_certifications.update')->middleware(['auth:admin', 'admin.auth']);
Route::delete('/admin/employee-certifications/{certification}', [EmployeeCertificationController::class, 'destroy'])->name('employee_certifications.destroy')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/employee-certifications/employee/{employeeId}', [EmployeeCertificationController::class, 'getEmployeeCertificates'])->name('employee_certifications.employee')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/employee-certifications/verify-password', [EmployeeCertificationController::class, 'verifyPassword'])->name('employee_certifications.verify_password')->middleware(['auth:admin', 'admin.auth']);

// Employee Awards Routes
Route::get('/admin/employee-awards', [EmployeeAwardController::class, 'getAllAwards'])->name('employee_awards.all')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/employee-awards/employee/{employeeId}', [EmployeeAwardController::class, 'getEmployeeAwards'])->name('employee_awards.employee')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/employee-awards', [EmployeeAwardController::class, 'store'])->name('employee_awards.store')->middleware(['auth:admin', 'admin.auth']);
Route::put('/admin/employee-awards/{awardId}/status', [EmployeeAwardController::class, 'updateStatus'])->name('employee_awards.update_status')->middleware(['auth:admin', 'admin.auth']);
Route::delete('/admin/employee-awards/{awardId}', [EmployeeAwardController::class, 'destroy'])->name('employee_awards.destroy')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/employee-awards/statistics/{employeeId?}', [EmployeeAwardController::class, 'getStatistics'])->name('employee_awards.statistics')->middleware(['auth:admin', 'admin.auth']);

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
Route::post('/admin/competency-gap-analysis/{id}/unassign-training', [CompetencyGapAnalysisController::class, 'unassignFromTraining'])->name('competency_gap_analysis.unassign_training')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-gap-analysis/fix-expiration-dates', [CompetencyGapAnalysisController::class, 'fixExpirationDates'])->name('competency_gap_analysis.fix_expiration_dates')->middleware(['auth:admin', 'admin.auth']);
Route::get('/admin/competency-gap-analysis/create-table', [CompetencyGapAnalysisController::class, 'createCompetencyGapsTable'])->name('competency_gap_analysis.create_table')->middleware(['auth:admin', 'admin.auth']);
Route::post('/admin/competency-gap-analysis/auto-detect-gaps', [CompetencyGapAnalysisController::class, 'autoDetectGaps'])->name('competency_gap_analysis.auto_detect_gaps')->middleware(['auth:admin', 'admin.auth']);

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
Route::post('/admin/employee-trainings-dashboard/fix-course-info', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'fixMissingCourseInfo'])->name('admin.employee_trainings_dashboard.fix_course_info')->middleware('auth:admin');
Route::post('/admin/employee-trainings-dashboard/export', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'exportTrainingData'])->name('admin.employee_trainings_dashboard.export')->middleware('auth:admin');
Route::get('/admin/employee-trainings-dashboard/remove-unknown-courses', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'removeUnknownCourseRecords'])->name('admin.employee_trainings_dashboard.remove_unknown_courses')->middleware('auth:admin');
Route::get('/admin/training-record-certificate-tracking', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'index'])->name('training_record_certificate_tracking.index')->middleware('auth:admin');
Route::get('/admin/training-record-certificate-tracking/{id}', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'show'])->name('training_record_certificate_tracking.show')->middleware('auth:admin');
Route::post('/admin/training-record-certificate-tracking', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'store'])->name('training_record_certificate_tracking.store')->middleware('auth:admin');
Route::put('/admin/training-record-certificate-tracking/{id}', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'update'])->name('training_record_certificate_tracking.update')->middleware('auth:admin');
Route::delete('/admin/training-record-certificate-tracking/{id}', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'destroy'])->name('training_record_certificate_tracking.destroy')->middleware('auth:admin');
Route::post('/admin/training-record-certificate-tracking/auto-generate', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'autoGenerateMissingCertificates'])->name('training_record_certificate_tracking.auto_generate')->middleware('auth:admin');
Route::get('/admin/training-record-certificate-tracking/create-table', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'createMissingTable'])->name('training_record_certificate_tracking.create_table')->middleware('auth:admin');
Route::get('/admin/training-record-certificate-tracking/force-create-table', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'forceCreateTable'])->name('training_record_certificate_tracking.force_create_table')->middleware('auth:admin');
Route::get('/admin/training-record-certificate-tracking/execute-table-creation', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'executeTableCreation'])->name('training_record_certificate_tracking.execute_table_creation')->middleware('auth:admin');
Route::get('/admin/training-certificate-tracking/fix-training-date-column', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'fixTrainingDateColumn'])->name('training_record_certificate_tracking.fix_training_date_column')->middleware('auth:admin');
Route::get('/admin/training-certificate-tracking/quick-fix-column', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'quickFixTrainingDateColumn'])->name('training_record_certificate_tracking.quick_fix_column')->middleware('auth:admin');
Route::get('/admin/training-certificate-tracking/execute-fix-now', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'executeFixNow'])->name('training_record_certificate_tracking.execute_fix_now')->middleware('auth:admin');
Route::get('/admin/training-certificate-tracking/fix-table-structure', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'fixTableStructure'])->name('training_record_certificate_tracking.fix_table_structure')->middleware('auth:admin');
Route::get('/admin/fix-training-date-column-now', [App\Http\Controllers\TrainingRecordCertificateTrackingController::class, 'fixColumnNow'])->name('training_record_certificate_tracking.fix_column_now')->middleware('auth:admin');
Route::get('/certificates/view/{id}', [App\Http\Controllers\CertificateGenerationController::class, 'viewCertificate'])->name('certificates.view');
Route::get('/certificates/download/{id}', [App\Http\Controllers\CertificateGenerationController::class, 'downloadCertificate'])->name('certificates.download');
Route::get('/certificates/force-generate/{id}', [App\Http\Controllers\CertificateGenerationController::class, 'forceGenerateCertificate'])->name('certificates.force_generate');

// Admin Routes - Training Management
// Specific routes must come before general resource routes
Route::post('/admin/destination-knowledge-training/assign-to-upcoming', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'assignToUpcomingTraining'])->name('admin.destination-knowledge-training.assign-to-upcoming')->middleware('auth:admin');
Route::get('/admin/destination-knowledge-training/export-excel', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'exportExcel'])->name('admin.destination-knowledge-training.export-excel')->middleware('auth:admin');
Route::get('/admin/destination-knowledge-training/export-pdf', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'exportPdf'])->name('admin.destination-knowledge-training.export-pdf')->middleware('auth:admin');
Route::post('/admin/destination-knowledge-training/sync-competency-profiles', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'syncAllWithCompetencyProfiles'])->name('admin.destination-knowledge-training.sync-competency-profiles')->middleware('auth:admin');
Route::post('/admin/destination-knowledge-training/store-possible', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'storePossibleDestination'])->name('admin.destination-knowledge-training.store-possible')->middleware('auth:admin');
Route::delete('/admin/destination-knowledge-training/destroy-possible/{id}', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'destroyPossible'])->name('admin.destination-knowledge-training.destroy-possible')->middleware('auth:admin');
Route::post('/admin/destination-knowledge-training/{id}/request-activation', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'requestActivation'])->name('admin.destination-knowledge-training.request-activation')->middleware('auth:admin');
Route::post('/admin/destination-knowledge-training/consolidate', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'consolidateDestinationTraining'])->name('admin.destination-knowledge-training.consolidate')->middleware('auth:admin');
Route::get('/admin/destination-knowledge-training/fix-missing-columns', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'fixMissingColumns'])->name('admin.destination-knowledge-training.fix-missing-columns')->middleware('auth:admin');
Route::post('/admin/destination-knowledge-training/employees-by-position', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'getEmployeesByPosition'])->name('admin.destination-knowledge-training.employees-by-position')->middleware('auth:admin');
Route::post('/admin/destination-knowledge-training/activate-all-centers', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'activateAllTrainingCenters'])->name('admin.destination-knowledge-training.activate-all-centers')->middleware('auth:admin');

// General resource routes
Route::get('/admin/destination-knowledge-training', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'index'])->name('admin.destination-knowledge-training.index')->middleware('auth:admin');
Route::post('/admin/destination-knowledge-training', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'store'])->name('admin.destination-knowledge-training.store')->middleware('auth:admin');
Route::put('/admin/destination-knowledge-training/{id}', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'update'])->name('admin.destination-knowledge-training.update')->middleware('auth:admin');
Route::delete('/admin/destination-knowledge-training/{id}', [App\Http\Controllers\DestinationKnowledgeTrainingController::class, 'destroy'])->name('admin.destination-knowledge-training.destroy')->middleware('auth:admin');

// Employee CSRF token endpoint
Route::get('/employee/csrf-token', function() {
    return response()->json(['token' => csrf_token(), 'csrf_token' => csrf_token()]);
});

// Test OTP email sending with detailed debugging
Route::get('/test-otp-email', function() {
    try {
        // Find first employee for testing
        $employee = \App\Models\Employee::first();

        if (!$employee) {
            return response()->json(['error' => 'No employee found for testing']);
        }

        // Show current email configuration
        $emailConfig = [
            'MAIL_MAILER' => env('MAIL_MAILER'),
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_USERNAME' => env('MAIL_USERNAME'),
            'MAIL_PASSWORD_LENGTH' => strlen(env('MAIL_PASSWORD')),
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME')
        ];

        $otpService = new \App\Services\OTPService();
        $result = $otpService->sendOTP($employee);

        return response()->json([
            'employee_email' => $employee->email,
            'email_config' => $emailConfig,
            'result' => $result,
            'timestamp' => now()
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'email_config' => [
                'MAIL_MAILER' => env('MAIL_MAILER'),
                'MAIL_HOST' => env('MAIL_HOST'),
                'MAIL_PORT' => env('MAIL_PORT'),
                'MAIL_USERNAME' => env('MAIL_USERNAME'),
                'MAIL_PASSWORD_LENGTH' => strlen(env('MAIL_PASSWORD')),
                'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
                'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
                'MAIL_FROM_NAME' => env('MAIL_FROM_NAME')
            ]
        ]);
    }
});

// Simple Gmail SMTP connection test
Route::get('/test-gmail-connection', function() {
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = env('MAIL_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = env('MAIL_PORT');

        // SSL options
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Test connection
        $mail->smtpConnect();
        $mail->smtpClose();

        return response()->json([
            'success' => true,
            'message' => 'Gmail SMTP connection successful!',
            'config' => [
                'host' => env('MAIL_HOST'),
                'port' => env('MAIL_PORT'),
                'username' => env('MAIL_USERNAME'),
                'encryption' => 'ssl'
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'config' => [
                'host' => env('MAIL_HOST'),
                'port' => env('MAIL_PORT'),
                'username' => env('MAIL_USERNAME'),
                'password_length' => strlen(env('MAIL_PASSWORD')),
                'encryption' => env('MAIL_ENCRYPTION')
            ]
        ]);
    }
});

// Quick Gmail App Password Test
Route::get('/test-gmail-auth', function() {
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        // Server settings for port 587 with TLS
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Enable debug output
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'html';

        // Test authentication only
        $result = $mail->smtpConnect();

        if ($result) {
            $mail->smtpClose();
            return response()->json([
                'success' => true,
                'message' => 'Gmail authentication successful!',
                'config' => [
                    'username' => env('MAIL_USERNAME'),
                    'host' => 'smtp.gmail.com',
                    'port' => 587,
                    'encryption' => 'tls'
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gmail authentication failed',
                'config' => [
                    'username' => env('MAIL_USERNAME'),
                    'password_length' => strlen(env('MAIL_PASSWORD')),
                    'host' => 'smtp.gmail.com',
                    'port' => 587
                ]
            ]);
        }

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
            'config' => [
                'username' => env('MAIL_USERNAME'),
                'password_length' => strlen(env('MAIL_PASSWORD')),
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls'
            ]
        ]);
    }
});


// Course Management approval routes
Route::post('/admin/course-management/{courseId}/approve', [App\Http\Controllers\CourseManagementController::class, 'approveCourseRequest'])->name('admin.course-management.approve')->middleware('auth:admin');
Route::post('/admin/course-management/{courseId}/reject', [App\Http\Controllers\CourseManagementController::class, 'rejectCourseRequest'])->name('admin.course-management.reject')->middleware('auth:admin');

// Training Request approval routes
Route::post('/admin/training-requests/{requestId}/approve', [App\Http\Controllers\TrainingRequestController::class, 'approve'])->name('admin.training-requests.approve')->middleware('auth:admin');
Route::post('/admin/training-requests/{requestId}/reject', [App\Http\Controllers\TrainingRequestController::class, 'reject'])->name('admin.training-requests.reject')->middleware('auth:admin');
Route::post('/admin/training-requests/sync-approved-with-progress', [App\Http\Controllers\TrainingRequestController::class, 'syncApprovedRequestsWithProgress'])->name('admin.training-requests.sync-approved')->middleware('auth:admin');

// Debug route for testing training request approval
Route::get('/admin/debug-training-requests', function() {
    try {
        $requests = \Illuminate\Support\Facades\DB::table('training_requests')->get();
        return response()->json([
            'success' => true,
            'total_requests' => $requests->count(),
            'requests' => $requests->map(function($req) {
                return [
                    'request_id' => $req->request_id,
                    'employee_id' => $req->employee_id,
                    'training_title' => $req->training_title,
                    'status' => $req->status
                ];
            })
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
})->middleware('auth:admin');
Route::get('/admin/customer-service-sales-skills-training', [App\Http\Controllers\CustomerServiceSalesSkillsTrainingController::class, 'index'])->name('customer_service_sales_skills_training.index')->middleware('auth:admin');
Route::post('/admin/customer-service-sales-skills-training', [App\Http\Controllers\CustomerServiceSalesSkillsTrainingController::class, 'store'])->name('customer_service_sales_skills_training.store')->middleware('auth:admin');
Route::put('/admin/customer-service-sales-skills-training/{id}', [App\Http\Controllers\CustomerServiceSalesSkillsTrainingController::class, 'update'])->name('customer_service_sales_skills_training.update')->middleware('auth:admin');
Route::delete('/admin/customer-service-sales-skills-training/{id}', [App\Http\Controllers\CustomerServiceSalesSkillsTrainingController::class, 'destroy'])->name('customer_service_sales_skills_training.destroy')->middleware('auth:admin');

// Admin Routes - Settings
Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings')->middleware('auth:admin');
Route::put('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.updateSettings')->middleware('auth:admin');
Route::put('/admin/settings/profile-picture', [AdminController::class, 'updateProfilePicture'])->name('admin.updateProfilePicture')->middleware('auth:admin');
Route::put('/admin/settings/password', [AdminController::class, 'updatePassword'])->name('admin.updatePassword')->middleware('auth:admin');
Route::post('/admin/revoke-session/{sessionId}', [AdminController::class, 'revokeSession'])->name('admin.revokeSession')->middleware('auth:admin');
Route::post('/admin/logout-all-devices', [AdminController::class, 'logoutAllDevices'])->name('admin.logoutAllDevices')->middleware('auth:admin');

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
Route::post('/admin/employee-request-forms/fix-status-case', [App\Http\Controllers\EmployeeRequestFormController::class, 'fixStatusCase'])->name('employee_request_forms.fix_status_case')->middleware('auth:admin');

// Add alias route for backward compatibility
Route::put('/admin/requests/{id}', [App\Http\Controllers\EmployeeRequestFormController::class, 'update'])->name('admin.requests.update')->middleware('auth:admin');
Route::get('/admin/employee-list', [EmployeeController::class, 'index'])->name('employee.list')->middleware('auth:admin');
Route::get('/admin/proxy-image', [EmployeeController::class, 'proxyImage'])->name('employee.proxy.image')->middleware('auth:admin');
Route::post('/admin/employees', [EmployeeController::class, 'store'])->name('employees.store')->middleware('auth:admin');
Route::post('/admin/employees/save-individual', [EmployeeController::class, 'saveIndividualEmployee'])->name('employees.save_individual')->middleware('auth:admin');
Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show')->middleware('auth:admin');
Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update')->middleware('auth:admin');
Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy')->middleware('auth:admin');


// Employee online status API routes
Route::post('/api/employees/check-online-status', [EmployeeController::class, 'checkOnlineStatus'])->name('api.employees.check_online_status')->middleware('auth:admin');
Route::get('/api/employees/{employeeId}/online-status', [EmployeeController::class, 'getEmployeeOnlineStatus'])->name('api.employees.online_status')->middleware('auth:admin');
Route::get('/api/employees/debug-session-data', [EmployeeController::class, 'debugSessionData'])->name('api.employees.debug_session_data')->middleware('auth:admin');
Route::get('/admin/debug-online-status', [EmployeeController::class, 'debugOnlineStatus'])->name('admin.debug_online_status')->middleware('auth:admin');

// Employee IP address tracking API routes
Route::post('/api/employees/check-ip-addresses', [EmployeeController::class, 'checkIPAddresses'])->name('api.employees.check_ip_addresses')->middleware('auth:admin');

// Test route for IP address API (remove this after testing)
Route::get('/admin/test-ip-api', function() {
    return view('test-ip-api');
})->name('admin.test_ip_api')->middleware('auth:admin');
Route::get('/admin/training-feedback', [App\Http\Controllers\AdminTrainingFeedbackController::class, 'index'])->name('admin.training_feedback.index')->middleware('auth:admin');
Route::get('/admin/training-feedback/{id}', [App\Http\Controllers\AdminTrainingFeedbackController::class, 'show'])->name('admin.training_feedback.show')->middleware('auth:admin');
Route::post('/admin/training-feedback/{id}/review', [App\Http\Controllers\AdminTrainingFeedbackController::class, 'markAsReviewed'])->name('admin.training_feedback.review')->middleware('auth:admin');
Route::post('/admin/training-feedback/{id}/respond', [App\Http\Controllers\AdminTrainingFeedbackController::class, 'respond'])->name('admin.training_feedback.respond')->middleware('auth:admin');
Route::get('/admin/training-feedback/export', [App\Http\Controllers\AdminTrainingFeedbackController::class, 'export'])->name('admin.training_feedback.export')->middleware('auth:admin');
Route::get('/admin/training-feedback/analytics', [App\Http\Controllers\AdminTrainingFeedbackController::class, 'getAnalytics'])->name('admin.training_feedback.analytics')->middleware('auth:admin');

// Admin Routes - Competency Feedback Requests (integrated with training feedback)
Route::get('/admin/competency-feedback/{id}', [App\Http\Controllers\AdminTrainingFeedbackController::class, 'showCompetencyRequest'])->name('admin.competency_feedback.show')->middleware('auth:admin');
Route::post('/admin/competency-feedback/{id}/respond', [App\Http\Controllers\AdminTrainingFeedbackController::class, 'respondToCompetencyRequest'])->name('admin.competency_feedback.respond')->middleware('auth:admin');
Route::post('/admin/competency-feedback/{id}/review', [App\Http\Controllers\AdminTrainingFeedbackController::class, 'markCompetencyRequestAsReviewed'])->name('admin.competency_feedback.review')->middleware('auth:admin');

// Debug route to check competency feedback requests
Route::get('/admin/debug-competency-requests', function() {
    $requests = \App\Models\CompetencyFeedbackRequest::with(['employee', 'competency'])->get();
    return response()->json([
        'total_requests' => $requests->count(),
        'requests' => $requests->map(function($request) {
            return [
                'id' => $request->id,
                'employee_id' => $request->employee_id,
                'employee_name' => optional($request->employee)->first_name . ' ' . optional($request->employee)->last_name,
                'competency_id' => $request->competency_id,
                'competency_name' => optional($request->competency)->competency_name,
                'status' => $request->status,
                'created_at' => $request->created_at,
                'request_message' => $request->request_message
            ];
        })
    ]);
})->middleware('auth:admin');

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
Route::get('/admin/succession-readiness-ratings/employee-data/{employeeId}', [App\Http\Controllers\SuccessionReadinessRatingController::class, 'getCompetencyData'])->name('succession_readiness_ratings.employee_data')->middleware('auth:admin');
Route::get('/succession_readiness_ratings/competency-data/{employeeId}', [App\Http\Controllers\SuccessionReadinessRatingController::class, 'getCompetencyData'])->name('succession_readiness_ratings.competency_data');
Route::get('/employee_training_dashboard/readiness-score/{employeeId}', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'getReadinessScore'])->name('employee_training_dashboard.readiness_score');
Route::get('/admin/fix-training-titles', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'fixMissingTrainingTitles'])->middleware('auth:admin');
Route::get('/admin/debug-training-dashboard', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'debugTrainingData'])->middleware('auth:admin');
Route::get('/admin/fix-missing-dates', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'fixMissingDates'])->middleware('auth:admin');
Route::get('/admin/fix-employee-training-dashboards-table', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'fixEmployeeTrainingDashboardsTable'])->middleware('auth:admin');
Route::get('/admin/remove-training-course-entries', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'removeTrainingCourseEntries'])->middleware('auth:admin');
Route::get('/admin/cleanup-duplicate-records', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'cleanupDuplicateRecords'])->middleware('auth:admin');
Route::get('/admin/debug-training-data', [AdminDashboardController::class, 'debugTrainingData'])->middleware('auth:admin');
Route::get('/admin/fix-missing-training-titles', [AdminDashboardController::class, 'fixMissingTrainingTitles'])->middleware('auth:admin');
Route::get('/admin/cleanup-duplicate-trainings', [AdminDashboardController::class, 'cleanupDuplicateTrainings'])->middleware('auth:admin');
Route::get('/admin/debug-communication-skills-duplicates', [AdminDashboardController::class, 'debugCommunicationSkillsDuplicates'])->middleware('auth:admin');
Route::get('/admin/debug-training-records', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'debugTrainingRecords'])->middleware('auth:admin');
Route::get('/admin/create-missing-training-entries', [App\Http\Controllers\EmployeeTrainingDashboardController::class, 'createMissingEntries'])->middleware('auth:admin');
Route::get('/admin/consolidate-destination-trainings', function() {
    $result = \App\Models\DestinationKnowledgeTraining::consolidateTables();
    return response()->json($result);
})->name('admin.consolidate_destination_trainings')->middleware('auth:admin');
Route::get('/admin/succession-simulations', [App\Http\Controllers\SuccessionSimulationController::class, 'index'])->name('succession_simulations.index')->middleware('auth:admin');
Route::post('/admin/succession-simulations', [App\Http\Controllers\SuccessionSimulationController::class, 'store'])->name('succession_simulations.store')->middleware('auth:admin');
Route::put('/admin/succession-simulations/{id}', [App\Http\Controllers\SuccessionSimulationController::class, 'update'])->name('succession_simulations.update')->middleware('auth:admin');
Route::delete('/admin/succession-simulations/{id}', [App\Http\Controllers\SuccessionSimulationController::class, 'destroy'])->name('succession_simulations.destroy')->middleware('auth:admin');
Route::post('/admin/succession-simulations/export', [App\Http\Controllers\SuccessionSimulationController::class, 'exportSuccessionData'])->name('succession_simulations.export')->middleware('auth:admin');
Route::get('/admin/succession-simulations/candidates/{positionId}', [App\Http\Controllers\SuccessionSimulationController::class, 'getCandidates'])->name('succession_simulations.candidates')->middleware('auth:admin');

// Succession Planning Dashboard - API Routes for Real Data Integration
Route::get('/admin/succession-planning', [App\Http\Controllers\SuccessionPlanningController::class, 'index'])->name('succession_planning.index')->middleware('auth:admin');
Route::get('/api/succession-planning/position/{positionId}/competency-gaps', [App\Http\Controllers\SuccessionPlanningController::class, 'getPositionCompetencyGaps'])->name('api.succession_planning.position_competency_gaps')->middleware('auth:admin');
Route::get('/api/succession-planning/position/{positionId}/training-status', [App\Http\Controllers\SuccessionPlanningController::class, 'getPositionTrainingStatus'])->name('api.succession_planning.position_training_status')->middleware('auth:admin');
Route::get('/api/succession-planning/position/{positionId}/candidates', [App\Http\Controllers\SuccessionPlanningController::class, 'getPositionCandidates'])->name('api.succession_planning.position_candidates')->middleware('auth:admin');

// Employee Routes - Self Service
Route::get('/employee/leave-applications', [App\Http\Controllers\LeaveApplicationController::class, 'index'])->name('employee.leave_applications.index')->middleware('employee.auth');
Route::post('/employee/leave-applications', [App\Http\Controllers\LeaveApplicationController::class, 'store'])->name('employee.leave_applications.store')->middleware('employee.auth');
Route::get('/employee/leave-applications/{id}', [App\Http\Controllers\LeaveApplicationController::class, 'show'])->name('employee.leave_applications.show')->middleware('employee.auth');
Route::put('/employee/leave-applications/{id}', [App\Http\Controllers\LeaveApplicationController::class, 'update'])->name('employee.leave_applications.update')->middleware('employee.auth');
Route::delete('/employee/leave-applications/{id}', [App\Http\Controllers\LeaveApplicationController::class, 'cancel'])->name('employee.leave_applications.cancel')->middleware('employee.auth');

// Admin Routes for Leave Management
Route::put('/admin/leave-applications/{id}/status', [App\Http\Controllers\LeaveApplicationController::class, 'adminUpdateStatus'])->name('admin.leave_applications.update_status')->middleware(['auth:admin', 'admin.auth']);
Route::get('/employee/attendance-logs', [App\Http\Controllers\AttendanceTimeLogController::class, 'index'])->name('employee.attendance_logs.index')->middleware('employee.auth');
Route::post('/employee/attendance/time-in', [App\Http\Controllers\AttendanceTimeLogController::class, 'timeIn'])->name('employee.attendance.time_in')->middleware('employee.auth');
Route::post('/employee/attendance/time-out', [App\Http\Controllers\AttendanceTimeLogController::class, 'timeOut'])->name('employee.attendance.time_out')->middleware('employee.auth');
Route::get('/employee/attendance/status', [App\Http\Controllers\AttendanceTimeLogController::class, 'getCurrentStatus'])->name('employee.attendance.status')->middleware('employee.auth');
Route::get('/employee/attendance/{logId}/details', [App\Http\Controllers\AttendanceTimeLogController::class, 'getDetails'])->name('employee.attendance.details')->middleware('employee.auth');
Route::post('/employee/attendance/correction-request', [App\Http\Controllers\AttendanceTimeLogController::class, 'submitCorrectionRequest'])->name('employee.attendance.correction_request')->middleware('employee.auth');

// Payslip Resource Routes (Admin)
Route::resource('payslips', App\Http\Controllers\PayslipController::class)->middleware('auth:admin');
Route::get('/payslips/download-all', [App\Http\Controllers\PayslipController::class, 'downloadAll'])->name('payslips.download_all')->middleware('auth:admin');
Route::get('/payslips/{id}/download', [App\Http\Controllers\PayslipController::class, 'download'])->name('payslips.download')->middleware('auth:admin');

// Employee Payslip Routes
Route::get('/employee/payslips', [App\Http\Controllers\PayslipController::class, 'index'])->name('employee.payslips.index')->middleware('employee.auth');
Route::get('/employee/payslips/download-all', [App\Http\Controllers\PayslipController::class, 'downloadAll'])->name('employee.payslips.download_all')->middleware('employee.auth');
Route::get('/employee/payslips/{id}/download', [App\Http\Controllers\PayslipController::class, 'download'])->name('employee.payslips.download')->middleware('employee.auth');
Route::get('/employee/payslips/{id}/print', [App\Http\Controllers\PayslipController::class, 'print'])->name('employee.payslips.print')->middleware('employee.auth');

// Payslip Table Creation Route (Admin only)
Route::get('/admin/create-payslips-table', function() {
    try {
        // Execute the payslips table creation script
        $output = shell_exec('cd ' . base_path() . ' && php create_payslips_table_fix.php 2>&1');

        return response()->json([
            'success' => true,
            'message' => 'Payslips table created successfully',
            'output' => $output
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create payslips table: ' . $e->getMessage()
        ], 500);
    }
})->middleware('auth:admin');

Route::get('/employee/claim-reimbursements', [App\Http\Controllers\ClaimReimbursementController::class, 'index'])->name('employee.claim_reimbursements.index')->middleware('employee.auth');
Route::post('/employee/claim-reimbursements', [App\Http\Controllers\ClaimReimbursementController::class, 'store'])->name('employee.claim_reimbursements.store')->middleware('employee.auth');
Route::get('/employee/claim-reimbursements/{id}', [App\Http\Controllers\ClaimReimbursementController::class, 'show'])->name('employee.claim_reimbursements.show')->middleware('employee.auth');
Route::put('/employee/claim-reimbursements/{id}', [App\Http\Controllers\ClaimReimbursementController::class, 'update'])->name('employee.claim_reimbursements.update')->middleware('employee.auth');
Route::delete('/employee/claim-reimbursements/{id}/cancel', [App\Http\Controllers\ClaimReimbursementController::class, 'cancel'])->name('employee.claim_reimbursements.cancel')->middleware('employee.auth');
Route::get('/employee/claim-reimbursements/{id}/download-receipt', [App\Http\Controllers\ClaimReimbursementController::class, 'downloadReceipt'])->name('employee.claim_reimbursements.download_receipt')->middleware('employee.auth');
Route::post('/employee/claim-reimbursements/test', [App\Http\Controllers\ClaimReimbursementController::class, 'testSubmission'])->name('employee.claim_reimbursements.test')->middleware('employee.auth');
Route::get('/employee/requests', [App\Http\Controllers\RequestFormController::class, 'index'])->name('employee.requests.index')->middleware('employee.auth');
Route::post('/employee/requests', [App\Http\Controllers\RequestFormController::class, 'store'])->name('employee.requests.store')->middleware('employee.auth');
Route::put('/employee/requests/{id}', [App\Http\Controllers\RequestFormController::class, 'update'])->name('employee.requests.update')->middleware('employee.auth');
Route::delete('/employee/requests/{id}', [App\Http\Controllers\RequestFormController::class, 'destroy'])->name('employee.requests.destroy')->middleware('employee.auth');
// Employee profile update route
Route::post('/employee/profile/update', [EmployeeController::class, 'updateProfile'])->name('employee.profile.update')->middleware('employee.auth');

// Employee Settings routes
Route::get('/employee/settings', [EmployeeController::class, 'settings'])->name('employee.settings')->middleware('employee.auth');
Route::put('/employee/settings', [EmployeeController::class, 'updateSettings'])->name('employee.updateSettings')->middleware('employee.auth');
Route::post('/employee/settings', [EmployeeController::class, 'updateSettings'])->name('employee.updateSettings.post')->middleware('employee.auth');
Route::post('/employee/settings-debug', [EmployeeController::class, 'updateSettingsDebug'])->name('employee.updateSettings.debug')->middleware('employee.auth');
Route::post('/employee/verify-password', [EmployeeController::class, 'verifyPassword'])->name('employee.verify_password')->middleware('employee.auth');

// Employee Settings Fix routes
Route::post('/employee/settings/fix-save', [EmployeeController::class, 'fixEmployeeSettingsSave'])->name('employee.settings.fix')->middleware('employee.auth');
Route::get('/employee/profile-picture-proxy', [EmployeeController::class, 'proxyProfilePicture'])->name('employee.profile_picture.proxy')->middleware('employee.auth');
Route::get('/employee/debug-auth', [EmployeeController::class, 'debugEmployeeAuth'])->name('employee.debug.auth')->middleware('employee.auth');

// Employee ping route for online status check
Route::get('/employee/ping', function() {
    return response()->json(['status' => 'online', 'timestamp' => now()]);
})->name('employee.ping')->middleware('employee.auth');



Route::get('/employee/my-trainings', [App\Http\Controllers\MyTrainingController::class, 'index'])->name('employee.my_trainings.index')->middleware('employee.auth');

// Training export routes
Route::get('/employee/trainings/export/pdf', [App\Http\Controllers\MyTrainingController::class, 'exportPdf'])->name('employee.trainings.export.pdf')->middleware('employee.auth');
Route::get('/employee/trainings/export/excel', [App\Http\Controllers\MyTrainingController::class, 'exportExcel'])->name('employee.trainings.export.excel')->middleware('employee.auth');

// Training CRUD routes
Route::post('/employee/my-trainings', [App\Http\Controllers\MyTrainingController::class, 'store'])->name('employee.my_trainings.store')->middleware('employee.auth');
Route::put('/employee/my-trainings/{id}', [App\Http\Controllers\MyTrainingController::class, 'update'])->name('employee.my_trainings.update')->middleware('employee.auth');
Route::delete('/employee/my-trainings/{id}', [App\Http\Controllers\MyTrainingController::class, 'destroy'])->name('employee.my_trainings.destroy')->middleware('employee.auth');

// Debug route for duplicate trainings
Route::get('/employee/debug-duplicate-trainings', [App\Http\Controllers\MyTrainingController::class, 'debugDuplicateTrainings'])->name('employee.debug_duplicate_trainings')->middleware('auth:admin');

// Fix assigned by names route
Route::get('/admin/fix-assigned-by-names', [App\Http\Controllers\MyTrainingController::class, 'fixAssignedByNames'])->name('admin.fix_assigned_by_names')->middleware('auth:admin');

// Fix competency assigned by names route
Route::get('/admin/fix-competency-assigned-by-names', [App\Http\Controllers\MyTrainingController::class, 'fixCompetencyAssignedByNames'])->name('admin.fix_competency_assigned_by_names')->middleware('auth:admin');

// Certificate download route
Route::get('/employee/certificate/download/{id}', function($id) {
    // Sample certificate download - replace with actual file download
    return response()->json(['message' => 'Certificate download functionality not yet implemented']);
})->name('employee.certificate.download')->middleware('employee.auth');

// Certificate tracking routes
Route::get('/certificates/view/{id}', [App\Http\Controllers\CertificateGenerationController::class, 'viewCertificate'])->name('certificates.view');
Route::get('/certificates/download/{id}', [App\Http\Controllers\CertificateGenerationController::class, 'downloadCertificate'])->name('certificates.download');

// Certificate generation routes
Route::get('/certificates/preview', [App\Http\Controllers\CertificateGenerationController::class, 'previewTemplate'])->name('certificates.preview')->middleware('auth:admin');
Route::post('/certificates/generate', [App\Http\Controllers\CertificateGenerationController::class, 'generateManualCertificate'])->name('certificates.generate')->middleware('auth:admin');
Route::post('/certificates/bulk-generate', [App\Http\Controllers\CertificateGenerationController::class, 'bulkGenerateCertificates'])->name('certificates.bulk_generate')->middleware('auth:admin');

// Certificate diagnostic routes
Route::get('/certificates/diagnostics', [App\Http\Controllers\CertificateGenerationController::class, 'diagnostics'])->name('certificates.diagnostics')->middleware('auth:admin');
Route::get('/certificates/test-generation', [App\Http\Controllers\CertificateGenerationController::class, 'testGeneration'])->name('certificates.test_generation')->middleware('auth:admin');

// Debug route for certificate generation
Route::get('/certificates/debug', function() {
    try {
        $storagePath = storage_path('app/public/certificates');

        // Create directory if it doesn't exist
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $service = app(\App\Services\AICertificateGeneratorService::class);
        $result = $service->generateCertificate('Test Employee', 'Test Course', now(), 'TEST001');

        return response()->json([
            'success' => true,
            'service_loaded' => true,
            'generation_result' => $result,
            'storage_path' => $storagePath,
            'storage_exists' => file_exists($storagePath),
            'storage_writable' => is_writable($storagePath),
            'dompdf_available' => class_exists('\Barryvdh\DomPDF\Facade\Pdf'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->middleware('auth:admin');

// Debug route for storage setup
Route::get('/admin/debug-storage-setup', function() {
    try {
        $storagePath = storage_path('app/public/certificates');
        $publicPath = public_path('storage/certificates');

        $debug = [
            'storage_path' => $storagePath,
            'storage_exists' => file_exists($storagePath),
            'storage_writable' => is_writable(dirname($storagePath)),
            'public_path' => $publicPath,
            'public_exists' => file_exists($publicPath),
            'symlink_exists' => is_link(public_path('storage')),
            'symlink_target' => is_link(public_path('storage')) ? readlink(public_path('storage')) : null,
            'certificates_count' => file_exists($storagePath) ? count(glob($storagePath . '/*')) : 0,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'storage_permissions' => file_exists($storagePath) ? substr(sprintf('%o', fileperms($storagePath)), -4) : 'N/A',
            'service_registered' => app()->bound(\App\Services\AICertificateGeneratorService::class)
        ];

        // Try to create certificates directory if it doesn't exist
        if (!file_exists($storagePath)) {
            $created = mkdir($storagePath, 0755, true);
            $debug['directory_created'] = $created;
        }

        return response()->json([
            'success' => true,
            'debug_info' => $debug
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->middleware('auth:admin');

// Public storage file link helper
// Example: /storage-file/certificates/yourfile.pdf or /storage-file/profile_picture/john.jpg
Route::get('/storage-file/{folder}/{path}', [App\Http\Controllers\StorageLinkController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.file');

// Exam routes
Route::get('/employee/exam/start/{courseId}', [ExamController::class, 'startExam'])->name('employee.exam.start')->middleware('employee.auth');
Route::get('/employee/exam/take/{attemptId}', [ExamController::class, 'take'])->name('employee.exam.take')->middleware('employee.auth');
Route::post('/employee/exam/submit/{attemptId}', [ExamController::class, 'submitAjax'])->name('employee.exam.submit')->middleware('employee.auth');
Route::post('/employee/exam/submit-ajax/{attemptId}', [ExamController::class, 'submitAjax'])->name('employee.exam.submit_ajax')->middleware('employee.auth');
Route::get('/employee/exam/result/{attemptId}', [ExamController::class, 'result'])->name('employee.exam.result')->middleware('employee.auth');
Route::get('/employee/exam/simple-result/{attemptId}', [ExamController::class, 'simpleResult'])->name('employee.exam.simple_result')->middleware('employee.auth');

// Training feedback routes
Route::get('/employee/training-feedback', [App\Http\Controllers\TrainingFeedbackController::class, 'index'])->name('employee.training_feedback.index')->middleware('employee.auth');
Route::post('/employee/training-feedback', [App\Http\Controllers\TrainingFeedbackController::class, 'store'])->name('employee.training_feedback.store')->middleware('employee.auth');
Route::get('/employee/training-feedback/{id}', [App\Http\Controllers\TrainingFeedbackController::class, 'show'])->name('employee.training_feedback.show')->middleware('employee.auth');
Route::put('/employee/training-feedback/{id}', [App\Http\Controllers\TrainingFeedbackController::class, 'update'])->name('employee.training_feedback.update')->middleware('employee.auth');
Route::delete('/employee/training-feedback/{id}', [App\Http\Controllers\TrainingFeedbackController::class, 'destroy'])->name('employee.training_feedback.destroy')->middleware('employee.auth');

// Admin route to create training feedback table if missing
Route::get('/admin/create-training-feedback-table', function() {
    if (!\Illuminate\Support\Facades\Schema::hasTable('training_feedback')) {
        \Illuminate\Support\Facades\Schema::create('training_feedback', function ($table) {
            $table->id();
            $table->string('feedback_id')->nullable();
            $table->string('employee_id')->nullable();
            $table->integer('course_id')->nullable();
            $table->string('training_title')->nullable();
            $table->integer('overall_rating')->nullable();
            $table->integer('content_quality')->nullable();
            $table->integer('instructor_effectiveness')->nullable();
            $table->integer('material_relevance')->nullable();
            $table->integer('training_duration')->nullable();
            $table->text('what_learned')->nullable();
            $table->text('most_valuable')->nullable();
            $table->text('improvements')->nullable();
            $table->text('additional_topics')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('recommend_training')->default(false);
            $table->string('training_format')->nullable();
            $table->date('training_completion_date')->nullable();
            $table->datetime('submitted_at')->nullable();
            $table->boolean('admin_reviewed')->default(false);
            $table->datetime('reviewed_at')->nullable();
            $table->text('admin_response')->nullable();
            $table->text('action_taken')->nullable();
            $table->datetime('response_date')->nullable();
            $table->timestamps();

            // Add indexes for better performance
            $table->index('employee_id');
            $table->index('course_id');
            $table->index('overall_rating');
            $table->index('admin_reviewed');
            $table->index('submitted_at');
        });
        return response()->json(['success' => true, 'message' => 'Training feedback table created successfully']);
    } else {
        return response()->json(['success' => true, 'message' => 'Training feedback table already exists']);
    }
})->name('admin.create_training_feedback_table')->middleware('auth:admin');
Route::get('/employee/competency-profile', [App\Http\Controllers\Employee\CompetencyProfileController::class, 'index'])->name('employee.competency_profile.index')->middleware('employee.auth');

// Competency Profile AJAX routes
Route::get('/employee/competency-profile/progress-data', [App\Http\Controllers\Employee\CompetencyProfileController::class, 'getProgressData'])->name('employee.competency_profile.progress_data')->middleware('employee.auth');

Route::get('/employee/competency-profile/{id}', [App\Http\Controllers\Employee\CompetencyProfileController::class, 'show'])->name('employee.competency_profile.show')->middleware('employee.auth');

Route::post('/employee/competency-profile/request-feedback', [App\Http\Controllers\Employee\CompetencyProfileController::class, 'requestFeedback'])->name('employee.competency_profile.request_feedback')->middleware('employee.auth');

Route::post('/employee/competency-profile/{id}/update-progress', [App\Http\Controllers\Employee\CompetencyProfileController::class, 'updateProgress'])->name('employee.competency_profile.update_progress')->middleware('employee.auth');

Route::post('/employee/competency-profile/{id}/start-training', [App\Http\Controllers\Employee\CompetencyProfileController::class, 'startTraining'])->name('employee.competency_profile.start_training')->middleware('employee.auth');

Route::get('/employee/profile-updates', [App\Http\Controllers\ProfileUpdateController::class, 'index'])->name('employee.profile_updates.index')->middleware('employee.auth');
Route::post('/employee/profile-updates', [App\Http\Controllers\ProfileUpdateController::class, 'store'])->name('employee.profile_updates.store')->middleware('employee.auth');
Route::get('/employee/profile-updates/{profileUpdate}', [App\Http\Controllers\ProfileUpdateController::class, 'show'])->name('employee.profile_updates.show')->middleware('employee.auth');
Route::get('/employee/profile-updates/{profileUpdate}/edit', [App\Http\Controllers\ProfileUpdateController::class, 'edit'])->name('employee.profile_updates.edit')->middleware('employee.auth');
Route::put('/employee/profile-updates/{profileUpdate}', [App\Http\Controllers\ProfileUpdateController::class, 'update'])->name('employee.profile_updates.update')->middleware('employee.auth');
Route::delete('/employee/profile-updates/{profileUpdate}', [App\Http\Controllers\ProfileUpdateController::class, 'destroy'])->name('employee.profile_updates.destroy')->middleware('employee.auth');
Route::get('/employee/profile-updates/{profileUpdate}/details', [App\Http\Controllers\ProfileUpdateController::class, 'details'])->name('employee.profile_updates.details')->middleware('employee.auth');


// Employee destination training response routes
Route::post('/employee/destination-training/accept', [App\Http\Controllers\MyTrainingController::class, 'acceptDestinationTraining'])->name('employee.destination_training.accept')->middleware('employee.auth');
Route::post('/employee/destination-training/decline', [App\Http\Controllers\MyTrainingController::class, 'declineDestinationTraining'])->name('employee.destination_training.decline')->middleware('employee.auth');
Route::get('/employee/destination-training/details/{id}', [App\Http\Controllers\MyTrainingController::class, 'getDestinationTrainingDetails'])->name('employee.destination_training.details')->middleware('employee.auth');

// Training progress update routes
Route::post('/employee/training/progress', [App\Http\Controllers\TrainingProgressUpdateController::class, 'store'])->name('employee.training.progress.store')->middleware('employee.auth');
Route::post('/employee/training/update-progress-after-exam', [App\Http\Controllers\TrainingProgressUpdateController::class, 'updateProgressAfterExam'])->name('employee.training.update_progress_after_exam')->middleware('employee.auth');
Route::post('/employee/training/refresh-progress', [App\Http\Controllers\TrainingProgressUpdateController::class, 'refreshProgress'])->name('employee.training.refresh_progress')->middleware('employee.auth');

// Debug route for testing exam progress
Route::get('/employee/debug-exam-progress/{courseId}', function($courseId) {
    $employeeId = Auth::user()->employee_id;

    $examAttempts = \App\Models\ExamAttempt::where('employee_id', $employeeId)
        ->where('course_id', $courseId)
        ->get();

    $calculatedProgress = \App\Models\ExamAttempt::calculateCombinedProgress($employeeId, $courseId);

    return response()->json([
        'employee_id' => $employeeId,
        'course_id' => $courseId,
        'exam_attempts' => $examAttempts,
        'calculated_progress' => $calculatedProgress,
        'debug' => 'Check exam progress calculation'
    ]);
})->middleware('employee.auth');

// Activity Logs Route
Route::get('/admin/activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity_logs.index')->middleware('auth:admin');

// Debug route for testing employee login redirection
Route::get('/employee/test-auth', function() {
    $employee = Auth::guard('employee')->user();
    if ($employee) {
        return response()->json([
            'authenticated' => true,
            'employee_id' => $employee->employee_id,
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'email' => $employee->email,
            'dashboard_url' => route('employee.dashboard')
        ]);
    } else {
        return response()->json([
            'authenticated' => false,
            'login_url' => route('employee.login')
        ]);
    }
})->middleware('employee.auth');

// Debug route for testing settings access without middleware
Route::get('/employee/debug-settings', function() {
    $employee = Auth::guard('employee')->user();
    if ($employee) {
        return response()->json([
            'message' => 'Employee authenticated successfully',
            'employee_id' => $employee->employee_id,
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'email' => $employee->email,
            'settings_url' => route('employee.settings'),
            'session_id' => session()->getId(),
            'guard_check' => Auth::guard('employee')->check()
        ]);
    } else {
        return response()->json([
            'message' => 'Employee not authenticated',
            'login_url' => route('employee.login'),
            'session_id' => session()->getId(),
            'guard_check' => Auth::guard('employee')->check()
        ]);
    }
});

// Simple settings test route without middleware
Route::get('/employee/settings-test', function() {
    $employee = Auth::guard('employee')->user();
    if (!$employee) {
        return redirect()->route('employee.login')->with('error', 'Please log in first.');
    }
    return view('employee_ess_modules.setting_employee', compact('employee'));
});

// Test route to bypass middleware completely
Route::get('/employee/settings-direct', function() {
    $employee = \App\Models\Employee::first();
    return view('employee_ess_modules.setting_employee', compact('employee'));
});

Route::post('/employee/settings-direct', function(Request $request) {
    $employee = \App\Models\Employee::first();

    $data = $request->validate([
        'department_id' => 'nullable|string|max:255',
        'status' => 'required|in:Active,Inactive,On Leave'
    ]);

    $employee->update($data);

    return redirect('/employee/settings-direct')->with('success', 'Settings updated successfully!');
});

// Debug route for customer service training data
Route::get('/debug/customer-service-training/{employeeId}', function($employeeId) {
    // Get training dashboard records for this employee
    $dashboardRecords = \App\Models\EmployeeTrainingDashboard::with(['employee', 'course'])
        ->where('employee_id', $employeeId)
        ->get();

    // Get exam attempts for this employee
    $examAttempts = \App\Models\ExamAttempt::where('employee_id', $employeeId)->get();

    // Get courses that might match Communication Skills
    $courses = \App\Models\CourseManagement::where('course_title', 'LIKE', '%Communication%')->get();

    return response()->json([
        'employee_id' => $employeeId,
        'dashboard_records' => $dashboardRecords,
        'exam_attempts' => $examAttempts,
        'communication_courses' => $courses,
        'debug_info' => [
            'dashboard_count' => $dashboardRecords->count(),
            'exam_attempts_count' => $examAttempts->count(),
            'courses_count' => $courses->count()
        ]
    ]);
});

// Employee Settings Routes
Route::middleware(['employee.auth'])->group(function () {
    Route::post('/employee/settings/save', [App\Http\Controllers\EmployeeSettingsController::class, 'saveSettings'])->name('employee.settings.save');
    Route::get('/employee/settings/get', [App\Http\Controllers\EmployeeSettingsController::class, 'getSettings'])->name('employee.settings.get');
});
