<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Entities\Master\ProductPack;
use DB;

class ProductHighSaleTable extends Table
{
    private function query(Request $request)
    {
        $startDate = $request->start_date . " 00:00:00";
        $endDate = $request->end_date . " 23:59:59";

        $model = ProductPack::leftJoin('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
            ->leftJoin('penjualan_so_item', 'master_products_packaging.id', '=', 'penjualan_so_item.product_packaging_id')
            ->leftJoin('penjualan_so', 'penjualan_so_item.so_id', '=', 'penjualan_so.id')
            ->selectRaw('
                master_products.brand_name AS brand,
                master_products_packaging.code AS product_code,
                master_products_packaging.name AS product_name,
                SUM(penjualan_so_item.qty_worked) AS total_qty
            ')
            ->where('penjualan_so.status', 4)
            ->whereBetween('penjualan_so.so_date', [$startDate, $endDate])
            ->groupBy('master_products_packaging.name');

        if ($request->brand != 'all') {
            $multipleBrand = explode(',', $request->brand);
            $model->whereIn('master_products.brand_name', $multipleBrand);
        }
    
        return $model->get();
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->addColumn('variant', function (ProductPack $model) {
            return $model->product_code . ' - ' . $model->product_name;
        });

        return $table->make(true);
    }
}