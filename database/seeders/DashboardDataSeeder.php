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
        // NOTE: All sample data seeding has been removed.
        // This seeder is intentionally left empty to ensure only real data
        // (synced from HR4/API, My Trainings, or other integrations) is used in the system.
        // 
        // Real data flow:
        // 1. Employees -> Synced from HR4 API via EmployeeSkillsSeeder
        // 2. Competencies -> Synced from CompetencyLibrarySeeder
        // 3. Courses -> Auto-synced from CompetencyLibrarySeeder or created via Training Record
        // 4. Certificates -> Auto-synced from TrainingRecordCertificateTrackingController logic
    }
}
