<?php

namespace App\Services;

use App\Models\AdminNotification;
use Illuminate\Support\Facades\Log;

class AdminNotificationService
{
    /**
     * Create a new admin notification
     */
    public static function createNotification($type, $title, $message, $actionUrl = null)
    {
        try {
            // Check if this notification already exists (to avoid duplicates)
            $exists = AdminNotification::where('type', $type)
                ->where('title', $title)
                ->where('message', $message)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->exists();

            if (!$exists) {
                AdminNotification::create([
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'action_url' => $actionUrl,
                    'is_read' => false
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Create new employee registration notification
     */
    public static function notifyNewEmployeeRegistration($employee)
    {
        $title = 'New Employee Registration';
        $message = $employee->first_name . ' ' . $employee->last_name . ' has been registered';

        self::createNotification('employee_registration', $title, $message, '/admin/employees');
    }

    /**
     * Create training request notification
     */
    public static function notifyTrainingRequest($trainingRequest)
    {
        $employeeName = 'Employee';
        if ($trainingRequest->employee) {
            $employeeName = $trainingRequest->employee->first_name . ' ' . $trainingRequest->employee->last_name;
        }

        $title = 'New Training Request';
        $message = $employeeName . ' requested training: ' . $trainingRequest->training_title;

        self::createNotification('training_request', $title, $message, '/admin/employee-trainings-dashboard');
    }

    /**
     * Create training completion notification
     */
    public static function notifyTrainingCompletion($completion)
    {
        $employeeName = $completion->employee ? $completion->employee->first_name . ' ' . $completion->employee->last_name : 'Employee';
        $courseName = $completion->course ? $completion->course->course_title : $completion->training_title;

        $title = 'Training Completed';
        $message = $employeeName . ' completed ' . $courseName;

        self::createNotification('training_completion', $title, $message, '/admin/employee-trainings-dashboard');
    }

    /**
     * Mark all notifications as read
     */
    public static function markAllAsRead()
    {
        AdminNotification::where('is_read', false)->update(['is_read' => true]);
    }

    /**
     * Get unread notification count
     */
    public static function getUnreadCount()
    {
        return AdminNotification::where('is_read', false)->count();
    }
}
