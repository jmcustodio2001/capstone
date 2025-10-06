<?php

namespace App\Http\Controllers;

use App\Models\AttendanceTimeLog;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;

class AttendanceTimeLogController extends Controller
{
    /**
     * Ensure the attendance_time_logs table exists
     */
    private function ensureTableExists()
    {
        try {
            if (!Schema::hasTable('attendance_time_logs')) {
                Schema::create('attendance_time_logs', function ($table) {
                    $table->id();
                    $table->string('employee_id', 20);
                    $table->date('log_date');
                    $table->time('time_in')->nullable();
                    $table->time('time_out')->nullable();
                    $table->decimal('hours_worked', 5, 2)->nullable();
                    $table->string('status', 50)->nullable();
                    $table->timestamps();
                    
                    $table->index(['employee_id', 'log_date']);
                    $table->index('log_date');
                    $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
                });
                
                // Sample data creation removed - no automatic entries
            }
        } catch (Exception $e) {
            // Log error but don't break functionality
            error_log("Error creating attendance_time_logs table: " . $e->getMessage());
        }
    }
    

    public function index()
    {
        // Ensure table exists before proceeding
        $this->ensureTableExists();
        
        $employee = Auth::guard('employee')->user();
        
        if (!$employee) {
            return redirect()->route('employee.login')->with('error', 'Please login to access attendance logs.');
        }
        
        // Get attendance logs for current employee
        $attendance_logs = AttendanceTimeLog::where('employee_id', $employee->employee_id)
            ->orderByDesc('log_date')
            ->orderByDesc('time_in')
            ->paginate(20);
        
        // Calculate statistics
        $stats = $this->calculateAttendanceStats($employee->employee_id);
        
        return view('employee_ess_modules.attendance_time_logs.attendance_time_logs', 
            compact('attendance_logs', 'stats'));
    }

    public function create()
    {
        $employees = Employee::all();
        return view('employee_ess_modules.attendance_time_logs.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'log_date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'hours_worked' => 'nullable|numeric',
            'status' => 'nullable|string',
        ]);
        AttendanceTimeLog::create($request->all());
        return redirect()->route('attendance_time_logs.index')->with('success', 'Attendance log created.');
    }

    public function show($log_id)
    {
        $log = AttendanceTimeLog::with('employee')->findOrFail($log_id);
        return view('employee_ess_modules.attendance_time_logs.show', compact('log'));
    }

    public function edit($log_id)
    {
        $log = AttendanceTimeLog::findOrFail($log_id);
        $employees = Employee::all();
        return view('employee_ess_modules.attendance_time_logs.edit', compact('log', 'employees'));
    }

    public function update(Request $request, $log_id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'log_date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'hours_worked' => 'nullable|numeric',
            'status' => 'nullable|string',
        ]);
        $log = AttendanceTimeLog::findOrFail($log_id);
        $log->update($request->all());
        return redirect()->route('attendance_time_logs.index')->with('success', 'Attendance log updated.');
    }

    public function destroy($log_id)
    {
        $log = AttendanceTimeLog::findOrFail($log_id);
        $log->delete();
        return redirect()->route('attendance_time_logs.index')->with('success', 'Attendance log deleted.');
    }

    /**
     * Handle employee time in
     */
    public function timeIn(Request $request)
    {
        try {
            // Ensure table exists before proceeding
            $this->ensureTableExists();
            
            $employee = Auth::guard('employee')->user();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login first.'
                ]);
            }
            
            $today = Carbon::today();
            
            // Check if already timed in today without timing out
            $existingLog = AttendanceTimeLog::where('employee_id', $employee->employee_id)
                ->where('log_date', $today)
                ->whereNotNull('time_in')
                ->whereNull('time_out')
                ->first();
            
            if ($existingLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already clocked in at ' . $existingLog->time_in . '. Please clock out first.'
                ]);
            }
            
            $timeIn = Carbon::now('Asia/Manila')->format('H:i:s');
            
            // Determine status based on time
            $standardTimeIn = Carbon::createFromTime(8, 0, 0); // 8:00 AM
            $currentTime = Carbon::now();
            $status = $currentTime->gt($standardTimeIn) ? 'Late' : 'Present';
            
            // Always create new record for each time-in
            $log = AttendanceTimeLog::create([
                'employee_id' => $employee->employee_id,
                'log_date' => $today,
                'time_in' => $timeIn,
                'status' => $status
            ]);
            
            // Log activity
            ActivityLog::createLog([
                'action' => 'Time In',
                'description' => "Employee {$employee->employee_id} timed in at {$timeIn} with status: {$status}",
                'module' => 'Attendance Time Logs'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Time in recorded successfully at {$timeIn}",
                'time_in' => $timeIn,
                'status' => $status,
                'log_id' => $log->id
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error recording time in: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle employee time out
     */
    public function timeOut(Request $request)
    {
        try {
            // Ensure table exists before proceeding
            $this->ensureTableExists();
            
            $employee = Auth::guard('employee')->user();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login first.'
                ]);
            }
            
            $today = Carbon::today();
            
            // Find today's latest attendance log that hasn't been timed out
            $log = AttendanceTimeLog::where('employee_id', $employee->employee_id)
                ->where('log_date', $today)
                ->whereNotNull('time_in')
                ->whereNull('time_out')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must time in first before timing out'
                ]);
            }
            
            $timeOut = Carbon::now('Asia/Manila')->format('H:i:s');
            
            // Calculate hours worked
            try {
                // Parse times and ensure they're on the same date
                $timeInCarbon = Carbon::parse($log->time_in);
                $timeOutCarbon = Carbon::parse($timeOut);
                
                // If time_in is stored as time only, set today's date
                if (strlen($log->time_in) <= 8) {
                    $timeInCarbon = Carbon::createFromFormat('H:i:s', $log->time_in);
                    $timeInCarbon->setDate($today->year, $today->month, $today->day);
                }
                
                // Set time_out to today's date
                $timeOutCarbon = Carbon::createFromFormat('H:i:s', $timeOut);
                $timeOutCarbon->setDate($today->year, $today->month, $today->day);
                
                // Calculate difference in minutes and convert to hours
                $minutesDiff = $timeInCarbon->diffInMinutes($timeOutCarbon);
                $hoursWorked = $minutesDiff / 60;
                
                // Ensure positive value
                $hoursWorked = abs($hoursWorked);
                
            } catch (\Exception $e) {
                // Fallback calculation if parsing fails
                $hoursWorked = 0;
            }
            
            // Update status based on checkout time
            $standardTimeOut = Carbon::createFromTime(18, 0, 0); // 6:00 PM
            $currentTime = Carbon::now();
            $status = $log->status;
            
            if ($currentTime->lt($standardTimeOut) && $hoursWorked < 8) {
                $status = 'Early Departure';
            } elseif ($currentTime->gt($standardTimeOut)) {
                $status = 'Overtime';
            }
            
            // Update the log
            $log->update([
                'time_out' => $timeOut,
                'hours_worked' => round($hoursWorked, 2),
                'status' => $status
            ]);
            
            // Log activity
            ActivityLog::createLog([
                'action' => 'Time Out',
                'description' => "Employee {$employee->employee_id} timed out at {$timeOut}. Hours worked: " . round($hoursWorked, 2) . " hours. Status: {$status}",
                'module' => 'Attendance Time Logs'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Time out recorded successfully at {$timeOut}",
                'time_out' => $timeOut,
                'hours_worked' => round($hoursWorked, 2),
                'status' => $status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error recording time out: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get current attendance status for today
     */
    public function getCurrentStatus()
    {
        try {
            // Ensure table exists before proceeding
            $this->ensureTableExists();
            
            $employee = Auth::guard('employee')->user();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login first.'
                ]);
            }
            
            $today = Carbon::today();
            
            // Check if currently clocked in (has time_in but no time_out)
            $currentLog = AttendanceTimeLog::where('employee_id', $employee->employee_id)
                ->where('log_date', $today)
                ->whereNotNull('time_in')
                ->whereNull('time_out')
                ->orderBy('created_at', 'desc')
                ->first();
            
            return response()->json([
                'success' => true,
                'has_timed_in' => $currentLog ? true : false,
                'has_timed_out' => $currentLog ? false : true,
                'time_in' => $currentLog ? $currentLog->time_in : null,
                'time_out' => null,
                'status' => $currentLog ? $currentLog->status : null
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting status: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Calculate attendance statistics
     */
    private function calculateAttendanceStats($employeeId)
    {
        $today = Carbon::today('Asia/Manila');
        $startOfWeek = Carbon::now('Asia/Manila')->startOfWeek();
        $startOfMonth = Carbon::now('Asia/Manila')->startOfMonth();
        
        // Today's hours
        $todayLog = AttendanceTimeLog::where('employee_id', $employeeId)
            ->where('log_date', $today)
            ->first();
        
        $todayHours = 0;
        $todayMinutes = 0;
        
        if ($todayLog && $todayLog->time_in) {
            if ($todayLog->time_out) {
                // Completed day
                $hoursWorked = (float) $todayLog->hours_worked;
                $todayHours = floor($hoursWorked);
                $todayMinutes = ($hoursWorked - $todayHours) * 60;
            } else {
                // Still working - calculate current hours
                try {
                    $timeIn = Carbon::parse($todayLog->time_in);
                    $now = Carbon::now('Asia/Manila');
                    $minutesWorked = $timeIn->diffInMinutes($now);
                    $todayHours = floor($minutesWorked / 60);
                    $todayMinutes = $minutesWorked % 60;
                } catch (\Exception $e) {
                    $todayHours = 0;
                    $todayMinutes = 0;
                }
            }
        }
        
        // This week's hours
        $weeklyHours = AttendanceTimeLog::where('employee_id', $employeeId)
            ->where('log_date', '>=', $startOfWeek)
            ->where('log_date', '<=', $today)
            ->sum('hours_worked') ?? 0;
        
        $weekHours = floor($weeklyHours);
        $weekMinutes = ($weeklyHours - $weekHours) * 60;
        
        // Attendance rate this month
        $totalWorkingDays = $this->getWorkingDaysInMonth($startOfMonth, $today);
        $presentDays = AttendanceTimeLog::where('employee_id', $employeeId)
            ->where('log_date', '>=', $startOfMonth)
            ->where('log_date', '<=', $today)
            ->whereNotNull('time_in')
            ->count();
        
        $attendanceRate = $totalWorkingDays > 0 ? round(($presentDays / $totalWorkingDays) * 100) : 0;
        
        // Late arrivals this month
        $lateCount = AttendanceTimeLog::where('employee_id', $employeeId)
            ->where('log_date', '>=', $startOfMonth)
            ->where('log_date', '<=', $today)
            ->where('status', 'Late')
            ->count();
        
        return [
            'today_hours' => sprintf('%dh %dm', max(0, $todayHours), max(0, round($todayMinutes))),
            'week_hours' => sprintf('%dh %dm', max(0, $weekHours), max(0, round($weekMinutes))),
            'attendance_rate' => $attendanceRate . '%',
            'late_count' => $lateCount
        ];
    }
    
    /**
     * Get detailed attendance information for a specific log
     */
    public function getDetails($logId)
    {
        try {
            // Ensure table exists before proceeding
            $this->ensureTableExists();
            
            $employee = Auth::guard('employee')->user();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login first.'
                ]);
            }
            
            // Find the attendance log and ensure it belongs to the current employee
            $log = AttendanceTimeLog::where('id', $logId)
                ->where('employee_id', $employee->employee_id)
                ->first();
            
            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found or access denied.'
                ]);
            }
            
            // Format the data for display
            $formattedLog = [
                'id' => $log->id,
                'formatted_date' => Carbon::parse($log->log_date)->format('F d, Y'),
                'day_of_week' => Carbon::parse($log->log_date)->format('l'),
                'time_in' => $log->time_in ? $this->formatTime($log->time_in) : 'Not recorded',
                'time_out' => $log->time_out ? $this->formatTime($log->time_out) : 'Not recorded',
                'hours_worked' => $log->hours_worked ? 
                    sprintf('%dh %dm', 
                        floor((float)$log->hours_worked), 
                        round(((float)$log->hours_worked - floor((float)$log->hours_worked)) * 60)
                    ) : '0h 0m',
                'status' => $log->status ?? 'Unknown',
                'status_color' => $this->getStatusColor($log->status ?? 'Unknown'),
                'remarks' => $log->remarks ?? null
            ];
            
            return response()->json([
                'success' => true,
                'log' => $formattedLog
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading attendance details: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Submit a correction request for attendance
     */
    public function submitCorrectionRequest(Request $request)
    {
        try {
            // Ensure table exists before proceeding
            $this->ensureTableExists();
            
            $employee = Auth::guard('employee')->user();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login first.'
                ]);
            }
            
            $request->validate([
                'log_id' => 'required|integer',
                'correction_type' => 'required|string',
                'reason' => 'required|string|min:10',
                'correct_time' => 'nullable|date_format:H:i'
            ]);
            
            // Verify the log belongs to the employee
            $log = AttendanceTimeLog::where('id', $request->log_id)
                ->where('employee_id', $employee->employee_id)
                ->first();
            
            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found or access denied.'
                ]);
            }
            
            // Log the correction request
            ActivityLog::createLog([
                'action' => 'Correction Request',
                'description' => "Employee {$employee->employee_id} requested correction for attendance log {$request->log_id}. Type: {$request->correction_type}. Reason: {$request->reason}" . 
                    ($request->correct_time ? " Correct time: {$request->correct_time}" : ""),
                'module' => 'Attendance Time Logs'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Correction request submitted successfully. HR will review your request within 24 hours.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting correction request: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Format time string for display
     */
    private function formatTime($time)
    {
        try {
            // Handle different time formats
            if (strpos($time, ' ') !== false) {
                // DateTime format
                return Carbon::parse($time)->format('g:i A');
            } else {
                // Time only format (H:i:s)
                return Carbon::createFromFormat('H:i:s', $time)->format('g:i A');
            }
        } catch (\Exception $e) {
            // Fallback for any parsing issues
            return $time;
        }
    }

    /**
     * Get status color for badges
     */
    private function getStatusColor($status)
    {
        switch (strtolower($status)) {
            case 'present':
                return 'success';
            case 'late':
                return 'warning';
            case 'absent':
                return 'danger';
            case 'early departure':
                return 'danger';
            case 'overtime':
                return 'info';
            default:
                return 'secondary';
        }
    }

    /**
     * Calculate working days in a month (excluding weekends)
     */
    private function getWorkingDaysInMonth($startDate, $endDate)
    {
        $workingDays = 0;
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            if ($current->isWeekday()) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        return $workingDays;
    }
    
    /**
     * Get location from IP address (simplified version)
     */
    private function getLocationFromIP($ipAddress)
    {
        // For localhost/development, return default location
        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1' || strpos($ipAddress, '192.168.') === 0) {
            return 'Office - Local Network';
        }
        
        // For production, you could integrate with a geolocation service
        // For now, return a generic location
        return 'Office Location';
    }
}
