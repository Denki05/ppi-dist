<?php

namespace App\Http\Controllers\Superuser;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class BackupController extends Controller
{
    public function index()
    {
        return view('superuser.backup.index');
    }

    public function createBackup()
    {
        try {
            // Backup DB SAJA (tanpa file project).
            // --only-db => Spatie memanggil dontBackupFilesystem().
            Artisan::call('backup:run', [
                '--only-db' => true,
                '--disable-notifications' => true,
            ]);

            // Get the output from the command for debugging
            $output = Artisan::output();

            return response()->json([
                'message' => 'Backup created successfully',
                'output' => $output
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Backup failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to create backup',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
