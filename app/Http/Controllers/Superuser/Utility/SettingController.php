<?php

namespace App\Http\Controllers\Superuser\Utility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Validator;
use DB;
use Auth;
use App;

class SettingController extends Controller
{
    public function index()
    {
        return view('superuser.utility.settings');
    }

    public function website(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'maintenance' => 'nullable',
                'maintenance_message' => 'nullable|string'
            ]);
  
            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];
  
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                setting([
                    'website.name' => $request->name,
                    'website.maintenance' => isset($request->maintenance),
                    'website.maintenance_message' => $request->maintenance_message,
                    'website.color_themes' => $request->color_themes
                ]);

                setting()->save();

                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Setting:Website updated',
                ];

                $response['redirect_to'] = 'reload()';

                return $this->response(200, $response);
            }
        }
    }

    public function enableMaintenanceMode()
    {
        // Enable maintenance mode
        Artisan::call('down', ['--message' => 'Situs sedang dalam pemeliharaan. Silakan coba lagi nanti!']);

        // return response()->json(['message' => 'Maintenance mode enabled'], 200);
        $response['notification'] = [
            'alert' => 'notify',
            'type' => 'success',
            'content' => 'Maintenance mode enabled',
        ];

        $response['redirect_to'] = 'reload()';

        return $this->response(200, $response);
    }

    public function disableMaintenanceMode()
    {
        // Disable maintenance mode
        Artisan::call('up');

        // return response()->json(['message' => 'Maintenance mode disabled'], 200);
        $response['notification'] = [
            'alert' => 'notify',
            'type' => 'success',
            'content' => 'Maintenance mode disabled',
        ];

        $response['redirect_to'] = 'reload()';

        return $this->response(200, $response);
    }

    public function backupDatabase()
    {
        try {
            // Create a backup job
            $backupJob = BackupJobFactory::createFromArray(config('backup'));
            
            // Set the backup destination
            $backupDestinations = BackupDestinationFactory::createFromArray(config('backup.destinations'));

            foreach ($backupDestinations as $backupDestination) {
                $backupJob->setBackupDestination($backupDestination);
            }

            // Start the backup process
            $backupJob->run();

            $response['notification'] = [
                'alert' => 'notify',
                'type' => 'success',
                'content' => 'Backup DB Success',
            ];
    
            $response['redirect_to'] = 'reload()';
    
            return $this->response(200, $response);
        } catch (\Exception $e) {
            $response['notification'] = [
                'alert' => 'block',
                'type' => 'alert-danger',
                'header' => 'Error',
                'content' => 'Backup DB Failed!',
            ];

            return $this->response(500, $response);
        }
    }
}