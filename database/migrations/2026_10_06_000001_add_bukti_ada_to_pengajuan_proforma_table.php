<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 1-jalan: penanda bukti capture sudah dilampirkan AO saat pengajuan.
// NULL = pengajuan lama (tidak diketahui, tetap lolos); 0/1 = eksplisit baru.
class AddBuktiAdaToPengajuanProformaTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('pengajuan_proforma', 'bukti_ada')) {
            Schema::table('pengajuan_proforma', function (Blueprint $table) {
                $table->boolean('bukti_ada')->nullable()->after('bukti_chat_at')
                    ->comment('1-jalan: bukti capture dilampirkan saat pengajuan');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('pengajuan_proforma', 'bukti_ada')) {
            Schema::table('pengajuan_proforma', function (Blueprint $table) {
                $table->dropColumn('bukti_ada');
            });
        }
    }
}
