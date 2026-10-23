<?php

namespace App\Http\Middleware;

use App\Services\ModuleAccessService;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Menolak akses langsung ke route modul jika user tidak punya modul tersebut.
 *
 * Dipasang di group /superuser dengan parameter modul TRS. Route profil,
 * logout, dan getToken tetap boleh diakses agar user bisa memperbaiki akun
 * atau keluar walaupun tidak punya akses transaksi.
 */
class EnsureModuleAccess
{
    public function handle($request, Closure $next, $module = 'TRS')
    {
        $user = Auth::guard('superuser')->user();

        if (!$user) {
            return redirect()->route('auth.superuser.index');
        }

        $routeName = $request->route() ? $request->route()->getName() : null;

        if ($routeName
            && (strpos($routeName, 'superuser.profile.') === 0
                || in_array($routeName, ['superuser.logout', 'superuser.getToken'], true))) {
            return $next($request);
        }

        if (!ModuleAccessService::hasAccess($user, $module)) {
            abort(403, 'Akun Anda tidak memiliki akses ke modul ' . strtoupper($module) . '. Silakan kembali ke portal dan pilih modul yang tersedia.');
        }

        return $next($request);
    }
}
