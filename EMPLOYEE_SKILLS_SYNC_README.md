# Employee Skills Auto-Sync to Competency Profiles

## Overview
This solution automatically tracks employee skills from the employee list and syncs them to the competency profiles. The skills will persist even after running `php artisan migrate:fresh`.

## How It Works

### 1. **Database Structure**
- Added `skills` column to the `employees` table to store employee skills
- Skills can be stored in various formats: comma-separated, newline-separated, JSON, etc.

### 2. **Automatic Syncing (Real-time)**
When employees are fetched from the API, their skills are automatically synced to competency profiles:

- **EmployeeCompetencyProfileController**: 
  - Fetches employees from API
  - Calls `syncEmployeeSkillsToCompetencies()` for each employee
  - Creates competency library entries for each skill
  - Creates competency profiles with max proficiency (5/5) for listed skills

### 3. **Observer Pattern (Future Updates)**
- **EmployeeSkillObserver**: Automatically syncs skills when employees are created or updated locally
- Registered in `AppServiceProvider`

### 4. **Database Seeding (After migrate:fresh)**
- **EmployeeSkillsSeeder**: Runs after `php artisan migrate:fresh --seed`
- Fetches all employees from API
- Recreates all competency profiles based on employee skills
- Ensures data persistence

## Usage

### Normal Operation
Skills are automatically synced when:
1. Viewing the employee competency profiles page
2. Any employee record is created or updated

### After migrate:fresh
```bash
php artisan migrate:fresh --seed
```

The `EmployeeSkillsSeeder` will automatically:
1. Fetch all employees from the API
2. Parse their skills
3. Create competency library entries
4. Create competency profiles with max proficiency

### Manual Seeding (Optional)
If you want to manually sync skills without running migrate:fresh:
```bash
php artisan db:seed --class=EmployeeSkillsSeeder
```

## Files Modified/Created

### Created Files:
1. `app/Observers/EmployeeSkillObserver.php` - Observer for automatic skill syncing
2. `database/seeders/EmployeeSkillsSeeder.php` - Seeder for data persistence
3. `database/migrations/2026_01_28_124907_add_skills_to_employees_table.php` - Migration for skills column

### Modified Files:
1. `app/Models/Employee.php` - Added 'skills' to fillable array
2. `app/Providers/AppServiceProvider.php` - Registered EmployeeSkillObserver
3. `database/seeders/DatabaseSeeder.php` - Added EmployeeSkillsSeeder
4. `app/Http/Controllers/EmployeeCompetencyProfileController.php` - Added skill syncing methods

## Features

### Skill Parsing
The system intelligently parses skills in multiple formats:
- Comma-separated: "PHP, JavaScript, Laravel"
- Newline-separated: "PHP\nJavaScript\nLaravel"
- Semicolon-separated: "PHP; JavaScript; Laravel"
- Pipe-separated: "PHP | JavaScript | Laravel"
- JSON array: ["PHP", "JavaScript", "Laravel"]

### Automatic Competency Creation
- Creates competency library entries for new skills
- Category: "Technical Skills"
- Description: "Auto-created from employee skills"

### Proficiency Level
- All skills from employee list are set to max proficiency (5/5)
- This indicates the employee has listed this as their skill

### Duplicate Prevention
- Checks for existing competency profiles before creating new ones
- Uses `updateOrCreate` to avoid duplicates

## Benefits

1. **Data Persistence**: Skills survive `migrate:fresh` operations
2. **Automatic Tracking**: No manual intervention needed
3. **Real-time Sync**: Skills are synced when viewing competency profiles
4. **Flexible Format**: Supports multiple skill input formats
5. **Audit Trail**: Logs all skill syncing operations

## Troubleshooting

### Skills not appearing after migrate:fresh
1. Make sure you run: `php artisan migrate:fresh --seed`
2. Check if EmployeeSkillsSeeder is in DatabaseSeeder
3. Check logs: `storage/logs/laravel.log`

### Skills not syncing in real-time
1. Clear cache: `php artisan cache:clear`
2. Check if observer is registered in AppServiceProvider
3. Verify API is returning employee data with skills

### Duplicate competencies
- The system prevents duplicates automatically
- If duplicates exist, they were created before this system was implemented
- You can manually clean them up or they will be consolidated over time

## API Requirements

The employee API endpoint should return data in this format:
```json
{
  "employee_id": "EMP001",
  "first_name": "John",
  "last_name": "Doe",
  "skills": "PHP, JavaScript, Laravel, Vue.js"
}
```

The `skills` field can be in any of the supported formats mentioned above.
