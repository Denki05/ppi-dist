<?php

namespace App\Helper;

use App\Entities\Account\Superuser;
use App\Entities\Utility\Permission;

class PermissionHelper
{
    const ACTIONS = [
        'manage', 'data', 'view', 'create', 'show', 'edit', 'delete'
    ];

    const MODULES = [
        // 'MASTER' => [

        // ],
        'ACCOUNT' => [
            'superuser',
            'salesperson'
        ],
        'UTILITY' => [
            'settings'
        ],
        'DEVELOPER' => [
            'boilerplate',
            'telescope',
            'terminal',
            'gate'
        ]
    ];

    public static function countSuperuserWithoutRole()
    {
        return Superuser::doesntHave('roles')->count();
    }

    public static function isPermissionExists($permission, $guard = 'web')
    {
        $permission = Permission::where([
            'name' => $permission,
            'guard_name' => $guard
        ])->first();

        if ($permission) {
            return true;
        } else {
            return false;
        }
    }
}