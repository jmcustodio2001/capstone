<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceTimeLog;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceTimeLogApiController extends Controller
{
    /**
     * Get attendance time logs for an employee
     */
    public function getAttendanceLogs(Request $request, $employeeId)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['employee_id' => $employeeId]), [
                'employee_id' => 'required|string',
                'api_key' => 'required|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'limit' => 'nullable|integer|min:1|max:100',
                'status' => 'nullable|in:Present,Absent,Late,Early Departure,Overtime'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$this->validateApiKey($request->api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key'
                ], 401);
            }

            $employee = Employee::where('employee_id', $employeeId)->first();
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $query = AttendanceTimeLog::where('employee_id', $employeeId)
                ->orderByDesc('log_date');

            // Apply filters
            if ($request->start_date) {
                $query->where('log_date', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $query->where('log_date', '<=', $request->end_date);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $limit = $request->limit ?? 50;
            $attendanceLogs = $query->limit($limit)->get();

            $logs = $attendanceLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'employee_id' => $log->employee_id,
                    'log_date' => $log->log_date,
                    'time_in' => $log->time_in,
                    'time_out' => $log->time_out,
                    'break_start_time' => $log->break_start_time,
                    'break_end_time' => $log->break_end_time,
                    'total_hours' => $log->total_hours,
                    'overtime_hours' => $log->overtime_hours,
                    'hours_worked' => $log->hours_worked,
                    'status' => $log->status,
                    'location' => $log->location,
                    'ip_address' => $log->ip_address,
                    'notes' => $log->notes,
                    'created_at' => $log->created_at->toISOString(),
                    'updated_at' => $log->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employeeId,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'attendance_logs' => $logs,
                    'total_records' => $logs->count(),
                    'limit_applied' => $limit,
                    'filters_applied' => [
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'status' => $request->status
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Attendance logs retrieval error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendance logs'
            ], 500);
        }
    }

    /**
     * Create a new attendance time log entry
     */
    public function createAttendanceLog(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|string|exists:employees,employee_id',
                'log_date' => 'required|date',
                'time_in' => 'nullable|date_format:H:i:s',
                'time_out' => 'nullable|date_format:H:i:s|after:time_in',
                'break_start_time' => 'nullable|date_format:H:i:s',
                'break_end_time' => 'nullable|date_format:H:i:s|after:break_start_time',
                'total_hours' => 'nullable|numeric|min:0|max:24',
                'overtime_hours' => 'nullable|numeric|min:0|max:24',
                'hours_worked' => 'nullable|numeric|min:0|max:24',
                'status' => 'required|in:Present,Absent,Late,Early Departure,Overtime',
                'location' => 'nullable|string|max:255',
                'ip_address' => 'nullable|ip',
                'notes' => 'nullable|string|max:1000',
                'api_key' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$this->validateApiKey($request->api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key'
                ], 401);
            }

            // Check if attendance log already exists for this employee and date
            $existingLog = AttendanceTimeLog::where('employee_id', $request->employee_id)
                ->where('log_date', $request->log_date)
                ->first();

            if ($existingLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance log already exists for this employee and date',
                    'existing_log_id' => $existingLog->id
                ], 409);
            }

            // Calculate hours if not provided
            $totalHours = $request->total_hours;
            if (!$totalHours && $request->time_in && $request->time_out) {
                $timeIn = Carbon::parse($request->time_in);
                $timeOut = Carbon::parse($request->time_out);
                $totalHours = $timeOut->diffInHours($timeIn, true);
            }

            $attendanceLog = AttendanceTimeLog::create([
                'employee_id' => $request->employee_id,
                'log_date' => $request->log_date,
                'time_in' => $request->time_in,
                'time_out' => $request->time_out,
                'break_start_time' => $request->break_start_time,
                'break_end_time' => $request->break_end_time,
                'total_hours' => $totalHours,
                'overtime_hours' => $request->overtime_hours ?? 0,
                'hours_worked' => $request->hours_worked ?? $totalHours,
                'status' => $request->status,
                'location' => $request->location,
                'ip_address' => $request->ip_address,
                'notes' => $request->notes
            ]);

            // Log activity
            ActivityLog::create([
                'employee_id' => $request->employee_id,
                'module' => 'Attendance Management API',
                'action' => 'Attendance Log Created via API',
                'description' => "API creation: Attendance log for {$request->log_date} with status {$request->status}",
                'timestamp' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance log created successfully',
                'data' => [
                    'id' => $attendanceLog->id,
                    'employee_id' => $attendanceLog->employee_id,
                    'log_date' => $attendanceLog->log_date,
                    'time_in' => $attendanceLog->time_in,
                    'time_out' => $attendanceLog->time_out,
                    'total_hours' => $attendanceLog->total_hours,
                    'status' => $attendanceLog->status,
                    'created_at' => $attendanceLog->created_at->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('API Attendance log creation error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error occurred while creating attendance log',
                'error_code' => 'ATTENDANCE_CREATION_ERROR'
            ], 500);
        }
    }

    /**
     * Update an existing attendance time log
     */
    public function updateAttendanceLog(Request $request, $logId)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['log_id' => $logId]), [
                'log_id' => 'required|integer',
                'time_in' => 'nullable|date_format:H:i:s',
                'time_out' => 'nullable|date_format:H:i:s',
                'break_start_time' => 'nullable|date_format:H:i:s',
                'break_end_time' => 'nullable|date_format:H:i:s',
                'total_hours' => 'nullable|numeric|min:0|max:24',
                'overtime_hours' => 'nullable|numeric|min:0|max:24',
                'hours_worked' => 'nullable|numeric|min:0|max:24',
                'status' => 'nullable|in:Present,Absent,Late,Early Departure,Overtime',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
                'admin_api_key' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$this->validateAdminApiKey($request->admin_api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid admin API key'
                ], 401);
            }

            $attendanceLog = AttendanceTimeLog::find($logId);
            if (!$attendanceLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance log not found'
                ], 404);
            }

            // Update only provided fields
            $updateData = array_filter($request->only([
                'time_in', 'time_out', 'break_start_time', 'break_end_time',
                'total_hours', 'overtime_hours', 'hours_worked', 'status',
                'location', 'notes'
            ]), function($value) {
                return $value !== null;
            });

            // Recalculate total hours if time_in or time_out is updated
            if (isset($updateData['time_in']) || isset($updateData['time_out'])) {
                $timeIn = isset($updateData['time_in']) ? $updateData['time_in'] : $attendanceLog->time_in;
                $timeOut = isset($updateData['time_out']) ? $updateData['time_out'] : $attendanceLog->time_out;
                
                if ($timeIn && $timeOut) {
                    $timeInCarbon = Carbon::parse($timeIn);
                    $timeOutCarbon = Carbon::parse($timeOut);
                    $updateData['total_hours'] = $timeOutCarbon->diffInHours($timeInCarbon, true);
                    $updateData['hours_worked'] = $updateData['total_hours'];
                }
            }

            $attendanceLog->update($updateData);

            // Log activity
            ActivityLog::create([
                'employee_id' => $attendanceLog->employee_id,
                'module' => 'Attendance Management API',
                'action' => 'Attendance Log Updated via API',
                'description' => "API update: Attendance log ID {$logId} updated with fields: " . implode(', ', array_keys($updateData)),
                'timestamp' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance log updated successfully',
                'data' => [
                    'id' => $attendanceLog->id,
                    'employee_id' => $attendanceLog->employee_id,
                    'log_date' => $attendanceLog->log_date,
                    'time_in' => $attendanceLog->time_in,
                    'time_out' => $attendanceLog->time_out,
                    'total_hours' => $attendanceLog->total_hours,
                    'status' => $attendanceLog->status,
                    'updated_at' => $attendanceLog->updated_at->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Attendance log update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error updating attendance log'
            ], 500);
        }
    }

    /**
     * Get attendance summary for an employee
     */
    public function getAttendanceSummary(Request $request, $employeeId)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['employee_id' => $employeeId]), [
                'employee_id' => 'required|string',
                'api_key' => 'required|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$this->validateApiKey($request->api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key'
                ], 401);
            }

            $employee = Employee::where('employee_id', $employeeId)->first();
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $query = AttendanceTimeLog::where('employee_id', $employeeId);

            // Apply date filters
            $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
            $endDate = $request->end_date ?? Carbon::now()->endOfMonth()->toDateString();

            $query->whereBetween('log_date', [$startDate, $endDate]);

            $logs = $query->get();

            // Calculate summary statistics
            $summary = [
                'total_days' => $logs->count(),
                'present_days' => $logs->where('status', 'Present')->count(),
                'absent_days' => $logs->where('status', 'Absent')->count(),
                'late_days' => $logs->where('status', 'Late')->count(),
                'early_departure_days' => $logs->where('status', 'Early Departure')->count(),
                'overtime_days' => $logs->where('status', 'Overtime')->count(),
                'total_hours_worked' => $logs->sum('total_hours'),
                'total_overtime_hours' => $logs->sum('overtime_hours'),
                'average_hours_per_day' => $logs->count() > 0 ? round($logs->sum('total_hours') / $logs->count(), 2) : 0
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employeeId,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ],
                    'summary' => $summary,
                    'generated_at' => Carbon::now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Attendance summary retrieval error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendance summary'
            ], 500);
        }
    }

    /**
     * Validate API key
     */
    private function validateApiKey($apiKey)
    {
        $validApiKeys = [
            'hr2ess_api_key_2025',
            'attendance_management_api_v1',
            env('ATTENDANCE_API_KEY', 'default_api_key')
        ];

        return in_array($apiKey, $validApiKeys);
    }

    /**
     * Validate admin API key
     */
    private function validateAdminApiKey($apiKey)
    {
        $validAdminApiKeys = [
            'hr2ess_admin_api_key_2025',
            'attendance_admin_api_v1',
            env('ATTENDANCE_ADMIN_API_KEY', 'default_admin_api_key')
        ];

        return in_array($apiKey, $validAdminApiKeys);
    }
}
