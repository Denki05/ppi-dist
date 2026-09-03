<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            $table->string('estimate_code', 20)->nullable()->after('so_code');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            $table->dropColumn('estimate_code');
        });
    }
};
