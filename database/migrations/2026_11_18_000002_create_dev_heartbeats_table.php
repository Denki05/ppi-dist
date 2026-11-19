<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevHeartbeatsTable extends Migration
{
    public function up()
    {
        Schema::create('dev_heartbeats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50)->unique();
            $table->timestamp('last_run_at')->nullable();
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dev_heartbeats');
    }
}
