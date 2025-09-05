<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\User;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'description',
        'model_type',
        'model_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the proper user_id for ActivityLog creation
     * Handles both admin users and employee authentication
     */
    public static function getUserId()
    {
        // Check if admin is authenticated
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->id();
        }
        
        // Check if regular user is authenticated
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->id();
        }
        
        // Check if employee is authenticated
        if (Auth::guard('employee')->check()) {
            $employeeId = Auth::guard('employee')->id();
            
            // Try to find corresponding User record with same email
            $employee = Employee::where('employee_id', $employeeId)->first();
            if ($employee) {
                $user = User::where('email', $employee->email)->first();
                if ($user) {
                    return $user->id;
                }
            }
            
            // If no corresponding User found, return system user ID (1)
            return 1;
        }
        
        // Default to system user if no authentication
        return 1;
    }

    /**
     * Create ActivityLog with proper user_id handling
     */
    public static function createLog($data)
    {
        $data['user_id'] = self::getUserId();
        return self::create($data);
    }
}
