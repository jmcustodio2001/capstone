<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceTimeLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'log_date',
        'time_in',
        'time_out',
        'break_start_time',
        'break_end_time',
        'total_hours',
        'overtime_hours',
        'hours_worked',
        'status',
        'location',
        'ip_address',
        'notes',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'log_date' => 'date',
        'hours_worked' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Get dynamic table columns configuration
     */
    public static function getTableColumns()
    {
        return [
            'employee_id' => [
                'label' => 'Employee ID',
                'type' => 'text',
                'sortable' => true,
                'searchable' => true
            ],
            'log_date' => [
                'label' => 'Date',
                'type' => 'date',
                'sortable' => true,
                'searchable' => true,
                'format' => 'M d, Y'
            ],
            'time_in' => [
                'label' => 'Clock In Time',
                'type' => 'time',
                'sortable' => true,
                'searchable' => false,
                'format' => 'g:i A'
            ],
            'time_out' => [
                'label' => 'Clock Out Time',
                'type' => 'time',
                'sortable' => true,
                'searchable' => false,
                'format' => 'g:i A'
            ],
            'break_start_time' => [
                'label' => 'Break Start',
                'type' => 'time',
                'sortable' => true,
                'searchable' => false,
                'format' => 'g:i A'
            ],
            'break_end_time' => [
                'label' => 'Break End',
                'type' => 'time',
                'sortable' => true,
                'searchable' => false,
                'format' => 'g:i A'
            ],
            'total_hours' => [
                'label' => 'Total Hours',
                'type' => 'decimal',
                'sortable' => true,
                'searchable' => false,
                'format' => 'hours'
            ],
            'overtime_hours' => [
                'label' => 'Overtime Hours',
                'type' => 'decimal',
                'sortable' => true,
                'searchable' => false,
                'format' => 'hours'
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'badge',
                'sortable' => true,
                'searchable' => true,
                'options' => ['Present', 'Absent', 'Late', 'Early Departure', 'Overtime']
            ],
            'location' => [
                'label' => 'Location',
                'type' => 'text',
                'sortable' => true,
                'searchable' => true
            ],
            'ip_address' => [
                'label' => 'IP Address',
                'type' => 'text',
                'sortable' => false,
                'searchable' => true
            ],
            'notes' => [
                'label' => 'Notes',
                'type' => 'text',
                'sortable' => false,
                'searchable' => true
            ],
            'created_at' => [
                'label' => 'Created At',
                'type' => 'date',
                'sortable' => true,
                'searchable' => false,
                'format' => 'M d, Y'
            ],
            'updated_at' => [
                'label' => 'Updated At',
                'type' => 'date',
                'sortable' => true,
                'searchable' => false,
                'format' => 'M d, Y'
            ]
        ];
    }

    /**
     * Get visible columns for the table (can be customized)
     */
    public static function getVisibleColumns()
    {
        return [
            'employee_id',
            'log_date', 
            'time_in',
            'time_out',
            'break_start_time',
            'break_end_time',
            'total_hours',
            'overtime_hours',
            'status',
            'location',
            'ip_address',
            'notes',
            'created_at',
            'updated_at'
        ];
    }

    /**
     * Format a field value based on its type
     */
    public function getFormattedValue($field)
    {
        $columns = self::getTableColumns();
        $config = $columns[$field] ?? null;
        
        if (!$config) {
            return $this->$field;
        }

        $value = $this->$field;
        
        if (is_null($value)) {
            return '--';
        }

        switch ($config['type']) {
            case 'date':
                try {
                    return Carbon::parse($value)->format($config['format'] ?? 'Y-m-d');
                } catch (\Exception $e) {
                    return $value;
                }
                
            case 'time':
                try {
                    return Carbon::parse($value)->format($config['format'] ?? 'H:i');
                } catch (\Exception $e) {
                    return $value;
                }
                
            case 'datetime':
                try {
                    return Carbon::parse($value)->format($config['format'] ?? 'Y-m-d H:i:s');
                } catch (\Exception $e) {
                    return $value;
                }
                
            case 'decimal':
                if ($config['format'] === 'hours') {
                    $hours = floor($value);
                    $minutes = round(($value - $hours) * 60);
                    return "{$hours}h {$minutes}m";
                }
                return number_format($value, 2);
                
            case 'badge':
                return $value;
                
            default:
                return $value;
        }
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        $status = strtolower(str_replace(' ', '-', $this->status ?? ''));
        return "badge badge-simulation status-{$status}";
    }
}
