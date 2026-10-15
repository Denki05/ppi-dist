<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class AddHalalHelperIndexes extends Migration
{
    /**
     * Prefix index agar muat di limit 767 bytes (ROW_FORMAT Compact,
     * utf8mb4). Aman untuk MySQL/MariaDB lama maupun baru.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE INDEX halal_helpers_kode_merek_idx ON halal_helpers (kode_produk(100), merek(50))');
        DB::statement('CREATE INDEX halal_helpers_nama_idx ON halal_helpers (nama_produk(191))');
        DB::statement('CREATE INDEX halal_helpers_sertifikat_idx ON halal_helpers (nomor_sertifikat(191))');
        DB::statement('CREATE INDEX halal_helpers_status_idx ON halal_helpers (status_pencocokan)');
        DB::statement('CREATE INDEX halal_helpers_nomor_list_idx ON halal_helpers (nomor_urut_list_halal)');
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('halal_helpers', function ($table) {
            $table->dropIndex('halal_helpers_kode_merek_idx');
            $table->dropIndex('halal_helpers_nama_idx');
            $table->dropIndex('halal_helpers_sertifikat_idx');
            $table->dropIndex('halal_helpers_status_idx');
            $table->dropIndex('halal_helpers_nomor_list_idx');
        });
    }
}
