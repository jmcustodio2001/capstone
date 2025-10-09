<?php

/**
 * HR2ESS API Testing Script
 * 
 * This script tests the API endpoints for:
 * - Leave Management
 * - Attendance Time Logs  
 * - Claim Reimbursement
 * 
 * Usage: php test_api_endpoints.php
 */

// Configuration
$baseUrl = 'http://localhost:8000'; // Change to your domain
$apiKeys = [
    'leave' => 'hr2ess_api_key_2025',
    'attendance' => 'hr2ess_api_key_2025', 
    'claims' => 'hr2ess_api_key_2025',
    'admin' => 'hr2ess_admin_api_key_2025'
];

$testEmployeeId = 'EMP001'; // Change to existing employee ID

echo "=== HR2ESS API Testing Script ===\n\n";

/**
 * Make HTTP request
 */
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

/**
 * Test API endpoint
 */
function testEndpoint($name, $url, $method = 'GET', $data = null, $headers = []) {
    echo "Testing: $name\n";
    echo "URL: $url\n";
    echo "Method: $method\n";
    
    $result = makeRequest($url, $method, $data, $headers);
    
    echo "HTTP Code: {$result['http_code']}\n";
    
    if ($result['error']) {
        echo "Error: {$result['error']}\n";
    } else {
        $response = json_decode($result['response'], true);
        if ($response) {
            echo "Success: " . ($response['success'] ? 'true' : 'false') . "\n";
            if (isset($response['message'])) {
                echo "Message: {$response['message']}\n";
            }
        } else {
            echo "Response: {$result['response']}\n";
        }
    }
    
    echo str_repeat('-', 50) . "\n\n";
}

// Test Leave Management API
echo "=== LEAVE MANAGEMENT API TESTS ===\n\n";

// Test get leave balance
testEndpoint(
    'Get Leave Balance',
    "{$baseUrl}/api/v1/leave/balance/{$testEmployeeId}?api_key={$apiKeys['leave']}"
);

// Test get leave history
testEndpoint(
    'Get Leave History',
    "{$baseUrl}/api/v1/leave/history/{$testEmployeeId}?api_key={$apiKeys['leave']}&limit=10"
);

// Test submit leave request
$leaveData = json_encode([
    'employee_id' => $testEmployeeId,
    'leave_type' => 'Vacation',
    'leave_days' => 2,
    'start_date' => date('Y-m-d', strtotime('+7 days')),
    'end_date' => date('Y-m-d', strtotime('+8 days')),
    'reason' => 'API Test Leave Request',
    'contact_info' => 'test@example.com',
    'api_key' => $apiKeys['leave']
]);

testEndpoint(
    'Submit Leave Request',
    "{$baseUrl}/api/v1/leave/submit",
    'POST',
    $leaveData,
    ['Content-Type: application/json']
);

// Test Attendance Time Logs API
echo "=== ATTENDANCE TIME LOGS API TESTS ===\n\n";

// Test get attendance logs
testEndpoint(
    'Get Attendance Logs',
    "{$baseUrl}/api/v1/attendance/logs/{$testEmployeeId}?api_key={$apiKeys['attendance']}&limit=10"
);

// Test get attendance summary
testEndpoint(
    'Get Attendance Summary',
    "{$baseUrl}/api/v1/attendance/summary/{$testEmployeeId}?api_key={$apiKeys['attendance']}&start_date=" . date('Y-m-01') . "&end_date=" . date('Y-m-t')
);

// Test create attendance log
$attendanceData = json_encode([
    'employee_id' => $testEmployeeId,
    'log_date' => date('Y-m-d'),
    'time_in' => '08:00:00',
    'time_out' => '17:00:00',
    'break_start_time' => '12:00:00',
    'break_end_time' => '13:00:00',
    'total_hours' => 8.0,
    'overtime_hours' => 0.0,
    'status' => 'Present',
    'location' => 'API Test Location',
    'notes' => 'Created via API test',
    'api_key' => $apiKeys['attendance']
]);

testEndpoint(
    'Create Attendance Log',
    "{$baseUrl}/api/v1/attendance/logs",
    'POST',
    $attendanceData,
    ['Content-Type: application/json']
);

// Test Claim Reimbursement API
echo "=== CLAIM REIMBURSEMENT API TESTS ===\n\n";

// Test get claim reimbursements
testEndpoint(
    'Get Claim Reimbursements',
    "{$baseUrl}/api/v1/claims/employee/{$testEmployeeId}?api_key={$apiKeys['claims']}&limit=10"
);

// Test get claim summary
testEndpoint(
    'Get Claim Summary',
    "{$baseUrl}/api/v1/claims/summary/{$testEmployeeId}?api_key={$apiKeys['claims']}&start_date=" . date('Y-01-01') . "&end_date=" . date('Y-12-31')
);

// Test create claim reimbursement
$claimData = json_encode([
    'employee_id' => $testEmployeeId,
    'claim_type' => 'API Test Expense',
    'description' => 'Test claim created via API',
    'amount' => 1000.00,
    'claim_date' => date('Y-m-d'),
    'payment_method' => 'Bank Transfer',
    'remarks' => 'API testing purposes',
    'api_key' => $apiKeys['claims']
]);

testEndpoint(
    'Create Claim Reimbursement',
    "{$baseUrl}/api/v1/claims/submit",
    'POST',
    $claimData,
    ['Content-Type: application/json']
);

echo "=== API TESTING COMPLETED ===\n";
echo "Check the results above to verify API functionality.\n";
echo "Update the configuration variables at the top of this script as needed.\n";
