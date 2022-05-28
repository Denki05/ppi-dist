<?php

use Illuminate\Database\Seeder;

class SuperuserRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            [
                'name'       => 'Developer',
                'guard_name' => 'superuser',
            ],
            [
                'name'       => 'SuperAdmin',
                'guard_name' => 'superuser',
            ],
            [
                'name'       => 'Admin',
                'guard_name' => 'superuser',
            ]
        ]);
    }
}
