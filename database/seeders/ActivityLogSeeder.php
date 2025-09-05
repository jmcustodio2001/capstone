<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have at least one user
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'System Admin',
                'email' => 'admin@jetlouge.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Create sample activity logs
        $activities = [
            [
                'user_id' => $user->id,
                'module' => 'Employee Management',
                'action' => 'Created',
                'description' => 'New employee John Doe added to the system',
                'model_type' => 'Employee',
                'model_id' => 1,
                'created_at' => Carbon::now()->subMinutes(5),
            ],
            [
                'user_id' => $user->id,
                'module' => 'Training Management',
                'action' => 'Updated',
                'description' => 'Customer Service Excellence training session completed',
                'model_type' => 'Training',
                'model_id' => 2,
                'created_at' => Carbon::now()->subMinutes(15),
            ],
            [
                'user_id' => $user->id,
                'module' => 'Competency Management',
                'action' => 'Assessed',
                'description' => 'Competency gap analysis completed for employee EMP001',
                'model_type' => 'CompetencyGap',
                'model_id' => 3,
                'created_at' => Carbon::now()->subMinutes(30),
            ],
            [
                'user_id' => $user->id,
                'module' => 'Leave Management',
                'action' => 'Approved',
                'description' => 'Leave application for vacation approved',
                'model_type' => 'LeaveApplication',
                'model_id' => 4,
                'created_at' => Carbon::now()->subHour(),
            ],
            [
                'user_id' => $user->id,
                'module' => 'Succession Planning',
                'action' => 'Created',
                'description' => 'New succession plan created for Senior Manager position',
                'model_type' => 'SuccessionPlan',
                'model_id' => 5,
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'user_id' => $user->id,
                'module' => 'Course Management',
                'action' => 'Published',
                'description' => 'New course "Leadership Fundamentals" published',
                'model_type' => 'Course',
                'model_id' => 6,
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'user_id' => $user->id,
                'module' => 'Employee Self-Service',
                'action' => 'Updated',
                'description' => 'Employee profile update request submitted',
                'model_type' => 'ProfileUpdate',
                'model_id' => 7,
                'created_at' => Carbon::now()->subHours(4),
            ],
            [
                'user_id' => $user->id,
                'module' => 'Attendance Management',
                'action' => 'Recorded',
                'description' => 'Time-in recorded for employee EMP002',
                'model_type' => 'AttendanceLog',
                'model_id' => 8,
                'created_at' => Carbon::now()->subHours(5),
            ],
        ];

        foreach ($activities as $activity) {
            ActivityLog::create($activity);
        }
    }
}
