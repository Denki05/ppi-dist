<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnrichmentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enrichment_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('fragrance_reference_id')->nullable();
            $table->foreign('fragrance_reference_id')->references('id')->on('fragrance_references')->onDelete('set null')->onUpdate('cascade');

            $table->string('driver', 30);
            $table->string('model', 50)->nullable();
            $table->boolean('use_search')->default(true);

            $table->string('status', 20);
            $table->integer('http_status')->nullable();
            $table->text('error_message')->nullable();
            $table->longText('raw_response')->nullable();
            $table->integer('duration_ms')->nullable();

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
        Schema::dropIfExists('enrichment_logs');
    }
}
