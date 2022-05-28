<?php

use App\Helper\PermissionHelper;
use Illuminate\Database\Seeder;

class SuperuserPermission extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permission = [];

        foreach (PermissionHelper::MODULES['DEVELOPER'] as  $module) {
            array_push($permission, [
                'name'       => $module . '-manage',
                'guard_name' => 'superuser',
            ]);
        }

        DB::table('permissions')->insert($permission);
    }
}
