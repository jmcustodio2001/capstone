<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    // Show employee login form
    public function showEmployeeLoginForm()
    {
        return view('employee_ess_modules.employee_login');
    }

    // Employee login
    public function employee_login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Debug: Log the login attempt
        Log::info('Employee login attempt for: ' . $request->email);

        // First check if employee exists
        $employee = \App\Models\Employee::where('email', $request->email)->first();

        if (!$employee) {
            Log::info('Employee not found: ' . $request->email);
            return redirect()->back()
                ->withErrors(['email' => 'No account found with this email address.'])
                ->withInput();
        }

        // Then attempt authentication
        if (Auth::guard('employee')->attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();
            Log::info('Employee login successful, redirecting to dashboard');
            return redirect()->route('employee.dashboard')->with('success', 'Login successful');
        }

        // If we get here, the password was wrong
        Log::info('Employee login failed - wrong password');
        return redirect()->back()
            ->withErrors(['password' => 'The password you entered is incorrect.'])
            ->withInput();
    }

    // Show admin login form
    public function showAdminLoginForm()
    {
        return view('admin_login');
    }

    // Admin login using User model only
    public function admin_login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Check if user exists in User model
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()
                ->withErrors(['email' => 'No admin account found with this email.'])
                ->withInput();
        }

        // Check if user has ADMIN role
        if (strtoupper($user->role) !== 'ADMIN') {
            return redirect()->back()
                ->withErrors(['email' => 'Access denied. Admin access only.'])
                ->withInput();
        }

        // Attempt authentication using admin guard
        if (Auth::guard('admin')->attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            Log::info('Admin login successful, redirecting to admin dashboard');
            return redirect()->route('admin.dashboard')->with('success', 'Welcome to the Admin Dashboard!');
        }

        return redirect()->back()
            ->withErrors(['password' => 'Invalid password.'])
            ->withInput();
    }
}
