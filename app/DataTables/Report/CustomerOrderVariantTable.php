<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use App\Entities\Penjualan\SalesOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class CustomerOrderVariantTable extends Table
{
    /**
     * Get the query source of dataTable.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function query(Request $request)
    {
        // Menggunakan Carbon untuk manipulasi tanggal yang lebih aman
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

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
                DB::raw('ELT(MONTH(penjualan_so.so_date), "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember") AS invoice_month'),
                DB::raw('YEAR(penjualan_so.so_date) AS invoice_year'), // Tahun tetap diambil
                'master_products_packaging.code AS product_code',
                'master_products_packaging.name AS product_name',
                DB::raw('SUM(penjualan_so_item.qty_worked) AS invoice_qty'),
                'master_packaging.pack_name AS packaging_name'
            )
            ->groupBy(
                'master_customer_other_addresses.name',
                'master_customer_other_addresses.text_kota',
                'penjualan_so.brand_name',
                // Mengulangi ekspresi SQL asli untuk GROUP BY invoice_month dan invoice_year
                DB::raw('MONTH(penjualan_so.so_date)'),
                DB::raw('YEAR(penjualan_so.so_date)'),
                'master_products_packaging.code',
                'master_products_packaging.name',
                'master_packaging.pack_name'
            );

        // Handle filter customer
        $this->applyWhereInFilter($model, $request, 'customer', 'penjualan_so.customer_other_address_id');

        // Handle filter brand
        // Pastikan 'master_products.brand_name' adalah kolom yang benar untuk brand
        $this->applyWhereInFilter($model, $request, 'brand_name', 'master_products.brand_name');

        // Handle filter produk
        $this->applyWhereInFilter($model, $request, 'product', 'master_products_packaging.id');

        return $model;
    }

    /**
     * Apply whereIn filter based on request parameter.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @param string $paramName
     * @param string $columnName
     * @return void
     */
    private function applyWhereInFilter($query, Request $request, string $paramName, string $columnName)
    {
        if ($request->filled($paramName)) {
            $values = is_array($request->$paramName) ? $request->$paramName : explode(',', $request->$paramName);
            if (!in_array('all', $values)) {
                $query->whereIn($columnName, $values);
            }
        }
    }

    /**
     * Build DataTable class.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->addColumn('combined_customer', function ($row) {
            return $row->customer_name . ' ' . $row->customer_kota;
        });

        $table->addColumn('combined_product', function ($row) {
            return $row->product_code . ' - ' . $row->product_name . ' / ' . $row->packaging_name;
        });

        $table->addColumn('combined_month_year', function ($row) {
            return $row->invoice_month . ' - ' . $row->invoice_year;
        });

        return $table->make(true);
    }
}