<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use App\HalalDocument;
use App\Services\Halal\HalalMatcher;

/**
 * Pipeline penuh langkah 1-7 dokumen:
 * simpan file sumber -> import list -> import PDF -> cocokkan -> tabel bantuan.
 *
 *   php artisan halal:import-sumber "storage/app/halal/inbox/List Produk Halal - ARAYA.xlsx" "storage/app/halal/inbox/SERTIFIKAT HALAL - KMI.pdf"
 *   php artisan halal:import-sumber "path/list.xlsx" "path/sertifikat.pdf" --tanggal-list=2026-10-13 --tanggal-pdf=2026-07-09
 */
class ImportHalalSumber extends Command
{
    protected $signature = 'halal:import-sumber
                            {excel : Path file Excel list halal}
                            {pdf : Path file PDF resmi}
                            {--tanggal-list= : Tanggal versi list (Y-m-d, default hari ini)}
                            {--tanggal-pdf= : Tanggal versi PDF (Y-m-d, default hari ini)}
                            {--tanggal-master= : Tanggal versi master (Y-m-d, default hari ini)}';

    protected $description = 'Import Excel list halal + PDF resmi lalu jalankan pencocokan ke tabel bantuan';

    public function handle()
    {
        $matcher = new HalalMatcher();
        $tList = $this->option('tanggal-list') ?: date('Y-m-d');
        $tPdf = $this->option('tanggal-pdf') ?: date('Y-m-d');
        $tMaster = $this->option('tanggal-master') ?: date('Y-m-d');

        foreach (['excel' => $this->argument('excel'), 'pdf' => $this->argument('pdf')] as $k => $p) {
            if (!is_file($p)) {
                $this->error("File {$k} tidak ditemukan: {$p}");
                return 1;
            }
        }

        $userId = Auth::id();
        $this->info('1/3 Import list halal...');
        $docList = HalalDocument::create([
            'jenis' => HalalDocument::JENIS_LIST,
            'file_path' => $this->argument('excel'),
            'original_name' => basename($this->argument('excel')),
            'tanggal_versi' => $tList,
            'created_by' => $userId,
        ]);
        $rList = $matcher->importList($this->argument('excel'), $docList->id, $tList);
        $docList->update(['total_baris' => $rList['total']]);
        $this->info("    {$rList['total']} baris list, sampah/judul dilewati: " . count($rList['junk']));

        $this->info('2/3 Import PDF resmi...');
        $docPdf = HalalDocument::create([
            'jenis' => HalalDocument::JENIS_PDF,
            'file_path' => $this->argument('pdf'),
            'original_name' => basename($this->argument('pdf')),
            'tanggal_versi' => $tPdf,
            'created_by' => $userId,
        ]);
        $rPdf = $matcher->importPdf($this->argument('pdf'), $docPdf->id);
        $docPdf->update(['total_baris' => $rPdf['total'], 'nomor_sertifikat' => $rPdf['cert']]);
        $this->info("    {$rPdf['total']} produk di {$rPdf['pages']} halaman, sertifikat: {$rPdf['cert']}");

        $this->info('3/3 Pencocokan master -> list -> PDF...');
        $stats = $matcher->match($docList->id, $docPdf->id, $rPdf['cert'], $tMaster, $tList, $tPdf);
        foreach ($stats as $k => $v) {
            $this->info("    {$k}: {$v}");
        }
        return 0;
    }
}
