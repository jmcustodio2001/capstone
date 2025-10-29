<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SecuritySetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SessionTimeoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        $settings = SecuritySetting::getInstance();
        
        if (!$settings->session_timeout) {
            return $next($request);
        }

        $timeoutDuration = $settings->timeout_duration * 60; // Convert minutes to seconds
        $sessionKey = 'last_activity_' . ($guard ?? 'web');
        $lastActivity = Session::get($sessionKey, time());

        // Check if session has expired
        if (time() - $lastActivity > $timeoutDuration) {
            // Log the timeout
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                \App\Models\AuditLog::logAction('Session timeout', [
                    'user_type' => $guard ?? 'web',
                    'user_id' => $user->id ?? null,
                    'user_name' => $user->name ?? 'Unknown',
                    'timeout_duration' => $settings->timeout_duration,
                    'last_activity' => date('Y-m-d H:i:s', $lastActivity)
                ]);
            }

            // Clear the session
            Auth::guard($guard)->logout();
            Session::flush();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired due to inactivity',
                    'redirect' => $this->getLoginRoute($guard)
                ], 401);
            }

            return redirect()->route($this->getLoginRoute($guard))
                ->with('message', 'Your session has expired due to inactivity. Please log in again.');
        }

        // Update last activity time
        Session::put($sessionKey, time());

        return $next($request);
    }

    /**
     * Get the appropriate login route based on guard
     */
    private function getLoginRoute($guard)
    {
        switch ($guard) {
            case 'admin':
                return 'admin.login';
            case 'employee':
                return 'employee.login';
            default:
                return 'login';
        }
    }
}
