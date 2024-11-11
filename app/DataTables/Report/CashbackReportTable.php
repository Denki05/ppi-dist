<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use App\Entities\Finance\Cashback;
use App\Entities\Finance\CashbackItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class CashbackReportTable extends Table
{
    private function query(Request $request)
    {
        $model = Cashback::where('finance_cashback.status', 1)
            ->leftJoin('finance_cashback_detail', 'finance_cashback.id', '=', 'finance_cashback_detail.cashback_id')
            ->leftJoin('penjualan_do', 'finance_cashback.do_id', '=', 'penjualan_do.id')
            ->leftJoin('master_customer_other_addresses', 'finance_cashback.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->leftJoin('master_products_packaging', 'finance_cashback_detail.product_packaging_id', '=', 'master_products_packaging.id')
            ->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id')
            ->selectRaw('
                master_customer_other_addresses.name AS customer_name,
                master_customer_other_addresses.text_kota AS customer_city,
                penjualan_do.do_code AS invoice_code,
                master_products_packaging.code AS product_code, 
                master_products_packaging.name AS product_name, 
                master_packaging.pack_name AS packaging_name, 
                SUM(finance_cashback_detail.amount_cashback) AS amount_cashback, 
                finance_cashback.created_at AS tanggal_buat
            ')
            ->whereBetween('finance_cashback.created_at', [$request->startDate, $request->endDate])
            ->where(function ($query) use ($request) {
                if ($request->customer != 'all') {
                    $query->where('master_customer_other_addresses.id', $request->customer);
                }
            })
            ->groupBy('finance_cashback.code')
            ->get();

        return $model;
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->editColumn('tanggal_buat', function (Cashback $model) {
            return [
            'display' => Carbon::parse($model->tanggal_buat)->format('d-m-Y'),
            'timestamp' => $model->tanggal_buat
            ];
        });

        $table->addColumn('account_customer', function (Cashback $model) {
            return $model->customer_name . ' ' . $model->customer_city;
        });

        $table->addColumn('product_list', function (Cashback $model) {
            return $model->product_code . ' - ' . $model->product_name . ' / ' . $model->packaging_name;
        });

        return $table->make(true);
    }
}