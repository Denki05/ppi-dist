<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddShProdukToSettingMenu extends Migration
{
    /**
     * Daftarkan menu SH Produk agar bisa diatur hak aksesnya
     * (Setting - Menu / hak akses user), portable ke production.
     *
     * @return void
     */
    public function up()
    {
        $exists = DB::table('setting_menu')
            ->where('route_name', 'superuser.master.sh_produk')
            ->exists();
        if (!$exists) {
            DB::table('setting_menu')->insert([
                'name' => 'Master - SH Produk',
                'route_name' => 'superuser.master.sh_produk',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        DB::table('setting_menu')->where('route_name', 'superuser.master.sh_produk')->delete();
    }
}
