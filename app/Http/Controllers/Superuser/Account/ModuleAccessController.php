<?php

namespace App\Http\Controllers\Superuser\Account;

use App\Entities\Account\User;
use App\Http\Controllers\Controller;
use App\Services\ModuleAccessService;
use Illuminate\Http\Request;

/**
 * Checklist admin: user mana yang berhak membuka modul apa saja.
 */
class ModuleAccessController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $module = strtoupper(trim((string) $request->input('module')));
        $tab = $request->input('tab', 'active');

        if (!in_array($tab, ['active', 'inactive', 'all'], true)) {
            $tab = 'active';
        }

        $codes = ModuleAccessService::codes();

        if ($module !== '' && !in_array($module, $codes, true)) {
            $module = '';
        }

        $query = User::where('is_superuser', 0);

        if ($tab === 'active') {
            $query->where('is_active', 1);
        } elseif ($tab === 'inactive') {
            $query->where('is_active', 0);
        }

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('division', 'like', '%' . $search . '%');
            });
        }

        if ($module !== '') {
            $query->where(function ($sub) use ($module) {
                $sub->where('modul_akses', 'like', '%' . $module . '%');

                if ($module === 'MIS') {
                    $sub->orWhere('can_access_mis', 1);
                }

                if ($module === 'TRS') {
                    $sub->orWhereNull('modul_akses')
                        ->orWhere('modul_akses', '');
                }
            });
        }

        $users = $query->orderBy('name')->paginate(25)->appends([
            'search' => $search,
            'module' => $module,
            'tab'    => $tab,
        ]);

        $users->setCollection($users->getCollection()->map(function ($user) {
            $user->modules = ModuleAccessService::forUser($user);
            return $user;
        }));

        $counts = array_fill_keys($codes, 0);
        $activeUsers = User::where('is_superuser', 0)->where('is_active', 1)->get();

        foreach ($activeUsers as $activeUser) {
            foreach (ModuleAccessService::forUser($activeUser) as $code) {
                if (isset($counts[$code])) {
                    $counts[$code]++;
                }
            }
        }

        return view('superuser.account.module_access.index', [
            'users'    => $users,
            'items'    => config('modules.items', []),
            'codes'    => $codes,
            'counts'   => $counts,
            'search'   => $search,
            'module'   => $module,
            'tab'      => $tab,
            'totalActive' => $activeUsers->count(),
        ]);
    }
}
