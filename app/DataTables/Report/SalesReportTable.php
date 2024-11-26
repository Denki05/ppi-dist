<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Penjualan\SalesOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class SalesReportTable extends Table
{
    private function query(Request $request)
    {
        $startDate = $request->start_date . " 00:00:00";
        $endDate = $request->end_date . " 23:59:59";

        $model = SalesOrder::leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->leftJoin('penjualan_do', 'penjualan_so.id', '=', 'penjualan_do.so_id')
            ->leftJoin('penjualan_do_details', 'penjualan_do.id', '=', 'penjualan_do_details.do_id')
            ->select(
                'master_customer_other_addresses.name AS customer_name', 
                'master_customer_other_addresses.text_kota AS customer_city', 
                'penjualan_so.so_code AS so_code',
                'penjualan_so.so_date AS so_date', 
                'penjualan_do.id AS id',
                'penjualan_do.do_code AS invoice_code', 
                DB::raw('SUM(
                    CASE
                        WHEN penjualan_do.type_transaction = "CASH" 
                        THEN IFNULL(penjualan_do_details.purchase_total_idr - penjualan_do_details.discount_idr, 0)
                    END
                ) AS invoice_cash'),
                DB::raw('SUM(
                    CASE
                        WHEN penjualan_do.type_transaction IN ("TEMPO", "COD", "MARKETPLACE") 
                        THEN IFNULL(penjualan_do_details.purchase_total_idr - penjualan_do_details.discount_idr, 0)
                    END
                ) AS invoice_tempo')
            )
            ->where('penjualan_so.status', 4)
            ->whereBetween('penjualan_so.so_date', [$startDate, $endDate])
            ->groupBy('penjualan_do.id', 'master_customer_other_addresses.name', 'penjualan_do.do_code');


        if ($request->customer != 'all') {
            $multipleCustomer = explode(',', $request->customer);
            $model->whereIn('penjualan_so.customer_other_address_id', $multipleCustomer);
        }

        return $model->get();
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->editColumn('so_date', function (SalesOrder $model) {
            return $model->so_date ? Carbon::parse($model->so_date)->format('d/m/Y') : '-';
        });

        $table->addColumn('combined_column', function (SalesOrder $model) {
            return $model->customer_name . ' ' . $model->customer_city;
        });

        $table->editColumn('invoice_cash', function (SalesOrder $model) {
            return 'Rp ' . number_format($model->invoice_cash, 0, ',', '.');
        });

        $table->editColumn('invoice_tempo', function (SalesOrder $model) {
            return 'Rp ' . number_format($model->invoice_tempo, 0, ',', '.');
        });

        $table->rawColumns(['combined_column']);

        return $table->make(true);
    }
}