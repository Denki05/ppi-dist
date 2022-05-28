<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Gudang\StockSalesOrder;
use Carbon\Carbon;

class StockSalesOrderTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $model = StockSalesOrder::SELECT('master_warehouses.name AS warehouse', 'master_products.name AS productName', 'stock_sales_order.quantity AS qty', 'stock_sales_order.created_at AS date')
                                ->leftJoin('master_warehouses', 'stock_sales_order.warehouse_id', '='. 'master_warehouses.id')
                                ->leftJoin('master_products', 'stock_sales_order.product_id', '=', 'master_products.id');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        // $table->setRowClass(function (Warehouse $model) {
        //     return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        // });

        // // $table->editColumn('type', function (Warehouse $model) {
        // //     return $model->type();
        // // });
        
        // $table->editColumn('status', function (Warehouse $model) {
        //     return $model->status();
        // });

        $table->editColumn('created_at', function (StockSalesOrder $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        // $table->addColumn('action', function (Warehouse $model) {
        //     $view = route('superuser.master.warehouse.show', $model);
        //     $edit = route('superuser.master.warehouse.edit', $model);
        //     $destroy = route('superuser.master.warehouse.destroy', $model);

        //     $html_view = "
        //         <a href=\"{$view}\">
        //             <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
        //                 <i class=\"fa fa-eye\"></i>
        //             </button>
        //         </a>
        //     ";

        //     $html_edit = "
        //         <a href=\"{$edit}\">
        //             <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Edit\">
        //                 <i class=\"fa fa-pencil\"></i>
        //             </button>
        //         </a>
        //     ";

        //     $html_destroy = "
        //         <a href=\"javascript:deleteConfirmation('{$destroy}')\">
        //             <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
        //                 <i class=\"fa fa-times\"></i>
        //             </button>
        //         </a>
        //     ";

        //     if ($model->status == $model::STATUS['DELETED']) {
        //         return $html_view;
        //     }
            
        //     if ($model->type == $model::TYPE['HEAD_OFFICE']) {
        //         return $html_view . $html_edit;
        //     } else {
        //         return $html_view . $html_edit . $html_destroy;
        //     }
        // });

        return $table->make(true);
    }
}