<?php
// Simple script to fix competency assigned by names
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UpcomingTraining;
use App\Models\User;
use App\Models\CompetencyGap;

try {
    $updatedCount = 0;
    
    echo "Starting competency assigned by name fix...\n";
    
    // Fix upcoming training records with competency source
    $competencyUpcomingTrainings = UpcomingTraining::where('source', 'competency_gap')
        ->where(function($query) {
            $query->where('assigned_by_name', 'Competency System')
                  ->orWhere('assigned_by_name', 'System Admin')
                  ->orWhereNull('assigned_by_name')
                  ->orWhere('assigned_by_name', '');
        })
        ->get();
    
    echo "Found " . $competencyUpcomingTrainings->count() . " records to fix\n";
    
    foreach ($competencyUpcomingTrainings as $training) {
        $assignedByName = null;
        
        echo "Processing training ID: {$training->upcoming_id}, Title: {$training->training_title}\n";
        
        // Try to get the admin name from the assigned_by ID
        if (is_numeric($training->assigned_by)) {
            $user = User::find($training->assigned_by);
            if ($user && !empty($user->name)) {
                $assignedByName = $user->name;
                echo "  Found admin by ID: {$assignedByName}\n";
            }
        }
        
        // If no user found, try to get from competency gap record
        if (!$assignedByName) {
            $competencyGap = CompetencyGap::where('employee_id', $training->employee_id)
                ->where('assigned_to_training', true)
                ->whereHas('competency', function($query) use ($training) {
                    $trainingTitle = $training->training_title;
                    $query->where('competency_name', 'LIKE', '%' . $trainingTitle . '%')
                          ->orWhere('competency_name', 'LIKE', '%' . str_replace(' Training', '', $trainingTitle) . '%');
                })
                ->first();
            
            if ($competencyGap && $competencyGap->assigned_by) {
                if (is_numeric($competencyGap->assigned_by)) {
                    $user = User::find($competencyGap->assigned_by);
                    if ($user && !empty($user->name)) {
                        $assignedByName = $user->name;
                        echo "  Found admin from competency gap: {$assignedByName}\n";
                    }
                } else {
                    $assignedByName = $competencyGap->assigned_by;
                    echo "  Found admin name from competency gap: {$assignedByName}\n";
                }
            }
        }
        
        // Final fallback
        if (!$assignedByName) {
            $assignedByName = 'Competency System';
            echo "  Using fallback: {$assignedByName}\n";
        }
        
        // Update the record
        $training->assigned_by_name = $assignedByName;
        $training->save();
        $updatedCount++;
        
        echo "  Updated with: {$assignedByName}\n\n";
    }
    
    echo "Successfully updated {$updatedCount} competency training records\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
