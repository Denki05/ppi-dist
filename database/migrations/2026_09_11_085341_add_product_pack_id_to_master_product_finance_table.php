<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddProductPackIdToMasterProductFinanceTable extends Migration
{
    /**
     * Normalisasi Product UV per mitra:
     * - tambah product_pack_id (FK logis ke master_products_packaging.id)
     * - tambah year (kode sudah set year, tapi kolom belum ada)
     * - cegah double via UNIQUE(product_pack_id, mitra_id)
     * - sediakan view audit long-format + pivot agar tidak parsing id string.
     */
    public function up()
    {
        Schema::table('master_product_finance', function (Blueprint $table) {
            if (! Schema::hasColumn('master_product_finance', 'product_pack_id')) {
                $table->string('product_pack_id', 50)->nullable()->after('id');
            }
            if (! Schema::hasColumn('master_product_finance', 'year')) {
                $table->year('year')->nullable()->after('buying_price_usd_unit');
            }
        });

        // Backfill untuk data lama yang id-nya masih format packId-mitraId.
        // product_pack_id = id tanpa suffix "-{mitra_id}" terakhir.
        DB::statement("
            UPDATE master_product_finance f
            JOIN master_mitra m ON m.id = f.mitra_id
            SET f.product_pack_id = CASE
                WHEN f.id LIKE CONCAT('%-', f.mitra_id)
                THEN LEFT(f.id, CHAR_LENGTH(f.id) - CHAR_LENGTH(f.mitra_id) - 1)
                ELSE f.id
            END
            WHERE f.product_pack_id IS NULL
        ");

        // Lengkapi product_pack_id yang masih null via code+packaging (tanpa parsing).
        DB::statement("
            UPDATE master_product_finance f
            JOIN master_products_packaging p ON p.code = f.code_product AND p.packaging_id = f.packaging_id
            SET f.product_pack_id = p.id
            WHERE f.product_pack_id IS NULL
        ");

        Schema::table('master_product_finance', function (Blueprint $table) {
            $table->index('product_pack_id', 'mpf_product_pack_id_idx');
            $table->unique(['product_pack_id', 'mitra_id'], 'mpf_pack_mitra_unique');
        });

        // View audit long-format: 1 row per (pack, mitra), tanpa parsing id.
        DB::statement('DROP VIEW IF EXISTS v_product_finance_audit');
        DB::statement("
            CREATE VIEW v_product_finance_audit AS
            SELECT
                f.id AS finance_id,
                f.product_pack_id,
                p.code AS code,
                p.name AS name,
                pk.pack_name AS kemasan,
                m.id AS mitra_id,
                m.name AS mitra,
                f.buying_price_usd_unit AS beli,
                f.selling_price_usd_unit AS jual,
                f.status
            FROM master_product_finance f
            JOIN master_products_packaging p ON p.id = f.product_pack_id
            JOIN master_packaging pk ON pk.id = p.packaging_id
            JOIN master_mitra m ON m.id = f.mitra_id
            WHERE f.deleted_at IS NULL
        ");

        // Perbaiki view pivot lama (join sebelumnya salah: f.product_id = p.id).
        DB::statement('DROP VIEW IF EXISTS v_product_uv_antar_mitra');
        DB::statement("
            CREATE VIEW v_product_uv_antar_mitra AS
            SELECT
                p.code AS code,
                p.name AS name,
                pk.pack_name AS kemasan,
                MAX(CASE WHEN m.name = 'UNIFRA' THEN f.buying_price_usd_unit END) AS beli_unifra,
                MAX(CASE WHEN m.name = 'UNIFRA' THEN f.selling_price_usd_unit END) AS jual_unifra,
                MAX(CASE WHEN m.name = 'CV X' THEN f.buying_price_usd_unit END) AS beli_cv_x,
                MAX(CASE WHEN m.name = 'CV X' THEN f.selling_price_usd_unit END) AS jual_cv_x,
                MAX(CASE WHEN m.name = 'ARAYA' THEN f.buying_price_usd_unit END) AS beli_araya,
                MAX(CASE WHEN m.name = 'ARAYA' THEN f.selling_price_usd_unit END) AS jual_araya
            FROM master_products_packaging p
            JOIN master_packaging pk ON pk.id = p.packaging_id
            LEFT JOIN master_product_finance f ON f.product_pack_id = p.id AND f.deleted_at IS NULL
            LEFT JOIN master_mitra m ON m.id = f.mitra_id
            WHERE p.deleted_at IS NULL
            GROUP BY p.id, p.code, p.name, pk.pack_name
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS v_product_uv_antar_mitra');
        DB::statement('DROP VIEW IF EXISTS v_product_finance_audit');

        Schema::table('master_product_finance', function (Blueprint $table) {
            $table->dropUnique('mpf_pack_mitra_unique');
            $table->dropIndex('mpf_product_pack_id_idx');
            if (Schema::hasColumn('master_product_finance', 'product_pack_id')) {
                $table->dropColumn('product_pack_id');
            }
            if (Schema::hasColumn('master_product_finance', 'year')) {
                $table->dropColumn('year');
            }
        });
    }
}
