<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('superuser_login_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('superuser_id');
            $table->string('token')->unique();
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();

            // Relasi ke tabel superuser
            $table->foreign('superuser_id')
                  ->references('id')
                  ->on('superusers')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('superuser_login_tokens');
    }
};
