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
        $model = Cashback::leftJoin('finance_cashback_detail', 'finance_cashback.id', '=', 'finance_cashback_detail.cashback_id')
            ->leftJoin('penjualan_do', 'finance_cashback.do_id', '=', 'penjualan_do.id')
            ->leftJoin('master_customer_other_addresses', 'finance_cashback.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->select(
                'finance_cashback.id AS id',
                'master_customer_other_addresses.name AS customer_name', 
                'master_customer_other_addresses.text_kota AS customer_city', 
                'penjualan_do.do_code AS invoice_code', 
                'finance_cashback.created_at AS tanggal_buat', 
                DB::raw('IFNULL(SUM(finance_cashback_detail.subtotal_item_idr), 0) AS total_jual'), 
                DB::raw('IFNULL(SUM(finance_cashback_detail.amount_cashback), 0) AS total_beli')
            )
            ->where('finance_cashback.status', Cashback::STATUS['ACTIVE'])
            ->whereBetween('finance_cashback.created_at', [$request->startDate . " 00:00:00", $request->endDate . " 23:59:59"])
            ->where(function ($query) use ($request) {
                if ($request->customer != 'all') {
                    $query->where('master_customer_other_addresses.id', $request->customer);
                }
            })
            ->groupBy(
                'finance_cashback.id', 
                'master_customer_other_addresses.name', 
                'master_customer_other_addresses.text_kota', 
                'penjualan_do.do_code', 
                'finance_cashback.created_at'
            )
            ->get();


        return $model;
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        // $table->editColumn('tanggal_buat', function (Cashback $model) {
        //     return [
        //     'display' => Carbon::parse($model->tanggal_buat)->format('d-m-Y'),
        //     'timestamp' => $model->tanggal_buat
        //     ];
        // });

        $table->addColumn('account_customer', function (Cashback $model) {
            return $model->customer_name . ' ' . $model->customer_city;
        });

        $table->addColumn('selisih_cashback', function (Cashback $model) {
            return $model->total_jual - $model->total_beli;
        });

        return $table->make(true);
    }
}