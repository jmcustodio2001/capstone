<?php

/**
 * Direct PHP Script to Setup Employee IP Tracking
 * Run this file directly in browser: http://your-domain/setup_employee_ip_tracking.php
 */

// Include Laravel bootstrap
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "<h1>Employee IP Tracking Setup</h1>";
echo "<p>Setting up employee login sessions table and IP tracking...</p>";

try {
    // Check if table exists
    if (Schema::hasTable('employee_login_sessions')) {
        echo "<p style='color: orange;'>✓ Table 'employee_login_sessions' already exists.</p>";
    } else {
        echo "<p>Creating 'employee_login_sessions' table...</p>";
        
        // Create the table
        Schema::create('employee_login_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('session_id');
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->timestamp('login_at');
            $table->timestamp('last_activity');
            $table->timestamp('logout_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['employee_id', 'is_active']);
            $table->index(['last_activity']);
            $table->index(['session_id']);
        });
        
        echo "<p style='color: green;'>✓ Table 'employee_login_sessions' created successfully!</p>";
    }

    // Insert some sample data for testing
    echo "<p>Inserting sample login sessions for testing...</p>";
    
    $sampleSessions = [
        [
            'employee_id' => 'EMP001',
            'session_id' => 'sample_session_1',
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'login_at' => now(),
            'last_activity' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'employee_id' => 'EMP002',
            'session_id' => 'sample_session_2',
            'ip_address' => '192.168.1.101',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'login_at' => now()->subMinutes(5),
            'last_activity' => now()->subMinutes(2),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'employee_id' => 'EMP003',
            'session_id' => 'sample_session_3',
            'ip_address' => '192.168.1.102',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'login_at' => now()->subMinutes(10),
            'last_activity' => now()->subMinutes(1),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];

    // Clear existing sample data first
    DB::table('employee_login_sessions')->where('session_id', 'like', 'sample_session_%')->delete();
    
    // Insert new sample data
    DB::table('employee_login_sessions')->insert($sampleSessions);
    
    echo "<p style='color: green;'>✓ Sample login sessions inserted successfully!</p>";

    // Test the IP address checking functionality
    echo "<h2>Testing IP Address Check API</h2>";
    
    $employeeIds = ['EMP001', 'EMP002', 'EMP003', 'EMP004', 'EMP005'];
    echo "<p>Testing with employee IDs: " . implode(', ', $employeeIds) . "</p>";
    
    // Simulate the controller logic
    $ipAddresses = [];
    $activeSessions = DB::table('employee_login_sessions')
        ->where('last_activity', '>=', now()->subMinutes(15))
        ->where('is_active', true)
        ->get();
    
    echo "<p>Found " . $activeSessions->count() . " active sessions in last 15 minutes</p>";
    
    foreach ($employeeIds as $employeeId) {
        $employeeIP = null;
        
        // Try to find IP from employee login sessions
        foreach ($activeSessions as $session) {
            if ($session->employee_id == $employeeId && !empty($session->ip_address)) {
                $employeeIP = $session->ip_address;
                break;
            }
        }
        
        if (!$employeeIP) {
            $employeeIP = 'N/A';
        }
        
        $ipAddresses[$employeeId] = $employeeIP;
        
        $status = $employeeIP !== 'N/A' ? 'Online' : 'Offline';
        $color = $employeeIP !== 'N/A' ? 'green' : 'gray';
        echo "<p style='color: {$color};'>• {$employeeId}: {$employeeIP} ({$status})</p>";
    }

    echo "<h2>Setup Complete!</h2>";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ What was fixed:</h3>";
    echo "<ul>";
    echo "<li><strong>Hover Color:</strong> Changed from harsh bright blue to soft green overlay (bg-success bg-opacity-3)</li>";
    echo "<li><strong>Button Hover:</strong> Added gentle hover effects with subtle background colors instead of harsh transitions</li>";
    echo "<li><strong>IP Tracking:</strong> Created employee_login_sessions table to track real IP addresses</li>";
    echo "<li><strong>Real IP Detection:</strong> Updated controller to check multiple sources for employee IP addresses</li>";
    echo "<li><strong>Session Tracking:</strong> Added login session tracking in AuthController</li>";
    echo "</ul>";
    echo "</div>";

    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>📋 Next Steps:</h3>";
    echo "<ul>";
    echo "<li>Visit the Employee List page to see the improved hover effects</li>";
    echo "<li>The IP addresses will now show real data from active employee sessions</li>";
    echo "<li>When employees log in, their IP addresses will be tracked automatically</li>";
    echo "<li>The system will show 'Online' status for employees with active sessions in the last 15 minutes</li>";
    echo "</ul>";
    echo "</div>";

    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🔧 Technical Details:</h3>";
    echo "<ul>";
    echo "<li><strong>Table:</strong> employee_login_sessions created with proper indexes</li>";
    echo "<li><strong>API Endpoint:</strong> /api/employees/check-ip-addresses (already exists)</li>";
    echo "<li><strong>Update Frequency:</strong> IP addresses refresh every 30 seconds</li>";
    echo "<li><strong>Session Timeout:</strong> 15 minutes of inactivity</li>";
    echo "</ul>";
    echo "</div>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><em>Setup completed at: " . date('Y-m-d H:i:s') . "</em></p>";
echo "<p><a href='/admin/employee-list' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Employee List</a></p>";

?>
