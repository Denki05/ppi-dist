<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\Product;
use Carbon\Carbon;

class ProductTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $model = Product::select('master_products.id', 'master_products.code', 'master_product_categories.name as category_name', 'master_products.name', 'master_products.status', 'master_products.created_at')
        ->join('master_product_categories', 'master_product_categories.id', '=', 'master_products.category_id');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        $table->setRowClass(function (Product $model) {
            if ($model->status == $model::STATUS['DELETED']) {
                return 'table-danger';
            } else if ($model->status == $model::STATUS['INACTIVE']) {
                return 'table-warning';
            }
        });
        
        $table->editColumn('name', function (Product $model) {
            $view = route('superuser.master.product.show', $model);
            return "<a href=\"{$view}\">$model->name</a>";
        });
        
        $table->editColumn('status', function (Product $model) {
            return $model->status();
        });

        $table->editColumn('created_at', function (Product $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('check', function (Product $model) {
            if ($model->status == $model::STATUS['DELETED']) {
                return "";
            } else {
                return "
                    <input type='checkbox' class='check-entity' value='" . $model->id . "' />
                ";
            }
        });

        $table->addColumn('action', function (Product $model) {
            $view = route('superuser.master.product.show', $model);
            $edit = route('superuser.master.product.edit', $model);
            $destroy = route('superuser.master.product.destroy', $model);

            if ($model->status == $model::STATUS['DELETED']) {
                return "
                    <a href=\"{$view}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                            <i class=\"fa fa-eye\"></i>
                        </button>
                    </a>
                ";
            }

            return "
                <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                        <i class=\"fa fa-times\"></i>
                    </button>
                </a>
            ";
        });

        $table->rawColumns(['name', 'check', 'action']);

        return $table->make(true);
    }
}