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
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->route('employee.login')->with('error', 'Please log in first.');
    }
}
