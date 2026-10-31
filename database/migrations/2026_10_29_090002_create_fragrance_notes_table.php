<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFragranceNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fragrance_notes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('slug')->unique();
            $table->string('name');

            $table->integer('status');

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fragrance_reference_notes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('fragrance_reference_id');
            $table->foreign('fragrance_reference_id')->references('id')->on('fragrance_references')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('fragrance_note_id');
            $table->foreign('fragrance_note_id')->references('id')->on('fragrance_notes')->onDelete('cascade')->onUpdate('cascade');

            $table->string('position', 10);

            $table->unique(['fragrance_reference_id', 'fragrance_note_id', 'position'], 'fragrance_reference_notes_unique');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fragrance_reference_notes');
        Schema::dropIfExists('fragrance_notes');
    }
}
