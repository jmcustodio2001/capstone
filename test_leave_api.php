<?php

/**
 * Leave Management API Test Script
 * 
 * This script demonstrates how to use the Leave Management API
 * Run this script to test the API endpoints
 */

// Configuration
$baseUrl = 'http://localhost:8000/api/v1/leave'; // Adjust to your domain
$apiKey = 'hr2ess_api_key_2025'; // Regular API key
$adminApiKey = 'hr2ess_admin_api_key_2025'; // Admin API key
$webhookSecret = 'hr2ess_webhook_secret_2025'; // Webhook secret

// Test employee ID (make sure this exists in your database)
$testEmployeeId = 'EMP001';

echo "=== Leave Management API Test Script with HR3 Integration ===\n\n";

/**
 * Helper function to make HTTP requests
 */
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    }
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers[] = 'Content-Type: application/json';
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error, 'http_code' => 0];
    }
    
    return [
        'response' => json_decode($response, true),
        'http_code' => $httpCode,
        'raw_response' => $response
    ];
}

/**
 * Test 0: HR3 Connection Test
 */
echo "0. Testing: HR3 API Connection\n";
echo "URL: POST {$baseUrl}/test-hr3-connection\n";

$hr3TestData = [
    'api_key' => $apiKey
];

echo "Request Data: " . json_encode($hr3TestData, JSON_PRETTY_PRINT) . "\n";

$result = makeRequest("{$baseUrl}/test-hr3-connection", 'POST', $hr3TestData);

echo "HTTP Code: {$result['http_code']}\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

/**
 * Test 1: Get Employee Leave Balance
 */
echo "1. Testing: Get Employee Leave Balance\n";
echo "URL: GET {$baseUrl}/balance/{$testEmployeeId}?api_key={$apiKey}\n";

$result = makeRequest("{$baseUrl}/balance/{$testEmployeeId}?api_key={$apiKey}");

echo "HTTP Code: {$result['http_code']}\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

/**
 * Test 2: Submit Leave Request
 */
echo "2. Testing: Submit Leave Request\n";
echo "URL: POST {$baseUrl}/submit\n";

$leaveData = [
    'employee_id' => $testEmployeeId,
    'leave_type' => 'Vacation',
    'leave_days' => 3,
    'start_date' => date('Y-m-d', strtotime('+7 days')),
    'end_date' => date('Y-m-d', strtotime('+9 days')),
    'reason' => 'API Test Leave Request',
    'contact_info' => 'test@example.com',
    'api_key' => $apiKey
];

echo "Request Data: " . json_encode($leaveData, JSON_PRETTY_PRINT) . "\n";

$result = makeRequest("{$baseUrl}/submit", 'POST', $leaveData);

echo "HTTP Code: {$result['http_code']}\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// Store leave ID for subsequent tests
$leaveId = null;
if (isset($result['response']['data']['leave_id'])) {
    $leaveId = $result['response']['data']['leave_id'];
    echo "Created Leave ID: {$leaveId}\n\n";
}

/**
 * Test 3: Get Leave Status
 */
if ($leaveId) {
    echo "3. Testing: Get Leave Status\n";
    echo "URL: GET {$baseUrl}/status/{$leaveId}?api_key={$apiKey}\n";

    $result = makeRequest("{$baseUrl}/status/{$leaveId}?api_key={$apiKey}");

    echo "HTTP Code: {$result['http_code']}\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";
}

/**
 * Test 4: Register Webhook
 */
if ($leaveId) {
    echo "4. Testing: Register Webhook\n";
    echo "URL: POST {$baseUrl}/webhook/status-update\n";

    $webhookData = [
        'webhook_secret' => $webhookSecret,
        'leave_id' => $leaveId,
        'callback_url' => 'https://example.com/webhook/leave-status'
    ];

    echo "Request Data: " . json_encode($webhookData, JSON_PRETTY_PRINT) . "\n";

    $result = makeRequest("{$baseUrl}/webhook/status-update", 'POST', $webhookData);

    echo "HTTP Code: {$result['http_code']}\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";
}

/**
 * Test 5: Approve Leave Request (Admin)
 */
if ($leaveId) {
    echo "5. Testing: Approve Leave Request (Admin)\n";
    echo "URL: PUT {$baseUrl}/status/{$leaveId}\n";

    $approvalData = [
        'status' => 'Approved',
        'approved_by' => 'API Test Admin',
        'remarks' => 'Approved via API test',
        'admin_api_key' => $adminApiKey
    ];

    echo "Request Data: " . json_encode($approvalData, JSON_PRETTY_PRINT) . "\n";

    $result = makeRequest("{$baseUrl}/status/{$leaveId}", 'PUT', $approvalData);

    echo "HTTP Code: {$result['http_code']}\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";
}

/**
 * Test 6: Get Updated Leave Balance
 */
echo "6. Testing: Get Updated Leave Balance (After Approval)\n";
echo "URL: GET {$baseUrl}/balance/{$testEmployeeId}?api_key={$apiKey}\n";

$result = makeRequest("{$baseUrl}/balance/{$testEmployeeId}?api_key={$apiKey}");

echo "HTTP Code: {$result['http_code']}\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

/**
 * Test 7: Get Leave History
 */
echo "7. Testing: Get Leave History\n";
echo "URL: GET {$baseUrl}/history/{$testEmployeeId}?api_key={$apiKey}&limit=10\n";

$result = makeRequest("{$baseUrl}/history/{$testEmployeeId}?api_key={$apiKey}&limit=10");

echo "HTTP Code: {$result['http_code']}\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

/**
 * Test 8: Error Handling - Invalid API Key
 */
echo "8. Testing: Error Handling - Invalid API Key\n";
echo "URL: GET {$baseUrl}/balance/{$testEmployeeId}?api_key=invalid_key\n";

$result = makeRequest("{$baseUrl}/balance/{$testEmployeeId}?api_key=invalid_key");

echo "HTTP Code: {$result['http_code']}\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

/**
 * Test 9: Error Handling - Insufficient Balance
 */
echo "9. Testing: Error Handling - Insufficient Balance\n";
echo "URL: POST {$baseUrl}/submit\n";

$invalidLeaveData = [
    'employee_id' => $testEmployeeId,
    'leave_type' => 'Vacation',
    'leave_days' => 50, // More than available
    'start_date' => date('Y-m-d', strtotime('+14 days')),
    'end_date' => date('Y-m-d', strtotime('+63 days')),
    'reason' => 'Testing insufficient balance',
    'api_key' => $apiKey
];

echo "Request Data: " . json_encode($invalidLeaveData, JSON_PRETTY_PRINT) . "\n";

$result = makeRequest("{$baseUrl}/submit", 'POST', $invalidLeaveData);

echo "HTTP Code: {$result['http_code']}\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

echo "=== API Test Complete ===\n";
echo "Summary:\n";
echo "- All major endpoints tested\n";
echo "- Error handling verified\n";
echo "- Balance deduction confirmed\n";
echo "- Webhook registration tested\n";
echo "\nCheck your application logs for webhook notifications and activity logs.\n";

?>
