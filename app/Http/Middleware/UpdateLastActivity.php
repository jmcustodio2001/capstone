<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class UpdateLastActivity
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'update')) {
            $user->update(['last_activity' => Carbon::now()]);
        }
        return $next($request);
    }
}
