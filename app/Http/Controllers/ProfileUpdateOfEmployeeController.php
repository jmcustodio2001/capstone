<?php

namespace App\Http\Controllers;

use App\Models\ProfileUpdate;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ProfileUpdateOfEmployeeController extends Controller
{
    public function index()
    {
        $updates = ProfileUpdate::with('employee')->latest()->paginate(10);
        return view('Employee_Self_Service.profile_update_of_employee', compact('updates'));
    }

    public function create()
    {
        return view('Employee_Self_Service.profile_update_of_employee');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required',
            'field_name' => 'required',
            'old_value' => 'required',
            'new_value' => 'required',
            'status' => 'required',
        ]);
        $update = ProfileUpdate::create($data);
        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'module' => 'Profile Update Of Employee',
            'description' => 'Created profile update request (ID: ' . $update->id . ')',
        ]);
        return redirect()->route('profile_update_of_employees.index')->with('success', 'Profile update request submitted successfully.');
    }

    public function show($id)
    {
        $update = ProfileUpdate::findOrFail($id);
        return view('Employee_Self_Service.profile_update_of_employee', compact('update'));
    }

    public function edit($id)
    {
        $update = ProfileUpdate::findOrFail($id);
        return view('Employee_Self_Service.profile_update_of_employee', compact('update'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'employee_id' => 'required',
            'field_name' => 'required',
            'old_value' => 'required',
            'new_value' => 'required',
            'status' => 'required',
        ]);
        $update = ProfileUpdate::findOrFail($id);
        $update->update($data);
        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'module' => 'Profile Update Of Employee',
            'description' => 'Updated profile update request (ID: ' . $update->id . ')',
        ]);
        return redirect()->route('profile_update_of_employees.index')->with('success', 'Profile update request updated successfully.');
    }

    public function destroy($id)
    {
        $update = ProfileUpdate::findOrFail($id);
        $update->delete();
        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'module' => 'Profile Update Of Employee',
            'description' => 'Deleted profile update request (ID: ' . $update->id . ')',
        ]);
        return redirect()->route('profile_update_of_employees.index')->with('success', 'Profile update request deleted successfully.');
    }

    public function approve($id)
    {
        $update = ProfileUpdate::findOrFail($id);

        if ($update->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been processed.'
            ], 400);
        }

        // Update the profile update status
        $update->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id()
        ]);

        // Field mapping to ensure correct column names in employees table
        $fieldMapping = [
            'phone' => 'phone_number',
            'phone_number' => 'phone_number',
            'emergency_contact_name' => 'emergency_contact_name',
            'emergency_contact_phone' => 'emergency_contact_phone',
            'emergency_contact_relationship' => 'emergency_contact_relationship',
            'profile_picture' => 'profile_picture',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'email' => 'email',
            'address' => 'address',
        ];

        $actualFieldName = $fieldMapping[$update->field_name] ?? $update->field_name;

        // Apply the change to the employee record
        if ($update->employee) {
            $update->employee->update([
                $actualFieldName => $update->new_value
            ]);

            // Log the update to employee record
            \Log::info('Profile update applied to employee record', [
                'employee_id' => $update->employee_id,
                'field' => $actualFieldName,
                'value' => $update->new_value
            ]);
        } else {
            \Log::warning('Employee not found for profile update approval', [
                'profile_update_id' => $update->id,
                'employee_id' => $update->employee_id
            ]);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'approve',
            'module' => 'Profile Update Of Employee',
            'description' => 'Approved profile update request (ID: ' . $update->id . ') for employee ' . ($update->employee ? $update->employee->first_name . ' ' . $update->employee->last_name : ($update->employee_name ?? 'Unknown')),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile update request approved successfully.'
        ]);
    }

    public function reject($id)
    {
        $update = ProfileUpdate::findOrFail($id);

        if ($update->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been processed.'
            ], 400);
        }

        $rejectionReason = request('rejection_reason', 'No reason provided');

        // Update the profile update status
        $update->update([
            'status' => 'rejected',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'rejection_reason' => $rejectionReason
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'reject',
            'module' => 'Profile Update Of Employee',
            'description' => 'Rejected profile update request (ID: ' . $update->id . ') for employee ' . ($update->employee ? $update->employee->first_name . ' ' . $update->employee->last_name : ($update->employee_name ?? 'Unknown')) . '. Reason: ' . $rejectionReason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile update request rejected successfully.'
        ]);
    }

    public function fixOldValues()
    {
        try {
            // Get all profile updates with N/A or empty old_value
            $updates = ProfileUpdate::whereIn('old_value', ['N/A', '', null])
                ->with('employee')
                ->get();

            $fixed = 0;
            $errors = 0;

            foreach ($updates as $update) {
                try {
                    if (!$update->employee) {
                        $errors++;
                        continue;
                    }

                    // Field mapping for proper database column names
                    $fieldMapping = [
                        'phone' => 'phone_number',
                        'phone_number' => 'phone_number',
                        'emergency_contact_name' => 'emergency_contact_name',
                        'emergency_contact_phone' => 'emergency_contact_phone',
                        'emergency_contact_relationship' => 'emergency_contact_relationship',
                    ];

                    $actualFieldName = $fieldMapping[$update->field_name] ?? $update->field_name;
                    $currentValue = $update->employee->{$actualFieldName};

                    // Handle special cases for better display
                    if ($currentValue === null || $currentValue === '' || $currentValue === 'N/A') {
                        $currentValue = 'Not set';
                    }

                    // Update the old_value
                    $update->update(['old_value' => $currentValue]);
                    $fixed++;

                } catch (\Exception $e) {
                    $errors++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Fixed {$fixed} profile update records. {$errors} errors encountered.",
                'fixed' => $fixed,
                'errors' => $errors,
                'total' => $updates->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fixing old values: ' . $e->getMessage()
            ], 500);
        }
    }
}
