<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SecuritySetting;
use Illuminate\Support\Facades\Validator;

class PasswordComplexityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply to password change/reset requests
        if (!$this->shouldValidatePassword($request)) {
            return $next($request);
        }

        $settings = SecuritySetting::getInstance();
        
        if (!$settings->password_complexity) {
            return $next($request);
        }

        $password = $request->input('password') ?? $request->input('new_password');
        
        if (!$password) {
            return $next($request);
        }

        $rules = $this->getPasswordRules($settings);
        $messages = $this->getPasswordMessages($settings);

        $validator = Validator::make(['password' => $password], ['password' => $rules], $messages);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password does not meet complexity requirements',
                    'errors' => $validator->errors()
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        return $next($request);
    }

    /**
     * Check if we should validate password for this request
     */
    private function shouldValidatePassword(Request $request)
    {
        $passwordFields = ['password', 'new_password'];
        
        foreach ($passwordFields as $field) {
            if ($request->has($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get password validation rules based on security settings
     */
    private function getPasswordRules(SecuritySetting $settings)
    {
        $rules = ['required', 'string', 'min:' . $settings->password_min_length];

        if ($settings->password_require_uppercase) {
            $rules[] = 'regex:/[A-Z]/';
        }

        if ($settings->password_require_lowercase) {
            $rules[] = 'regex:/[a-z]/';
        }

        if ($settings->password_require_numbers) {
            $rules[] = 'regex:/[0-9]/';
        }

        if ($settings->password_require_symbols) {
            $rules[] = 'regex:/[@$!%*?&]/';
        }

        return $rules;
    }

    /**
     * Get custom error messages for password validation
     */
    private function getPasswordMessages(SecuritySetting $settings)
    {
        $messages = [
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password must be at least ' . $settings->password_min_length . ' characters long.',
        ];

        if ($settings->password_require_uppercase) {
            $messages['password.regex'] = 'Password must contain at least one uppercase letter.';
        }

        if ($settings->password_require_lowercase) {
            $messages['password.regex'] = 'Password must contain at least one lowercase letter.';
        }

        if ($settings->password_require_numbers) {
            $messages['password.regex'] = 'Password must contain at least one number.';
        }

        if ($settings->password_require_symbols) {
            $messages['password.regex'] = 'Password must contain at least one special character (@$!%*?&).';
        }

        return $messages;
    }
}
