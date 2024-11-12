<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use App\Entities\Accounting\InvoiceTax;
use App\Entities\Accounting\InvoiceTaxDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class InvoiceTaxJualReportTable extends Table
{
    private function query(Request $request)
    {
        $model = InvoiceTax::where('finance_invoice_mitra.status', 1)
            ->leftJoin('finance_invoice_mitra_detail', 'finance_invoice_mitra.id', '=', 'finance_invoice_mitra_detail.invoice_tax_id')
            ->leftJoin('penjualan_do', 'finance_invoice_mitra.do_id', '=', 'penjualan_do.id')
            ->leftJoin('master_customer_other_addresses', 'penjualan_do.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->selectRaw('
                finance_invoice_mitra.id AS id, 
                finance_invoice_mitra.code AS kode, 
                finance_invoice_mitra.date AS tanggal_buat, 
                finance_invoice_mitra.grand_total AS nominal_sub_total, 
                finance_invoice_mitra.status AS status, 
                master_customer_other_addresses.name AS customer_nama, 
                master_customer_other_addresses.text_kota AS customer_kota
            ')
            ->where('finance_invoice_mitra.type', 1)
            ->where(function ($query) use ($request) {
                if ($request->customer != 'all') {
                    $query->where('master_customer_other_addresses.id', $request->customer);
                }
                if (!empty($request->bulan)) {
                    $query->whereMonth('finance_invoice_mitra.date', $request->bulan);
                }
            })
            ->groupBy('finance_invoice_mitra.id')
            ->get();

        return $model;
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->addColumn('account_customer', function (InvoiceTax $model) {
            return $model->customer_nama . ' ' . $model->customer_kota;
        });

        return $table->make(true);
    }
}