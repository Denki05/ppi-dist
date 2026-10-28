<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Superuser
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
        if (!Auth::guard('superuser')->check()) {
            return redirect()->route('auth.superuser.index');
        }

        $user = Auth::guard('superuser')->user();

        // Akun nonaktif harus langsung diputus, walau sesi / remember cookie
        // dari sebelum dinonaktifkan masih ada.
        if ((int) ($user->is_active ?? 0) !== 1) {
            Auth::guard('superuser')->logout();

            try {
                DB::table('superusers')
                    ->where('id', $user->id)
                    ->update(['remember_token' => null]);
            } catch (\Throwable $e) {
                report($e);
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.superuser.index')
                ->withErrors(['Akun Anda sudah dinonaktifkan. Hubungi admin jika ini keliru.']);
        }

        $this->superuser = $user;

        view()->share('superuser', $this->superuser);

        return $next($request);
    }
}
