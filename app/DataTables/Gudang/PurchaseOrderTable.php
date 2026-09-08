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
        $model = PurchaseOrder::select('id', 'code', 'warehouse_id', 'brand_lokal_id', 'etd', 'edit_counter', 'updated_by', 'status', 'sub_type', 'count_send_spk', 'note', 'created_at')
                ->where('type', PurchaseOrder::TYPE['PO'])
                ->with(['warehouse', 'brandLokal']);

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
            $status = $model->status();
            $map = [
                'DRAFT' => 'warning',
                'ACTIVE' => 'info',
                'ACC' => 'success',
                'SENT' => 'primary',
                'DELETED' => 'danger',
            ];
            $color = isset($map[$status]) ? $map[$status] : 'secondary';

            return '<span class="badge badge-' . $color . '">' . $status . '</span>';
        });

        $table->addColumn('warehouse', function (PurchaseOrder $model) {
            return optional($model->warehouse)->name ?? '-';
        });

        $table->addColumn('brand', function (PurchaseOrder $model) {
            return optional($model->brandLokal)->brand_name ?? '-';
        });

        $table->editColumn('etd', function (PurchaseOrder $model) {
            return $model->etd ? Carbon::parse($model->etd)->format('d-m-Y') : '-';
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
            $pdf = route('superuser.gudang.purchase_order.print_pdf', $model);
            $cancel_acc = route('superuser.gudang.purchase_order.cancel_acc', $model);
            $sent = route('superuser.gudang.purchase_order.send', $model);
            $cancel_send = route('superuser.gudang.purchase_order.cancel_send', $model);
            $send_spk = route('superuser.gudang.purchase_order.send_spk', $model);

            switch ($model->status) {
                case $model::STATUS['ACTIVE']:
                    return "
                        <a href=\"{$view}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                                <i class=\"fa fa-eye\"></i>
                            </button>
                        </a>
                        <a href=\"{$edit}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Edit\">
                                <i class=\"fa fa-pencil\"></i>
                            </button>
                        </a>
                        <a href=\"javascript:saveConfirmation('{$acc}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-success\" title=\"ACC\">
                                <i class=\"fa fa-check\"></i>
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

                        <a href=\"{$pdf}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Print Out\">
                                <i class=\"fa fa-print\"></i>
                            </button>
                        </a>

                        <a href=\"javascript:saveConfirmation('{$cancel_acc}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Cancel Approve\">
                                <i class=\"fa fa-refresh\"></i>
                            </button>
                        </a>

                        <a href=\"javascript:saveConfirmation('{$sent}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Send\">
                                <i class=\"fa fa-paper-plane\"></i>
                            </button>
                        </a>
                        
                    ";

                    case $model::STATUS['SENT']:

                        $btnSendSpk = '';

                        // \Log::info('DEBUG SPK', [
                        //     'id' => $model->id,
                        //     'status' => $model->status,
                        //     'sub_type_attr' => $model->getAttributes()['sub_type'],
                        //     'sub_type_cast' => $model->sub_type,
                        //     'count_send_spk' => $model->count_send_spk,
                        // ]);                        
                    
                        if (
                            $model->sub_type === PurchaseOrder::SUB_TYPE['INDUSTRI'] &&
                            $model->count_send_spk === 0
                        ) {
                            $btnSendSpk = "
                                <a href=\"javascript:saveConfirmation('{$send_spk}')\">
                                    <button type=\"button\"
                                            class=\"btn btn-sm btn-circle btn-alt-success\"
                                            title=\"Generate SPK\">
                                        <i class=\"fa fa-industry\"></i>
                                    </button>
                                </a>
                            ";
                        }
                    
                        return "
                            <a href=\"{$view}\">
                                <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                                    <i class=\"fa fa-eye\"></i>
                                </button>
                            </a>
                    
                            <a href=\"{$pdf}\" target=\"_blank\">
                                <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Print Out\">
                                    <i class=\"fa fa-print\"></i>
                                </button>
                            </a>
                    
                            <a href=\"javascript:saveConfirmation2('{$cancel_send}')\">
                                <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Cancel\">
                                    <i class=\"fa fa-ban\"></i>
                                </button>
                            </a>
                    
                            {$btnSendSpk}
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

        $table->rawColumns(['action', 'status']);

        return $table->make(true);
    }
}