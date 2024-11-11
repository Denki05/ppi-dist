<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use App\Entities\Finance\Payable;
use App\Entities\Finance\PayableDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class PaymentReportTable extends Table
{
    private function query(Request $request)
    {
        $model = Payable::leftJoin('finance_payable_detail', 'finance_payable.id', '=', 'finance_payable_detail.payable_id')
            ->leftJoin('finance_invoicing', 'finance_payable_detail.invoice_id', '=', 'finance_invoicing.id')
            ->leftJoin('master_customer_other_addresses', 'finance_invoicing.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->selectRaw('
                finance_payable.id AS id,
                finance_payable.code AS payable_code,
                finance_payable.pay_date AS payable_date,
                finance_payable_detail.total AS payable_total, 
                finance_invoicing.grand_total_idr AS invoice_total, 
                finance_invoicing.code AS invoice_code, 
                master_customer_other_addresses.name AS customer_name, 
                master_customer_other_addresses.text_kota AS customer_city
            ')
            ->where('finance_payable.status', Payable::STATUS['ACC'])
            ->whereBetween('finance_payable.pay_date', [$request->startDate, $request->endDate])
            ->get();

        return $model;
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->addColumn('account_customer', function (Payable $model) {
            return $model->customer_name . ' ' . $model->customer_city;
        });

        $table->editColumn('payable_date', function (Payable $model) {
            return [
            'display' => Carbon::parse($model->payable_date)->format('d-m-Y'),
            'timestamp' => $model->payable_date
            ];
        });

        return $table->make(true);
    }
}