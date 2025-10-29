<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SecuritySetting;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class IPRestrictionMiddleware
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
        $settings = SecuritySetting::getInstance();
        
        if (!$settings->ip_restriction) {
            return $next($request);
        }

        $clientIP = $this->getClientIP($request);
        $allowedIPs = $settings->getAllowedIPs();

        // If no IPs are configured, allow all (fail-safe)
        if (empty($allowedIPs)) {
            return $next($request);
        }

        // Check if client IP is in allowed list
        if (!$this->isIPAllowed($clientIP, $allowedIPs)) {
            // Log the blocked attempt
            AuditLog::logAction('IP Access Blocked', [
                'ip_address' => $clientIP,
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ]);

            Log::warning('Access blocked for IP: ' . $clientIP, [
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied from your IP address'
                ], 403);
            }

            abort(403, 'Access denied from your IP address');
        }

        return $next($request);
    }

    /**
     * Get the client's real IP address
     */
    private function getClientIP(Request $request)
    {
        // Check for various headers that might contain the real IP
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];

        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Handle comma-separated IPs (X-Forwarded-For can contain multiple IPs)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP format
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback to Laravel's method
        return $request->ip();
    }

    /**
     * Check if IP is allowed
     */
    private function isIPAllowed($clientIP, $allowedIPs)
    {
        foreach ($allowedIPs as $allowedIP) {
            if ($this->matchIP($clientIP, $allowedIP)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Match IP against allowed IP (supports CIDR notation)
     */
    private function matchIP($clientIP, $allowedIP)
    {
        // Exact match
        if ($clientIP === $allowedIP) {
            return true;
        }

        // CIDR notation support
        if (strpos($allowedIP, '/') !== false) {
            list($subnet, $mask) = explode('/', $allowedIP);
            
            if (filter_var($subnet, FILTER_VALIDATE_IP) && is_numeric($mask)) {
                $clientLong = ip2long($clientIP);
                $subnetLong = ip2long($subnet);
                $maskLong = -1 << (32 - $mask);
                
                return ($clientLong & $maskLong) === ($subnetLong & $maskLong);
            }
        }

        // Wildcard support (e.g., 192.168.1.*)
        if (strpos($allowedIP, '*') !== false) {
            $pattern = str_replace('*', '.*', preg_quote($allowedIP, '/'));
            return preg_match('/^' . $pattern . '$/', $clientIP);
        }

        return false;
    }
}
