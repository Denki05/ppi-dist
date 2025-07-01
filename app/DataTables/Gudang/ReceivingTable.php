<?php

namespace App\DataTables\Gudang;

use App\DataTables\Table;
use App\Entities\Gudang\Receiving;
use App\Entities\Master\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReceivingTable extends Table
{
    /**
     * Get query source of dataTable.
     */
    private function query()
    {
        return Receiving::select(
                'receiving.id',
                'receiving.code',
                'receiving.status',
                'master_warehouses.name as warehouse',
                'receiving.created_at',
                'receiving.pbm_date',
                'receiving.note'
            )
            ->join('master_warehouses', 'master_warehouses.id', '=', 'receiving.warehouse_id');
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());

        $table->addIndexColumn();

        $table->setRowClass(function (Receiving $model) {
            return $model->status === $model::STATUS['DELETED'] ? 'table-danger' : '';
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
                'timestamp' => $model->pbm_date
            ];
        });

        $table->editColumn('status', function (Receiving $model) {
            return $model->status();
        });

        $table->editColumn('warehouse', function (Receiving $model) {
            return $model->warehouse;
        });

        $table->addColumn('action', function (Receiving $model) {
            $view    = route('superuser.gudang.receiving.show', $model);
            $edit    = route('superuser.gudang.receiving.step', $model);
            $destroy = route('superuser.gudang.receiving.destroy', $model);
            $acc     = route('superuser.gudang.receiving.acc_ri', $model);
            $cancel  = route('superuser.gudang.receiving.cancel', $model);

            $btn = '';

            switch ($model->status) {
                case $model::STATUS['ACTIVE']:
                    $btn .= "
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
                    break;

                case $model::STATUS['QC']:
                    $btn .= "
                        <a href=\"{$edit}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Edit\">
                                <i class=\"fa fa-pencil\"></i>
                            </button>
                        </a>
                        <a href=\"javascript:saveConfirmation2('{$cancel}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Cancel\">
                                <i class=\"fa fa-undo\"></i>
                            </button>
                        </a>
                    ";
                    break;

                case $model::STATUS['READY']:
                    $btn .= "
                        <a href=\"{$view}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                                <i class=\"fa fa-eye\"></i>
                            </button>
                        </a>
                    ";

                    // Cek hak akses user untuk tombol ACC
                    $allowedRoles = ['Management', 'Admin', 'Developer'];
                    if (in_array(Auth::user()->division, $allowedRoles)) {
                        $btn .= "
                            <a href=\"javascript:saveConfirmation2('{$acc}')\">
                                <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Approve\">
                                    <i class=\"fa fa-check\"></i>
                                </button>
                            </a>
                        ";
                    }
                    break;

                default:
                    $btn .= "
                        <a href=\"{$view}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                                <i class=\"fa fa-eye\"></i>
                            </button>
                        </a>
                    ";
                    break;
            }

            return $btn;
        });

        return $table->make(true);
    }
}