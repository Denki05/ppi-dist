<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use App\Entities\Penjualan\SalesOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class ProductPerformanceTable extends Table
{
    private function query(Request $request)
    {
        $startDate = $request->start_date . " 00:00:00";
        $endDate = $request->end_date . " 23:59:59";

        $model = SalesOrder::leftJoin('penjualan_so_item', 'penjualan_so.id', '=', 'penjualan_so_item.so_id')
            ->leftJoin('master_products_packaging', 'penjualan_so_item.product_packaging_id', '=', 'master_products_packaging.id')
            ->leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->leftJoin('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('penjualan_do', 'penjualan_so.id', '=', 'penjualan_do.so_id')
            ->whereBetween('penjualan_so.so_date', [$startDate, $endDate])
            ->where('penjualan_do.status', 6)
            ->select(
                'master_customer_other_addresses.id AS id', 
                'master_products.brand_name AS brand', 
                'master_customer_other_addresses.name AS customer_name', 
                'master_customer_other_addresses.text_kota AS customer_city', 
                'master_products_packaging.id AS product_id', 
                'master_products_packaging.name AS product_name', 
                'master_products_packaging.code AS product_code', 
                'master_packaging.pack_name AS packaging', 
                'penjualan_so_item.qty_worked AS qty'
            );

        // Filter brand
        if ($request->filled('brand')) {
            $brands = is_array($request->brand) ? $request->brand : explode(',', $request->brand);
            if (!in_array('all', $brands)) {
                $model->whereIn('master_products.brand_name', $brands);
            }
        }

        // Filter produk jika diperlukan
        if ($request->filled('product')) {
            $products = is_array($request->product) ? $request->product : explode(',', $request->product);
            if (!in_array('all', $products)) {
                $model->whereIn('master_products_packaging.id', $products);
            }
        }

        return $model->get();
    }


    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->addColumn('customer', function (SalesOrder $model) {
            return $model->customer_name . ' ' . $model->customer_city;
        });

        $table->addColumn('product', function (SalesOrder $model) {
            return $model->product_code . ' - ' . $model->product_name . ' / ' . $model->packaging;
        });

        return $table->make(true);
    }
}