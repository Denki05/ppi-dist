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

            switch ($model->status) {
                case $model::STATUS['ACTIVE']:
                    return "
                        <a href=\"javascript:saveConfirmation2('{$acc}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-success\" title=\"ACC\">
                                <i class=\"fa fa-check\"></i>
                            </button>
                        </a>
                        <a href=\"{$edit}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Edit\">
                                <i class=\"fa fa-pencil\"></i>
                            </button>
                        </a>
                        <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                                <i class=\"fa fa-times\"></i>
                            </button>
                        </a>
                    ";
                case $model::STATUS['DRAFT']:
                    return "
                        <a href=\"{$edit}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Edit\">
                                <i class=\"fa fa-pencil\"></i>
                            </button>
                        </a>
                        <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                                <i class=\"fa fa-times\"></i>
                            </button>
                        </a>
                    ";
                case $model::STATUS['ACC']:
                    return "
                        <a href=\"{$view}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                                <i class=\"fa fa-eye\"></i>
                            </button>
                        </a>
                    ";
                default:
                    return "
                        <a href=\"{$view}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                                <i class=\"fa fa-eye\"></i>
                            </button>
                        </a>
                    ";
            }

        });

        return $table->make(true);
    }
}