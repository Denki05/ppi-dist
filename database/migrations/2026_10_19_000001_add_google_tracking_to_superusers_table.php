<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoogleTrackingToSuperusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('superusers', function (Blueprint $table) {
            $table->timestamp('google_linked_at')->nullable()->after('email_verified_at');
            $table->timestamp('google_last_login_at')->nullable()->after('google_linked_at');
        });

        // Backfill akun yang sudah ter-link Google sebelum kolom ini ada.
        DB::table('superusers')
            ->whereNotNull('google_id')
            ->whereNull('google_linked_at')
            ->update(['google_linked_at' => DB::raw('COALESCE(email_verified_at, updated_at)')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('superusers', function (Blueprint $table) {
            $table->dropColumn(['google_linked_at', 'google_last_login_at']);
        });
    }
}
