<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use App\Services\ModuleAccessService;
use App\Services\ModuleSsoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('superuser')->user();
        $items = ModuleAccessService::itemsForUser($user);

        // Kalau hanya punya satu modul, langsung masuk modul tersebut.
        // Tambahkan ?choose=1 jika ingin tetap melihat halaman pilihan.
        if (count($items) === 1 && !$request->boolean('choose')) {
            $code = key($items);

            return redirect()->away(ModuleSsoService::urlFor($code, $user));
        }

        return view('superuser.modules', [
            'superuser' => $user,
            'items'     => $items,
            'allItems'  => config('modules.items', []),
        ]);
    }

    public function go($module)
    {
        $user = Auth::guard('superuser')->user();
        $module = strtoupper(trim((string) $module));

        if (!ModuleAccessService::item($module)) {
            return redirect()->route('portal.modules')
                ->with('error', 'Modul tidak dikenal.');
        }

        if (!ModuleAccessService::hasAccess($user, $module)) {
            return redirect()->route('portal.modules')
                ->with('error', 'Akun Anda tidak memiliki akses ke modul ' . $module . '. Hubungi admin jika ini keliru.');
        }

        return redirect()->away(ModuleSsoService::urlFor($module, $user));
    }
}
