<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJabatanToSuperusers extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('superusers', 'jabatan')) {
            Schema::table('superusers', function (Blueprint $table) {
                $table->string('jabatan', 100)->nullable()->after('division');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('superusers', 'jabatan')) {
            Schema::table('superusers', function (Blueprint $table) {
                $table->dropColumn('jabatan');
            });
        }
    }
}
