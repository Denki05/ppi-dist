<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_assets', function (Blueprint $table) {
            $table->bigIncrements('id');

            // relasi product
            $table->bigInteger('product_id')->nullable();
            $table->string('product_code')->nullable();
            $table->string('product_name')->nullable();

            // data asli
            $table->string('merek')->nullable();
            $table->string('brand')->nullable();
            $table->string('searah')->nullable();

            // hasil mapping folder
            $table->string('internal_folder')->nullable();
            $table->string('brand_folder')->nullable();
            $table->string('searah_folder')->nullable();
            $table->string('variant_folder')->nullable();

            // path utama
            $table->text('base_path')->nullable();

            $table->timestamps();

            // index untuk search cepat
            $table->index(['product_code']);
            $table->index(['brand']);
            $table->index(['searah']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_assets');
    }
}
