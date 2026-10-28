<?php

namespace App\Console\Commands;

use App\Entities\Account\User;
use App\Services\MenuAccessService;
use Illuminate\Console\Command;

class SyncMenuAccess extends Command
{
    protected $signature = 'menu:sync-access {--user= : User id or username} {--force : Replace existing menu rows}';

    protected $description = 'Sinkronkan akses menu user dari profil akses dan add-on';

    public function handle()
    {
        $userId = $this->option('user');
        $force = (bool) $this->option('force');

        $query = User::where('is_superuser', 0);

        if ($userId !== null && $userId !== '') {
            $query->where(function ($query) use ($userId) {
                $query->where('id', $userId)->orWhere('username', $userId);
            });
        }

        $users = $query->orderBy('id')->get();

        if ($users->isEmpty()) {
            $this->warn('Tidak ada user yang diproses.');
            return 0;
        }

        foreach ($users as $user) {
            $profile = MenuAccessService::profileKeyForUser($user);
            $count = MenuAccessService::sync($user, $force);

            $this->line(sprintf(
                '#%s %s [%s] profile=%s rows=%s',
                $user->id,
                $user->username,
                $user->division ?: '-',
                $profile ?: '-',
                $count
            ));
        }

        return 0;
    }
}
