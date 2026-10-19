<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class RelaxHalalHelperUnique extends Migration
{
    /**
     * Unique (kode, merek, sumber) membuat semua master TANPA KODE dari merek
     * yang sama bertabrakan dalam 1 baris (nama campur aduk, mis. Aquiora vs
     * Arvelle). Dilonggarkan menjadi (kode, merek, sumber, master) agar baris
     * kode-kosong boleh satu-per-master. Baris berkode tidak terpengaruh:
     * match() tetap merge per (kode, merek, sumber) di level aplikasi.
     * Prefix index agar muat di limit 767 bytes MySQL/MariaDB lama.
     *
     * @return void
     */
    public function up()
    {
        try {
            DB::statement('ALTER TABLE halal_helpers DROP INDEX halal_helpers_kode_merek_sumber_unique');
        } catch (\Throwable $e) {
            // Belum ada (mis. DB production tanpa unique manual) — lanjut.
        }
        DB::statement('CREATE UNIQUE INDEX halal_helpers_kode_merek_sumber_master_unique ON halal_helpers (kode_produk(100), merek(50), sumber(10), master_product_id(25))');
    }

    /**
     * @return void
     */
    public function down()
    {
        try {
            DB::statement('ALTER TABLE halal_helpers DROP INDEX halal_helpers_kode_merek_sumber_master_unique');
        } catch (\Throwable $e) {
            // Abaikan.
        }
        DB::statement('CREATE UNIQUE INDEX halal_helpers_kode_merek_sumber_unique ON halal_helpers (kode_produk(100), merek(50), sumber(10))');
    }
}
