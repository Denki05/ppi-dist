<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DevHeartbeat extends Command
{
    protected $signature = 'dev:heartbeat';

    protected $description = 'Catat heartbeat scheduler untuk Health Check';

    public function handle()
    {
        $exists = DB::table('dev_heartbeats')->where('name', 'scheduler')->exists();

        if ($exists) {
            DB::table('dev_heartbeats')->where('name', 'scheduler')->update([
                'last_run_at' => now(),
                'note' => php_uname('n'),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('dev_heartbeats')->insert([
                'name' => 'scheduler',
                'last_run_at' => now(),
                'note' => php_uname('n'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return 0;
    }
}
