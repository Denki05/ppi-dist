<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as Middleware;
use Illuminate\Support\Facades\App;
use Closure;
use Auth;

class CheckForMaintenanceMode extends Middleware
{
    /**
     * URI yang tetap bisa diakses saat maintenance ON.
     * Penting: maintenance-status & logout harus lolos agar
     * user online bisa diberi tahu lalu dipaksa logout.
     *
     * @var array
     */
    protected $except = [
        '/',
        'auth/superuser',
        'auth/superuser/*',
        'superuser/utility/settings/maintenance-status',
        'superuser/logout',
        'superuser/getToken',
    ];

    public function handle($request, Closure $next)
    {
        if (App::isDownForMaintenance() && !$this->inExceptArray($request)) {
            // Request area superuser didelegasikan ke middleware
            // MaintenanceForceLogout (yang jalan setelah session/auth)
            // agar bisa bedakan admin vs user biasa + paksa logout.
            // Global middleware jalan sebelum session sehingga
            // Auth::user() belum tersedia di sini.
            if ($request->is(['superuser', 'superuser/*'])) {
                return $next($request);
            }

            // User biasa yang sedang login -> paksa logout.
            // (Catatan: pada tahap global ini session biasanya belum
            // tersedia, jadi kasus login ditangani tuntas oleh
            // MaintenanceForceLogout. Blok ini untuk jaga-jaga.)
            if ($request->user('superuser')) {
                \Illuminate\Support\Facades\Auth::guard('superuser')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Request AJAX / fetch dari polling JS -> balas JSON 503
                // agar frontend bisa menampilkan popup lalu redirect.
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

            // Tamu / belum login -> halaman 503 seperti biasa.
            return abort(503, 'Situs sedang dalam pemeliharaan. Silakan coba lagi nanti!');
        }

        // read IP from config, or use another codition you need
        // if ($request->getClientIp() === '127.0.0.1') {
        //     return $next($request);
        // }

        return $next($request);
    }

    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }

    protected function isAdmin($request)
    {
        try {
            $user = $request->user('superuser')
                ?? \Illuminate\Support\Facades\Auth::guard('superuser')->user();

            if ($user && method_exists($user, 'hasAnyRole')) {
                return $user->hasAnyRole(['Developer', 'SuperAdmin']);
            }

            // Fallback untuk instalasi lama tanpa role.
            if ($user && isset($user->is_superuser) && $user->is_superuser == 1) {
                return true;
            }
        } catch (\Throwable $e) {
            // abaikan, anggap bukan admin
        }

        return false;
    }
}