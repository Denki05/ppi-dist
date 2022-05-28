<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Module extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module 
                            {name : Module name you want to use.}
                            {--namespace= : Namespace option.}
                            {--ie : Import/Export option.}
                            {--migration= : Create migration (table name).}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module';

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
        $name = $this->argument('name');
        $migration = $this->option('migration');
        $namespace = $this->option('namespace');
        $ie = $this->option('ie');

        $this->info('Creating ' . $name . ' module.');

        if ($namespace != null) {
            $this->info('with namespace: ' . $namespace);
        }

        if ($migration != null) {
            $this->info('with migration table: ' . $migration);
            $this->info('create_' . $migration . '_table');
        }

        $this->info('...');

        if ($migration != null) {
            $this->info('Migration : create_' . $migration . '_table');
            Artisan::call('make:migration create_' . $migration . '_table');
        }

        $controller = ($namespace == null) ? $name . 'Controller' : $namespace . '/'. $name . 'Controller';
        $this->info('Controller : ' . $controller);
        Artisan::call('make:controller ' . $controller);
        
        $model = 'Entities/' . $name;
        $this->info('Model : ' . $model);
        Artisan::call('make:model ' . $model);

        $datatable = $name . 'Table';
        $this->info('DataTable : ' . $datatable);
        Artisan::call('make:datatable ' . $datatable);

        if ($ie != null) {
            $import = $name . 'Import';
            $this->info('Import : ' . $import);
            Artisan::call('make:import ' . $import);

            $import_template = $name . 'ImportTemplate';
            $this->info('Export : ' . $import_template);
            Artisan::call('make:export ' . $import_template);

            $export = $name . 'Export';
            $this->info('Export : ' . $export);
            Artisan::call('make:export ' . $export);
        }
    }
}
