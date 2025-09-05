<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

try {
    // Find JM CUSTODIO employee
    $employee = \App\Models\Employee::where('first_name', 'JM')->where('last_name', 'CUSTODIO')->first();
    if (!$employee) {
        echo "Employee JM CUSTODIO not found\n";
        exit;
    }

    echo "Employee ID: " . $employee->employee_id . "\n";
    echo "Name: " . $employee->first_name . " " . $employee->last_name . "\n\n";

    // Check training requests for this employee
    $requests = \App\Models\TrainingRequest::where('employee_id', $employee->employee_id)->get();
    echo "Training Requests (" . $requests->count() . "):\n";
    foreach ($requests as $request) {
        echo "- Request ID: " . $request->request_id . "\n";
        echo "  Status: " . $request->status . "\n";
        echo "  Course ID: " . ($request->course_id ?? 'NULL') . "\n";
        echo "  Training Title: " . ($request->training_title ?? 'NULL') . "\n";
        
        if ($request->course_id) {
            $course = \App\Models\CourseManagement::find($request->course_id);
            if ($course) {
                echo "  Course Found: " . $course->course_title . "\n";
            } else {
                echo "  Course NOT FOUND for ID: " . $request->course_id . "\n";
            }
        }
        echo "\n";
    }

    // Check employee training dashboard records
    $dashboardRecords = \App\Models\EmployeeTrainingDashboard::where('employee_id', $employee->employee_id)->get();
    echo "Employee Training Dashboard Records (" . $dashboardRecords->count() . "):\n";
    foreach ($dashboardRecords as $record) {
        echo "- ID: " . $record->id . "\n";
        echo "  Course ID: " . ($record->course_id ?? 'NULL') . "\n";
        echo "  Progress: " . ($record->progress ?? 'NULL') . "%\n";
        if ($record->course_id) {
            $course = \App\Models\CourseManagement::find($record->course_id);
            echo "  Course: " . ($course ? $course->course_title : 'NOT FOUND') . "\n";
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
