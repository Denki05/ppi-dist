<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropSoIndentArchiveTables extends Migration
{
    public function up()
    {
        Schema::dropIfExists('so_indent_archive_item');
        Schema::dropIfExists('so_indent_archive');
    }

    public function down()
    {
        // Tidak bisa di-rollback karena tabel sudah dihapus
    }
}
