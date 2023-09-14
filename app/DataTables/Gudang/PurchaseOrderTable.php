<?php

namespace App\DataTables\Gudang;

use App\DataTables\Table;
use App\Entities\Gudang\PurchaseOrder;
use Carbon\Carbon;

class PurchaseOrderTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $model = PurchaseOrder::select('id', 'code', 'edit_counter', 'updated_by', 'status', 'created_at', 'updated_by');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());

        $table->addIndexColumn();

        $table->setRowClass(function (PurchaseOrder $model) {

            switch ($model->status) {
                case $model::STATUS['DELETED']:
                    return 'table-danger';
                // case $model::STATUS['DRAFT']:
                //     return 'table-secondary';
                // case $model::STATUS['ACC']:
                //     return 'table-success';
                // case $model::STATUS['ACTIVE']:
                //     return 'table-info';    
                default:
                    return '';
            }
        });
        
        $table->editColumn('status', function (PurchaseOrder $model) {
            return $model->status();
        });

        $table->editColumn('updated_by', function (PurchaseOrder $model) {
            return $model->updateBySuperuser();
        });

        $table->editColumn('created_at', function (PurchaseOrder $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('action', function (PurchaseOrder $model) {
            $view = route('superuser.gudang.purchase_order.show', $model);
            $edit = route('superuser.gudang.purchase_order.step', $model);
            $destroy = route('superuser.gudang.purchase_order.destroy', $model);
            $acc = route('superuser.gudang.purchase_order.acc', $model);

            $html_view = "
                <a href=\"{$view}\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                        <i class=\"mdi mdi-eye\"></i>
                    </button>
                </a>
            ";

            $html_edit = "
                <a href=\"{$edit}\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Edit\">
                        <i class=\"mdi mdi-lead-pencil\"></i>
                    </button>
                </a>
            ";

            $html_destroy = "
                <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                        <i class=\"mdi mdi-delete\"></i>
                    </button>
                </a>
            ";

            $html_acc = "
                <a href=\"javascript:deleteConfirmation('{$acc}')\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Acc\">
                        <i class=\"mdi mdi-checkbox-marked-outline\"></i>
                    </button>
                </a>
            ";

            if ($model->status == $model::STATUS['ACTIVE']) {
                return $html_acc . $html_edit . $html_destroy;
            }
            
            if ($model->status == $model::STATUS['DRAFT']) {
                return $html_edit . $html_destroy;
            }

            if ($model->status == $model::STATUS['ACC']) {
                return $html_view;
            }

            if ($model->status == $model::STATUS['DELETED']) {
                return $html_view;
            }
        });

        return $table->make(true);
    }
}