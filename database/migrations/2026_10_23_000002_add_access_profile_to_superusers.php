<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccessProfileToSuperusers extends Migration
{
    public function up()
    {
        Schema::table('superusers', function (Blueprint $table) {
            $table->string('access_profile')->nullable()->after('modul_akses');
            $table->text('access_addons')->nullable()->after('access_profile');
        });
    }

    public function down()
    {
        Schema::table('superusers', function (Blueprint $table) {
            $table->dropColumn(['access_profile', 'access_addons']);
        });
    }
}
