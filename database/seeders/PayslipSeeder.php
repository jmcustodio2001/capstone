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
            'January 2024',
            'February 2024', 
            'March 2024',
            'April 2024',
            'May 2024',
            'June 2024',
            'July 2024',
            'August 2024',
            'September 2024'
        ];
        
        foreach ($employees as $employee) {
            foreach ($payPeriods as $index => $period) {
                $basicPay = rand(15000, 25000);
                $allowances = rand(2000, 5000);
                $deductions = rand(1500, 3500);
                $netPay = $basicPay + $allowances - $deductions;
                
                Payslip::create([
                    'employee_id' => $employee->employee_id,
                    'pay_period' => $period,
                    'basic_pay' => $basicPay,
                    'allowances' => $allowances,
                    'deductions' => $deductions,
                    'net_pay' => $netPay,
                    'release_date' => Carbon::create(2024, $index + 1, 15),
                    'status' => 'Released'
                ]);
            }
        }
        
        $this->command->info('Sample payslip data created successfully!');
        $this->command->info('Total records: ' . Payslip::count());
    }
}
