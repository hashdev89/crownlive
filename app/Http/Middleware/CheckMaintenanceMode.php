<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class CheckMaintenanceMode
{
    public function handle($request, Closure $next)
{
    // Only apply maintenance if the flag is ON
    if (config('app.maintenance')) {

        // Bagisto admin uses 'admin' guard
        if (auth()->guard('admin')->check()) {
            return $next($request); // allow logged-in admins
        }

        // All others see maintenance page
        return response()->view('maintenance'); // make sure this view exists
    }

    return $next($request);
}

}
