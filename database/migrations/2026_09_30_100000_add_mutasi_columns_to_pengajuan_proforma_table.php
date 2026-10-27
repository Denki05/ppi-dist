<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMutasiColumnsToPengajuanProformaTable extends Migration
{
    private $cols = [
        'prospect_id'        => ['string', 50],
        'customer_id_hasil'  => ['unsignedBigInteger', null],
        'member_id_hasil'    => ['string', 50],
        'parent_dibuat_baru' => ['boolean', null],
        'member_dibuat_baru' => ['boolean', null],
        'notif_ao_status'    => ['string', 20],
        'ktp_photo_path'     => ['string', 255],
        'npwp_photo_path'    => ['string', 255],
    ];

    public function up()
    {
        Schema::table('pengajuan_proforma', function (Blueprint $table) {
            foreach ($this->cols as $name => $def) {
                if (!Schema::hasColumn('pengajuan_proforma', $name)) {
                    $col = $def[1] ? $table->{$def[0]}($name, $def[1]) : $table->{$def[0]}($name);
                    $col->nullable();
                }
            }
        });
    }

    public function down()
    {
        Schema::table('pengajuan_proforma', function (Blueprint $table) {
            $drop = [];
            foreach (array_keys($this->cols) as $name) {
                if (Schema::hasColumn('pengajuan_proforma', $name)) {
                    $drop[] = $name;
                }
            }
            if ($drop) {
                $table->dropColumn($drop);
            }
        });
    }
}