<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Penjualan\SalesOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class ReveneuReportTable extends Table
{
    private function query(Request $request)
    {
        $startDate = $request->start_date . " 00:00:00";
        $endDate = $request->end_date . " 23:59:59";

        $model = CustomerOtherAddress::leftJoin('penjualan_so', 'master_customer_other_addresses.id', '=', 'penjualan_so.customer_other_address_id')
            ->leftJoin('penjualan_do', 'penjualan_so.id', '=', 'penjualan_do.so_id')
            ->leftJoin('penjualan_do_details', 'penjualan_do.id', '=', 'penjualan_do_details.do_id')
            ->select(
                'master_customer_other_addresses.id AS customer_id', 
                'master_customer_other_addresses.name AS customer_name', 
                'master_customer_other_addresses.text_kota AS customer_city', 
                DB::raw('SUM(penjualan_do_details.purchase_total_idr) AS total_purchase')
            )
            ->where('master_customer_other_addresses.status', 1)
            ->where('penjualan_so.status', 4)
            ->where('penjualan_so.type_so', 'nonppn')
            ->whereBetween('penjualan_so.so_date', [$startDate, $endDate])
            ->groupBy('master_customer_other_addresses.id');

        if ($request->customer != 'all') {
            $multipleCustomer = explode(',', $request->customer);
            $model->whereIn('penjualan_so.customer_other_address_id', $multipleCustomer);
        }

        return $model->get();
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addColumn('combined_column', function (CustomerOtherAddress $model) {
            return $model->customer_name . ' ' . $model->customer_city;
        });

        $table->editColumn('total_purchase', function (CustomerOtherAddress $model) {
            return 'Rp ' . number_format($model->total_purchase, 0, ',', '.');
        });

        // Add detail column with order history for each customer
        $table->addColumn('detail', function (CustomerOtherAddress $model) use ($request) {
            $startDate = $request->start_date . " 00:00:00";
            $endDate = $request->end_date . " 23:59:59";

            $so_history = SalesOrder::where('penjualan_so.customer_other_address_id', $model->customer_id)
                ->leftJoin('penjualan_do', 'penjualan_so.id', '=', 'penjualan_do.so_id')
                ->leftJoin('penjualan_do_details', 'penjualan_do.id', '=', 'penjualan_do_details.do_id')
                ->select(
                    'penjualan_do.do_code AS invoice_code', 
                    'penjualan_do.type_transaction AS type', 
                    'penjualan_do_details.purchase_total_idr AS purchase_invoice'
                )
                ->where('penjualan_so.status', 4)
                ->where('penjualan_so.type_so', 'nonppn')
                ->whereBetween('penjualan_so.so_date', [$startDate, $endDate])
                ->get();

            $detail_html = '<table class="table table-dark" style="margin-top: -5px !important;margin-bottom: 0px;">
                <thead class="thead-light">
                    <tr>
                        <th class="w-100" colspan="3" style="text-align: left; font-weight: bold; font-size: 20px;">Order History : ' . $model->customer_name . ' ' . $model->customer_city . '</th>
                    </tr>
                    <tr>
                        <th class="w-20">Invoice</th>
                        <th class="w-20">Type</th>
                        <th class="w-20">Total</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($so_history as $history) {
                $total = $history->purchase_invoice ? 'Rp. ' . number_format($history->purchase_invoice, 2, ',', '.') : '';

                $detail_html .= '<tr>
                    <td>' . $history->invoice_code . '</td>
                    <td>' . $history->type . '</td>
                    <td>' . $total . '</td>
                </tr>';
            }

            $detail_html .= '</tbody></table>';

            return $detail_html;
        });

        $table->rawColumns(['detail']);

        return $table->make(true);
    }
}