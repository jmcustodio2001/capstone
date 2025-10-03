<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'payslip_id',
        'pay_period',
        'period_start',
        'period_end',
        'basic_pay',
        'overtime_pay',
        'allowances',
        'gross_pay',
        'tax_deduction',
        'sss_deduction',
        'philhealth_deduction',
        'pagibig_deduction',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'release_date',
        'payslip_file',
        'status'
    ];

    protected $casts = [
        'basic_pay' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'allowances' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'sss_deduction' => 'decimal:2',
        'philhealth_deduction' => 'decimal:2',
        'pagibig_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'release_date' => 'date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    // Generate payslip ID automatically
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($payslip) {
            if (empty($payslip->payslip_id)) {
                $payslip->payslip_id = 'PS' . date('Y') . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Calculate gross pay automatically
    public function getGrossPayAttribute($value)
    {
        if ($value) {
            return $value;
        }
        return ($this->basic_pay ?? 0) + ($this->overtime_pay ?? 0) + ($this->allowances ?? 0);
    }

    // Calculate total deductions automatically
    public function getTotalDeductionsAttribute($value)
    {
        if ($value) {
            return $value;
        }
        return ($this->tax_deduction ?? 0) + ($this->sss_deduction ?? 0) + 
               ($this->philhealth_deduction ?? 0) + ($this->pagibig_deduction ?? 0) + 
               ($this->other_deductions ?? 0);
    }
}
