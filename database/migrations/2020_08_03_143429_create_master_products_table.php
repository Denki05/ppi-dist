<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_products', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('brand_reference_id');
            $table->foreign('brand_reference_id')->references('id')->on('master_brand_references')->onDelete('restrict');

            $table->unsignedBigInteger('sub_brand_reference_id');
            $table->foreign('sub_brand_reference_id')->references('id')->on('master_sub_brand_references')->onDelete('restrict');

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('master_product_categories')->onDelete('restrict');

            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('master_product_types')->onDelete('restrict');

            $table->string('code')->unique();
            $table->string('name');
            $table->string('material_code');
            $table->string('material_name');
            $table->text('description')->nullable();

            $table->decimal('default_quantity', 16, 4)->default(0);

            $table->unsignedBigInteger('default_unit_id');
            $table->foreign('default_unit_id')->references('id')->on('master_units')->onDelete('restrict');

            $table->unsignedBigInteger('default_warehouse_id');
            $table->foreign('default_warehouse_id')->references('id')->on('master_warehouses')->onDelete('restrict');
            
            $table->decimal('buying_price', 16, 4)->default(0);
            $table->decimal('selling_price', 16, 4)->default(0);

            $table->string('image')->nullable();
            $table->string('image_hd')->nullable();

            $table->integer('status');

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
        Schema::dropIfExists('master_products');
    }
}
