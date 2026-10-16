<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\HalalHelper;

/**
 * Seed awal tabel bantuan dari master_products (langkah 1-2 dokumen).
 *
 * Satu produk master = satu baris helper, status awal "Tidak cocok",
 * search_keyword terisi agar landing langsung bisa mencari.
 * Aman dijalankan ulang (updateOrCreate per kode+merek).
 *
 *   php artisan halal:seed-master
 */
class SeedHalalMaster extends Command
{
    protected $signature = 'halal:seed-master
                            {--tanggal-master= : Tanggal versi master (default: hari ini, format Y-m-d)}';

    protected $description = 'Isi tabel bantuan halal_helpers dari master_products (status awal Tidak cocok)';

    public function handle()
    {
        $tanggal = $this->option('tanggal-master') ?: date('Y-m-d');

        $rows = DB::table('master_products')
            ->select('id', 'brand_name', 'code', 'name')
            ->orderBy('brand_name')
            ->orderBy('code')
            ->get();

        if ($rows->isEmpty()) {
            $this->error('master_products kosong.');
            return 1;
        }

        $seen = [];
        $inserted = 0;
        $skipped = 0;

        foreach ($rows as $r) {
            $kode = trim((string) $r->code);
            $merek = trim((string) $r->brand_name);
            $nama = trim((string) $r->name);

            // Lewati baris sampah: kode/nama kosong.
            if ($kode === '' || $nama === '') {
                $skipped++;
                continue;
            }

            // Tandai baris ganda (kombinasi merek+kode sama): pakai baris pertama.
            $key = mb_strtolower($merek . '|' . $kode);
            if (isset($seen[$key])) {
                $skipped++;
                continue;
            }
            $seen[$key] = true;

            HalalHelper::updateOrCreate(
                ['kode_produk' => $kode, 'merek' => $merek],
                [
                    'master_product_id' => $r->id,
                    'nama_produk' => $nama,
                    'status_pencocokan' => HalalHelper::STATUS_TIDAK,
                    'status_pdf' => HalalHelper::PDF_BELUM,
                    'tanggal_master' => $tanggal,
                    'search_keyword' => HalalHelper::normalizeKeyword($merek, $nama, $kode),
                ]
            );
            $inserted++;
        }

        $this->info("Selesai: {$inserted} baris helper, {$skipped} baris sampah/ganda dilewati.");
        return 0;
    }
}
