<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Check if payslips table exists before trying to modify it
        if (!Schema::hasTable('payslips')) {
            return; // Skip this migration if table doesn't exist
        }
        
        Schema::table('payslips', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('payslips', 'payslip_id')) {
                $table->string('payslip_id')->unique()->after('id');
            }
            if (!Schema::hasColumn('payslips', 'period_start')) {
                $table->date('period_start')->after('pay_period');
            }
            if (!Schema::hasColumn('payslips', 'period_end')) {
                $table->date('period_end')->after('period_start');
            }
            if (!Schema::hasColumn('payslips', 'overtime_pay')) {
                $table->decimal('overtime_pay', 10, 2)->nullable()->after('basic_pay');
            }
            if (!Schema::hasColumn('payslips', 'gross_pay')) {
                $table->decimal('gross_pay', 10, 2)->nullable()->after('allowances');
            }
            if (!Schema::hasColumn('payslips', 'tax_deduction')) {
                $table->decimal('tax_deduction', 10, 2)->nullable()->after('gross_pay');
            }
            if (!Schema::hasColumn('payslips', 'sss_deduction')) {
                $table->decimal('sss_deduction', 10, 2)->nullable()->after('tax_deduction');
            }
            if (!Schema::hasColumn('payslips', 'philhealth_deduction')) {
                $table->decimal('philhealth_deduction', 10, 2)->nullable()->after('sss_deduction');
            }
            if (!Schema::hasColumn('payslips', 'pagibig_deduction')) {
                $table->decimal('pagibig_deduction', 10, 2)->nullable()->after('philhealth_deduction');
            }
            if (!Schema::hasColumn('payslips', 'other_deductions')) {
                $table->decimal('other_deductions', 10, 2)->nullable()->after('pagibig_deduction');
            }
            if (!Schema::hasColumn('payslips', 'total_deductions')) {
                $table->decimal('total_deductions', 10, 2)->nullable()->after('other_deductions');
            }
            if (!Schema::hasColumn('payslips', 'payslip_file')) {
                $table->string('payslip_file')->nullable()->after('release_date');
            }

            // Drop the old deductions column if it exists
            if (Schema::hasColumn('payslips', 'deductions')) {
                $table->dropColumn('deductions');
            }
        });
    }

    public function down()
    {
        Schema::table('payslips', function (Blueprint $table) {
            // Add back the old deductions column
            $table->decimal('deductions', 10, 2)->nullable();
            
            // Drop the new columns
            $table->dropColumn([
                'payslip_id',
                'period_start',
                'period_end', 
                'overtime_pay',
                'gross_pay',
                'tax_deduction',
                'sss_deduction',
                'philhealth_deduction',
                'pagibig_deduction',
                'other_deductions',
                'total_deductions',
                'payslip_file'
            ]);
        });
    }
};
