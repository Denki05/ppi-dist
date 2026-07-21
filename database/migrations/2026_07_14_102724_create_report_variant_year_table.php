<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportVariantYearTable extends Migration
{
    public function up()
    {
        Schema::create('report_variant_year', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('brand_name')->nullable();
            $table->string('material_code')->nullable();
            $table->string('material_name')->nullable();
            $table->string('product_code')->nullable();
            $table->string('product_name')->nullable();
            $table->string('packaging')->nullable();
            $table->unsignedSmallInteger('tahun');
            $table->decimal('qty', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(
                ['product_code', 'packaging', 'tahun'],
                'uniq_report_variant_year'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_variant_year');
    }
}