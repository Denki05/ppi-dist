<?php

namespace App\DataTables\Finance;

use App\DataTables\Table;
use App\Entities\Finance\Invoicing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoicingTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $query = Invoicing::query()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->from('finance_invoicing as fi')
            ->whereNull('fi.deleted_at')
            ->join('penjualan_do as do', 'do.id', '=', 'fi.do_id')
            ->join('penjualan_so as so', 'so.id', '=', 'do.so_id')
            ->join('master_customer_other_addresses as cust', 'cust.id', '=', 'fi.customer_other_address_id')
            ->where('fi.status', 1)
            ->where('so.type_so', 'nonppn')
            ->select([
                'fi.id',
                'fi.do_id',
                'fi.created_at',
                'fi.code as invoice_code',
                'fi.grand_total_idr',
                'fi.status',
                'cust.name as customer_name',
                'cust.text_kota as customer_kota',
                'do.type_transaction as transaksi',
                'so.so_code',
                'so.payment_status'
            ]);

        if ($request->filled('start_date') && $request->filled('end_date')) {

            $query->whereBetween('fi.created_at', [
                "{$request->start_date} 00:00:00",
                "{$request->end_date} 23:59:59"
            ]);

        } else {

            $hasFilter =
                $request->filled('customer');

            if (!$hasFilter) {
                // Load pertama SAJA
                $query->whereBetween('fi.created_at', [
                    now()->startOfYear()->format('Y-m-d H:i:s'),
                    now()->endOfYear()->format('Y-m-d H:i:s')
                ]);
            }

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

            switch ($model->status) {
                case $model::STATUS['ACTIVE']:
                    return "
                        <a href=\"{$view}\">
                            <button type=\"button\" class=\"btn btn-primary btn-sm btn-flat\" title=\"View\">
                                <i class=\"fa fa-eye\"></i>
                            </button>
                        </a>
                        <a href=\"{$print}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-outline-primary btn-sm btn-flat\" title=\"Print\">
                                <i class=\"fa fa-print\"></i>
                            </button>
                        </a>
                        <a href=\"{$print_full}\" target=\"_blank\">
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