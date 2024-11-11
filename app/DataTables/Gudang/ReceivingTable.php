<?php

namespace App\DataTables\Gudang;

use App\DataTables\Table;
use App\Entities\Gudang\Receiving;
use App\Entities\Master\Warehouse;
use Carbon\Carbon;

class ReceivingTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $model = Receiving::select('receiving.id', 'receiving.code', 'receiving.status', 'master_warehouses.name as warehouse', 'receiving.created_at', 'receiving.pbm_date', 'receiving.note')
            ->join('master_warehouses', 'master_warehouses.id', '=', 'receiving.warehouse_id');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        $table->setRowClass(function (Receiving $model) {

            switch ($model->status) {
                case $model::STATUS['DELETED']:
                    return 'table-danger';
                // case $model::STATUS['ACTIVE']:
                //     return 'table-primary';
                default:
                    return '';
            }
        });

        $table->editColumn('created_at', function (Receiving $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });
        
        $table->editColumn('pbm_date', function (Receiving $model) {
            return [
              'display' => Carbon::parse($model->pbm_date)->format('d/m/Y'),
              'timestamp' => $model->created_at
            ];
        });
        
        $table->editColumn('status', function (Receiving $model) {
            return $model->status();
        });

        $table->editColumn('warehouse', function (Receiving $model) {
            return $model->warehouse;
        });

        $table->addColumn('action', function (Receiving $model) {
            $view = route('superuser.gudang.receiving.show', $model);
            $edit = route('superuser.gudang.receiving.step', $model);
            $destroy = route('superuser.gudang.receiving.destroy', $model);
            $acc = route('superuser.gudang.receiving.acc', $model);

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