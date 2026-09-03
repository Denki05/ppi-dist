<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsArchivedToPenjualanSo extends Migration
{
    public function up()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            $table->boolean('is_archived')->default(0)->after('so_indent');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
        });
    }

    public function down()
    {
        Schema::table('penjualan_so', function (Blueprint $table) {
            $table->dropColumn(['is_archived', 'archived_at']);
        });
    }
}
