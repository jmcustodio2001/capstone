<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    // List all employees with all columns
    public function index()
    {
        $employees = Employee::all();
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
    }

    public function store(Request $request)
    {
        // Verify admin password first
        $adminPasswordValidation = $request->validate([
            'admin_password' => 'required|string'
        ]);

        // Check if the provided password matches the authenticated admin's password
        $admin = Auth::guard('admin')->user(); // Get the authenticated admin user
        if (!$admin) {
            return back()->withErrors(['admin_password' => 'You must be logged in as an admin to perform this action.'])->withInput();
        }

        if (!Hash::check($adminPasswordValidation['admin_password'], $admin->password)) {
            return back()->withErrors(['admin_password' => 'The password you entered is incorrect. Please try again.'])->withInput();
        }

        $data = $request->validate([
            'employee_id' => 'required|string|unique:employees,employee_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'password' => 'nullable|string|min:6',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'department_id' => 'nullable|integer',
            'position' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        // Hash the provided password or use default password
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            $data['password'] = Hash::make('password123');
        }

        Employee::create($data);

        return redirect()->route('employee.list')->with('success', 'Employee created successfully!');
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
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'department_id' => 'nullable|integer',
            'position' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive',
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

    /**
     * Show the employee login form
     */
    public function showLoginForm()
    {
        return view('employee_ess_modules.employee_login');
    }

    /**
     * Handle employee login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('employee')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/employee/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Handle employee logout
     */
    public function logout(Request $request)
    {
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employee.login');
    }

    /**
     * Show employee dashboard
     */
    public function dashboard()
    {
        $employee = Auth::guard('employee')->user();
        $employeeId = $employee->employee_id;

        // Use EmployeeDashboardController logic for real data
        $dashboardController = new \App\Http\Controllers\EmployeeDashboardController();
        return $dashboardController->index();
    }

    /**
     * Show employee settings page
     */
    public function settings()
    {
        $employee = Auth::guard('employee')->user();
        return view('employee_ess_modules.setting_employee', compact('employee'));
    }

    /**
     * Update employee settings
     */
    public function updateSettings(Request $request)
    {
        $employee = Auth::guard('employee')->user();

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->employee_id . ',employee_id',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'department_id' => 'nullable|integer',
            'status' => 'required|in:Active,Inactive,On Leave',
            'password' => 'nullable|string|min:6',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Handle password update
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // Don't update password if not provided
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($employee->profile_picture) {
                Storage::disk('public')->delete($employee->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $employee->update($data);

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Update employee profile (for self-service)
     */
    public function updateProfile(Request $request)
    {
        $employee = Auth::guard('employee')->user();

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->employee_id . ',employee_id',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
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

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'profile_picture_url' => $employee->profile_picture ? asset('storage/' . $employee->profile_picture) : null
        ]);
    }

    /**
     * Handle employee response to competency training assignment (accept/decline)
     */
    public function respondToCompetencyTraining(Request $request)
    {
        try {
            $request->validate([
                'upcoming_id' => 'required|integer',
                'action' => 'required|in:accept,decline'
            ]);

            $employee = Auth::guard('employee')->user();
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to respond to training assignments.'
                ], 401);
            }

            // Find the upcoming training record
            $upcomingTraining = \App\Models\UpcomingTraining::where('upcoming_id', $request->upcoming_id)
                ->where('employee_id', $employee->employee_id)
                ->first();

            if (!$upcomingTraining) {
                return response()->json([
                    'success' => false,
                    'message' => 'Training assignment not found.'
                ], 404);
            }

            // Update the training status based on action
            $newStatus = $request->action === 'accept' ? 'Accepted' : 'Declined';
            $upcomingTraining->update([
                'status' => $newStatus,
                'needs_response' => false,
                'updated_at' => now()
            ]);

            // Log the activity
            \App\Models\ActivityLog::create([
                'user_id' => $employee->employee_id,
                'module' => 'Employee Self Service',
                'action' => 'competency_training_response',
                'description' => "Employee {$employee->first_name} {$employee->last_name} {$request->action}ed competency training: {$upcomingTraining->training_title}",
                'model_type' => \App\Models\UpcomingTraining::class,
                'model_id' => $upcomingTraining->upcoming_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Training {$request->action}ed successfully!"
            ]);

        } catch (\Exception $e) {
            Log::error('Error responding to competency training: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing your response: ' . $e->getMessage()
            ], 500);
        }
    }
}
