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

    public function toggleMaintenanceMode(Request $request)
    {
        if (app()->isDownForMaintenance()) {
            // Jika dalam mode maintenance, maka matikan
            Artisan::call('up');
            $message = 'Maintenance mode disabled';
        } else {
            // Laravel 6: php artisan down hanya mendukung
            // --message, --retry, --allow (tidak ada --render).
            // Tampilan custom errors/503.blade.php otomatis dipakai
            // lewat abort(503) di CheckForMaintenanceMode.
            $customMessage = $request->input('message')
                ?: 'Kami sedang dalam perbaikan. Coba lagi nanti!';

            Artisan::call('down', [
                '--message' => $customMessage,
            ]);
            $message = 'Maintenance mode enabled';
        }

        $response['notification'] = [
            'alert' => 'notify',
            'type' => 'success',
            'content' => $message,
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

    /**
     * Dipolling oleh JS di semua halaman superuser.
     * Harus tetap bisa diakses saat maintenance ON
     * (lihat $except di CheckForMaintenanceMode).
     *
     * Admin (Developer/SuperAdmin) selalu dijawab down=false
     * agar tidak ikut kena popup/auto-logout.
     */
    public function maintenanceStatus()
    {
        $user = Auth::guard('superuser')->user();

        if ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Developer', 'SuperAdmin'])) {
            return response()->json([
                'down' => false,
                'bypass' => true,
                'message' => 'Anda login sebagai admin, maintenance tidak berlaku.',
                'timestamp' => now()->toDateTimeString(),
            ]);
        }

        $down = app()->isDownForMaintenance();

        $message = 'Sistem sedang dalam pemeliharaan. Silakan coba lagi nanti!';
        try {
            $data = json_decode(@file_get_contents(storage_path('framework/down')), true);
            if (!empty($data['message'])) {
                $message = $data['message'];
            }
        } catch (\Throwable $e) {
            // pakai pesan default
        }

        return response()->json([
            'down' => $down,
            'bypass' => false,
            'message' => $message,
            'logout_url' => route('superuser.logout'),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}