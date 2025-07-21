<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use App\Entities\Penjualan\SalesOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class CustomerOrderVariantTable extends Table
{
    private function query(Request $request)
    {
        $startDate = $request->start_date . " 00:00:00";
        $endDate = $request->end_date . " 23:59:59";

        $model = SalesOrder::where('penjualan_so.status', 4)
            ->whereBetween('penjualan_so.so_date', [$startDate, $endDate])
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
                    END AS invoice_month'
                ),
                'master_products_packaging.code AS product_code',
                'master_products_packaging.name AS product_name',
                DB::raw('SUM(penjualan_so_item.qty_worked) AS invoice_qty'),
                'master_packaging.pack_name AS packaging_name'
            )
            ->groupBy(
                'master_products_packaging.name',
                'master_customer_other_addresses.name',
                'master_customer_other_addresses.text_kota',
                'penjualan_so.brand_name',
                'invoice_month',
                'master_products_packaging.code',
                'master_products_packaging.name',
                'master_packaging.pack_name'
            );

        // Handle filter customer
        if ($request->filled('customer')) {
            $customer = is_array($request->customer) ? $request->customer : explode(',', $request->customer);
            if (!in_array('all', $customer)) {
                $model->whereIn('penjualan_so.customer_other_address_id', $customer);
            }
        }

        // Handle filter brand
        if ($request->filled('brand_name')) {
            $brands = is_array($request->brand_name) ? $request->brand_name : explode(',', $request->brand_name);
            if (!in_array('all', $brands)) {
                $model->whereIn('master_products.brand_name', $brands);
            }
        }

        // Handle filter produk
        if ($request->filled('product')) {
            $products = is_array($request->product) ? $request->product : explode(',', $request->product);
            if (!in_array('all', $products)) {
                $model->whereIn('master_products_packaging.id', $products);
            }
        }

        return $model;
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->addColumn('combined_column', function ($row) {
            return $row->customer_name . ' ' . $row->customer_kota;
        });

        $table->addColumn('combined_column2', function ($row) {
            return $row->product_code . ' - ' . $row->product_name . ' / ' . $row->packaging_name;
        });

        return $table->make(true);
    }
}