<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Show the admin login form
     */
    public function showLoginForm()
    {
        return view('admin_login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        // Attempt to authenticate the user with admin guard
        if (Auth::guard('admin')->attempt($credentials)) {
            $user = Auth::guard('admin')->user();

            // Check if user has admin role (case-insensitive)
            if (strcasecmp($user->role, 'admin') === 0) {
                $request->session()->regenerate();
                return redirect()->route('admin.dashboard');
            } else {
                Auth::guard('admin')->logout();
                return back()->withErrors([
                    'email' => 'You do not have admin privileges.',
                ]);
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Verify admin password for additional security
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::guard('admin')->user();

        if ($user && Hash::check($request->password, $user->password)) {
            // Store verification in session for this admin user
            $request->session()->put('admin_password_verified_' . $user->id, true);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid password'], 401);
    }

    /**
     * Check if admin password is already verified in current session
     */
    public function checkPasswordVerification(Request $request)
    {
        $user = Auth::guard('admin')->user();

        if ($user && $request->session()->has('admin_password_verified_' . $user->id)) {
            return response()->json(['verified' => true]);
        }

        return response()->json(['verified' => false]);
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        // Redirect to the AdminDashboardController
        return app(AdminDashboardController::class)->index();
    }

    /**
     * Show admin settings page
     */
    public function settings()
    {
        $admin = Auth::guard('admin')->user();
        return view('setting_admin', compact('admin'));
    }

    /**
     * Update admin settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::guard('admin')->id(),
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:superadmin,admin,editor',
        ]);

        $admin = Auth::guard('admin')->user();

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    public function create()
    {
        return view('admin_create_user');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin_edit_user', compact('user'));
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->route('admin.settings')->with('success', 'User deleted');
    }
}
