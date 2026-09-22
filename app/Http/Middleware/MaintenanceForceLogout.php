<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Dijalankan SETELAH session & auth (didaftarkan di grup superuser).
 * Tugas: paksa logout user biasa saat maintenance ON.
 * Admin Developer/SuperAdmin di-bypass agar bisa mematikan maintenance.
 */
class MaintenanceForceLogout
{
    /**
     * URI yang tidak dicegat (status polling, login, logout).
     *
     * @var array
     */
    protected $except = [
        'auth/superuser',
        'auth/superuser/*',
        'superuser/utility/settings/maintenance-status',
        'superuser/logout',
        'superuser/getToken',
    ];

    public function handle($request, Closure $next)
    {
        if (!app()->isDownForMaintenance()) {
            return $next($request);
        }

        if ($this->inExceptArray($request)) {
            return $next($request);
        }

        // Admin tetap bisa bekerja.
        if ($this->isAdmin()) {
            return $next($request);
        }

        // User biasa yang masih login -> paksa logout.
        if (Auth::guard('superuser')->check()) {
            Auth::guard('superuser')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'maintenance' => true,
                    'logout' => true,
                    'message' => 'Sistem sedang maintenance. Anda telah di-logout otomatis.',
                ], 503);
            }

            return redirect()
                ->route('auth.superuser.index')
                ->withErrors(['Sistem sedang dalam pemeliharaan. Anda telah di-logout otomatis. Silakan login kembali nanti.']);
        }

        // Tamu yang lolos sampai sini (mis. halaman login superuser sudah di-except)
        // biarkan lanjut; global CheckForMaintenanceMode yang menangani path publik.
        return $next($request);
    }

    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            $except = trim($except, '/');
            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }

    protected function isAdmin()
    {
        try {
            $user = Auth::guard('superuser')->user();

            if ($user && method_exists($user, 'hasAnyRole')) {
                return $user->hasAnyRole(['Developer', 'SuperAdmin']);
            }

            if ($user && isset($user->is_superuser) && $user->is_superuser == 1) {
                return true;
            }
        } catch (\Throwable $e) {
            // abaikan
        }

        return false;
    }
}
