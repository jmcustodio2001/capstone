<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableCSRFForExams
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Disable CSRF verification for exam submission routes
        if ($request->is('employee/exam/submit/*')) {
            $request->session()->regenerateToken();
        }
        
        return $next($request);
    }
}
