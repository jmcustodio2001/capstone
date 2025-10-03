<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/admin_login',
        'admin_login',
        // Removed employee/* exclusions to fix CSRF token mismatch issues
        // OTP verification should use proper CSRF tokens for security
        '/employee/exam/submit-ajax/*',
        '/employee/quiz/submit-ajax/*',
        'employee/exam/submit-ajax/*',
        'employee/quiz/submit-ajax/*',
        // CSRF refresh endpoint must be excluded to get new tokens
        '/admin/refresh-csrf',
        // Temporary exception for maintenance mode
        '/admin/maintenance-mode/enable',
    ];
}
