<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompetencyLibrary;
use App\Models\CourseManagement;
use Illuminate\Support\Facades\Log;

class SyncCompetenciesToCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'competencies:sync-to-courses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all competencies from competency library to course management';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting competency to course sync...');
        
        try {
            // Get all competencies from competency library
            $competencies = CompetencyLibrary::all();
            
            $synced = 0;
            $skipped = 0;
            
            $this->info("Found {$competencies->count()} competencies to process");
            
            foreach ($competencies as $competency) {
                // Check if course already exists for this competency
                $existingCourse = CourseManagement::where('course_title', $competency->competency_name)->first();
                
                if (!$existingCourse) {
                    // Create new course from competency
                    $course = CourseManagement::create([
                        'course_title' => $competency->competency_name,
                        'description' => $competency->description ?? 'Auto-synced from Competency Library',
                        'start_date' => now(),
                        'status' => 'Active'
                    ]);
                    
                    $synced++;
                    $this->line("✓ Synced: {$competency->competency_name}");
                } else {
                    $skipped++;
                }
            }
            
            $this->info("Sync completed: {$synced} new courses created, {$skipped} already existed");
            Log::info("Manual competency sync completed: {$synced} new courses created, {$skipped} already existed");
            
        } catch (\Exception $e) {
            $this->error('Error syncing competencies to courses: ' . $e->getMessage());
            Log::error('Error in manual competency sync: ' . $e->getMessage());
        }
    }
}
