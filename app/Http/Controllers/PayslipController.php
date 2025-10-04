<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PayslipController extends Controller
{
    public function index()
    {
        $employee = null;
        $payslips = collect();
        $summaryData = [
            'total_earnings_ytd' => 0,
            'average_net_pay' => 0,
            'taxes_paid_ytd' => 0,
            'last_payslip_amount' => 0,
            'last_payslip_date' => null
        ];
        
        // Check if user is authenticated as employee
        if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            
            if ($employee) {
                // Get payslips for the logged-in employee
                $payslips = Payslip::where('employee_id', $employee->employee_id)
                    ->orderByDesc('period_end')
                    ->get();
                
                // Calculate summary data
                if ($payslips->isNotEmpty()) {
                    $currentYear = date('Y');
                    $currentYearPayslips = $payslips->filter(function($payslip) use ($currentYear) {
                        return $payslip->period_end && date('Y', strtotime($payslip->period_end)) == $currentYear;
                    });
                    
                    $summaryData['total_earnings_ytd'] = $currentYearPayslips->sum('gross_pay') ?? $currentYearPayslips->sum('net_pay') * 1.3;
                    $summaryData['average_net_pay'] = $currentYearPayslips->avg('net_pay') ?? 0;
                    $summaryData['taxes_paid_ytd'] = $currentYearPayslips->sum('tax_deduction') ?? $summaryData['total_earnings_ytd'] * 0.15;
                    
                    $lastPayslip = $payslips->first();
                    $summaryData['last_payslip_amount'] = $lastPayslip->net_pay ?? 0;
                    $summaryData['last_payslip_date'] = $lastPayslip->period_end ?? null;
                }
            }
        } elseif (Auth::check()) {
            // Fallback for regular user authentication
            $user = Auth::user();
            $employee = $user->employee;
            
            if ($employee) {
                $payslips = Payslip::where('employee_id', $employee->employee_id)
                    ->orderByDesc('period_end')
                    ->get();
            }
        }
        
        return view('employee_ess_modules.payslips.payslip_access', compact('payslips', 'employee', 'summaryData'));
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
        $employee = null;
        
        // Check employee authentication first
        if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
        } elseif (Auth::check()) {
            $user = Auth::user();
            $employee = $user->employee;
        }
        
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Get all payslips for the logged-in employee
        $payslips = Payslip::where('employee_id', $employee->employee_id)
            ->orderByDesc('period_end')
            ->get();

        if ($payslips->isEmpty()) {
            return response()->json(['error' => 'No payslips found'], 404);
        }

        try {
            // Create a ZIP file with all payslips
            $zipFileName = 'payslips_' . $employee->employee_id . '_' . date('Y-m-d') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);
            
            // Ensure temp directory exists
            if (!Storage::exists('temp')) {
                Storage::makeDirectory('temp');
            }
            
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                return response()->json(['error' => 'Cannot create ZIP file'], 500);
            }
            
            foreach ($payslips as $payslip) {
                $pdf = $this->generatePayslipPDF($payslip, $employee);
                $pdfContent = $pdf->output();
                $pdfFileName = 'payslip_' . ($payslip->payslip_id ?? 'PS' . $payslip->id) . '.pdf';
                $zip->addFromString($pdfFileName, $pdfContent);
            }
            
            $zip->close();
            
            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate ZIP file: ' . $e->getMessage()], 500);
        }
    }

    public function download($id)
    {
        $employee = null;
        
        // Check employee authentication first
        if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
        } elseif (Auth::check()) {
            $user = Auth::user();
            $employee = $user->employee;
        }
        
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

        try {
            // Generate PDF
            $pdf = $this->generatePayslipPDF($payslip, $employee);
            $filename = 'payslip_' . ($payslip->payslip_id ?? 'PS' . $payslip->id) . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
        }
    }
    
    private function generatePayslipPDF($payslip, $employee)
    {
        $data = [
            'payslip' => $payslip,
            'employee' => $employee,
            'company' => [
                'name' => 'Jetlouge Travels',
                'address' => '123 Business Avenue, City, Country'
            ]
        ];
        
        $html = view('employee_ess_modules.payslips.pdf_template', $data)->render();
        
        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true
            ]);
    }
    
    public function print($id)
    {
        $employee = null;
        
        // Check employee authentication first
        if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
        } elseif (Auth::check()) {
            $user = Auth::user();
            $employee = $user->employee;
        }
        
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

        $data = [
            'payslip' => $payslip,
            'employee' => $employee,
            'company' => [
                'name' => 'Jetlouge Travels',
                'address' => '123 Business Avenue, City, Country'
            ]
        ];
        
        return view('employee_ess_modules.payslips.print_template', $data);
    }
}
