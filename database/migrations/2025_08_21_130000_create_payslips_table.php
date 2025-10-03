<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('payslip_id')->unique();
            $table->string('employee_id', 20);
            $table->string('pay_period');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('basic_pay', 10, 2);
            $table->decimal('overtime_pay', 10, 2)->nullable();
            $table->decimal('allowances', 10, 2)->nullable();
            $table->decimal('gross_pay', 10, 2)->nullable();
            $table->decimal('tax_deduction', 10, 2)->nullable();
            $table->decimal('sss_deduction', 10, 2)->nullable();
            $table->decimal('philhealth_deduction', 10, 2)->nullable();
            $table->decimal('pagibig_deduction', 10, 2)->nullable();
            $table->decimal('other_deductions', 10, 2)->nullable();
            $table->decimal('total_deductions', 10, 2)->nullable();
            $table->decimal('net_pay', 10, 2);
            $table->date('release_date');
            $table->string('payslip_file')->nullable();
            $table->string('status')->default('Released');
            $table->timestamps();

            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payslips');
    }
};
