<?php

namespace App\DataTables\Finance;

use App\DataTables\Table;
use App\Entities\Finance\Cashback;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class CashbackTable extends Table
{
    private function query(Request $request)
    {
        $month = $request->input('bulan', now()->month);
        $year = $request->input('tahun', now()->year);

        $model = Cashback::where('finance_cashback.status', 1)
            ->leftJoin('finance_cashback_detail', 'finance_cashback.id', '=', 'finance_cashback_detail.cashback_id')
            ->leftJoin('penjualan_do', 'penjualan_do.id', '=', 'finance_cashback.do_id')
            ->leftJoin('master_customer_other_addresses', 'master_customer_other_addresses.id', '=', 'finance_cashback.customer_other_address_id')
            ->select(
                'finance_cashback.id AS id', 
                'finance_cashback.code AS code', 
                'master_customer_other_addresses.name AS customer_name', 
                'master_customer_other_addresses.text_kota AS customer_city', 
                'finance_cashback.status AS status', 
                'penjualan_do.do_code AS code_invoice',  
                'finance_cashback.created_at AS tanggal_buat', 
                DB::raw('IFNULL(SUM(finance_cashback_detail.subtotal_item_idr), 0) AS total_jual'), 
                DB::raw('IFNULL(SUM(finance_cashback_detail.amount_cashback), 0) AS total_beli'), 
            )
            ->whereMonth('finance_cashback.created_at', $month)
            ->whereYear('finance_cashback.created_at', $year) // Add year filter
            ->groupBy('finance_cashback.code')
            ->get();

        return $model;
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->addColumn('account_customer', function (Cashback $model) {
            return $model->customer_name . ' ' . $model->customer_city;
        });

        $table->addColumn('selisih_cashback', function (Cashback $model) {
            return $model->total_jual - $model->total_beli;
        });

        $table->editColumn('status', function (Cashback $model) {
            return $model->status();
        });

        $table->addColumn('action', function (Cashback $model) {
            if ($model->status == $model::STATUS['ACTIVE']) {
                $destroyUrl = route('superuser.finance.cashback.destroy', $model->id);
                $printInvoiceBeli = route('superuser.finance.cashback.print_invoice_beli', $model->id);
                $printInvoiceJual = route('superuser.finance.cashback.print_invoice_jual', $model->id);
                return "
                    <a href=\"{$printInvoiceBeli}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-success\" title=\"Invoice Beli\">
                            <i class=\"fa fa-print\"></i>
                        </button>
                    </a>

                    <a href=\"{$printInvoiceJual}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-primary\" title=\"Invoice Jual\">
                            <i class=\"fa fa-print\"></i>
                        </button>
                    </a>
                        
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger delete-button\" data-url=\"{$destroyUrl}\" title=\"Destroy\">
                        <i class=\"fa fa-trash\"></i>
                    </button>
                ";
            }
        });

        return $table->make(true);
    }
}