<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOwnerToPengajuanProformaTable extends Migration
{
    public function up()
    {
        Schema::table('pengajuan_proforma', function (Blueprint $table) {
            // Nama owner / contact person (pisah dari nama perusahaan)
            if (!Schema::hasColumn('pengajuan_proforma', 'owner')) {
                $table->string('owner', 255)->nullable()->after('perusahaan');
            }
        });
    }

    public function down()
    {
        Schema::table('pengajuan_proforma', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_proforma', 'owner')) {
                $table->dropColumn('owner');
            }
        });
    }
}
