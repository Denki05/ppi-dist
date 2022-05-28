<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install this project';

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
        $this->comment('Checking database...');

        try {
            DB::connection()->getPdo();

            $this->info("connected to database: " . DB::connection()->getDatabaseName());
        } catch (\Exception $e) {    
            $this->error('Database not connected');
        }

        if (Schema::hasTable('migrations')) {
            if ($this->confirm('Data already exists in your database, clear & reinstall?')) {
                $this->call('migrate:reset');
            } else {
                $this->comment('Exit');
                exit;
            }
        }

        $this->call('migrate');
        $this->line('');
        $this->call('init:superuser');
    }
}
