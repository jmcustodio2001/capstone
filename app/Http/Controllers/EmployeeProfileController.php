<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ActivityLog;

class EmployeeProfileController extends Controller
{
    /**
     * Handle profile picture upload and update employee record.
     */
    public function uploadPicture(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'profile_picture' => 'required|image|max:2048', // max 2MB
        ]);

        $user = Auth::user();

        // Delete old picture if exists
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Store the new profile picture in storage/app/public/profile_pictures
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        // Save the path in the database
        $user->profile_picture = $path;
        $user->save();

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'update',
            'module' => 'Employee Profile',
            'description' => 'Updated profile picture for user ID: ' . $user->id,
        ]);
        // Redirect back with success message
        return back()->with('success', 'Profile picture updated!');
    }
}
