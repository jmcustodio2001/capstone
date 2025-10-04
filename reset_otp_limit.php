<?php

// Simple script to reset OTP rate limit for testing
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;

// Find employee by email
$email = 'jm.custodio092001@gmail.com'; // Change this to your test email
$employee = Employee::where('email', $email)->first();

if ($employee) {
    // Reset OTP fields
    $employee->update([
        'last_otp_sent_at' => null,
        'otp_code' => null,
        'otp_expires_at' => null,
        'otp_attempts' => 0,
        'otp_verified' => false
    ]);
    
    echo "✅ OTP rate limit reset for: {$email}\n";
    echo "📧 Employee ID: {$employee->employee_id}\n";
    echo "🔄 You can now request OTP immediately\n";
} else {
    echo "❌ Employee not found with email: {$email}\n";
    echo "📋 Available employees:\n";
    
    $employees = Employee::select('employee_id', 'email', 'first_name', 'last_name')->get();
    foreach ($employees as $emp) {
        echo "   - {$emp->email} ({$emp->first_name} {$emp->last_name})\n";
    }
}
