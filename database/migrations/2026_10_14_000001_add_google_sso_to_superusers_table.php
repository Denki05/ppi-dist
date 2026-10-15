<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoogleSsoToSuperusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('superusers', function (Blueprint $table) {
            $table->string('google_id', 100)->nullable()->unique()->after('email');
            $table->text('avatar')->nullable()->after('google_id');
            $table->timestamp('email_verified_at')->nullable()->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('superusers', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'avatar', 'email_verified_at']);
        });
    }
}
