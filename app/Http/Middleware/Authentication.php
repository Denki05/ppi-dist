<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->is(['auth', 'auth/*'])) {
            if (Auth::guard('superuser')->check() == true) {
                return redirect()->route('superuser.index');
            }
        }

        return $next($request);

    }
}
