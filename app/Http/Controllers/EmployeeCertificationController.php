<?php

namespace App\Http\Controllers;

use App\Models\EmployeeCertification;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\ActivityLog;

class EmployeeCertificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $certifications = EmployeeCertification::with('employee')->orderBy('created_at', 'desc')->get();
        $employees = Employee::all();
        
        return view('employee_certifications.index', compact('certifications', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'certificate_name' => 'required|string|max:255',
            'issuing_organization' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB max
        ]);

        // Handle file upload
        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $filename = time() . '_' . $validated['employee_id'] . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('certificates', $filename, 'public');
            $validated['certificate_file'] = $filename;
        }

        $certification = EmployeeCertification::create($validated);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'module' => 'Employee Certifications',
            'action' => 'create',
            'description' => 'Added certificate: ' . $validated['certificate_name'] . ' for employee ID: ' . $validated['employee_id'],
            'model_type' => EmployeeCertification::class,
            'model_id' => $certification->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Certificate added successfully!',
                'certification' => $certification->load('employee')
            ]);
        }

        return redirect()->back()->with('success', 'Certificate added successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeCertification $certification)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'certificate_name' => 'required|string|max:255',
            'issuing_organization' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        // Handle file upload
        if ($request->hasFile('certificate_file')) {
            // Delete old file if exists
            if ($certification->certificate_file) {
                Storage::disk('public')->delete('certificates/' . $certification->certificate_file);
            }

            $file = $request->file('certificate_file');
            $filename = time() . '_' . $validated['employee_id'] . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('certificates', $filename, 'public');
            $validated['certificate_file'] = $filename;
        }

        $certification->update($validated);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'module' => 'Employee Certifications',
            'action' => 'update',
            'description' => 'Updated certificate: ' . $validated['certificate_name'] . ' for employee ID: ' . $validated['employee_id'],
            'model_type' => EmployeeCertification::class,
            'model_id' => $certification->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Certificate updated successfully!',
                'certification' => $certification->load('employee')
            ]);
        }

        return redirect()->back()->with('success', 'Certificate updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeCertification $certification)
    {
        $certificateName = $certification->certificate_name;
        $employeeId = $certification->employee_id;

        // Delete file if exists
        if ($certification->certificate_file) {
            Storage::disk('public')->delete('certificates/' . $certification->certificate_file);
        }

        $certification->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'module' => 'Employee Certifications',
            'action' => 'delete',
            'description' => 'Deleted certificate: ' . $certificateName . ' for employee ID: ' . $employeeId,
            'model_type' => EmployeeCertification::class,
            'model_id' => $certification->id,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Certificate deleted successfully!'
            ]);
        }

        return redirect()->back()->with('success', 'Certificate deleted successfully!');
    }

    /**
     * Get certificates for a specific employee
     */
    public function getEmployeeCertificates($employeeId)
    {
        try {
            $employee = Employee::where('employee_id', $employeeId)->first();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.'
                ], 404);
            }

            $certifications = EmployeeCertification::where('employee_id', $employeeId)
                ->orderBy('issue_date', 'desc')
                ->get();

            // Add status to each certification
            $certifications->each(function ($cert) {
                if ($cert->expiry_date) {
                    $cert->status = now()->gt($cert->expiry_date) ? 'Expired' : 'Active';
                } else {
                    $cert->status = 'No Expiry';
                }
            });

            return response()->json([
                'success' => true,
                'certificates' => $certifications,
                'employee' => $employee
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving certificates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify admin password for certification operations
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $admin = Auth::guard('admin')->user();
        
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password'
            ], 401);
        }

        // Store verification in session for a short time
        session(['admin_password_verified' => true, 'admin_password_verified_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Password verified successfully'
        ]);
    }
}
