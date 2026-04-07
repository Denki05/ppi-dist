<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFolderMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folder_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');

            // mapping internal brand
            $table->string('merek_db')->nullable();
            $table->string('merek_folder')->nullable();

            // mapping brand
            $table->string('brand_db')->nullable();
            $table->string('brand_folder')->nullable();

            // mapping searah
            $table->string('searah_db')->nullable();
            $table->string('searah_folder')->nullable();

            $table->timestamps();

            // optional: hindari duplikasi
            $table->unique(['brand_db', 'searah_db']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('folder_mappings');
    }
}
