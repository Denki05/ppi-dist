<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use App\Services\Halal\HalalSgImporter;

/**
 * Import SH Singapore (MUIS) dari link QR + pencocokan ke tabel bantuan (sumber SG).
 *
 *   php artisan halal:import-sg "https://halal.muis.gov.sg/qr?n=..." --tanggal-list=2026-04-10
 */
class ImportHalalSg extends Command
{
    protected $signature = 'halal:import-sg
                            {qr : URL QR MUIS}
                            {--tanggal-list= : Tanggal versi list (Y-m-d, default hari ini)}
                            {--tanggal-master= : Tanggal versi master (Y-m-d, default hari ini)}';

    protected $description = 'Import QR MUIS Singapore lalu jalankan pencocokan (sumber SG)';

    public function handle()
    {
        $importer = new HalalSgImporter();
        $tList = $this->option('tanggal-list') ?: date('Y-m-d');
        $tMaster = $this->option('tanggal-master') ?: date('Y-m-d');

        $this->info('1/2 Import QR MUIS...');
        try {
            $r = $importer->importQr($this->argument('qr'), $tList, Auth::id());
        } catch (\Exception $e) {
            $this->error('Gagal import QR: ' . $e->getMessage());
            return 1;
        }
        $this->info("    {$r['total']} produk SG, sertifikat: {$r['cert']}, expired: {$r['expired']}");

        $this->info('2/2 Pencocokan master -> list SG...');
        $stats = $importer->match(
            $r['doc']->id, null, $r['cert'], $tMaster, $tList, null, $r['expired'], $r['doc']->nama_perusahaan
        );
        foreach ($stats as $k => $v) {
            $this->info("    {$k}: {$v}");
        }

        return 0;
    }
}
