<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\CompetencyLibrary;
use App\Models\CompetencyGap;
use App\Models\CourseManagement;
use App\Models\EmployeeTrainingDashboard;

class CompetencyGapSeeder extends Seeder
{
    public function run(): void
    {
        // Create employees
        $employee = Employee::updateOrCreate(
            ['employee_id' => 1],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'status' => 'Active',
                'password' => bcrypt('password123'),
            ]
        );

        // Create competencies
        $competency = CompetencyLibrary::firstOrCreate([
            'competency_name' => 'Customer Service',
            'category' => 'Customer Service & Sales',
            'rate' => 5,
        ]);

        // Create a gap
    CompetencyGap::create([
            'employee_id' => $employee->employee_id,
            'competency_id' => $competency->id,
            'required_level' => 5,
            'current_level' => 2,
            'gap' => 3,
            'gap_description' => 'Needs improvement in customer service skills.'
        ]);

        // Create a course
        $course = CourseManagement::firstOrCreate([
            'course_title' => 'Customer Service Training',
            'status' => 'Active',
        ]);

        // Do NOT assign training to employee here, so auto-assign will work
    }
}
