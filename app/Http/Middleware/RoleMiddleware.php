<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        if (!Auth::check() || strtoupper(Auth::user()->role) !== strtoupper($role)) {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'Access denied. Admins only.');
        }
        return $next($request);
    }
}
