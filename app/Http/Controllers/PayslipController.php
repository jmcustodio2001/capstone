<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{
    public function index()
    {
        $employee = null;
        $payslips = collect(); // Empty collection by default
        
        if (Auth::check()) {
            $user = Auth::user();
            $employee = $user->employee;
            
            if ($employee) {
                // Filter payslips by logged-in employee only
                $payslips = Payslip::where('employee_id', $employee->employee_id)
                    ->orderByDesc('release_date')
                    ->paginate(20);
            }
        }
        
        return view('employee_ess_modules.payslips.payslip_access', compact('payslips', 'employee'));
    }

    public function show($id)
    {
        $payslip = Payslip::with('employee')->findOrFail($id);
        return view('employee_ess_modules.payslips.show', compact('payslip'));
    }

    public function create()
    {
        $employees = Employee::all();
        return view('employee_ess_modules.payslips.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'pay_period' => 'required|string',
            'basic_pay' => 'required|numeric',
            'allowances' => 'nullable|numeric',
            'deductions' => 'nullable|numeric',
            'net_pay' => 'required|numeric',
            'release_date' => 'required|date',
            'status' => 'nullable|string',
        ]);
        Payslip::create($request->all());
        return redirect()->route('payslips.index')->with('success', 'Payslip created.');
    }

    public function edit($id)
    {
        $payslip = Payslip::findOrFail($id);
        $employees = Employee::all();
        return view('employee_ess_modules.payslips.edit', compact('payslip', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'pay_period' => 'required|string',
            'basic_pay' => 'required|numeric',
            'allowances' => 'nullable|numeric',
            'deductions' => 'nullable|numeric',
            'net_pay' => 'required|numeric',
            'release_date' => 'required|date',
            'status' => 'nullable|string',
        ]);
        $payslip = Payslip::findOrFail($id);
        $payslip->update($request->all());
        return redirect()->route('payslips.index')->with('success', 'Payslip updated.');
    }

    public function destroy($id)
    {
        $payslip = Payslip::findOrFail($id);
        $payslip->delete();
        return redirect()->route('payslips.index')->with('success', 'Payslip deleted.');
    }

    public function downloadAll()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Get all payslips for the logged-in employee
        $payslips = Payslip::where('employee_id', $employee->employee_id)
            ->orderByDesc('release_date')
            ->get();

        if ($payslips->isEmpty()) {
            return response()->json(['error' => 'No payslips found'], 404);
        }

        // In a real application, you would generate a ZIP file with all payslips
        // For now, we'll return a JSON response with payslip data
        $payslipData = $payslips->map(function ($payslip) {
            return [
                'id' => $payslip->id,
                'pay_period' => $payslip->pay_period,
                'basic_pay' => $payslip->basic_pay,
                'allowances' => $payslip->allowances,
                'deductions' => $payslip->deductions,
                'net_pay' => $payslip->net_pay,
                'release_date' => date('Y-m-d', strtotime($payslip->release_date)),
                'status' => $payslip->status
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Payslips data prepared for download',
            'payslips' => $payslipData,
            'total_count' => $payslips->count()
        ]);
    }

    public function download($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Find payslip and ensure it belongs to the logged-in employee
        $payslip = Payslip::where('id', $id)
            ->where('employee_id', $employee->employee_id)
            ->first();

        if (!$payslip) {
            return response()->json(['error' => 'Payslip not found'], 404);
        }

        // In a real application, you would generate a PDF here
        return response()->json([
            'success' => true,
            'message' => 'Payslip download prepared',
            'payslip' => [
                'id' => $payslip->id,
                'pay_period' => $payslip->pay_period,
                'basic_pay' => $payslip->basic_pay,
                'allowances' => $payslip->allowances,
                'deductions' => $payslip->deductions,
                'net_pay' => $payslip->net_pay,
                'release_date' => date('Y-m-d', strtotime($payslip->release_date)),
                'status' => $payslip->status
            ]
        ]);
    }
}
