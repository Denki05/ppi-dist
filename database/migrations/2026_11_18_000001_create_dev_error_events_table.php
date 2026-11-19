<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevErrorEventsTable extends Migration
{
    public function up()
    {
        Schema::create('dev_error_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fingerprint', 64)->index();
            $table->string('exception_class', 255);
            $table->text('message')->nullable();
            $table->string('file', 500)->nullable();
            $table->integer('line')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('route', 255)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('user_label', 100)->nullable();
            $table->string('user_type', 100)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('input_keys')->nullable();
            $table->mediumText('trace')->nullable();
            $table->unsignedInteger('occurrences')->default(1);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->tinyInteger('is_resolved')->default(0)->index();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dev_error_events');
    }
}
