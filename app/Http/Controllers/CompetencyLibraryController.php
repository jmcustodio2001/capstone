<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompetencyLibrary;
use App\Models\Employee;
use App\Models\CompetencyGap;
use App\Models\EmployeeCompetencyProfile;
use App\Models\ActivityLog;
use App\Models\DestinationKnowledgeTraining;
use App\Models\TrainingNotification;
use App\Models\CourseManagementNotification;
use App\Models\CourseManagement;
use App\Models\AssessmentQuestion;
use Illuminate\Support\Facades\Auth;
class CompetencyLibraryController extends Controller
{
    private const NULLABLE_STRING = 'nullable|string';

    public function index()
    {
        // Exclude all destination training related competencies from the library view
        // But keep legitimate competencies like "Destination and Product Knowledge" from seeder
        $competencies = CompetencyLibrary::where('category', '!=', 'Destination Knowledge')
            ->where('category', '!=', 'General')
            ->where('competency_name', 'NOT LIKE', '%BESTLINK%')
            ->where('competency_name', 'NOT LIKE', '%ITALY%')
            ->where('competency_name', 'NOT LIKE', '%Destination Knowledge -%') // Only exclude auto-generated destination knowledge
            ->where('description', 'NOT LIKE', '%Auto-created from destination knowledge training%')
            ->orderBy('id', 'asc')
            ->paginate(50);

        $employees = Employee::all();
        $gaps = CompetencyGap::with(['employee', 'competency'])->get();
        $positions = \App\Models\OrganizationalPosition::all();
        $questions = AssessmentQuestion::all();

        return view('competency_management.competency_library', compact(
            'competencies',
            'employees',
            'gaps',
            'positions',
            'questions'
        ));
    }

    public function store(Request $request)
    {
        // Check if user is admin
        if (!Auth::guard('admin')->check() || strtoupper(Auth::guard('admin')->user()->role) !== 'ADMIN') {
            abort(403, 'Access denied. Admin privileges required.');
        }

        try {
            $request->validate([
                'competency_name' => 'required|string|max:255',
                'description' => self::NULLABLE_STRING,
                'category' => self::NULLABLE_STRING,
                'rate' => 'required|integer|min:1|max:5',
            ]);

            // Check for duplicate competency names
            $existingCompetency = CompetencyLibrary::where('competency_name', $request->competency_name)
                ->first();

            if ($existingCompetency) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Competency "' . $request->competency_name . '" already exists in the library!'
                    ], 422);
                }
                return redirect()->route('admin.competency_library.index')
                    ->with('error', 'Competency "' . $request->competency_name . '" already exists in the library!');
            }

            // Check for similar competency names
            $similarCompetency = CompetencyLibrary::where('competency_name', 'LIKE', '%' . $request->competency_name . '%')
                ->orWhere(function($query) use ($request) {
                    $query->whereRaw('UPPER(competency_name) LIKE ?', ['%' . strtoupper($request->competency_name) . '%']);
                })
                ->first();

            if ($similarCompetency) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Similar competency "' . $similarCompetency->competency_name . '" already exists. Please use the existing one or choose a different name.'
                    ], 422);
                }
                return redirect()->route('admin.competency_library.index')
                    ->with('error', 'Similar competency "' . $similarCompetency->competency_name . '" already exists. Please use the existing one or choose a different name.');
            }

            // Auto-detect and set destination knowledge category and proficiency
            $competencyData = $request->all();
            if ($this->isDestinationKnowledgeCompetency($request->competency_name)) {
                $competencyData['category'] = 'Destination Knowledge';
                $competencyData['rate'] = 5; // 100% proficiency for destination knowledge
            }

            $competency = CompetencyLibrary::create($competencyData);

            // Auto-sync to course management
            $this->syncCompetencyToCourseManagement($competency);

            ActivityLog::createLog([
                'module' => 'Competency Management',
                'action' => 'create',
                'description' => 'Added competency: ' . $competency->competency_name .
                    ($competency->category === 'Destination Knowledge' ? ' (Auto-categorized as Destination Knowledge with 100% proficiency)' : '') .
                    ' (Auto-synced to Course Management)',
                'model_type' => CompetencyLibrary::class,
                'model_id' => $competency->id,
            ]);

            $successMessage = 'Competency added successfully!' .
                ($competency->category === 'Destination Knowledge' ? ' Auto-categorized as Destination Knowledge with 100% proficiency.' : '');

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'competency' => $competency
                ]);
            }

            return redirect()->route('admin.competency_library.index')->with('success', $successMessage);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the competency: ' . $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        // Check if user is admin
        if (!Auth::guard('admin')->check() || strtoupper(Auth::guard('admin')->user()->role) !== 'ADMIN') {
            abort(403, 'Access denied. Admin privileges required.');
        }

        try {
            $request->validate([
                'competency_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'nullable|string',
                'rate' => 'required|integer|min:1|max:5',
            ]);

            // Check for duplicate names (excluding current competency)
            $existingCompetency = CompetencyLibrary::where('competency_name', $request->competency_name)
                ->where('id', '!=', $id)
                ->first();

            if ($existingCompetency) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Competency "' . $request->competency_name . '" already exists in the library!'
                    ], 422);
                }
                return redirect()->route('admin.competency_library.index')
                    ->with('error', 'Competency "' . $request->competency_name . '" already exists in the library!');
            }

            $competency = CompetencyLibrary::findOrFail($id);

            // Auto-detect and set destination knowledge category and proficiency
            $competencyData = $request->all();
            if ($this->isDestinationKnowledgeCompetency($request->competency_name)) {
                $competencyData['category'] = 'Destination Knowledge';
                $competencyData['rate'] = 5; // 100% proficiency for destination knowledge
            }

            $competency->update($competencyData);

            // Auto-sync to course management
            $this->syncCompetencyToCourseManagement($competency);

            ActivityLog::createLog([
                'module' => 'Competency Management',
                'action' => 'update',
                'description' => 'Updated competency: ' . $competency->competency_name .
                    ($competency->category === 'Destination Knowledge' ? ' (Auto-categorized as Destination Knowledge with 100% proficiency)' : '') .
                    ' (Auto-synced to Course Management)',
                'model_type' => CompetencyLibrary::class,
                'model_id' => $competency->id,
            ]);

            $successMessage = 'Competency updated successfully!' .
                ($competency->category === 'Destination Knowledge' ? ' Auto-categorized as Destination Knowledge with 100% proficiency.' : '');

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'competency' => $competency
                ]);
            }

            return redirect()->route('admin.competency_library.index')->with('success', $successMessage);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the competency: ' . $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    public function destroy($id)
    {
        // Check if user is admin
        if (!Auth::guard('admin')->check() || strtoupper(Auth::guard('admin')->user()->role) !== 'ADMIN') {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Delete related competency gaps
        CompetencyGap::where('competency_id', $id)->delete();

    // Delete related employee competency profiles
    EmployeeCompetencyProfile::where('competency_id', $id)->delete();

    // Now delete the competency itself
    $competency = CompetencyLibrary::findOrFail($id);
    $competencyName = $competency->competency_name;
    $competency->delete();
    ActivityLog::createLog([
        'module' => 'Competency Management',
        'action' => 'delete',
        'description' => 'Deleted competency: ' . $competencyName,
        'model_type' => CompetencyLibrary::class,
        'model_id' => $id,
    ]);
    return redirect()->route('admin.competency_library.index')->with('success', 'Competency deleted successfully!');
    }

    /**
     * Check if competency name indicates destination knowledge
     */
    private function isDestinationKnowledgeCompetency($competencyName)
    {
        $destinationKeywords = [
            'destination', 'location', 'place', 'city', 'terminal', 'station',
            'baguio', 'quezon', 'cubao', 'boracay', 'cebu', 'davao', 'manila',
            'baesa', 'geography', 'route', 'travel', 'area knowledge', 'local knowledge'
        ];

        $competencyNameLower = strtolower($competencyName);

        foreach ($destinationKeywords as $keyword) {
            if (strpos($competencyNameLower, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fix all existing destination knowledge competencies
     */
    public function fixDestinationCompetencies()
    {
        // Check if user is admin
        if (!Auth::guard('admin')->check() || strtoupper(Auth::guard('admin')->user()->role) !== 'ADMIN') {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $competencies = CompetencyLibrary::all();
        $updated = 0;

        foreach ($competencies as $competency) {
            if ($this->isDestinationKnowledgeCompetency($competency->competency_name)) {
                $competency->update([
                    'category' => 'Destination Knowledge',
                    'rate' => 5 // 100% proficiency
                ]);

                // Update related employee competency profiles
                \App\Models\EmployeeCompetencyProfile::where('competency_id', $competency->id)
                    ->update(['proficiency_level' => 5]);

                // Update competency gaps
                \App\Models\CompetencyGap::where('competency_id', $competency->id)
                    ->update(['current_level' => 100, 'gap' => 0]);

                $updated++;
            }
        }

        ActivityLog::createLog([
            'module' => 'Competency Management',
            'action' => 'bulk_update',
            'description' => "Fixed {$updated} destination knowledge competencies to 100% proficiency",
            'model_type' => CompetencyLibrary::class,
        ]);

        return redirect()->route('admin.competency_library.index')
            ->with('success', "Successfully updated {$updated} destination knowledge competencies to 100% proficiency!");
    }

    /**
     * Sync all destination knowledge training records to competency library
     */
    public function syncDestinationKnowledgeTraining()
    {
        // Check if user is admin
        if (!Auth::guard('admin')->check() || strtoupper(Auth::guard('admin')->user()->role) !== 'ADMIN') {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $destinationTrainings = DestinationKnowledgeTraining::select('destination_name')
            ->distinct()
            ->get();

        $synced = 0;
        $skipped = 0;

        foreach ($destinationTrainings as $training) {
            $competencyName = "Destination Knowledge - " . $training->destination_name;

            // Enhanced duplicate checking - check for both exact match and variations
            $existingCompetency = CompetencyLibrary::where('competency_name', $competencyName)->first();

            // Also check if plain destination name exists (like "BAESA" when we want "Destination Knowledge - BAESA")
            if (!$existingCompetency) {
                $existingCompetency = CompetencyLibrary::where('competency_name', $training->destination_name)->first();
            }

            // Check for any variation that contains the destination name
            if (!$existingCompetency) {
                $existingCompetency = CompetencyLibrary::where('competency_name', 'LIKE', '%' . $training->destination_name . '%')->first();
            }

            if (!$existingCompetency) {
                // Create new competency
                $competency = CompetencyLibrary::create([
                    'competency_name' => $competencyName,
                    'description' => "Knowledge and expertise about {$training->destination_name} destination, including routes, attractions, and local information.",
                    'category' => 'Destination Knowledge',
                    'rate' => 5 // 100% proficiency for destination knowledge
                ]);

                $synced++;

                ActivityLog::createLog([
                    'module' => 'Competency Management',
                    'action' => 'sync_create',
                    'description' => "Auto-synced destination knowledge competency: {$competencyName}",
                    'model_type' => CompetencyLibrary::class,
                    'model_id' => $competency->id,
                ]);
            } else {
                $skipped++;
            }
        }

        ActivityLog::createLog([
            'module' => 'Competency Management',
            'action' => 'bulk_sync',
            'description' => "Synced destination knowledge training: {$synced} created, {$skipped} skipped (already exist)",
            'model_type' => CompetencyLibrary::class,
        ]);

        $message = "Sync completed! {$synced} new destination knowledge competencies created";
        if ($skipped > 0) {
            $message .= ", {$skipped} already existed";
        }

        return redirect()->route('admin.competency_library.index')
            ->with('success', $message);
    }

    /**
     * Clean up auto-generated destination knowledge competencies to prevent duplication
     */
    public function cleanupDestinationDuplicates()
    {
        // Check if user is admin
        if (!Auth::guard('admin')->check() || strtoupper(Auth::guard('admin')->user()->role) !== 'ADMIN') {
            abort(403, 'Access denied. Admin privileges required.');
        }

        try {
            $destinationKeywords = [
                'destination', 'baesa', 'quezon', 'bestlink', 'college', 'philippines',
                'cubao', 'baguio', 'boracay', 'cebu', 'davao', 'manila'
            ];

            // Find auto-generated destination knowledge competencies
            $autoGenerated = CompetencyLibrary::where('description', 'LIKE', 'Auto-generated from completed training:%')
                ->where(function($query) use ($destinationKeywords) {
                    foreach ($destinationKeywords as $keyword) {
                        $query->orWhere('competency_name', 'LIKE', '%' . $keyword . '%');
                    }
                })->get();

            $deletedCount = 0;
            $profilesDeleted = 0;
            $gapsDeleted = 0;

            foreach ($autoGenerated as $competency) {
                // Delete related employee competency profiles
                $profiles = EmployeeCompetencyProfile::where('competency_id', $competency->competency_id)->delete();
                $profilesDeleted += $profiles;

                // Delete related competency gaps
                $gaps = CompetencyGap::where('competency_id', $competency->competency_id)->delete();
                $gapsDeleted += $gaps;

                // Delete the competency itself
                $competency->delete();
                $deletedCount++;
            }

            ActivityLog::createLog([
                'module' => 'Competency Management',
                'action' => 'cleanup',
                'description' => "Cleaned up {$deletedCount} auto-generated destination knowledge competencies, {$profilesDeleted} related profiles, and {$gapsDeleted} related gaps to prevent duplication with Destination Knowledge Training system.",
                'model_type' => CompetencyLibrary::class,
            ]);

            return redirect()->route('admin.competency_library.index')
                ->with('success', "Successfully cleaned up {$deletedCount} auto-generated destination knowledge competencies. Destination training data now only exists in Destination Knowledge Training system.");

        } catch (\Exception $e) {
            return redirect()->route('admin.competency_library.index')
                ->with('error', 'Error during cleanup: ' . $e->getMessage());
        }
    }

    /**
     * Remove all destination training competencies from competency library
     */
    public function removeDestinationTrainingCompetencies()
    {
        // Check if user is admin
        if (!Auth::guard('admin')->check() || strtoupper(Auth::guard('admin')->user()->role) !== 'ADMIN') {
            abort(403, 'Access denied. Admin privileges required.');
        }

        try {
            // Find all destination training related competencies
            $destinationCompetencies = CompetencyLibrary::where(function($query) {
                $query->where('category', 'Destination Knowledge')
                    ->orWhere('category', 'General')
                    ->orWhere('competency_name', 'LIKE', '%BESTLINK%')
                    ->orWhere('competency_name', 'LIKE', '%ITALY%')
                    ->orWhere('competency_name', 'LIKE', '%destination%')
                    ->orWhere('description', 'LIKE', '%Auto-created from destination knowledge training%');
            })->get();

            $deletedCount = 0;
            $profilesDeleted = 0;
            $gapsDeleted = 0;

            foreach ($destinationCompetencies as $competency) {
                // Delete related employee competency profiles
                $profiles = EmployeeCompetencyProfile::where('competency_id', $competency->id)->delete();
                $profilesDeleted += $profiles;

                // Delete related competency gaps
                $gaps = CompetencyGap::where('competency_id', $competency->id)->delete();
                $gapsDeleted += $gaps;

                // Delete the competency itself
                $competency->delete();
                $deletedCount++;
            }

            ActivityLog::createLog([
                'module' => 'Competency Management',
                'action' => 'cleanup',
                'description' => "Removed {$deletedCount} destination training competencies from competency library, {$profilesDeleted} related profiles, and {$gapsDeleted} related gaps. Destination training data should only exist in Destination Knowledge Training system.",
                'model_type' => CompetencyLibrary::class,
            ]);

            return redirect()->route('admin.competency_library.index')
                ->with('success', "Successfully removed {$deletedCount} destination training competencies from competency library. These should only exist in the Destination Knowledge Training system.");

        } catch (\Exception $e) {
            return redirect()->route('admin.competency_library.index')
                ->with('error', 'Error during removal: ' . $e->getMessage());
        }
    }

    /**
     * Notify course management about active courses using this competency
     */
    public function notifyCourseManagement(Request $request, $id)
    {
        // Check if user is admin
        if (!Auth::guard('admin')->check() || strtoupper(Auth::guard('admin')->user()->role) !== 'ADMIN') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ], 403);
        }

        try {
            $competency = CompetencyLibrary::findOrFail($id);

            // Find active courses that use this competency
            // This is a simplified approach - you may need to adjust based on your actual course-competency relationship
            $activeCourses = \App\Models\CourseManagement::where('status', 'Active')
                ->where(function($query) use ($competency) {
                    $query->where('course_title', 'LIKE', '%' . $competency->competency_name . '%')
                          ->orWhere('description', 'LIKE', '%' . $competency->competency_name . '%');
                })
                ->get();

            // Create notification for course management
            $notification = CourseManagementNotification::create([
                'competency_id' => $competency->id,
                'competency_name' => $competency->competency_name,
                'message' => 'Competency "' . $competency->competency_name . '" has been updated. ' .
                           ($activeCourses->count() > 0 ?
                           'Found ' . $activeCourses->count() . ' active courses that may be affected.' :
                           'No active courses found using this competency.'),
                'notification_type' => 'competency_update',
                'created_by' => Auth::guard('admin')->id(),
            ]);

            // Log the notification action
            ActivityLog::createLog([
                'module' => 'Competency Management',
                'action' => 'notification',
                'description' => 'Sent notification to course management about competency: ' . $competency->competency_name .
                               ' (' . $activeCourses->count() . ' active courses affected)',
                'model_type' => CompetencyLibrary::class,
                'model_id' => $competency->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification sent to course management successfully. ' .
                           ($activeCourses->count() > 0 ?
                           $activeCourses->count() . ' active courses may be affected.' :
                           'No active courses found using this competency.'),
                'competency' => $competency->competency_name,
                'active_courses_count' => $activeCourses->count(),
                'notification_id' => $notification->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-sync a single competency to course management
     */
    private function syncCompetencyToCourseManagement($competency)
    {
        try {
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

                \Illuminate\Support\Facades\Log::info("Auto-synced competency '{$competency->competency_name}' to course management");
                return $course;
            }

            return $existingCourse;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error syncing competency to course management: ' . $e->getMessage());
            return null;
        }
    }
}
