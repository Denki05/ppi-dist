<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPresenceTable extends Migration
{
    public function up()
    {
        Schema::create('user_presence', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('session_id', 191)->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_presence');
    }
}
