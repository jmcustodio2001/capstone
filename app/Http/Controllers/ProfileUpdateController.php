<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\ProfileUpdate;
use App\Models\Employee;

class ProfileUpdateController extends Controller
{
    /**
     * Display a listing of profile updates for the authenticated employee.
     */
    public function index()
    {
        $employee = Auth::guard('employee')->user();

        if (!$employee) {
            return redirect()->route('employee.login')->with('error', 'Please login to access this page.');
        }

        // Debug: Convert employee to array to check structure
        $employeeArray = $employee->toArray();

        // Get profile updates for the current employee
        $updates = ProfileUpdate::where('employee_id', $employee->employee_id)
            ->with('employee')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('employee_ess_modules.profile_updates.index', compact('updates', 'employee', 'employeeArray'));
    }

    /**
     * Show the form for creating a new profile update.
     */
    public function create()
    {
        return view('employee_ess_modules.profile_updates.create');
    }

    /**
     * Store a newly created profile update in storage.
     */
    public function store(Request $request)
    {
        $validationRules = [
            'field_name' => 'required|string|max:255',
            'reason' => 'nullable|string|max:1000'
        ];

        // Add validation rules based on field type
        if ($request->field_name === 'profile_picture') {
            $validationRules['new_value_file'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048';
        } else {
            $validationRules['new_value'] = 'required|string|max:500';
        }

        $request->validate($validationRules);

        $employee = Auth::guard('employee')->user();

        // Get the current value from employee record with proper field mapping
        $fieldMapping = [
            'phone' => 'phone_number',
            'phone_number' => 'phone_number',
            'emergency_contact_name' => 'emergency_contact_name',
            'emergency_contact_phone' => 'emergency_contact_phone',
            'emergency_contact_relationship' => 'emergency_contact_relationship',
        ];

        $actualFieldName = $fieldMapping[$request->field_name] ?? $request->field_name;

        // Debug: Log the field mapping
        Log::info('Profile Update Field Mapping', [
            'requested_field' => $request->field_name,
            'actual_field' => $actualFieldName,
            'employee_data' => $employee->toArray()
        ]);

        $oldValue = $employee->{$actualFieldName};

        // Handle special cases for better display
        if ($oldValue === null || $oldValue === '' || $oldValue === 'N/A') {
            $oldValue = 'Not set';
        }

        $newValue = '';

        // Handle file upload for profile picture
        if ($request->field_name === 'profile_picture' && $request->hasFile('new_value_file')) {
            $file = $request->file('new_value_file');
            $filename = time() . '_' . $employee->employee_id . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_pictures', $filename, 'public');
            $newValue = $path;
        } else {
            $newValue = $request->new_value;
        }

        ProfileUpdate::create([
            'employee_id' => $employee->employee_id,
            'field_name' => $request->field_name,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason' => $request->reason,
            'status' => 'pending',
            'requested_at' => now()
        ]);

        return redirect()->route('employee.profile_updates.index')
            ->with('success', 'Profile update request submitted successfully!');
    }

    /**
     * Display the specified profile update.
     */
    public function show(ProfileUpdate $profileUpdate)
    {
        $employee = Auth::guard('employee')->user();

        // Ensure employee can only view their own updates
        if ($profileUpdate->employee_id !== $employee->employee_id) {
            abort(403, 'Unauthorized access to profile update.');
        }

        return view('employee_ess_modules.profile_updates.show', compact('profileUpdate'));
    }

    /**
     * Show the form for editing the specified profile update.
     */
    public function edit(ProfileUpdate $profileUpdate)
    {
        $employee = Auth::guard('employee')->user();

        // Ensure employee can only edit their own pending updates
        if ($profileUpdate->employee_id !== $employee->employee_id || $profileUpdate->status !== 'pending') {
            abort(403, 'Cannot edit this profile update.');
        }

        return view('employee_ess_modules.profile_updates.edit', compact('profileUpdate'));
    }

    /**
     * Update the specified profile update in storage.
     */
    public function update(Request $request, ProfileUpdate $profileUpdate)
    {
        $employee = Auth::guard('employee')->user();

        // Ensure employee can only update their own pending updates
        if ($profileUpdate->employee_id !== $employee->employee_id || $profileUpdate->status !== 'pending') {
            abort(403, 'Cannot update this profile update.');
        }

        $validationRules = [
            'field_name' => 'required|string|max:255',
            'reason' => 'nullable|string|max:1000'
        ];

        // Add validation rules based on field type
        if ($request->field_name === 'profile_picture') {
            $validationRules['new_value_file'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048';
        } else {
            $validationRules['new_value'] = 'required|string|max:500';
        }

        $request->validate($validationRules);

        // Get the current value from employee record with proper field mapping
        $fieldMapping = [
            'phone' => 'phone_number',
            'phone_number' => 'phone_number',
            'emergency_contact_name' => 'emergency_contact_name',
            'emergency_contact_phone' => 'emergency_contact_phone',
            'emergency_contact_relationship' => 'emergency_contact_relationship',
        ];

        $actualFieldName = $fieldMapping[$request->field_name] ?? $request->field_name;
        $oldValue = $employee->{$actualFieldName};

        // Handle special cases for better display
        if ($oldValue === null || $oldValue === '' || $oldValue === 'N/A') {
            $oldValue = 'Not set';
        }

        $newValue = '';

        // Handle file upload for profile picture
        if ($request->field_name === 'profile_picture' && $request->hasFile('new_value_file')) {
            $file = $request->file('new_value_file');
            $filename = time() . '_' . $employee->employee_id . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_pictures', $filename, 'public');
            $newValue = $path;
        } else {
            $newValue = $request->new_value;
        }

        $profileUpdate->update([
            'field_name' => $request->field_name,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason' => $request->reason
        ]);

        return redirect()->route('employee.profile_updates.index')
            ->with('success', 'Profile update request updated successfully!');
    }

    /**
     * Get profile update details for AJAX requests.
     */
    public function details(ProfileUpdate $profileUpdate)
    {
        $employee = Auth::guard('employee')->user();

        // Ensure employee can only view their own updates
        if ($profileUpdate->employee_id !== $employee->employee_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to profile update.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'update' => [
                'id' => $profileUpdate->id,
                'field_name' => $profileUpdate->field_name,
                'formatted_field_name' => $profileUpdate->formatted_field_name,
                'old_value' => $profileUpdate->old_value,
                'new_value' => $profileUpdate->new_value,
                'reason' => $profileUpdate->reason,
                'status' => $profileUpdate->status,
                'status_badge_class' => $profileUpdate->status_badge_class,
                'requested_at' => $profileUpdate->requested_at,
                'approved_at' => $profileUpdate->approved_at,
                'approved_by' => $profileUpdate->approved_by,
                'rejection_reason' => $profileUpdate->rejection_reason,
                'formatted_date' => $profileUpdate->requested_at ? $profileUpdate->requested_at->format('d/m/Y H:i') : 'N/A',
                'formatted_approved_date' => $profileUpdate->approved_at ? $profileUpdate->approved_at->format('d/m/Y H:i') : null
            ]
        ]);
    }

    /**
     * Remove the specified profile update from storage.
     */
    public function destroy(ProfileUpdate $profileUpdate)
    {
        $employee = Auth::guard('employee')->user();

        // Ensure employee can only delete their own pending updates
        if ($profileUpdate->employee_id !== $employee->employee_id || $profileUpdate->status !== 'pending') {
            abort(403, 'Cannot delete this profile update.');
        }

        $profileUpdate->delete();

        return redirect()->route('employee.profile_updates.index')
            ->with('success', 'Profile update request deleted successfully!');
    }

    /**
     * Verify employee password for profile update submission.
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6'
        ]);

        $employee = Auth::guard('employee')->user();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not authenticated'
            ], 401);
        }

        // Check if the password matches the employee's password
        if (Hash::check($request->password, $employee->password)) {
            return response()->json([
                'success' => true,
                'message' => 'Password verified successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password'
            ], 422);
        }
    }
}
