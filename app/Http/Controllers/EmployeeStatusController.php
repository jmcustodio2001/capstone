<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Employee;

class EmployeeStatusController extends Controller
{
    public function checkOnlineStatus(Request $request)
    {
        $employeeIds = $request->input('employee_ids', []);
        $onlineStatus = [];
        $now = Carbon::now();
        $threshold = $now->subMinutes(5); // 5 minutes threshold for online

        $employees = Employee::whereIn('employee_id', $employeeIds)->get();
        foreach ($employees as $employee) {
            $onlineStatus[$employee->employee_id] = $employee->last_activity && $employee->last_activity > $threshold;
        }

        return response()->json([
            'success' => true,
            'online_status' => $onlineStatus,
        ]);
    }
}
