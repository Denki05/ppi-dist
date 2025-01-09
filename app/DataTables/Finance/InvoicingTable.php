<?php

namespace App\DataTables\Finance;

use App\DataTables\Table;
use App\Entities\Finance\Invoicing;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoicingTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $query = Invoicing::query()
            ->join('master_customer_other_addresses', 'master_customer_other_addresses.id', '=', 'finance_invoicing.customer_other_address_id')
            ->join('penjualan_do', 'penjualan_do.id', '=', 'finance_invoicing.do_id')
            ->join('penjualan_so', 'penjualan_so.id', '=', 'penjualan_do.so_id')
            ->where('finance_invoicing.status', 1)
            ->where('penjualan_so.type_so', 'nonppn')
            ->select(
                'finance_invoicing.id AS id',
                'finance_invoicing.do_id AS do_id',
                'master_customer_other_addresses.id AS IdCustomer',
                'finance_invoicing.created_at AS created_at',
                'finance_invoicing.code AS invoice_code',
                'finance_invoicing.grand_total_idr AS grand_total_idr',
                'finance_invoicing.status AS status',
                'master_customer_other_addresses.name AS customer_name',
                'master_customer_other_addresses.text_kota AS customer_kota',
                'penjualan_do.type_transaction AS transaksi',
                'penjualan_so.so_code AS so_code',
                'penjualan_so.payment_status AS status_so'
            );

        // Apply customer filter if specified and valid
        if (!empty($request->customer) && $request->customer && $request->customer > 0) {
            $query->where('master_customer_other_addresses.id', $request->customer);
        }

        // dd((int)$request->customer);

        // Apply date range filter with specific start and end times
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $query->whereBetween('finance_invoicing.created_at', [
                $request->start_date . " 00:00:00",
                $request->end_date . " 23:59:59"
            ]);
        }

        return $query;
    }
    

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->addColumn('account_customer', function (Invoicing $model) {
            return $model->customer_name . ' ' . $model->customer_kota;
        });

        $table->editColumn('created_at', function (Invoicing $model) {
            return [
            'display' => Carbon::parse($model->created_at)->format('d-m-Y'),
            'timestamp' => $model->created_at
            ];
        });

        $table->editColumn('invoice_code', function (Invoicing $model) {
            $history = route('superuser.finance.invoicing.history_payable', $model);
            return "<a href=\"{$history}\">$model->invoice_code</a>";
        });

        $table->addColumn('action', function (Invoicing $model) {
            $view = route('superuser.finance.invoicing.detail', $model->do_id);
            $print = route('superuser.finance.invoicing.print', $model);
            $print_full = route('superuser.finance.invoicing.print2', $model);
            // $print_pdf = route('superuser.finance.invoicing.download_invoice', $model);

            switch ($model->status) {
                case $model::STATUS['ACTIVE']:
                    return "
                        <a href=\"{$view}\">
                            <button type=\"button\" class=\"btn btn-primary btn-sm btn-flat\" title=\"View\">
                                <i class=\"fa fa-eye\"></i>
                            </button>
                        </a>
                        <a href=\"{$print}\">
                            <button type=\"button\" class=\"btn btn-outline-primary btn-sm btn-flat\" title=\"Print\">
                                <i class=\"fa fa-print\"></i>
                            </button>
                        </a>
                        <a href=\"{$print_full}\">
                            <button type=\"button\" class=\"btn btn-outline-success btn-sm btn-flat\" title=\"Print Full\">
                                <i class=\"fa fa-print\"></i>
                            </button>
                        </a>
                    ";
            }
        });

        $table->rawColumns(['invoice_code', 'action']);
        
        return $table->make(true);
    }

}