<?php
// database/seeders/CompetencyGapDisplaySeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\CompetencyLibrary;
use App\Models\CompetencyGap;

class CompetencyGapDisplaySeeder extends Seeder
{
    public function run(): void
    {
        // Create or get an employee
        $employee = Employee::updateOrCreate(
            ['employee_id' => 3],
            [
                'first_name' => 'Mark',
                'last_name' => 'Lee',
                'email' => 'mark.lee@example.com',
                'status' => 'Active',
                'password' => bcrypt('password123'),
            ]
        );

        // Create or get a competency in Customer Service & Sales
        $competency = CompetencyLibrary::updateOrCreate(
            ['competency_name' => 'Customer Service Excellence', 'category' => 'Customer Service & Sales'],
            ['rate' => 5]
        );

        // Add a gap record with gap > 0
        CompetencyGap::create([
            'employee_id' => $employee->employee_id,
            'competency_id' => $competency->id,
            'required_level' => 5,
            'current_level' => 2,
            'gap' => 3,
            'gap_description' => 'Needs improvement in customer service excellence.'
        ]);
    }
}
