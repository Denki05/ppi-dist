<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use App\Entities\Penjualan\SalesOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class SalesmanReportTable extends Table
{
    private function query(Request $request)
    {
        $model = SalesOrder::where('penjualan_so.status', 4)
                    ->whereBetween('penjualan_so.so_date', [$request->start_date . " 00:00:00", $request->end_date . " 23:59:59"])
                    ->leftJoin('penjualan_do', 'penjualan_so.id', '=', 'penjualan_do.so_id')
                    ->leftJoin('penjualan_do_details', 'penjualan_do.id', '=', 'penjualan_do_details.do_id')
                    ->leftJoin('penjualan_do_item', 'penjualan_do.id', '=', 'penjualan_do_item.do_id')
                    ->leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
                    ->select(
                        'penjualan_so.code AS invoice_code', 
                        'penjualan_so.id AS id',
                        DB::raw('
                            CASE 
                                WHEN penjualan_so.sales_id = 1 THEN "Lindy"
                                WHEN penjualan_so.sales_id = 3 THEN "Super Administrator"
                                WHEN penjualan_so.sales_id = 5 THEN "Rudy"
                            END AS salesman
                        '),
                        DB::raw('SUM(penjualan_do_item.qty) AS total_qty'),
                        // DB::raw('SUM(penjualan_do_details.purchase_total_idr) AS total_omset'),
                        'penjualan_do_details.purchase_total_idr AS total_omset',
                        'master_customer_other_addresses.name AS customer_name', 
                        'master_customer_other_addresses.text_kota AS customer_kota'
                    )
                    ->groupBy('penjualan_so.code');

        if($request->salesman != 'all') {
            $model = $model->where('penjualan_so.sales_id', $request->salesman);
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

        return $table->make(true);
    }
}