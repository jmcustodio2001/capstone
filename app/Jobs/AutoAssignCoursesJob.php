<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CompetencyGap;
use App\Models\CourseManagement;
use App\Models\EmployeeTrainingDashboard;

class AutoAssignCoursesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $employeeId;

    /**
     * Create a new job instance.
     */
    public function __construct($employeeId)
    {
        $this->employeeId = $employeeId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $gaps = CompetencyGap::with('competency')->where('employee_id', $this->employeeId)->where('gap', '>', 0)->get();
        $toInsert = [];
        $today = now()->toDateString();
        $existingAssignments = EmployeeTrainingDashboard::where('employee_id', $this->employeeId)
            ->pluck('course_id')->toArray();
        foreach ($gaps as $gap) {
            $courses = CourseManagement::where('status', 'Active')
                ->where(function($q) use ($gap) {
                    $q->where('course_title', 'LIKE', '%' . $gap->competency->competency_name . '%')
                      ->orWhere('description', 'LIKE', '%' . $gap->competency->competency_name . '%');
                })
                ->get();
            foreach ($courses as $course) {
                if (!in_array($course->course_id, $existingAssignments)) {
                    $toInsert[] = [
                        'employee_id' => $this->employeeId,
                        'course_id' => $course->course_id,
                        'training_date' => $today,
                        'progress' => 0,
                        'status' => 'Not Started',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $existingAssignments[] = $course->course_id;
                }
            }
        }
        if (!empty($toInsert)) {
            EmployeeTrainingDashboard::insert($toInsert);
        }
    }
}
