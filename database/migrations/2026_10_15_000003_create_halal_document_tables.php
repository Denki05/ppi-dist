<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHalalDocumentTables extends Migration
{
    /**
     * Versi sumber agar user bisa upload ulang sendiri dari halaman SH Produk:
     * - halal_documents : tiap file Excel list / PDF resmi yang diupload.
     * - halal_pdf_items : isi PDF per halaman (nomor, nama, halaman fisik).
     * - halal_lists.document_id : list dari dokumen versi mana.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('halal_documents')) {
            Schema::create('halal_documents', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('jenis', 10)->comment('list | pdf');
                $table->string('file_path', 255);
                $table->string('original_name', 255);
                $table->date('tanggal_versi')->comment('Tanggal versi sumber');
                $table->string('nomor_sertifikat', 255)->nullable();
                $table->integer('total_baris')->unsigned()->default(0);
                $table->integer('created_by')->nullable();
                $table->timestamps();

                $table->index('jenis');
            });
        }

        if (!Schema::hasTable('halal_pdf_items')) {
            Schema::create('halal_pdf_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('document_id');
                $table->integer('nomor_urut')->unsigned()->comment('Nomor di PDF resmi');
                $table->text('nama_produk');
                $table->integer('halaman')->unsigned()->comment('Halaman fisik file PDF');
                $table->timestamps();

                $table->index(['document_id', 'nomor_urut']);
            });
        }

        if (Schema::hasTable('halal_lists') && !Schema::hasColumn('halal_lists', 'document_id')) {
            Schema::table('halal_lists', function (Blueprint $table) {
                $table->unsignedBigInteger('document_id')->nullable()->after('id');
                $table->index('document_id');
            });
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('halal_lists') && Schema::hasColumn('halal_lists', 'document_id')) {
            Schema::table('halal_lists', function (Blueprint $table) {
                $table->dropIndex(['document_id']);
                $table->dropColumn('document_id');
            });
        }
        Schema::dropIfExists('halal_pdf_items');
        Schema::dropIfExists('halal_documents');
    }
}
