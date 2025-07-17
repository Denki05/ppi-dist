<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Master\ProductPack;
use App\Entities\Master\CustomerOtherAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class CustomerOrderVariantTable extends Table
{
    private function query(Request $request)
    {
        $model = SalesOrder::where('penjualan_so.status', 4)
                    ->whereBetween('penjualan_so.so_date', [$request->start_date . " 00:00:00", $request->end_date . " 23:59:59"])
                    ->leftJoin('penjualan_so_item', 'penjualan_so.id', '=', 'penjualan_so_item.so_id')
                    ->leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
                    ->leftJoin('master_products_packaging', 'penjualan_so_item.product_packaging_id', '=', 'master_products_packaging.id')
                    ->leftJoin('master_products', 'master_products_packaging.product_id', '=', 'master_products.id')
                    ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
                    ->select(
                        'master_customer_other_addresses.name AS customer_name', 
                        'master_customer_other_addresses.text_kota AS customer_kota', 
                        'penjualan_so.brand_name AS invoice_brand', 
                        DB::raw('
                            CASE 
                                WHEN MONTH(penjualan_so.so_date) = 1 THEN "Januari"
                                WHEN MONTH(penjualan_so.so_date) = 2 THEN "Februari"
                                WHEN MONTH(penjualan_so.so_date) = 3 THEN "Maret"
                                WHEN MONTH(penjualan_so.so_date) = 4 THEN "April"
                                WHEN MONTH(penjualan_so.so_date) = 5 THEN "Mei"
                                WHEN MONTH(penjualan_so.so_date) = 6 THEN "Juni"
                                WHEN MONTH(penjualan_so.so_date) = 7 THEN "Juli"
                                WHEN MONTH(penjualan_so.so_date) = 8 THEN "Agustus"
                                WHEN MONTH(penjualan_so.so_date) = 9 THEN "September"
                                WHEN MONTH(penjualan_so.so_date) = 10 THEN "Oktober"
                                WHEN MONTH(penjualan_so.so_date) = 11 THEN "November"
                                WHEN MONTH(penjualan_so.so_date) = 12 THEN "Desember"
                            END AS invoice_month
                        '),
                        'master_products_packaging.code AS product_code', 
                        'master_products_packaging.name AS product_name', 
                        DB::raw('SUM(penjualan_so_item.qty_worked) AS invoice_qty'),
                        'master_packaging.pack_name AS packaging_name'
                    )
                    ->groupBy('master_products_packaging.name', 'master_customer_other_addresses.name');

        if($request->customer != 'all') {
            $model = $model->where('penjualan_so.customer_other_address_id', $request->customer);
        }

        if($request->brand_name != 'all') {
            $multiple_brand = explode(',', $request->brand_name);
            $model->whereIn('master_products.brand_name', $multiple_brand);
        }
        
        if($request->product != 'all') {
            $model = $model->where('master_products_packaging.id', $request->product);
        }

        return $model;
    }      
    
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->addColumn('combined_column', function (SalesOrder $model) {
            return $model->customer_name . ' ' . $model->customer_kota;
        });

        $table->addColumn('combined_column2', function (SalesOrder $model) {
            return $model->product_code . ' - ' . $model->product_name . ' / ' . $model->packaging_name;
        });

        return $table->make(true);
    }
}