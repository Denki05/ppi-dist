<?php

namespace App\DataTables\Gudang;

use App\DataTables\Table;
use App\Entities\Gudang\QualityControl;
use App\Entities\Master\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class QualityControlTable extends Table
{
    /**
     * Get query source of dataTable.
     */
    private function query()
    {
        return QualityControl::select(
                'receiving.id',
                'receiving.code',
                'receiving.status',
                'master_warehouses.name as warehouse',
                'receiving.created_at',
                'receiving.pbm_date',
                'receiving.note',
                'receiving_detail.po_id'
            )
            ->where('receiving.type', 1)
            ->join('master_warehouses', 'master_warehouses.id', '=', 'receiving.warehouse_id')
            ->join('receiving_detail', 'receiving_detail.receiving_id', 'receiving.id');
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());

        $table->addIndexColumn();

        $table->setRowClass(function (QualityControl $model) {
            return $model->status === $model::STATUS['DELETED'] ? 'table-danger' : '';
        });

        $table->editColumn('created_at', function (QualityControl $model) {
            return [
                'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
                'timestamp' => $model->created_at
            ];
        });

        $table->editColumn('pbm_date', function (QualityControl $model) {
            return [
                'display' => Carbon::parse($model->pbm_date)->format('d/m/Y'),
                'timestamp' => $model->pbm_date
            ];
        });

        $table->editColumn('status', function (QualityControl $model) {
            return $model->status();
        });

        $table->editColumn('warehouse', function (QualityControl $model) {
            return $model->warehouse;
        });

        $table->addColumn('action', function (QualityControl $model) {
            $view    = route('superuser.gudang.quality_control.show', $model);
            $edit    = route('superuser.gudang.quality_control.step', $model);
            $destroy = route('superuser.gudang.quality_control.destroy', $model);
            $acc     = route('superuser.gudang.quality_control.acc_ri', $model);
            $cancel  = route('superuser.gudang.quality_control.cancel', $model);
            $pdf    = route('superuser.penjualan.sale_return.pdf_sj', $model->po_id);

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
                                <i class=\"fa fa-trash\"></i>
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
                    
                case $model::STATUS['ACC']:
                    $btn .= "
                        <a href=\"{$view}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                                <i class=\"fa fa-eye\"></i>
                            </button>
                        </a>
                        <a href=\"{$pdf}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-info\" title=\"PDF\">
                                <i class=\"fa fa-file-pdf-o\"></i>
                            </button>
                        </a>
                    ";
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