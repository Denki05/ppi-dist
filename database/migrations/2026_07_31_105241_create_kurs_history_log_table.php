<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKursHistoryLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kurs_history_log', function (Blueprint $table) {
            $table->id();
            $table->decimal('kurs_lama', 15, 2)->nullable();
            $table->decimal('kurs_baru', 15, 2);
            $table->unsignedBigInteger('diubah_oleh');
            $table->timestamp('waktu');
            $table->text('alasan')->nullable();
            $table->string('referensi')->nullable()->comment('kode DO/nota terkait, jika ada');
            $table->timestamps();

            $table->index('referensi');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kurs_history_log');
    }
}
