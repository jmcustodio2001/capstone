<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            if (strcasecmp($user->role, 'admin') === 0) {
                return $next($request);
            }
        }

        Auth::guard('admin')->logout();
        return redirect()->route('admin.login')->with('error', 'Access denied. Admins only.');
    }
}
