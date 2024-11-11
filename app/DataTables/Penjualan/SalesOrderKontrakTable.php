<?php

namespace App\DataTables\Penjualan;

use App\DataTables\Table;
use App\Entities\Penjualan\SalesOrderKontrak;
use Carbon\Carbon;

class SalesOrderKontrakTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $model = SalesOrderKontrak::select(
            'penjualan_so_kontrak.id', 
            'penjualan_so_kontrak.code', 
            'penjualan_so_kontrak.contract_range', 
            'penjualan_so_kontrak.sales_senior', 
            'penjualan_so_kontrak.sales_junior', 
            'penjualan_so_kontrak.status', 
            'penjualan_so_kontrak.catatan', 
            'penjualan_so_kontrak.note', 
            'penjualan_so_kontrak.created_by', 
            'penjualan_so_kontrak.updated_by', 
            'penjualan_so_kontrak.acc_by',
            'penjualan_so_kontrak.created_at',
            'master_customer_other_addresses.name as customer_name',
            'master_customer_other_addresses.text_kota AS customer_kota',
            'master_products_packaging.code AS product_code', 
            'master_products_packaging.name AS product_name', 
            'master_packaging.pack_name AS product_pack',
        );

        $model = $model->leftJoin('penjualan_so_kontrak_item', 'penjualan_so_kontrak.id', '=', 'penjualan_so_kontrak_item.so_kontrak_id');
        $model = $model->leftJoin('master_products_packaging', 'penjualan_so_kontrak_item.product_packaging_id', '=', 'master_products_packaging.id');
        $model = $model->leftJoin('master_packaging', 'master_products_packaging.packaging_id', '=', 'master_packaging.id');
        $model = $model->leftJoin('master_customer_other_addresses', 'penjualan_so_kontrak.customer_other_address_id', '=', 'master_customer_other_addresses.id');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());

        $table->addIndexColumn();

        $table->setRowClass(function (SalesOrderKontrak $model) {

            switch ($model->status) {
                case $model::STATUS['DELETED']:
                    return 'table-danger';
                case $model::STATUS['ACC']:
                    return 'table-info';
                case $model::STATUS['ACTIVE']:
                    return 'table-active';    
                case $model::STATUS['COMPLETE']:
                    return 'table-success';
                default:
                    return '';
            }
        });
        
        $table->editColumn('status', function (SalesOrderKontrak $model) {
            return $model->status();
        });

        // $table->editColumn('updated_by', function (PurchaseOrder $model) {
        //     return $model->updateBySuperuser();
        // });

        $table->editColumn('created_at', function (SalesOrderKontrak $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('customer', function (SalesOrderKontrak $model) {
            return $model->customer_name . ' ' . $model->customer_kota;
        });

        $table->addColumn('product', function (SalesOrderKontrak $model) {
            return $model->product_code . ' - ' . $model->product_name. ' / ' .$model->product_pack;
        });

        $table->addColumn('action', function (SalesOrderKontrak $model) {
            $view = route('superuser.penjualan.sales_order_kontrak.show', base64_encode($model->id));
            $edit = route('superuser.penjualan.sales_order_kontrak.edit', base64_encode($model->id));
            $destroy = route('superuser.penjualan.sales_order_kontrak.destroy', base64_encode($model->id));
            $acc = route('superuser.penjualan.sales_order_kontrak.acc', base64_encode($model->id));
            $complete = route('superuser.penjualan.sales_order_kontrak.complete', base64_encode($model->id));
            $revisi = route('superuser.penjualan.sales_order_kontrak.revisi', base64_encode($model->id));
            $cancel_approve = route('superuser.penjualan.sales_order_kontrak.cancel_aprove', base64_encode($model->id));
            // $pdf = route('superuser.gudang.purchase_order.print_pdf', $model);
            // $cancel_acc = route('superuser.gudang.purchase_order.cancel_acc', $model);

            switch ($model->status) {
                case $model::STATUS['ACTIVE']:
                    return "
                        <a href=\"javascript:saveConfirmation('{$acc}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-success\" title=\"Acc\">
                                <i class=\"fa fa-check\"></i>
                            </button>
                        </a>

                        <a href=\"{$edit}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Edit\">
                                <i class=\"fa fa-pencil\"></i>
                            </button>
                        </a>

                        <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-success\" title=\"Deleted\">
                                <i class=\"fa fa-trash\"></i>
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
                        <a href=\"javascript:saveConfirmation('{$cancel_approve}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Cancel Approve\">
                                <i class=\"fa fa-times\"></i>
                            </button>
                        </a>

                        <a href=\"javascript:saveConfirmation('{$complete}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-success\" title=\"Complete\">
                                <i class=\"fa fa-check-square-o\"></i>
                            </button>
                        </a>
                        
                        <a href=\"{$revisi}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-success\" title=\"Revisi\">
                                <i class=\"fa fa-sync\"></i>
                            </button>
                        </a>
                    ";

                case $model::STATUS['COMPLETE']:
                        return "
                            <a href=\"\">
                                <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                                    <i class=\"fa fa-eye\"></i>
                                </button>
                            </a>
                        ";
                default:
                    return "
                        <a href=\"{$view}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-success\" title=\"View\">
                                <i class=\"fa fa-eye\"></i>
                            </button>
                        </a>
                    ";
            }

        });

        return $table->make(true);
    }
}