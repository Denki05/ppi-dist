<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHalalHelperTables extends Migration
{
    /**
     * Tabel baru terpisah dari cms_*.
     * - halal_lists   : staging list halal araya mentah (725 baris, bernomor).
     * - halal_helpers : tabel bantuan = kata kunci landing halal.lsfragrance.id.
     * Portable untuk DB lokal (ppi_unifra) maupun production
     * (ppi_araya / lssoftxy_unifra) karena ikut koneksi default via .env.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('halal_lists')) {
            Schema::create('halal_lists', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('nomor_urut')->unsigned()->comment('Nomor 1-725 di list halal');
                $table->text('nama_lengkap')->comment('Penulisan lengkap di list halal, merek di depan');
                $table->string('brand_parsed', 100)->nullable()->comment('Merek hasil parsing langkah 3');
                $table->string('nama_parsed', 255)->nullable();
                $table->string('kode_parsed', 255)->nullable();
                $table->string('sumber_version', 50)->nullable()->comment('Tanggal/versi list halal, cth 2026-10-13');
                $table->timestamps();

                $table->unique('nomor_urut');
                $table->index('brand_parsed');
            });
        }

        if (!Schema::hasTable('halal_helpers')) {
            Schema::create('halal_helpers', function (Blueprint $table) {
                $table->bigIncrements('id');
                // Link logis ke master_products.id (varchar 50, isinya = code).
                // Nullable + tanpa FK agar tetap portable antar DB.
                $table->string('master_product_id', 50)->nullable();
                $table->string('kode_produk', 255)->comment('Kode di master');
                $table->string('merek', 225)->comment('Merek di master');
                $table->string('nama_produk', 255)->comment('Nama di master');
                $table->text('nama_di_list_halal')->nullable();
                $table->integer('nomor_urut_list_halal')->unsigned()->nullable();
                $table->integer('halaman_pdf')->unsigned()->nullable();
                $table->integer('nomor_urut_pdf')->unsigned()->nullable();
                // Hidden id sertifikasi untuk landing page (kolom tambahan, bukan kunci cocok).
                $table->string('nomor_sertifikat', 255)->nullable();
                $table->string('status_pencocokan', 20)->default('Tidak cocok')
                    ->comment('Cocok | Perlu ditinjau | Tidak cocok');
                $table->string('kunci_yang_dipakai', 50)->nullable()
                    ->comment('Kode | merek+nama | nama saja');
                $table->string('status_pdf', 30)->default('belum')
                    ->comment('Terverifikasi PDF | belum');
                $table->text('catatan_tinjauan')->nullable();
                $table->date('tanggal_master')->nullable();
                $table->date('tanggal_list')->nullable();
                $table->date('tanggal_pdf')->nullable();
                // Keyword normalisasi (lowercase, tanpa aksen/spasi ganda) untuk LIKE cepat.
                $table->text('search_keyword')->nullable();
                $table->timestamps();

                // Index dibuat di migration terpisah (prefix index, muat di
                // limit 767 bytes MySQL/MariaDB lama). Lihat
                // 2026_10_15_000002_add_halal_helper_indexes.php
            });
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('halal_helpers');
        Schema::dropIfExists('halal_lists');
    }
}
