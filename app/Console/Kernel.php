<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->command('backup:run')->daily()->at('02:00');
        
        // Arsipkan SO Indent yang sudah lebih dari 14 hari - setiap hari jam 03:00
        $schedule->command('so:archive-old-indent --days=14')->daily()->at('03:00');
        
        // Arsipkan SO Awal CASH/TEMPO yang masih AWAL setelah 7 hari - setiap hari jam 03:30
        $schedule->command('so:archive-old-awal --days=7')->daily()->at('03:30');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
