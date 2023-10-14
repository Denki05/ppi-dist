<?php

namespace App\DataTables\Finance;

use App\DataTables\Table;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Finance\Payable;
use App\Entities\Finance\PayableDetail;
use App\Entities\Finance\Invoicing;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayableTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $model = Payable::select(
            'finance_payable.code as payableCode', 
            'finance_payable.status as status', 
            'finance_payable_detail.total as payableTotal', 
            'master_customers.id as id',
            'master_customers.name as customer',
            'master_customers.text_kota as customerCity',
            'finance_invoicing.code as invoiceCode'
        );

        $model = $model->leftJoin('finance_payable_detail', 'finance_payable_detail.payable_id', '=', 'finance_payable.id');
        $model = $model->leftJoin('finance_invoicing', 'finance_invoicing.id', '=', 'finance_payable_detail.invoice_id');
        $model = $model->leftJoin('master_customers', 'master_customers.id', '=', 'finance_payable.customer_id');


        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->setRowClass(function (Payable $model) {
            return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        });

        $table->editColumn('status', function (Payable $model) {
            return $model->status();
        });

        $table->editColumn('created_at', function (Payable $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('action', function (Payable $model) {
            $add_payable = route('superuser.finance.payable.create', " 'id' => '$model->customer_id'");
            
            return "
                <a href=\"{$add_payable}\" class=\"btn btn-primary btn-lg active\" role=\"button\" aria-pressed=\"true\">Create</a>
            ";
        });
        
        return $table->make(true);
    }
}