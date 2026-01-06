<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminNotification;
use App\Models\Employee;
use App\Models\TrainingRequest;
use App\Models\EmployeeTrainingDashboard;
use Illuminate\Support\Facades\Schema;

class SyncAdminNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-admin-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync recent system events to admin notifications table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing admin notifications...');

        // Clear existing notifications
        AdminNotification::truncate();
        $count = 0;

        // Sync recent employee registrations
        $recentEmployees = Employee::where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentEmployees as $employee) {
            AdminNotification::create([
                'type' => 'employee_registration',
                'title' => 'New Employee Registration',
                'message' => $employee->first_name . ' ' . $employee->last_name . ' has been registered',
                'action_url' => '/admin/employees',
                'is_read' => false,
                'created_at' => $employee->created_at
            ]);
            $count++;
        }

        // Sync recent pending training requests
        $pendingRequests = TrainingRequest::where('status', 'Pending')
            ->where('created_at', '>=', now()->subDays(7))
            ->with('employee')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($pendingRequests as $request) {
            $employeeName = $request->employee
                ? $request->employee->first_name . ' ' . $request->employee->last_name
                : 'Employee';

            AdminNotification::create([
                'type' => 'training_request',
                'title' => 'New Training Request',
                'message' => $employeeName . ' requested training: ' . $request->training_title,
                'action_url' => '/admin/employee-trainings-dashboard',
                'is_read' => false,
                'created_at' => $request->created_at
            ]);
            $count++;
        }

        // Sync recent training completions
        $completions = EmployeeTrainingDashboard::where('progress', '>=', 100)
            ->where('updated_at', '>=', now()->subDays(3))
            ->with(['employee', 'course'])
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($completions as $completion) {
            $employeeName = $completion->employee
                ? $completion->employee->first_name . ' ' . $completion->employee->last_name
                : 'Employee';
            $courseName = $completion->course
                ? $completion->course->course_title
                : $completion->training_title;

            AdminNotification::create([
                'type' => 'training_completion',
                'title' => 'Training Completed',
                'message' => $employeeName . ' completed ' . $courseName,
                'action_url' => '/admin/employee-trainings-dashboard',
                'is_read' => false,
                'created_at' => $completion->updated_at
            ]);
            $count++;
        }

        $this->info("Successfully synced $count notifications!");
        return 0;
    }
}
