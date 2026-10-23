<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ReconcileSettingMenuRoutes extends Migration
{
    public function up()
    {
        $this->renameRoute('superuser.penjualan.return', 'superuser.penjualan.sale_return');
        $this->renameRoute('superuser.report,product_high_sell', 'superuser.report.product_high_sell');

        $menus = [
            'superuser.penjualan.pengajuan_proforma' => 'Penjualan - Pengajuan Proforma',
            'superuser.gudang.sj_mutasi_internal' => 'Gudang - SJ Mutasi Internal',
            'superuser.finance.nota_kredit_finance' => 'Finance - Nota Kredit Finance',
            'superuser.finance.void' => 'Finance - Void DO',
            'superuser.report.forecast_supplier' => 'Report - Forecast Supplier',
            'superuser.report.employee_performance_product' => 'Report - Employee Performance Product',
        ];

        foreach ($menus as $routeName => $name) {
            if (!DB::table('setting_menu')->where('route_name', $routeName)->exists()) {
                DB::table('setting_menu')->insert([
                    'name' => $name,
                    'route_name' => $routeName,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down()
    {
        $routes = [
            'superuser.penjualan.pengajuan_proforma',
            'superuser.gudang.sj_mutasi_internal',
            'superuser.finance.nota_kredit_finance',
            'superuser.finance.void',
            'superuser.report.forecast_supplier',
            'superuser.report.employee_performance_product',
        ];

        DB::table('setting_menu')->whereIn('route_name', $routes)->delete();

        $this->renameRoute('superuser.penjualan.sale_return', 'superuser.penjualan.return');
        $this->renameRoute('superuser.report.product_high_sell', 'superuser.report,product_high_sell');
    }

    protected function renameRoute($from, $to)
    {
        if (DB::table('setting_menu')->where('route_name', $to)->exists()) {
            return;
        }

        DB::table('setting_menu')
            ->where('route_name', $from)
            ->update([
                'route_name' => $to,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }
}
