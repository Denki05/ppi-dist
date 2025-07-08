<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Penjualan\ImportSoHeader;
use App\Imports\Penjualan\ImportSoList;

class MigrasiImportController extends Controller
{
    public function import(Request $request)
    {
        try {
            if ($request->hasFile('header')) {
                Excel::import(new ImportSoHeader, $request->file('header'));
            }

            if ($request->hasFile('list')) {
                Excel::import(new ImportSoList, $request->file('list'));
            }

            return back()->with('success', 'Import berhasil.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }
}