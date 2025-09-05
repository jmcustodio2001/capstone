<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            // Check if this is an admin route or admin-related request
            if ($request->is('admin/*') || 
                $request->routeIs('admin.*') || 
                str_contains($request->getPathInfo(), 'admin')) {
                return route('admin.login');
            }
            // Check if this is an employee route
            if ($request->is('employee/*') || $request->routeIs('employee.*')) {
                return route('employee.login');
            }
            // Default to admin login for other routes
            return route('admin.login');
        }
        return null;
    }
}
