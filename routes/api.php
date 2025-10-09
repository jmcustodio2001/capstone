<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Routes for Leave Management Integration
Route::prefix('v1/leave')->group(function () {
    // Submit leave request via API
    Route::post('/submit', [App\Http\Controllers\Api\LeaveApplicationApiController::class, 'submitLeaveRequest'])
        ->name('api.leave.submit');
    
    // Get leave application status
    Route::get('/status/{leaveId}', [App\Http\Controllers\Api\LeaveApplicationApiController::class, 'getLeaveStatus'])
        ->name('api.leave.status');
    
    // Update leave status (approve/reject) - Admin only
    Route::put('/status/{leaveId}', [App\Http\Controllers\Api\LeaveApplicationApiController::class, 'updateLeaveStatus'])
        ->name('api.leave.update_status');
    
    // Get employee leave balance
    Route::get('/balance/{employeeId}', [App\Http\Controllers\Api\LeaveApplicationApiController::class, 'getLeaveBalance'])
        ->name('api.leave.balance');
    
    // Get employee leave history
    Route::get('/history/{employeeId}', [App\Http\Controllers\Api\LeaveApplicationApiController::class, 'getLeaveHistory'])
        ->name('api.leave.history');
    
    // Test HR3 connection
    Route::post('/test-hr3-connection', [App\Http\Controllers\Api\LeaveApplicationApiController::class, 'testHR3Connection'])
        ->name('api.leave.test_hr3');
    
    // Webhook endpoint for external systems to receive leave status updates
    Route::post('/webhook/status-update', [App\Http\Controllers\Api\LeaveApplicationApiController::class, 'webhookStatusUpdate'])
        ->name('api.leave.webhook.status_update');
});

// API Routes for Attendance Time Logs Integration
Route::prefix('v1/attendance')->group(function () {
    // Get attendance logs for an employee
    Route::get('/logs/{employeeId}', [App\Http\Controllers\Api\AttendanceTimeLogApiController::class, 'getAttendanceLogs'])
        ->name('api.attendance.logs');
    
    // Create new attendance log entry
    Route::post('/logs', [App\Http\Controllers\Api\AttendanceTimeLogApiController::class, 'createAttendanceLog'])
        ->name('api.attendance.create');
    
    // Update attendance log entry - Admin only
    Route::put('/logs/{logId}', [App\Http\Controllers\Api\AttendanceTimeLogApiController::class, 'updateAttendanceLog'])
        ->name('api.attendance.update');
    
    // Get attendance summary for an employee
    Route::get('/summary/{employeeId}', [App\Http\Controllers\Api\AttendanceTimeLogApiController::class, 'getAttendanceSummary'])
        ->name('api.attendance.summary');
});

// API Routes for Claim Reimbursement Integration
Route::prefix('v1/claims')->group(function () {
    // Get claim reimbursements for an employee
    Route::get('/employee/{employeeId}', [App\Http\Controllers\Api\ClaimReimbursementApiController::class, 'getClaimReimbursements'])
        ->name('api.claims.employee');
    
    // Create new claim reimbursement
    Route::post('/submit', [App\Http\Controllers\Api\ClaimReimbursementApiController::class, 'createClaimReimbursement'])
        ->name('api.claims.submit');
    
    // Get claim details
    Route::get('/details/{claimId}', [App\Http\Controllers\Api\ClaimReimbursementApiController::class, 'getClaimDetails'])
        ->name('api.claims.details');
    
    // Update claim status (approve/reject/process) - Admin only
    Route::put('/status/{claimId}', [App\Http\Controllers\Api\ClaimReimbursementApiController::class, 'updateClaimStatus'])
        ->name('api.claims.update_status');
    
    // Get claim summary for an employee
    Route::get('/summary/{employeeId}', [App\Http\Controllers\Api\ClaimReimbursementApiController::class, 'getClaimSummary'])
        ->name('api.claims.summary');
});
    