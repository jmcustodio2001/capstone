<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        // Check if logged in via standard Auth guard
        if (Auth::guard('employee')->check()) {
            return $next($request);
        }

        // Check if logged in via session (External Employee)
        if ($request->session()->has('external_employee_data')) {
            // Manually log in the user for this request using the session data
            $data = session('external_employee_data');
            $employee = new \App\Models\Employee();
            $employee->forceFill($data);
            
            // Set the user on the guard for this request so Auth::guard('employee')->user() works in controllers
            Auth::guard('employee')->setUser($employee);
            
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->route('employee.login')->with('error', 'Please log in first.');
    }
}
