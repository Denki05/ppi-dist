<?php

use Illuminate\Database\Seeder;

class Superuser extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('superusers')->insert([
            [
                'username'     => 'developer',
                'email'        => 'contact.willystwn@gmail.com',
                'password'     => bcrypt('12345678'),
            ],
            [
                'username'     => 'admin',
                'email'        => 'admin@email.com',
                'password'     => bcrypt('12345678'),
            ]
        ]);
    }
}
