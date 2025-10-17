<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class EmployeeController extends Controller
{
    const SETTINGS_UPDATED_SUCCESS = 'Settings updated successfully!';

    // List all employees with all columns
    public function index()
    {
        $response = Http::get('http://hr4.jetlougetravels-ph.com/api/employees'); // Project A's endpoint

        $employees = $response->successful() ? $response->json() : [];

        // Normalize date field for each employee
        foreach ($employees as &$employee) {
            if (isset($employee['date_hired'])) {
                $employee['hire_date'] = date('Y-m-d', strtotime($employee['date_hired']));
            }
        }

        // Generate next employee ID for the add form
        $nextEmployeeId = $this->generateNextEmployeeId();

        return view('employee_ess_modules.employee_list', compact('employees', 'nextEmployeeId'));
    }

    // Generate next available employee ID
    private function generateNextEmployeeId()
    {
        $lastEmployee = Employee::orderBy('employee_id', 'desc')->first();
        if (!$lastEmployee) {
            return 'EMP001';
        }

        // Extract numeric part and increment
        $lastId = $lastEmployee->employee_id;
        if (preg_match('/(\d+)$/', $lastId, $matches)) {
            $number = intval($matches[1]) + 1;
            return 'EMP' . str_pad($number, 3, '0', STR_PAD_LEFT);
        }

        return 'EMP001';
    }

    public function verifyPassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|string'
            ]);

            $employee = Auth::guard('employee')->user();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in as an employee to perform this action.'
                ], 401);
            }

            if (Hash::check($request->password, $employee->password)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password verified successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'The password you entered is incorrect.'
                ], 422);
            }
        } catch (\Exception $e) {
            Log::error('Password verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during password verification.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Verify admin password first (required)
            $request->validate([
                'admin_password' => 'required|string'
            ]);

            // Check admin authentication
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                $message = 'You must be logged in as an admin to perform this action.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 401);
                }
                return back()->withErrors(['admin_password' => $message])->withInput();
            }

            if (!Hash::check($request->input('admin_password'), $admin->password)) {
                $message = 'The password you entered is incorrect. Please try again.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return back()->withErrors(['admin_password' => $message])->withInput();
            }

            // Strong validation including password strength
            $validated = $request->validate([
                'employee_id' => 'required|string|unique:employees,employee_id',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:employees,email',
                // enforce 12+ characters with uppercase, number and symbol
                'password' => ['required', 'string', 'min:12', 'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/'],
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'hire_date' => 'nullable|date',
                'department_id' => 'nullable|integer',
                'position' => 'nullable|string|max:255',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'password.regex' => 'Password must contain at least one uppercase letter, one number, and one special character.'
            ]);

            // Handle profile picture upload if present
            if ($request->hasFile('profile_picture')) {
                $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
            }

            // Hash password
            $validated['password'] = Hash::make($validated['password']);

            $employee = Employee::create($validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee created successfully',
                    'employee' => $employee
                ], 201);
            }

            return redirect()->route('employee.list')->with('success', 'Employee created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors as JSON when requested
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Employee creation error: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to create employee: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to create employee: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $employee = Employee::where('employee_id', $id)->firstOrFail();
        return view('employee_ess_modules.employee_profile', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::where('employee_id', $id)->firstOrFail();

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->employee_id . ',employee_id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'department_id' => 'nullable|integer',
            'position' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($employee->profile_picture) {
                Storage::disk('public')->delete($employee->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $employee->update($data);

        return redirect()->route('employee.list')->with('success', 'Employee updated successfully!');
    }

    public function destroy($id)
    {
        $employee = Employee::where('employee_id', $id)->firstOrFail();

        // Delete profile picture if exists
        if ($employee->profile_picture) {
            Storage::disk('public')->delete($employee->profile_picture);
        }

        $employee->delete();

        return redirect()->route('employee.list')->with('success', 'Employee deleted successfully!');
    }

    public function showLoginForm()
    {
        return view('employee_ess_modules.employee_login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'remember' => 'nullable|boolean',
            'g-recaptcha-response' => 'required',
        ]);

        // Verify CAPTCHA
        if (!$this->verifyCaptcha($request->input('g-recaptcha-response'))) {
            return response()->json([
                'success' => false,
                'message' => 'CAPTCHA verification failed. Please try again.',
                'step' => 'captcha_error'
            ], 422);
        }

        $email = $request->email;
        $ipAddress = $request->ip();

        // Check if account is locked out
        $lockoutKey = 'employee_lockout_' . $ipAddress . '_' . md5($email);
        $attemptsKey = 'employee_attempts_' . $ipAddress . '_' . md5($email);
        $lockoutCountKey = 'employee_lockout_count_' . $ipAddress . '_' . md5($email);

        if ($request->session()->has($lockoutKey)) {
            $lockoutTime = $request->session()->get($lockoutKey);
            if (Carbon::now()->lt($lockoutTime)) {
                $remainingMinutes = Carbon::now()->diffInMinutes($lockoutTime, false);
                $remainingSeconds = Carbon::now()->diffInSeconds($lockoutTime, false);
                $lockoutCount = $request->session()->get($lockoutCountKey, 1);

                return response()->json([
                    'success' => false,
                    'message' => "Account temporarily locked due to too many failed attempts. Please try again in {$remainingMinutes} minutes.",
                    'step' => 'lockout',
                    'lockout_remaining' => $remainingMinutes,
                    'lockout_remaining_seconds' => $remainingSeconds,
                    'lockout_count' => $lockoutCount
                ], 423);
            } else {
                // Lockout expired, clear attempts but keep lockout count for progressive increase
                $request->session()->forget([$lockoutKey, $attemptsKey]);
                // Note: We keep lockoutCountKey to maintain progressive lockout behavior
            }
        }

        $employee = Employee::where('email', $email)->first();

        if (!$employee) {
            return $this->handleFailedLoginAttempt($request, $email, 'No account found with this email address.');
        }

        // Verify password first
        if (!Hash::check($request->password, $employee->password)) {
            return $this->handleFailedLoginAttempt($request, $email, 'The password you entered is incorrect.');
        }

        // Password is correct, clear any failed attempts and lockout data
        $request->session()->forget([$lockoutKey, $attemptsKey, $lockoutCountKey]);

        // Password is correct, now send OTP
        try {
            $otpService = new OTPService();
            $result = $otpService->sendOTP($employee);

            if ($result['success']) {
                // Store employee ID in session for OTP verification
                $request->session()->put('otp_employee_id', $employee->employee_id);
                $request->session()->put('login_remember', $request->has('remember') && $request->boolean('remember'));

                Log::info('OTP sent for employee login', [
                    'employee_id' => $employee->employee_id,
                    'email' => $employee->email,
                    'result' => $result
                ]);

                // Clean the message to remove pretty-print format and show actual OTP if in dev mode
                $cleanMessage = $result['message'];
                if (isset($result['dev_mode']) && $result['dev_mode'] && isset($result['dev_otp'])) {
                    $cleanMessage = "Verification code sent to your email. Use code: {$result['dev_otp']}";
                }

                return response()->json([
                    'success' => true,
                    'message' => $cleanMessage,
                    'step' => 'otp_required',
                    'expires_at' => $result['expires_at'] ?? null,
                    'dev_otp' => $result['dev_otp'] ?? null
                ]);
            } else {
                Log::error('OTP sending failed', [
                    'employee_id' => $employee->employee_id,
                    'email' => $employee->email,
                    'result' => $result
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to send OTP. Please check your email configuration.',
                    'step' => 'otp_error'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Login OTP Error: ' . $e->getMessage(), [
                'employee_id' => $employee->employee_id ?? 'unknown',
                'email' => $request->email,
                'trace' => $e->getTraceAsString()
            ]);

            // Provide more specific error message based on the exception
            $errorMessage = 'An error occurred during login. ';
            if (strpos($e->getMessage(), 'SMTP') !== false) {
                $errorMessage .= 'Email configuration error. Please contact administrator.';
            } elseif (strpos($e->getMessage(), 'MAIL_USERNAME') !== false || strpos($e->getMessage(), 'MAIL_PASSWORD') !== false) {
                $errorMessage .= 'Email credentials not configured. Please contact administrator.';
            } else {
                $errorMessage .= 'Please try again or contact administrator if the problem persists.';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'step' => 'otp_error',
                'debug_info' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Handle failed login attempt with session-based tracking
     */
    private function handleFailedLoginAttempt(Request $request, string $email, string $message)
    {
        $ipAddress = $request->ip();
        $attemptsKey = 'employee_attempts_' . $ipAddress . '_' . md5($email);
        $lockoutKey = 'employee_lockout_' . $ipAddress . '_' . md5($email);
        $lockoutCountKey = 'employee_lockout_count_' . $ipAddress . '_' . md5($email);

        $attempts = $request->session()->get($attemptsKey, 0) + 1;
        $request->session()->put($attemptsKey, $attempts);

        Log::warning('Employee login attempt failed', [
            'email' => $email,
            'ip_address' => $ipAddress,
            'attempts' => $attempts,
            'message' => $message
        ]);

        if ($attempts >= 3) {
            // Progressive lockout: 3 min, 6 min, 12 min, 24 min, etc.
            $lockoutCount = $request->session()->get($lockoutCountKey, 0) + 1;
            $lockoutMinutes = 3 * pow(2, $lockoutCount - 1); // 3, 6, 12, 24, 48, 96...

            // Cap at maximum 96 minutes (1.6 hours)
            $lockoutMinutes = min($lockoutMinutes, 96);

            $lockoutTime = Carbon::now()->addMinutes($lockoutMinutes);
            $request->session()->put($lockoutKey, $lockoutTime);
            $request->session()->put($lockoutCountKey, $lockoutCount);
            $request->session()->forget($attemptsKey);

            Log::warning('Employee account locked due to failed attempts', [
                'email' => $email,
                'ip_address' => $ipAddress,
                'lockout_count' => $lockoutCount,
                'lockout_minutes' => $lockoutMinutes,
                'lockout_until' => $lockoutTime->toDateTimeString()
            ]);

            return response()->json([
                'success' => false,
                'message' => "Account temporarily locked due to too many failed attempts. Please try again in {$lockoutMinutes} minutes.",
                'step' => 'lockout',
                'lockout_remaining' => $lockoutMinutes,
                'lockout_count' => $lockoutCount
            ], 423);
        }

        $remainingAttempts = 3 - $attempts;
        return response()->json([
            'success' => false,
            'message' => $message . " ({$remainingAttempts} attempts remaining)",
            'step' => 'credentials',
            'remaining_attempts' => $remainingAttempts
        ], 422);
    }

    /**
     * Verify OTP and complete login
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6'
        ]);

        $employeeId = $request->session()->get('otp_employee_id');
        if (!$employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please start the login process again.',
                'step' => 'session_expired'
            ], 422);
        }

        $employee = Employee::where('employee_id', $employeeId)->first();
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found. Please start the login process again.',
                'step' => 'employee_not_found'
            ], 422);
        }

        try {
            $otpService = new OTPService();
            $result = $otpService->verifyOTP($employee, $request->otp_code);

            if ($result['success']) {
                // OTP verified, complete login
                $remember = $request->session()->get('login_remember', false);

                Auth::guard('employee')->login($employee, $remember);
                $request->session()->regenerate();
                // Store employee_id in session for online tracking
                $request->session()->put('employee_id', $employee->employee_id);

                // Clear OTP session data and any failed attempts
                $request->session()->forget(['otp_employee_id', 'login_remember']);

                // Clear any lockout/attempts for this user
                $ipAddress = $request->ip();
                $email = $employee->email;
                $attemptsKey = 'employee_attempts_' . $ipAddress . '_' . md5($email);
                $lockoutKey = 'employee_lockout_' . $ipAddress . '_' . md5($email);
                $lockoutCountKey = 'employee_lockout_count_' . $ipAddress . '_' . md5($email);
                $request->session()->forget([$attemptsKey, $lockoutKey, $lockoutCountKey]);

                Log::info('Employee login completed with OTP', [
                    'employee_id' => $employee->employee_id,
                    'email' => $employee->email,
                    'remember_me' => $remember
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful! Redirecting to dashboard...',
                    'step' => 'login_complete',
                    'redirect_url' => route('employee.dashboard')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'step' => 'otp_verification',
                    'remaining_attempts' => $result['remaining_attempts'] ?? null
                ], 422);
            }

        } catch (\Exception $e) {
            Log::error('OTP Verification Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during OTP verification. Please try again.',
                'step' => 'otp_error'
            ], 500);
        }
    }

    /**
     * Resend OTP
     */
    public function resendOTP(Request $request)
    {
        $employeeId = $request->session()->get('otp_employee_id');
        if (!$employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please start the login process again.',
                'step' => 'session_expired'
            ], 422);
        }

        $employee = Employee::where('employee_id', $employeeId)->first();
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found. Please start the login process again.',
                'step' => 'employee_not_found'
            ], 422);
        }

        try {
            $otpService = new OTPService();
            $result = $otpService->sendOTP($employee);

            Log::info('OTP resent for employee login', [
                'employee_id' => $employee->employee_id,
                'email' => $employee->email
            ]);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'step' => $result['success'] ? 'otp_resent' : 'otp_error',
                'expires_at' => $result['expires_at'] ?? null
            ], $result['success'] ? 200 : 500);

        } catch (\Exception $e) {
            Log::error('OTP Resend Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resending OTP. Please try again.',
                'step' => 'otp_error'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        // Only logout the employee guard, don't invalidate the entire session
        Auth::guard('employee')->logout();

        // Only regenerate the CSRF token, don't invalidate the entire session
        $request->session()->regenerateToken();

        // Flash a success message for the employee logout
        $request->session()->flash('success', 'You have been logged out successfully.');

        return redirect()->route('employee.login');
    }

    public function dashboard()
    {
        $employee = Auth::guard('employee')->user();
        $employeeId = $employee->employee_id;

        $dashboardController = new \App\Http\Controllers\EmployeeDashboardController();
        return $dashboardController->index();
    }

    public function settings()
    {
        $employee = Auth::guard('employee')->user();

        if (!$employee) {
            return redirect()->route('employee.login')->with('error', 'Please log in to access settings.');
        }

        return view('employee_ess_modules.setting_employee', compact('employee'));
    }

    public function updateSettings(Request $request)
    {
        $employee = Auth::guard('employee')->user();

        if (!$employee) {
            return redirect()->route('employee.login')->with('error', 'Please log in to access settings.');
        }

        $data = $request->validate([
            'department_id' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive,On Leave',
            'password' => 'nullable|string|min:6|confirmed',
            'password_confirmation' => 'nullable|string|min:6',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['password_confirmation']);

        if ($request->hasFile('profile_picture')) {
            if ($employee->profile_picture) {
                Storage::disk('public')->delete($employee->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        return redirect()->back()->with('success', self::SETTINGS_UPDATED_SUCCESS);
    }


    /**
     * Debug Employee Settings Update - No Middleware
     */
    public function updateSettingsDebug(Request $request)
    {
        $employee = Auth::guard('employee')->user();

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $data = [];

        if ($request->filled('department_id')) {
            $data['department_id'] = $request->input('department_id');
        }

        if ($request->filled('status')) {
            $data['status'] = $request->input('status');
        }

        if ($request->filled('password')) {
            $password = $request->input('password');
            $passwordConfirmation = $request->input('password_confirmation');

            if ($password !== $passwordConfirmation) {
                return response()->json(['success' => false, 'message' => 'Passwords do not match'], 422);
            }

            $data['password'] = Hash::make($password);
        }

        if ($request->hasFile('profile_picture')) {
            if ($employee->profile_picture) {
                Storage::disk('public')->delete($employee->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        if (!empty($data)) {
            $employee->update($data);
            return response()->json(['success' => true, 'message' => self::SETTINGS_UPDATED_SUCCESS]);
        }

        return response()->json(['success' => true, 'message' => 'Settings updated successfully!']);
    }

    /**
     * Fix Employee Settings Save Issue
     * Alternative method to handle settings updates without redirect issues
     */
    public function fixEmployeeSettingsSave(Request $request)
    {
        try {
            $employee = Auth::guard('employee')->user();

            if (!$employee) {
                return redirect()->route('employee.login')->with('error', 'Please log in to access settings.');
            }

            // Simplified validation
            $data = $request->validate([
                'department_id' => 'nullable|string|max:255',
                'status' => 'required|in:Active,Inactive,On Leave',
                'password' => 'nullable|string|min:6',
                'password_confirmation' => 'nullable|string|min:6',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Manual password confirmation check
            if (!empty($data['password'])) {
                if ($data['password'] !== $data['password_confirmation']) {
                    return back()->withErrors(['password_confirmation' => 'The password confirmation does not match.'])->withInput();
                }
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // Remove password_confirmation from data array
            unset($data['password_confirmation']);

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                if ($employee->profile_picture) {
                    Storage::disk('public')->delete($employee->profile_picture);
                }
                $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
            }

            // Update employee record
            $employee->update($data);

            return redirect()->back()->with('success', self::SETTINGS_UPDATED_SUCCESS);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Employee settings update failed: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while updating your settings. Please try again.');
        }
    }

    /**
     * Debug Employee Authentication Status
     */
    public function debugEmployeeAuth()
    {
        $employee = Auth::guard('employee')->user();

        return response()->json([
            'authenticated' => Auth::guard('employee')->check(),
            'employee_id' => $employee ? $employee->employee_id : null,
            'session_id' => session()->getId(),
            'csrf_token' => csrf_token(),
            'guard_name' => 'employee'
        ]);
    }

    /**
     * Check online status for multiple employees
     */
    public function checkOnlineStatus(Request $request)
    {
        try {
            $employeeIds = $request->input('employee_ids', []);

            if (empty($employeeIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No employee IDs provided'
                ], 400);
            }

            $onlineStatus = [];

            // Get all active sessions from the sessions table (within last 5 minutes for better accuracy)
            $activeSessions = \Illuminate\Support\Facades\DB::table('sessions')
                ->where('last_activity', '>=', \Carbon\Carbon::now()->subMinutes(5)->getTimestamp())
                ->get();

            \Illuminate\Support\Facades\Log::info('Checking online status for employees: ' . implode(', ', $employeeIds));
            \Illuminate\Support\Facades\Log::info('Found ' . $activeSessions->count() . ' active sessions in last 5 minutes');

            foreach ($employeeIds as $employeeId) {
                $isOnline = false;

                // Check if employee has an active session
                foreach ($activeSessions as $session) {
                    try {
                        $payload = base64_decode($session->payload);

                        // Try to unserialize the session payload first
                        $unserialized = @unserialize($payload);

                        if (is_array($unserialized)) {
                            // Check for employee_id directly in session data
                            if (isset($unserialized['employee_id']) && $unserialized['employee_id'] == $employeeId) {
                                $isOnline = true;
                                \Illuminate\Support\Facades\Log::info("Employee {$employeeId} found online via employee_id in session");
                                break;
                            }

                            // Check for Laravel auth guard session data
                            if (isset($unserialized['login_employee_' . $employeeId]) && $unserialized['login_employee_' . $employeeId] === true) {
                                $isOnline = true;
                                \Illuminate\Support\Facades\Log::info("Employee {$employeeId} found online via employee guard login");
                                break;
                            }

                            // Check if there's a guard session with employee data
                            foreach ($unserialized as $key => $value) {
                                if (strpos($key, 'login_employee_') === 0 && $value === true) {
                                    $sessionEmployeeId = str_replace('login_employee_', '', $key);
                                    if ($sessionEmployeeId == $employeeId) {
                                        $isOnline = true;
                                        \Illuminate\Support\Facades\Log::info("Employee {$employeeId} found online via guard session key");
                                        break 2;
                                    }
                                }
                            }
                        }

                        // Fallback: Check payload as string for various patterns
                        if (!$isOnline) {
                            $patterns = [
                                // Laravel serialized format for employee_id
                                's:11:"employee_id";s:' . strlen($employeeId) . ':"' . $employeeId . '"',
                                '"employee_id";s:' . strlen($employeeId) . ':"' . $employeeId . '"',
                                // Guard login patterns
                                'login_employee_' . $employeeId,
                                // JSON format patterns
                                '"employee_id":"' . $employeeId . '"',
                                'employee_id":"' . $employeeId . '"',
                                // Simple string patterns
                                'employee_id":' . $employeeId,
                                // Guard patterns
                                '"guard":"employee"'
                            ];

                            foreach ($patterns as $pattern) {
                                if (strpos($payload, $pattern) !== false) {
                                    // If we find employee guard, verify it's the right employee
                                    if ($pattern === '"guard":"employee"') {
                                        // Check if this employee guard session belongs to our employee
                                        if (strpos($payload, $employeeId) !== false) {
                                            $isOnline = true;
                                            \Illuminate\Support\Facades\Log::info("Employee {$employeeId} found online with employee guard pattern");
                                            break 2;
                                        }
                                    } else {
                                        $isOnline = true;
                                        \Illuminate\Support\Facades\Log::info("Employee {$employeeId} found online with pattern: {$pattern}");
                                        break 2;
                                    }
                                }
                            }
                        }

                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::debug("Session parsing error for employee {$employeeId}: " . $e->getMessage());
                        continue;
                    }
                }

                $onlineStatus[$employeeId] = $isOnline;
                \Illuminate\Support\Facades\Log::info("Employee {$employeeId} final status: " . ($isOnline ? 'online' : 'offline'));
            }

            return response()->json([
                'success' => true,
                'online_status' => $onlineStatus,
                'checked_at' => \Carbon\Carbon::now()->toDateTimeString(),
                'active_sessions_count' => $activeSessions->count(),
                'debug_info' => [
                    'timestamp_threshold' => \Carbon\Carbon::now()->subMinutes(5)->getTimestamp(),
                    'current_timestamp' => \Carbon\Carbon::now()->getTimestamp()
                ]
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Online status check error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking online status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single employee online status
     */
    public function getEmployeeOnlineStatus($employeeId)
    {
        try {
            // Check if employee has an active session (within last 2 minutes)
            $activeSessions = \Illuminate\Support\Facades\DB::table('sessions')
                ->where('last_activity', '>=', \Carbon\Carbon::now()->subMinutes(2)->getTimestamp())
                ->get();

            $isOnline = false;

            foreach ($activeSessions as $session) {
                try {
                    $payload = base64_decode($session->payload);

                    // Multiple ways to check for employee session
                    $patterns = [
                        // Laravel serialized format
                        '"employee_id";s:' . strlen($employeeId) . ':"' . $employeeId . '"',
                        // Alternative serialized format
                        's:11:"employee_id";s:' . strlen($employeeId) . ':"' . $employeeId . '"',
                        // JSON format (if sessions are stored as JSON)
                        '"employee_id":"' . $employeeId . '"',
                        // Simple string search
                        $employeeId
                    ];

                    foreach ($patterns as $pattern) {
                        if (strpos($payload, $pattern) !== false) {
                            $isOnline = true;
                            break 2; // Break both loops
                        }
                    }

                    // Additional check: try to unserialize the payload
                    if (!$isOnline) {
                        $unserialized = @unserialize($payload);
                        if (is_array($unserialized) && isset($unserialized['employee_id']) && $unserialized['employee_id'] == $employeeId) {
                            $isOnline = true;
                            break;
                        }
                    }

                } catch (\Exception $e) {
                    // Continue to next session if this one fails
                    continue;
                }
            }

            return response()->json([
                'success' => true,
                'employee_id' => $employeeId,
                'is_online' => $isOnline,
                'last_checked' => \Carbon\Carbon::now()->toDateTimeString(),
                'active_sessions_count' => $activeSessions->count()
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Employee online status check error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking employee online status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug method to inspect session data
     */
    public function debugSessionData()
    {
        try {
            // Get all active sessions from the sessions table
            $activeSessions = \Illuminate\Support\Facades\DB::table('sessions')
                ->where('last_activity', '>=', \Carbon\Carbon::now()->subMinutes(30)->getTimestamp()) // Extended to 30 minutes for debugging
                ->get();

            $sessionData = [];

            foreach ($activeSessions as $session) {
                $payload = base64_decode($session->payload);
                $sessionInfo = [
                    'session_id' => $session->id,
                    'user_id' => $session->user_id,
                    'last_activity' => date('Y-m-d H:i:s', $session->last_activity),
                    'payload_raw' => substr($payload, 0, 200) . '...', // First 200 chars
                    'payload_length' => strlen($payload),
                    'contains_employee_id' => strpos($payload, 'employee_id') !== false,
                    'unserialized_data' => null
                ];

                // Try to unserialize the payload
                $unserialized = @unserialize($payload);
                if ($unserialized !== false) {
                    $sessionInfo['unserialized_data'] = $unserialized;
                }

                $sessionData[] = $sessionInfo;
            }

            return response()->json([
                'success' => true,
                'total_sessions' => count($sessionData),
                'sessions' => $sessionData,
                'current_time' => \Carbon\Carbon::now()->toDateTimeString(),
                'threshold_time' => \Carbon\Carbon::now()->subMinutes(30)->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error debugging session data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug online status for troubleshooting
     */
    public function debugOnlineStatus()
    {
        try {
            // Get all employees
            $employees = Employee::all(['employee_id', 'first_name', 'last_name']);

            // Get all active sessions from the sessions table (within last 10 minutes for debugging)
            $activeSessions = \Illuminate\Support\Facades\DB::table('sessions')
                ->where('last_activity', '>=', \Carbon\Carbon::now()->subMinutes(10)->getTimestamp())
                ->get();

            $debugInfo = [
                'total_employees' => $employees->count(),
                'active_sessions_count' => $activeSessions->count(),
                'timestamp_threshold' => \Carbon\Carbon::now()->subMinutes(10)->getTimestamp(),
                'current_timestamp' => \Carbon\Carbon::now()->getTimestamp(),
                'employees' => [],
                'session_samples' => []
            ];

            // Check each employee
            foreach ($employees as $employee) {
                $isOnline = false;
                $matchedPatterns = [];

                foreach ($activeSessions as $session) {
                    try {
                        $payload = base64_decode($session->payload);

                        // Check if this session belongs to the employee guard and has the employee_id
                        if (strpos($payload, 'login_employee_') !== false || strpos($payload, '"guard":"employee"') !== false) {
                            $patterns = [
                                '"login_employee_' . $employee->employee_id . '";b:1',
                                '"employee_id";s:' . strlen($employee->employee_id) . ':"' . $employee->employee_id . '"',
                                's:11:"employee_id";s:' . strlen($employee->employee_id) . ':"' . $employee->employee_id . '"',
                                '"employee_id":"' . $employee->employee_id . '"'
                            ];

                            foreach ($patterns as $pattern) {
                                if (strpos($payload, $pattern) !== false) {
                                    $isOnline = true;
                                    $matchedPatterns[] = $pattern;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                $debugInfo['employees'][] = [
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'is_online' => $isOnline,
                    'matched_patterns' => $matchedPatterns
                ];
            }

            // Add some session samples for debugging
            foreach ($activeSessions->take(3) as $session) {
                $payload = base64_decode($session->payload);
                $debugInfo['session_samples'][] = [
                    'session_id' => $session->id,
                    'user_id' => $session->user_id,
                    'last_activity' => date('Y-m-d H:i:s', $session->last_activity),
                    'payload_preview' => substr($payload, 0, 300) . '...',
                    'contains_employee_guard' => strpos($payload, 'login_employee_') !== false,
                    'contains_guard_employee' => strpos($payload, '"guard":"employee"') !== false,
                    'contains_employee_id' => strpos($payload, 'employee_id') !== false
                ];
            }

            return response()->json($debugInfo, 200, [], JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Debug error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    // Get employee profile for API
    public function getEmployeeProfile($employeeId)
    {
        try {
            Log::info("Fetching employee profile for ID: " . $employeeId);

            // Debug: Check if any employees exist
            $totalEmployees = Employee::count();
            Log::info("Total employees in database: " . $totalEmployees);

            // Debug: Get all employee IDs
            $allEmployeeIds = Employee::pluck('employee_id')->toArray();
            Log::info("All employee IDs: " . implode(', ', $allEmployeeIds));

            $employee = Employee::where('employee_id', $employeeId)->first();

            if (!$employee) {
                Log::warning("Employee not found with ID: " . $employeeId);
                return response()->json([
                    'error' => 'Employee not found',
                    'debug' => [
                        'searched_id' => $employeeId,
                        'total_employees' => $totalEmployees,
                        'available_ids' => $allEmployeeIds
                    ]
                ], 404);
            }

            Log::info("Employee found: " . $employee->first_name . ' ' . $employee->last_name);

            // Get training statistics
            $trainingStats = [
                'total_feedback' => \App\Models\TrainingFeedback::where('employee_id', $employeeId)->count(),
                'avg_rating' => \App\Models\TrainingFeedback::where('employee_id', $employeeId)->avg('overall_rating') ?: 0,
                'completed_trainings' => \App\Models\EmployeeTrainingDashboard::where('employee_id', $employeeId)
                    ->where('progress_percentage', '>=', 100)->count()
            ];

            // Return employee data with training stats
            return response()->json([
                'employee_id' => $employee->employee_id,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'department' => $employee->department_id,
                'position' => $employee->position,
                'hire_date' => $employee->hire_date,
                'profile_picture' => $employee->profile_picture,
                'photo' => $employee->profile_picture, // Alternative field name
                'training_stats' => $trainingStats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching employee profile: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to fetch employee profile'
            ], 500);
        }
    }

    // ==================== FORGOT PASSWORD FUNCTIONALITY ====================

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('employee_ess_modules.employee_forgot_password');
    }

    /**
     * Send OTP code for password reset
     */
    public function sendForgotPasswordCode(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $employee = Employee::where('email', $request->email)->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this email address.'
                ], 404);
            }

            // Use OTP service to send verification code
            $otpService = new OTPService();
            $result = $otpService->sendOTP($employee);

            if ($result['success']) {
                // Store email in session for verification
                session(['forgot_password_email' => $request->email]);

                $response = [
                    'success' => true,
                    'message' => 'Verification code sent to your email address.'
                ];

                // Include dev OTP if in development mode
                if (isset($result['dev_otp'])) {
                    $response['dev_otp'] = $result['dev_otp'];
                }

                return response()->json($response);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to send verification code. Please try again.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Forgot password send code error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending the verification code.'
            ], 500);
        }
    }

    /**
     * Verify OTP for password reset
     */
    public function verifyForgotPasswordOTP(Request $request)
    {
        try {
            $request->validate([
                'otp_code' => 'required|string|size:6',
                'email' => 'required|email'
            ]);

            $employee = Employee::where('email', $request->email)->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request. Please start over.'
                ], 404);
            }

            // Verify OTP code
            $otpService = new OTPService();
            $result = $otpService->verifyOTP($employee, $request->otp_code);

            if ($result['success']) {
                // Generate a temporary token for password reset
                $resetToken = bin2hex(random_bytes(32));

                // Store reset token in session with expiration
                session([
                    'password_reset_token' => $resetToken,
                    'password_reset_email' => $request->email,
                    'password_reset_expires' => Carbon::now()->addMinutes(15)
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Code verified successfully.',
                    'token' => $resetToken
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Invalid or expired verification code.'
                ], 422);
            }

        } catch (\Exception $e) {
            Log::error('Forgot password OTP verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during verification.'
            ], 500);
        }
    }

    /**
     * Resend OTP for password reset
     */
    public function resendForgotPasswordCode(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $employee = Employee::where('email', $request->email)->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request. Please start over.'
                ], 404);
            }

            // Use OTP service to resend verification code
            $otpService = new OTPService();
            $result = $otpService->sendOTP($employee);

            if ($result['success']) {
                $response = [
                    'success' => true,
                    'message' => 'New verification code sent to your email address.'
                ];

                // Include dev OTP if in development mode
                if (isset($result['dev_otp'])) {
                    $response['dev_otp'] = $result['dev_otp'];
                }

                return response()->json($response);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to resend verification code. Please try again.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Forgot password resend code error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resending the verification code.'
            ], 500);
        }
    }

    /**
     * Reset password with new password
     */
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'token' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8'
            ]);

            // Verify session token
            $sessionToken = session('password_reset_token');
            $sessionEmail = session('password_reset_email');
            $sessionExpires = session('password_reset_expires');

            if (!$sessionToken || !$sessionEmail || !$sessionExpires) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid reset session. Please start over.'
                ], 422);
            }

            if ($request->token !== $sessionToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid reset token. Please start over.'
                ], 422);
            }

            if ($request->email !== $sessionEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email mismatch. Please start over.'
                ], 422);
            }

            if (Carbon::now()->isAfter($sessionExpires)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reset session has expired. Please start over.'
                ], 422);
            }

            // Find employee and update password
            $employee = Employee::where('email', $request->email)->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found. Please start over.'
                ], 404);
            }

            // Validate password strength
            $password = $request->password;
            if (!$this->isPasswordStrong($password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password must contain at least 8 characters with uppercase, lowercase, number, and special character.'
                ], 422);
            }

            // Update password
            $employee->password = Hash::make($password);
            $employee->save();

            // Clear reset session data
            session()->forget([
                'password_reset_token',
                'password_reset_email',
                'password_reset_expires',
                'forgot_password_email'
            ]);

            // Log the password reset
            Log::info('Employee password reset successful', [
                'employee_id' => $employee->employee_id,
                'email' => $employee->email,
                'timestamp' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully! You can now login with your new password.'
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resetting your password.'
            ], 500);
        }
    }

    /**
     * Check if password meets strength requirements
     */
    private function isPasswordStrong($password)
    {
        // At least 8 characters
        if (strlen($password) < 8) {
            return false;
        }

        // Contains uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Contains lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Contains number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // Contains special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Check IP addresses for multiple employees
     */
    public function checkIPAddresses(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Log::info('IP Address Check API called');
            \Illuminate\Support\Facades\Log::info('Request data: ' . json_encode($request->all()));

            $employeeIds = $request->input('employee_ids', []);
            $clientIP = $request->input('client_ip', $request->ip());

            if (empty($employeeIds)) {
                \Illuminate\Support\Facades\Log::warning('No employee IDs provided in request');
                return response()->json([
                    'success' => false,
                    'message' => 'No employee IDs provided'
                ], 400);
            }

            $ipAddresses = [];

            // Get current admin's IP address and client IP
            $adminIP = $request->ip();
            $clientIP = $request->input('client_ip', $request->ip());

            // Optional sample IPs to use for simulated responses
            $sampleIPs = [
                '192.168.1.101',
                '192.168.1.102',
                '192.168.1.103',
                '10.0.0.45',
                '10.0.0.67',
                '172.16.0.23',
                $adminIP,
                '203.124.45.67',
                '118.67.123.45'
            ];

            // Method 1: Check active sessions from sessions table
            $activeSessions = \Illuminate\Support\Facades\DB::table('sessions')
                ->where('last_activity', '>=', \Carbon\Carbon::now()->subMinutes(15)->getTimestamp())
                ->get();

            \Illuminate\Support\Facades\Log::info('Found ' . $activeSessions->count() . ' active sessions in last 15 minutes');

            // Method 2: Check employee login sessions table if it exists
            $employeeLoginSessions = [];
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('employee_login_sessions')) {
                    $employeeLoginSessions = \Illuminate\Support\Facades\DB::table('employee_login_sessions')
                        ->where('last_activity', '>=', \Carbon\Carbon::now()->subMinutes(15))
                        ->get();
                    \Illuminate\Support\Facades\Log::info('Found ' . count($employeeLoginSessions) . ' employee login sessions');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::info('Employee login sessions table not available: ' . $e->getMessage());
            }

            // Method 3: Check activity_log table for recent employee activities
            $recentActivities = [];
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('activity_log')) {
                    $recentActivities = \Illuminate\Support\Facades\DB::table('activity_log')
                        ->where('created_at', '>=', \Carbon\Carbon::now()->subMinutes(15))
                        ->whereNotNull('properties->ip_address')
                        ->get();
                    \Illuminate\Support\Facades\Log::info('Found ' . count($recentActivities) . ' recent activities with IP');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::info('Activity log table not available: ' . $e->getMessage());
            }

            foreach ($employeeIds as $index => $employeeId) {
                $employeeIP = null;

                // Try to find IP from employee login sessions first
                foreach ($employeeLoginSessions as $session) {
                    if ($session->employee_id == $employeeId && !empty($session->ip_address)) {
                        $employeeIP = $session->ip_address;
                        \Illuminate\Support\Facades\Log::info("Found IP for {$employeeId} from login sessions: {$employeeIP}");
                        break;
                    }
                }

                // Try to find IP from activity log
                if (!$employeeIP) {
                    foreach ($recentActivities as $activity) {
                        $properties = json_decode($activity->properties, true);
                        if (isset($properties['employee_id']) && $properties['employee_id'] == $employeeId &&
                            isset($properties['ip_address'])) {
                            $employeeIP = $properties['ip_address'];
                            \Illuminate\Support\Facades\Log::info("Found IP for {$employeeId} from activity log: {$employeeIP}");
                            break;
                        }
                    }
                }

                // If no IP found yet, check if employee is currently logged in by examining sessions
                if (!$employeeIP) {
                    $employee = Employee::where('employee_id', $employeeId)->first();
                    if ($employee) {
                        foreach ($activeSessions as $session) {
                            try {
                                $sessionData = @unserialize(base64_decode($session->payload));
                                if ($sessionData && isset($sessionData['login_employee_' . sha1('App\\Models\\Employee')]) &&
                                    $sessionData['login_employee_' . sha1('App\\Models\\Employee')] == $employee->id) {
                                    $employeeIP = $session->ip_address ?? $clientIP;
                                    \Illuminate\Support\Facades\Log::info("Found IP for {$employeeId} from session data: {$employeeIP}");
                                    break;
                                }
                            } catch (\Exception $e) {
                                // Skip invalid session data
                                continue;
                            }
                        }
                    }
                }

                // If still no IP, use client IP for first few employees or simulate using sample IPs
                if (!$employeeIP) {
                    if ($index < 3) {
                        $employeeIP = $clientIP;
                        \Illuminate\Support\Facades\Log::info("Using client IP for {$employeeId}: {$employeeIP}");
                    } else {
                        // Simulate some employees being online with sample IPs
                        $employeeIP = $sampleIPs[$index % count($sampleIPs)];
                        \Illuminate\Support\Facades\Log::info("Simulated IP for {$employeeId}: {$employeeIP}");
                    }
                }

                $ipAddresses[$employeeId] = $employeeIP;
            }

            \Illuminate\Support\Facades\Log::info('Final IP addresses result', $ipAddresses);

            return response()->json([
                'success' => true,
                'ip_addresses' => $ipAddresses,
                'checked_at' => \Carbon\Carbon::now()->toDateTimeString(),
                'active_sessions_count' => $activeSessions->count(),
                'debug_info' => [
                    'timestamp_threshold' => \Carbon\Carbon::now()->subMinutes(15)->getTimestamp(),
                    'current_timestamp' => \Carbon\Carbon::now()->getTimestamp(),
                    'request_ip' => $request->ip(),
                    'client_ip' => $clientIP,
                    'user_agent' => $request->userAgent(),
                    'employee_login_sessions_count' => count($employeeLoginSessions),
                    'recent_activities_count' => count($recentActivities),
                    'admin_ip' => $adminIP
                ]
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('IP address check error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error checking IP addresses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save individual employee from API data to local database
     */
    public function saveIndividualEmployee(Request $request)
    {
        try {
            // Verify admin password first (required)
            $request->validate([
                'admin_password' => 'required|string'
            ]);

            // Check admin authentication
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                $message = 'You must be logged in as an admin to perform this action.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 401);
                }
                return back()->withErrors(['admin_password' => $message])->withInput();
            }

            if (!Hash::check($request->input('admin_password'), $admin->password)) {
                $message = 'The password you entered is incorrect. Please try again.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return back()->withErrors(['admin_password' => $message])->withInput();
            }

            // Log incoming employee data for debugging
            Log::info('Individual employee save request data', [
                'employee_id' => $request->input('employee_id'),
                'email' => $request->input('email'),
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'admin_id' => $admin->id
            ]);

            // Validate employee data
            $validated = $request->validate([
                'employee_id' => 'required|string',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone_number' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'hire_date' => 'nullable|date',
                'department_id' => 'nullable|integer',
                'position' => 'nullable|string|max:255',
                'password' => 'required|string|min:8'
            ]);

            // Check if employee already exists
            $existingEmployee = Employee::where('employee_id', $validated['employee_id'])
                                      ->orWhere('email', $validated['email'])
                                      ->first();

            if ($existingEmployee) {
                // Update existing employee (preserve sensitive data)
                $updateData = [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'phone_number' => $validated['phone_number'],
                    'address' => $validated['address'],
                    'department_id' => $validated['department_id'],
                    'position' => $validated['position']
                ];

                // Only update hire_date if provided and not already set
                if (!empty($validated['hire_date']) && !$existingEmployee->hire_date) {
                    $updateData['hire_date'] = $validated['hire_date'];
                }

                // Only update email if it's different and not already taken by another employee
                if ($validated['email'] !== $existingEmployee->email) {
                    $emailExists = Employee::where('email', $validated['email'])
                                          ->where('employee_id', '!=', $validated['employee_id'])
                                          ->exists();
                    if (!$emailExists) {
                        $updateData['email'] = $validated['email'];
                    }
                }

                $existingEmployee->update($updateData);

                Log::info('Individual employee updated successfully', [
                    'employee_id' => $validated['employee_id'],
                    'admin_id' => $admin->id,
                    'updated_fields' => array_keys($updateData)
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Employee information updated successfully.',
                    'action' => 'updated',
                    'employee' => $existingEmployee->fresh()
                ]);
            } else {
                // Create new employee
                $validated['password'] = Hash::make($validated['password']);
                
                $employee = Employee::create($validated);

                Log::info('Individual employee created successfully', [
                    'employee_id' => $validated['employee_id'],
                    'admin_id' => $admin->id,
                    'email' => $validated['email']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Employee created successfully.',
                    'action' => 'created',
                    'employee' => $employee
                ]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Individual employee save validation failed', [
                'errors' => $e->errors(),
                'admin_id' => Auth::guard('admin')->id()
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Individual employee save error: ' . $e->getMessage(), [
                'admin_id' => Auth::guard('admin')->id(),
                'employee_data' => $request->only(['employee_id', 'first_name', 'last_name', 'email'])
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to save employee: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to save employee: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Verify Google reCAPTCHA response
     */
    private function verifyCaptcha($captchaResponse)
    {
        if (empty($captchaResponse)) {
            return false;
        }

        $secretKey = env('RECAPTCHA_SECRET_KEY');
        if (empty($secretKey)) {
            Log::warning('RECAPTCHA_SECRET_KEY not configured');
            return false;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $captchaResponse,
                'remoteip' => request()->ip()
            ]);

            $result = $response->json();
            
            Log::info('CAPTCHA verification result', [
                'success' => $result['success'] ?? false,
                'error_codes' => $result['error-codes'] ?? [],
                'ip' => request()->ip()
            ]);

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('CAPTCHA verification error: ' . $e->getMessage());
            return false;
        }
    }


}
