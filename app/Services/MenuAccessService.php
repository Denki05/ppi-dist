<?php

namespace App\Services;

use Auth;
use DB;

class MenuAccessService
{
    protected static $permissionCache = [];
    protected static $menuKeys = null;

    public static function menuKeys()
    {
        if (self::$menuKeys === null) {
            self::$menuKeys = DB::table('setting_menu')
                ->orderByRaw('LENGTH(route_name) DESC')
                ->pluck('route_name')
                ->all();
        }

        return self::$menuKeys;
    }

    public static function menuKeyForRoute($routeName)
    {
        $routeName = trim((string) $routeName);

        if ($routeName === '') {
            return null;
        }

        foreach (config('access_matrix.route_aliases', []) as $alias => $key) {
            if ($routeName === $alias || strpos($routeName, $alias . '.') === 0) {
                return $key;
            }
        }

        foreach (self::menuKeys() as $key) {
            if ($routeName === $key || strpos($routeName, $key . '.') === 0) {
                return $key;
            }
        }

        return null;
    }

    public static function profileKey($division, $jabatan)
    {
        $division = trim((string) $division);
        $jabatan = strtolower(trim((string) $jabatan));

        foreach (config('access_matrix.overrides', []) as $override) {
            if (!empty($override['division']) && $override['division'] !== $division) {
                continue;
            }

            foreach (($override['jabatan_contains'] ?? []) as $needle) {
                $needle = strtolower(trim((string) $needle));
                if ($needle !== '' && strpos($jabatan, $needle) !== false) {
                    return $override['profile'];
                }
            }
        }

        $profiles = config('access_matrix.profiles', []);

        return isset($profiles[$division]) ? $division : null;
    }

    public static function profileKeyForUser($user)
    {
        if (!$user) {
            return null;
        }

        $profiles = config('access_matrix.profiles', []);
        $selected = trim((string) ($user->access_profile ?? ''));

        if ($selected !== '' && isset($profiles[$selected])) {
            return $selected;
        }

        return self::profileKey($user->division ?? '', $user->jabatan ?? '');
    }

    public static function profileForUser($user)
    {
        $key = self::profileKeyForUser($user);

        return $key !== null ? config('access_matrix.profiles.' . $key) : null;
    }

    public static function addonKeys($user)
    {
        if (!$user) {
            return [];
        }

        $raw = $user->access_addons ?? [];

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($raw)) {
            return [];
        }

        return array_values(array_intersect(
            array_map('strval', $raw),
            array_keys(config('access_matrix.addons', []))
        ));
    }

    public static function capabilities($user)
    {
        if (!$user) {
            return [];
        }

        if ((int) ($user->is_superuser ?? 0) === 1) {
            return ['*'];
        }

        $profileKey = self::profileKeyForUser($user);
        $capabilities = $profileKey !== null
            ? (array) config('access_matrix.profile_capabilities.' . $profileKey, [])
            : [];

        foreach (self::addonKeys($user) as $addonKey) {
            $addonCapabilities = (array) config('access_matrix.addons.' . $addonKey . '.capabilities', []);
            $capabilities = array_merge($capabilities, $addonCapabilities);
        }

        return array_values(array_unique(array_filter($capabilities)));
    }

    public static function hasCapability($user, $capability)
    {
        if (!$user) {
            return false;
        }

        if ((int) ($user->is_superuser ?? 0) === 1) {
            return true;
        }

        $capabilities = self::capabilities($user);

        return in_array('*', $capabilities, true)
            || in_array($capability, $capabilities, true);
    }

    public static function effectivePermissions($user)
    {
        $profileKey = self::profileKeyForUser($user);

        if ($profileKey === null) {
            return [];
        }

        $permissions = config('access_matrix.profiles.' . $profileKey, []);

        foreach (self::addonKeys($user) as $addonKey) {
            $addonPermissions = config('access_matrix.addons.' . $addonKey . '.permissions', []);

            foreach ($addonPermissions as $routeName => $flags) {
                if (!is_array($flags)) {
                    continue;
                }

                if (!isset($permissions[$routeName]) || !is_array($permissions[$routeName])) {
                    $permissions[$routeName] = $flags;
                    continue;
                }

                $merged = $permissions[$routeName];
                foreach (array_values($flags) as $index => $flag) {
                    $merged[$index] = max((int) ($merged[$index] ?? 0), (int) $flag);
                }
                $permissions[$routeName] = $merged;
            }
        }

        return $permissions;
    }

    public static function permissions($user)
    {
        if (!$user || empty($user->id)) {
            return [];
        }

        $id = (int) $user->id;

        if ((int) ($user->is_superuser ?? 0) === 1) {
            return ['*' => [1, 1, 1, 1, 1, 1]];
        }

        if (array_key_exists($id, self::$permissionCache)) {
            return self::$permissionCache[$id];
        }

        $rows = DB::table('setting_user_menu as um')
            ->join('setting_menu as m', 'm.id', '=', 'um.menu_id')
            ->where('um.user_id', $id)
            ->select(
                'm.route_name',
                'um.can_read',
                'um.can_create',
                'um.can_update',
                'um.can_delete',
                'um.can_approve',
                'um.can_print'
            )
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $result[$row->route_name] = [
                (int) $row->can_read,
                (int) $row->can_create,
                (int) $row->can_update,
                (int) $row->can_delete,
                (int) $row->can_approve,
                (int) $row->can_print,
            ];
        }

        self::$permissionCache[$id] = $result;

        return $result;
    }

    public static function stageCapabilityForRoute($routeName)
    {
        $routeName = trim((string) $routeName);

        $routes = [
            'superuser.penjualan.sales_order.index_lanjutan' => 'so_lanjutan',
            'superuser.penjualan.sales_order.lanjutkan' => 'so_lanjutan',
            'superuser.penjualan.sales_order.destroy_lanjutan' => 'so_lanjutan',
            'superuser.penjualan.sales_order.tutup_so' => 'so_lanjutan',
            'superuser.penjualan.sales_order.tidak_lanjut_so' => 'so_lanjutan',
            'superuser.penjualan.sales_order_ppn.index_ppn_lanjutan' => 'ppn_lanjutan',
            'superuser.penjualan.sales_order_ppn.lanjutkan' => 'ppn_lanjutan',
            'superuser.penjualan.sales_order_ppn.destroy_lanjutan' => 'ppn_lanjutan',
            'superuser.penjualan.sales_order_ppn.tutup_so_ppn' => 'ppn_lanjutan',
        ];

        return $routes[$routeName] ?? null;
    }

    public static function allows($user, $routeName, $ability = 'read')
    {
        if (!$user) {
            return false;
        }

        if ((int) ($user->is_superuser ?? 0) === 1) {
            return true;
        }

        $index = array_search($ability, ['read', 'create', 'update', 'delete', 'approve', 'print'], true);
        if ($index === false) {
            return false;
        }

        $key = self::menuKeyForRoute($routeName);
        if ($key === null) {
            return false;
        }

        $requiredCapability = self::stageCapabilityForRoute($routeName);
        if ($requiredCapability !== null && !self::hasCapability($user, $requiredCapability)) {
            return false;
        }

        $permissions = self::permissions($user);

        return !empty($permissions[$key][$index]);
    }

    public static function canSeeRoute($user, $routeName)
    {
        return self::allows($user, $routeName, 'read');
    }

    public static function canAnyRoute($user, array $routeNames)
    {
        if ($user && (int) ($user->is_superuser ?? 0) === 1) {
            return true;
        }

        foreach ($routeNames as $routeName) {
            if (self::canSeeRoute($user, $routeName)) {
                return true;
            }
        }

        return false;
    }

    public static function canSeeGroup($user, $group)
    {
        if (!$user) {
            return false;
        }

        if ((int) ($user->is_superuser ?? 0) === 1) {
            return true;
        }

        foreach (config('access_matrix.groups.' . $group, []) as $routeName) {
            if (self::canSeeRoute($user, $routeName)) {
                return true;
            }
        }

        return false;
    }

    public static function sync($user, $force = true)
    {
        if (!$user || empty($user->id) || (int) ($user->is_superuser ?? 0) === 1) {
            return 0;
        }

        $profileKey = self::profileKeyForUser($user);
        if ($profileKey === null) {
            return 0;
        }

        $permissions = self::effectivePermissions($user);
        $menuIds = DB::table('setting_menu')->pluck('id', 'route_name');
        $flagsByRoute = [];

        if (isset($permissions['*']) && is_array($permissions['*'])) {
            foreach ($menuIds as $routeName => $menuId) {
                $flagsByRoute[$routeName] = $permissions['*'];
            }
        }

        foreach ($permissions as $routeName => $flags) {
            if ($routeName === '*' || !is_array($flags) || !isset($menuIds[$routeName])) {
                continue;
            }

            $flagsByRoute[$routeName] = $flags;
        }

        if ($force) {
            DB::table('setting_user_menu')->where('user_id', $user->id)->delete();
        }

        $now = date('Y-m-d H:i:s');
        $createdBy = Auth::id();
        $count = 0;

        foreach ($flagsByRoute as $routeName => $flags) {
            if (!isset($menuIds[$routeName])) {
                continue;
            }

            $flags = array_values(array_map('intval', array_slice($flags, 0, 6)));
            while (count($flags) < 6) {
                $flags[] = 0;
            }

            if (array_sum($flags) === 0) {
                continue;
            }

            $row = [
                'user_id' => $user->id,
                'menu_id' => $menuIds[$routeName],
                'can_read' => $flags[0],
                'can_create' => $flags[1],
                'can_update' => $flags[2],
                'can_delete' => $flags[3],
                'can_approve' => $flags[4],
                'can_print' => $flags[5],
                'updated_at' => $now,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ];

            if ($force) {
                $row['created_at'] = $now;
                DB::table('setting_user_menu')->insert($row);
            } else {
                $query = DB::table('setting_user_menu')
                    ->where('user_id', $user->id)
                    ->where('menu_id', $menuIds[$routeName]);

                if ($query->exists()) {
                    $query->update($row);
                } else {
                    $row['created_at'] = $now;
                    DB::table('setting_user_menu')->insert($row);
                }
            }

            $count++;
        }

        self::flush($user->id);

        return $count;
    }

    public static function flush($userId = null)
    {
        if ($userId === null) {
            self::$permissionCache = [];
            return;
        }

        unset(self::$permissionCache[(int) $userId]);
    }
}
