<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoInternalRevisionsTable extends Migration
{
    public function up()
    {
        Schema::create('do_internal_revisions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('do_id');
            $table->unsignedBigInteger('so_id')->nullable();
            $table->unsignedTinyInteger('origin_status'); // snapshot status DO: 5 atau 6
            $table->unsignedBigInteger('requested_by');
            $table->timestamp('requested_at')->nullable();
            $table->text('request_reason');
            $table->longText('revision_detail')->nullable();
            $table->boolean('items_changed')->default(false);
            $table->unsignedTinyInteger('status')->default(1); // 1 pending, 2 approved, 3 rejected
            $table->string('otp_hash')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->unsignedTinyInteger('otp_attempts')->default(0);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_reason')->nullable();
            $table->timestamps();

            $table->index('do_id');
            $table->index('status');
        });

        Schema::table('penjualan_do', function (Blueprint $table) {
            $table->unsignedTinyInteger('internal_revision_status')->nullable();
            $table->unsignedInteger('internal_revision_count')->default(0);
        });
    }

    public function down()
    {
        Schema::table('penjualan_do', function (Blueprint $table) {
            $table->dropColumn(['internal_revision_status', 'internal_revision_count']);
        });
        Schema::dropIfExists('do_internal_revisions');
    }
}