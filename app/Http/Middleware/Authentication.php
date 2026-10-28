<?php

namespace App\Http\Middleware;

use App;
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
            // Route SSO Google tidak boleh kena auto-redirect:
            // 1. /sso/google        → user login klik "Hubungkan Google" harus sampai ke Google.
            // 2. /sso/google/callback → callback Google harus diproses (linking / login).
            // 3. /sso               → halaman SSO sendiri sudah handle redirect di controller.
            $isSsoPath = $request->is('auth/superuser/sso*');

            if (!$isSsoPath && Auth::guard('superuser')->check() == true) {
                return redirect()->route('portal.modules');
            }
        }

        return $next($request);

    }
}
