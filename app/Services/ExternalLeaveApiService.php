<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExternalLeaveApiService
{
    private $baseUrl = 'https://hr3.jetlougetravels-ph.com';
    private $endpoint = '/api/receive';
    
    /**
     * Send leave request data to external HR3 system
     */
    public function sendLeaveRequest($leaveApplication, $employee = null)
    {
        try {
            // Prepare data for external API
            $requestData = [
                'employee_id' => $leaveApplication->employee_id,
                'leave_id' => $leaveApplication->leave_id ?? $leaveApplication->id,
                'employee_name' => $employee ? 
                    $employee->first_name . ' ' . $employee->last_name : 
                    ($leaveApplication->employee ? 
                        $leaveApplication->employee->first_name . ' ' . $leaveApplication->employee->last_name : 
                        'Unknown Employee'),
                'employee_email' => $employee ? 
                    $employee->email : 
                    ($leaveApplication->employee ? $leaveApplication->employee->email : null),
                'leave_type' => $leaveApplication->leave_type,
                'leave_days' => $leaveApplication->days_requested ?? $leaveApplication->leave_days,
                'start_date' => $leaveApplication->start_date,
                'end_date' => $leaveApplication->end_date,
                'reason' => $leaveApplication->reason,
                'contact_info' => $leaveApplication->contact_info,
                'status' => $leaveApplication->status ?? 'Pending',
                'applied_date' => $leaveApplication->applied_date ?? $leaveApplication->created_at,
                'approved_by' => $leaveApplication->approved_by,
                'approved_date' => $leaveApplication->approved_date,
                'remarks' => $leaveApplication->remarks,
                'source_system' => 'HR2ESS',
                'timestamp' => Carbon::now()->toISOString()
            ];

            Log::info('Sending leave request to HR3 system', [
                'url' => $this->baseUrl . $this->endpoint,
                'leave_id' => $leaveApplication->leave_id ?? $leaveApplication->id,
                'employee_id' => $leaveApplication->employee_id
            ]);

            // Send HTTP request to external API
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'HR2ESS-LeaveAPI/1.0',
                    // Add authentication headers if required by HR3
                    // 'Authorization' => 'Bearer ' . env('HR3_API_TOKEN'),
                    // 'X-API-Key' => env('HR3_API_KEY'),
                ])
                ->post($this->baseUrl . $this->endpoint, $requestData);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('Successfully sent leave request to HR3', [
                    'leave_id' => $leaveApplication->leave_id ?? $leaveApplication->id,
                    'response_status' => $response->status(),
                    'response_data' => $responseData
                ]);

                return [
                    'success' => true,
                    'message' => 'Leave request successfully sent to HR3 system',
                    'hr3_response' => $responseData,
                    'status_code' => $response->status()
                ];
            } else {
                Log::error('Failed to send leave request to HR3', [
                    'leave_id' => $leaveApplication->leave_id ?? $leaveApplication->id,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'request_data' => $requestData
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to send leave request to HR3 system',
                    'error' => $response->body(),
                    'status_code' => $response->status()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception while sending leave request to HR3', [
                'leave_id' => $leaveApplication->leave_id ?? $leaveApplication->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending to HR3: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send leave status update to external HR3 system
     */
    public function sendStatusUpdate($leaveApplication, $oldStatus, $newStatus)
    {
        try {
            $updateData = [
                'employee_id' => $leaveApplication->employee_id,
                'leave_id' => $leaveApplication->leave_id ?? $leaveApplication->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'approved_by' => $leaveApplication->approved_by,
                'approved_date' => $leaveApplication->approved_date,
                'remarks' => $leaveApplication->remarks,
                'source_system' => 'HR2ESS',
                'timestamp' => Carbon::now()->toISOString()
            ];

            Log::info('Sending status update to HR3 system', [
                'leave_id' => $leaveApplication->leave_id ?? $leaveApplication->id,
                'status_change' => "$oldStatus -> $newStatus"
            ]);

            // You might need a different endpoint for status updates
            $statusEndpoint = '/api/leave-requests/status-update';
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'HR2ESS-LeaveAPI/1.0',
                ])
                ->post($this->baseUrl . $statusEndpoint, $updateData);

            if ($response->successful()) {
                Log::info('Successfully sent status update to HR3', [
                    'leave_id' => $leaveApplication->leave_id ?? $leaveApplication->id,
                    'status_change' => "$oldStatus -> $newStatus"
                ]);

                return [
                    'success' => true,
                    'message' => 'Status update successfully sent to HR3 system',
                    'hr3_response' => $response->json()
                ];
            } else {
                Log::warning('Failed to send status update to HR3', [
                    'leave_id' => $leaveApplication->leave_id ?? $leaveApplication->id,
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to send status update to HR3 system',
                    'error' => $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception while sending status update to HR3', [
                'leave_id' => $leaveApplication->leave_id ?? $leaveApplication->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while sending status update to HR3',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test connection to HR3 API
     */
    public function testConnection()
    {
        try {
            $testData = [
                'test' => true,
                'source_system' => 'HR2ESS',
                'timestamp' => Carbon::now()->toISOString()
            ];

            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'HR2ESS-LeaveAPI/1.0',
                ])
                ->post($this->baseUrl . $this->endpoint, $testData);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response' => $response->json(),
                'message' => $response->successful() ? 
                    'Connection to HR3 API successful' : 
                    'Connection to HR3 API failed'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
}
