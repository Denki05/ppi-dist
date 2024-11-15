<?php

namespace App\Http\Controllers\Superuser;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        // Get list of backup files
        $backupFiles = Storage::disk('local')->files('Laravel');

        return view('superuser.backup.index', ['backupFiles' => $backupFiles]);
    }

    public function createBackup()
    {
        try {
            // Run the backup command
            Artisan::call('backup:run');

            // Get the output from the command for debugging
            $output = Artisan::output();

            return response()->json([
                'message' => 'Backup created successfully',
                'output' => $output
            ], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'error' => 'Failed to create backup',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function download($fileName)
    {
        // Ensure file exists
        if (Storage::disk('local')->exists("Laravel/$fileName")) {
            return Storage::disk('local')->download("Laravel/$fileName");
        }

        return redirect()->back()->withErrors('File not found.');
    }
}
