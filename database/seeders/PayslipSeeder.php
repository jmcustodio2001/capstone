<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payslip;
use App\Models\Employee;
use Carbon\Carbon;

class PayslipSeeder extends Seeder
{
    public function run()
    {
        // Get some employees to create payslips for
        $employees = Employee::limit(5)->get();
        
        if ($employees->isEmpty()) {
            $this->command->info('No employees found. Please seed employees first.');
            return;
        }
        
        // Clear existing payslips
        Payslip::truncate();
        
        $payPeriods = [
            ['period' => 'January 2024', 'start' => '2024-01-01', 'end' => '2024-01-31'],
            ['period' => 'February 2024', 'start' => '2024-02-01', 'end' => '2024-02-29'],
            ['period' => 'March 2024', 'start' => '2024-03-01', 'end' => '2024-03-31'],
            ['period' => 'April 2024', 'start' => '2024-04-01', 'end' => '2024-04-30'],
            ['period' => 'May 2024', 'start' => '2024-05-01', 'end' => '2024-05-31'],
            ['period' => 'June 2024', 'start' => '2024-06-01', 'end' => '2024-06-30'],
            ['period' => 'July 2024', 'start' => '2024-07-01', 'end' => '2024-07-31'],
            ['period' => 'August 2024', 'start' => '2024-08-01', 'end' => '2024-08-31'],
            ['period' => 'September 2024', 'start' => '2024-09-01', 'end' => '2024-09-30']
        ];
        
        foreach ($employees as $employee) {
            foreach ($payPeriods as $index => $periodData) {
                $basicPay = rand(15000, 25000);
                $overtimePay = rand(500, 2000);
                $allowances = rand(2000, 5000);
                $grossPay = $basicPay + $overtimePay + $allowances;
                
                // Calculate deductions
                $taxDeduction = $grossPay * 0.15; // 15% tax
                $sssDeduction = $grossPay * 0.05; // 5% SSS
                $philhealthDeduction = $grossPay * 0.03; // 3% PhilHealth
                $pagibigDeduction = $grossPay * 0.02; // 2% Pag-IBIG
                $otherDeductions = rand(0, 500);
                $totalDeductions = $taxDeduction + $sssDeduction + $philhealthDeduction + $pagibigDeduction + $otherDeductions;
                
                $netPay = $grossPay - $totalDeductions;
                
                Payslip::create([
                    'payslip_id' => 'PS' . str_pad(($index + 1) + ($employees->search($employee) * 9), 3, '0', STR_PAD_LEFT),
                    'employee_id' => $employee->employee_id,
                    'pay_period' => $periodData['period'],
                    'period_start' => Carbon::parse($periodData['start']),
                    'period_end' => Carbon::parse($periodData['end']),
                    'basic_pay' => $basicPay,
                    'overtime_pay' => $overtimePay,
                    'allowances' => $allowances,
                    'gross_pay' => $grossPay,
                    'tax_deduction' => $taxDeduction,
                    'sss_deduction' => $sssDeduction,
                    'philhealth_deduction' => $philhealthDeduction,
                    'pagibig_deduction' => $pagibigDeduction,
                    'other_deductions' => $otherDeductions,
                    'total_deductions' => $totalDeductions,
                    'net_pay' => $netPay,
                    'release_date' => Carbon::parse($periodData['end'])->addDays(5),
                    'status' => 'Released'
                ]);
            }
        }
        
        $this->command->info('Sample payslip data created successfully!');
        $this->command->info('Total records: ' . Payslip::count());
    }
}
