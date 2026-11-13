<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WidenNoteOnMasterProductsTables extends Migration
{
    public function up()
    {
        Schema::table('master_products', function (Blueprint $table) {
            $table->text('note')->nullable()->change();
        });

        Schema::table('master_products_packaging', function (Blueprint $table) {
            $table->text('note')->nullable()->change();
        });

        // Bersihkan nilai placeholder dari form lama yang tersimpan sebagai note.
        DB::table('master_products')
            ->whereRaw('UPPER(TRIM(note)) = ?', ['PILIH NOTE'])
            ->update(['note' => null]);

        DB::table('master_products_packaging')
            ->whereRaw('UPPER(TRIM(note)) = ?', ['PILIH NOTE'])
            ->update(['note' => null]);
    }

    public function down()
    {
        Schema::table('master_products', function (Blueprint $table) {
            $table->string('note')->nullable()->change();
        });

        Schema::table('master_products_packaging', function (Blueprint $table) {
            $table->string('note')->nullable()->change();
        });
    }
}
