<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('claim_reimbursements')) {
            Schema::create('claim_reimbursements', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20)->index(); // Match employees table structure
            $table->string('claim_id', 20)->unique();
            $table->enum('claim_type', [
                'Travel Expense',
                'Meal Allowance', 
                'Transportation',
                'Accommodation',
                'Medical Expense',
                'Office Supplies',
                'Training Materials',
                'Communication Expense',
                'Other'
            ]);
            $table->text('description');
            $table->decimal('amount', 10, 2);
            $table->date('claim_date');
            $table->string('receipt_file')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Processed'])->default('Pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->datetime('approved_date')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->datetime('processed_date')->nullable();
            $table->enum('payment_method', ['Bank Transfer', 'Cash', 'Check', 'Payroll Deduction'])->nullable();
            $table->string('reference_number')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Note: Foreign key constraints will be added after employees table is created

            // Indexes for better performance
            $table->index(['employee_id', 'status']);
            $table->index(['claim_date', 'status']);
            $table->index('claim_type');
            $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_reimbursements');
    }
};
