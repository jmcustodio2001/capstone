<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use App\Models\CourseManagement;
use App\Models\CompetencyLibrary;
use App\Models\PotentialSuccessor;
use App\Models\TrainingRecordCertificateTracking;
use App\Models\AttendanceTimeLog;
use App\Models\EmployeeCompetencyProfile;
use App\Models\CompetencyGap;
use Carbon\Carbon;

class DashboardDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample users with employee role
        $users = [
            ['name' => 'John Smith', 'email' => 'john.smith@jetlouge.com', 'role' => 'employee'],
            ['name' => 'Sarah Johnson', 'email' => 'sarah.johnson@jetlouge.com', 'role' => 'employee'],
            ['name' => 'Mike Wilson', 'email' => 'mike.wilson@jetlouge.com', 'role' => 'employee'],
            ['name' => 'Lisa Chen', 'email' => 'lisa.chen@jetlouge.com', 'role' => 'employee'],
            ['name' => 'David Brown', 'email' => 'david.brown@jetlouge.com', 'role' => 'employee'],
            ['name' => 'Emma Davis', 'email' => 'emma.davis@jetlouge.com', 'role' => 'employee'],
            ['name' => 'James Miller', 'email' => 'james.miller@jetlouge.com', 'role' => 'employee'],
            ['name' => 'Anna Garcia', 'email' => 'anna.garcia@jetlouge.com', 'role' => 'employee'],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['password' => bcrypt('password')])
            );
        }

        // Create sample employees
        $employees = [
            ['employee_id' => 'EMP001', 'first_name' => 'John', 'last_name' => 'Smith', 'email' => 'john.smith@jetlouge.com', 'position' => 'Travel Consultant', 'department_id' => null, 'status' => 'active', 'password' => bcrypt('password')],
            ['employee_id' => 'EMP002', 'first_name' => 'Sarah', 'last_name' => 'Johnson', 'email' => 'sarah.johnson@jetlouge.com', 'position' => 'Customer Service Rep', 'department_id' => null, 'status' => 'active', 'password' => bcrypt('password')],
            ['employee_id' => 'EMP003', 'first_name' => 'Mike', 'last_name' => 'Wilson', 'email' => 'mike.wilson@jetlouge.com', 'position' => 'Tour Guide', 'department_id' => null, 'status' => 'active', 'password' => bcrypt('password')],
            ['employee_id' => 'EMP004', 'first_name' => 'Lisa', 'last_name' => 'Chen', 'email' => 'lisa.chen@jetlouge.com', 'position' => 'Marketing Specialist', 'department_id' => null, 'status' => 'active', 'password' => bcrypt('password')],
            ['employee_id' => 'EMP005', 'first_name' => 'David', 'last_name' => 'Brown', 'email' => 'david.brown@jetlouge.com', 'position' => 'Operations Manager', 'department_id' => null, 'status' => 'active', 'password' => bcrypt('password')],
            ['employee_id' => 'EMP006', 'first_name' => 'Emma', 'last_name' => 'Davis', 'email' => 'emma.davis@jetlouge.com', 'position' => 'HR Specialist', 'department_id' => null, 'status' => 'active', 'password' => bcrypt('password')],
            ['employee_id' => 'EMP007', 'first_name' => 'James', 'last_name' => 'Miller', 'email' => 'james.miller@jetlouge.com', 'position' => 'Finance Manager', 'department_id' => null, 'status' => 'active', 'password' => bcrypt('password')],
            ['employee_id' => 'EMP008', 'first_name' => 'Anna', 'last_name' => 'Garcia', 'email' => 'anna.garcia@jetlouge.com', 'position' => 'IT Support', 'department_id' => null, 'status' => 'active', 'password' => bcrypt('password')],
        ];

        foreach ($employees as $empData) {
            Employee::firstOrCreate(
                ['employee_id' => $empData['employee_id']],
                $empData
            );
        }

        // Create sample courses
        $courses = [
            ['course_title' => 'Customer Service Excellence', 'description' => 'Advanced customer service training', 'status' => 'Active'],
            ['course_title' => 'Destination Knowledge Training', 'description' => 'Comprehensive destination training', 'status' => 'Active'],
            ['course_title' => 'Sales Techniques', 'description' => 'Modern sales strategies and techniques', 'status' => 'Active'],
            ['course_title' => 'Leadership Development', 'description' => 'Leadership skills for managers', 'status' => 'Active'],
            ['course_title' => 'Digital Marketing Basics', 'description' => 'Introduction to digital marketing', 'status' => 'Active'],
            ['course_title' => 'Safety Training', 'description' => 'Workplace safety protocols', 'status' => 'Active'],
            ['course_title' => 'Communication Skills', 'description' => 'Effective communication training', 'status' => 'Active'],
            ['course_title' => 'Project Management', 'description' => 'Project management fundamentals', 'status' => 'Active'],
        ];

        foreach ($courses as $courseData) {
            CourseManagement::firstOrCreate(
                ['course_title' => $courseData['course_title']],
                $courseData
            );
        }

        // Create sample competencies
        $competencies = [
            ['competency_name' => 'Customer Service', 'description' => 'Ability to provide excellent customer service', 'category' => 'Customer Relations'],
            ['competency_name' => 'Sales Skills', 'description' => 'Effective sales techniques and strategies', 'category' => 'Sales'],
            ['competency_name' => 'Destination Knowledge', 'description' => 'Knowledge of travel destinations', 'category' => 'Product Knowledge'],
            ['competency_name' => 'Communication', 'description' => 'Effective verbal and written communication', 'category' => 'Soft Skills'],
            ['competency_name' => 'Leadership', 'description' => 'Leadership and management capabilities', 'category' => 'Management'],
            ['competency_name' => 'Digital Marketing', 'description' => 'Digital marketing and social media skills', 'category' => 'Marketing'],
            ['competency_name' => 'Project Management', 'description' => 'Project planning and execution skills', 'category' => 'Management'],
        ];

        foreach ($competencies as $compData) {
            CompetencyLibrary::firstOrCreate(
                ['competency_name' => $compData['competency_name']],
                $compData
            );
        }

        // Create sample training records
        $employees = Employee::all();
        $courses = CourseManagement::all();

        if ($employees->count() > 0 && $courses->count() > 0) {
            for ($i = 0; $i < 20; $i++) {
                $employee = $employees->random();
                $course = $courses->random();

                TrainingRecordCertificateTracking::firstOrCreate([
                    'employee_id' => $employee->employee_id,
                    'course_id' => $course->course_id,
                ], [
                    'training_date' => Carbon::now()->subDays(rand(1, 30)),
                    'status' => ['Active', 'Completed', 'Ongoing'][rand(0, 2)],
                    'certificate_number' => rand(0, 1) ? 'CERT-' . rand(1000, 9999) : null,
                    'certificate_expiry' => rand(0, 1) ? Carbon::now()->addYears(rand(1, 3)) : null,
                    'issue_date' => Carbon::now(),
                ]);
            }
        }

        // Create sample succession plans
        if ($employees->count() > 0) {
            for ($i = 0; $i < 5; $i++) {
                $employee = $employees->random();
                PotentialSuccessor::firstOrCreate([
                    'employee_id' => $employee->employee_id,
                    'potential_role' => ['Senior Manager', 'Team Lead', 'Department Head', 'Director'][rand(0, 3)],
                ], [
                    'identified_date' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        // Create sample attendance logs
        if ($employees->count() > 0) {
            foreach ($employees as $employee) {
                for ($day = 0; $day < 7; $day++) {
                    $date = Carbon::now()->subDays($day);
                    if ($date->isWeekday()) {
                        AttendanceTimeLog::firstOrCreate([
                            'employee_id' => $employee->employee_id,
                            'log_date' => $date->format('Y-m-d'),
                        ], [
                            'time_in' => $date->setTime(8, rand(0, 30))->format('H:i:s'),
                            'time_out' => $date->setTime(17, rand(0, 30))->format('H:i:s'),
                            'hours_worked' => 8 + (rand(-30, 30) / 60),
                            'status' => ['Present', 'Late', 'Half Day'][rand(0, 2)],
                        ]);
                    }
                }
            }
        }

        // Create sample Employee Training Dashboard records (Recent Trainings)
        if ($employees->count() > 0 && $courses->count() > 0) {
            for ($i = 0; $i < 30; $i++) {
                $employee = $employees->random();
                $course = $courses->random();

                \App\Models\EmployeeTrainingDashboard::firstOrCreate([
                    'employee_id' => $employee->employee_id,
                    'course_id' => $course->course_id,
                ], [
                    'training_title' => $course->course_title,
                    'training_date' => Carbon::now()->subDays(rand(1, 60)),
                    'status' => ['Completed', 'Ongoing', 'In Progress', 'Scheduled'][rand(0, 3)],
                    'progress' => rand(0, 100),
                    'remarks' => 'Auto-generated via seeder',
                    'assigned_by' => User::first()->id ?? null,
                ]);
            }
        }
    }
}
