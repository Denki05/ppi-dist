<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFragranceReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fragrance_references', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('ref_key')->unique();
            $table->string('brand');
            $table->string('searah');

            $table->string('match_status', 20)->nullable();
            $table->string('matched_name')->nullable();
            $table->string('matched_brand')->nullable();
            $table->string('concentration', 20)->nullable();
            $table->text('match_note')->nullable();

            $table->smallInteger('release_year')->nullable();
            $table->string('release_year_confidence', 10)->nullable();
            $table->text('perfumers')->nullable();

            $table->string('gender_target', 10)->nullable();
            $table->string('scent_family', 20)->nullable();
            $table->text('main_accords')->nullable();
            $table->text('notes_top')->nullable();
            $table->text('notes_middle')->nullable();
            $table->text('notes_base')->nullable();

            $table->text('tags_mood')->nullable();
            $table->text('tags_season')->nullable();
            $table->text('tags_time_of_day')->nullable();
            $table->text('description_id')->nullable();

            $table->string('confidence', 10)->nullable();
            $table->text('sources')->nullable();
            $table->string('reference_notes_status', 20)->nullable();
            $table->text('notes_differences')->nullable();

            $table->boolean('needs_review')->default(false);
            $table->text('review_reasons')->nullable();

            $table->integer('status');
            $table->timestamp('verified_at')->nullable();
            $table->integer('verified_by')->nullable();

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fragrance_references');
    }
}
