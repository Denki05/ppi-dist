<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProformaCancelLogTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('proforma_cancel_log')) {
            return;
        }

        Schema::create('proforma_cancel_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Referensi pengajuan asal
            $table->unsignedBigInteger('pengajuan_proforma_id')->nullable()->index();
            $table->string('estimate_number', 100)->nullable()->index();
            // Data customer yang dibatalkan (snapshot untuk audit)
            $table->string('customer_name', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('ktp', 20)->nullable();
            $table->string('npwp', 25)->nullable();
            $table->string('ao_pic', 100)->nullable()->index();
            // Referensi ke customer existing yang sudah dimutasi (untuk di-rollback/hapus)
            $table->unsignedBigInteger('customer_id')->nullable()
                ->comment('ID master_customers yang sudah dibuat saat mutasi');
            $table->string('member_id', 50)->nullable()
                ->comment('ID master_customer_other_addresses yang sudah dibuat saat mutasi');
            // Ringkasan proforma
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->string('proforma_code', 100)->nullable();
            // Siapa yang cancel dan kapan
            $table->string('cancelled_by', 100)->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('alasan')->nullable();
            // Apakah customer sudah dihapus dari existing?
            $table->boolean('customer_rolled_back')->default(false);
            $table->timestamps();

            $table->index(['customer_name', 'phone']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('proforma_cancel_log');
    }
}
