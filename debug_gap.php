<?php
use App\Models\UpcomingTraining;
use App\Models\CompetencyGap;
use App\Models\Employee;

$training = UpcomingTraining::where('training_title', 'LIKE', '%Marketing and Promotion%')->first();

if ($training) {
    echo "Training Found: " . $training->training_title . "\n";
    echo "Employee ID: " . $training->employee_id . "\n";
    echo "Source: " . $training->source . "\n";
    
    $gaps = CompetencyGap::with('competency')->where('employee_id', $training->employee_id)->get();
    echo "Gaps found for employee: " . $gaps->count() . "\n";
    
    foreach ($gaps as $gap) {
        echo "Gap ID: " . $gap->id . "\n";
        echo "Competency Name: " . ($gap->competency ? $gap->competency->competency_name : 'NULL') . "\n";
        echo "Expired Date: " . $gap->expired_date . "\n";
    }
} else {
    echo "Training not found.\n";
}
