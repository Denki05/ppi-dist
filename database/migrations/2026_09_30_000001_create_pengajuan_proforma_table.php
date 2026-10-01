<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengajuanProformaTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pengajuan_proforma')) {
            return;
        }

        Schema::create('pengajuan_proforma', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Kunci idempoten dari AO (nomor estimate)
            $table->string('estimate_number', 100)->unique();
            $table->string('ao_pic', 100)->nullable()->index();
            // Customer
            $table->string('prospect_name', 255);
            $table->string('perusahaan', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            // Dokumen (angka saja di v1; foto menyusul di P4)
            $table->string('ktp', 20);
            $table->string('npwp', 25)->nullable();
            $table->string('termin', 20)->default('CASH');
            $table->boolean('foto_ktp_ada')->default(false);
            $table->boolean('foto_npwp_ada')->default(false);
            // Ringkasan estimate
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->integer('items_count')->default(0);
            $table->text('items_json')->nullable();
            $table->timestamp('submitted_at')->nullable();
            // Status verifikasi: menunggu | disetujui | ditolak
            $table->string('status', 20)->default('menunggu')->index();
            $table->text('catatan')->nullable();
            $table->string('verified_by', 100)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengajuan_proforma');
    }
}
