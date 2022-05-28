<?php

namespace App\Console\Commands\Init;

use Illuminate\Support\Facades\Artisan;
use App\Entities\Account\Superuser as SuperuserEntities;
use App\Helper\PermissionHelper;
use Illuminate\Console\Command;

class Superuser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:superuser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeding superuser role and account';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Initialize superuser credentials');
        
        $this->comment('Seeding superuser accounts');
        Artisan::call('db:seed --class=Superuser');
        $this->info('OK');

        $this->comment('Seeding superuser roles');
        Artisan::call('db:seed --class=SuperuserRole');
        $this->info('OK');

        $this->comment('Seeding superuser permissions');
        Artisan::call('db:seed --class=SuperuserPermission');
        $this->info('OK');

        $this->comment('Assign roles & permission to superuser accounts');
        $superuser = SuperuserEntities::get();

        foreach ($superuser as $person) {
            if ($person->username == 'developer') {
                $person->assignRole('Developer');
                $person->assignRole('SuperAdmin');
                $person->assignRole('Admin');

                foreach (PermissionHelper::MODULES['DEVELOPER'] as $module) {
                    $person->givePermissionTo($module . '-manage');
                }
            }

            if ($person->username == 'admin') {
                $person->assignRole('SuperAdmin');
                $person->assignRole('Admin');
            }
        }
        $this->info('OK');

    }
}
