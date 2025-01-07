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
        'finance_payable.id AS id', 
        'finance_payable.code AS code', 
        'master_customers.id AS customer_id', 
        'master_customers.name AS customer_name', 
        'master_customers.text_kota AS customer_kota', 
        'finance_payable.total AS total_pay',
        'finance_payable.pay_date AS payable_date',
        'finance_payable.created_at AS created_at',
        'finance_payable.status AS status',
        'finance_invoicing.id AS invoice_id',
        'finance_invoicing.code AS invoice_code',
        'finance_payable_detail.total AS total_payable_item',
    )
    ->leftJoin('master_customers', 'master_customers.id', '=', 'finance_payable.customer_id')
    ->leftJoin('finance_payable_detail', 'finance_payable.id', '=', 'finance_payable_detail.payable_id')
    ->leftJoin('finance_invoicing', 'finance_payable_detail.invoice_id', '=', 'finance_invoicing.id')
    ->where(function ($query) use ($request) {
        if ($request->customer_name != 'all') {
            $query->where('finance_payable.customer_id', $request->customer_name);
        }
    })
    ->where(function ($query) use ($request) {
        if ($request->status != 'all') {
            $query->where('finance_payable.status', $request->status);
        }
    });

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

        $table->editColumn('invoice_code', function (Payable $model) {
            return "<a href=\"#\" class=\"view-invoice-code\" data-id=\"$model->invoice_id\" data-toggle=\"modal\" data-target=\"#modalInvoiceCode\">$model->invoice_code</a>";
        });

        $table->editColumn('status', function (Payable $model) {
            return $model->status();
        });

        $table->editColumn('payable_date', function (Payable $model) {
            return [
                'display' => Carbon::parse($model->payable_date)->format('d-m-Y'),
                'timestamp' => $model->payable_date
            ];
        });

        $table->editColumn('created_at', function (Payable $model) {
            return [
                'display' => Carbon::parse($model->created_at)->format('d-m-Y'),
                'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('customer', function (Payable $model) {
            return $model->customer_name . ' ' . $model->customer_kota;
        });

        $table->editColumn('total_pay', function ($model) {
            return 'Rp ' . number_format($model->total_pay, 0, ',', '.');
        });

        $table->editColumn('total_payable_item', function ($model) {
            return 'Rp ' . number_format($model->total_payable_item, 0, ',', '.');
        });

        $table->addColumn('action', function (Payable $model) {
            $view = route('superuser.finance.payable.detail', $model->id);
            $edit = route('superuser.finance.payable.edit', $model->id);
            $acc = route('superuser.finance.payable.approve', $model->id);
            $destroy = route('superuser.finance.payable.destroy', $model->id);
            $cancel_approved = route('superuser.finance.payable.cancel_approve', $model->id);
            $update_new = route('superuser.finance.payable.cancel_edit', $model->id);
        
            if ($model->status == $model::STATUS['DELETED']) {
                return "
                    <a href=\"{$view}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                            <i class=\"fa fa-eye\"></i>
                        </button>
                    </a>
                ";
            }
        
            if ($model->status == $model::STATUS['ACTIVE']) {
                return "
                    <a href=\"{$edit}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Edit\">
                            <i class=\"fa fa-pencil\"></i>
                        </button>
                    </a>
        
                    <a href=\"javascript:saveConfirmation('{$acc}')\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Acc\">
                            <i class=\"fa fa-check\"></i>
                        </button>
                    </a>
        
                    <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Destroy\">
                            <i class=\"fa fa-trash\"></i>
                        </button>
                    </a>
                ";
            }
        
            if ($model->status == $model::STATUS['ACC']) {
                $buttons = "
                    <a href=\"{$view}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                            <i class=\"fa fa-eye\"></i>
                        </button>
                    </a>
                ";
        
                // Show Destroy button if the user is a superuser
                if (auth()->user()->is_superuser) {
                    $buttons .= "
                        <a href=\"javascript:saveConfirmation('{$cancel_approved}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Cancel Approve\">
                                <i class=\"fa fa-times\"></i>
                            </button>
                        </a>
                    ";
                }
        
                return $buttons;
            }
        
            if ($model->status == $model::STATUS['REVISI']) {
                $buttons = "
                    <a href=\"{$update_new}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                            <i class=\"fa fa-pencil\"></i>
                        </button>
                    </a>
                ";
        
                // Show Destroy button if the user is a superuser
                if (auth()->user()->is_superuser) {
                    $buttons .= "
                        <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Destroy\">
                                <i class=\"fa fa-trash\"></i>
                            </button>
                        </a>
                    ";
                }
        
                return $buttons;
            }
        });        

        $table->rawColumns(['invoice_code', 'action']);
        
        return $table->make(true);
    }
}