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
        $model = Product::select('master_product.id', 'master_product.code', 'master_product.brand_name as brand_name', 'master_product_category.name as category_name', 'master_product.name', 'master_product.status', 'master_product.created_at', 'master_packaging.pack_name')
        ->join('master_product_category', 'master_product_category.id', '=', 'master_product.category_id')
        ->join('master_packaging', 'master_product_category.packaging_id', '=', 'master_packaging.id')
        ->get();

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

            if ($model->status == $model::STATUS['ACTIVE']) {
                return "
                    <a href=\"{$view}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                            <i class=\"mdi mdi-eye\"></i>
                        </button>
                    </a>
                    <a href=\"{$edit}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Edit\">
                            <i class=\"mdi mdi-lead-pencil\"></i>
                        </button>
                    </a>
                    
                    <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                        <i class=\"mdi mdi-delete\"></i>
                    </button>
                </a>
                ";
            }

            return "
                        <a href=\"{$view}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"View\">
                            <i class=\"mdi mdi-eye\"></i>
                        </button>
                        </a>
            ";
        });

        $table->rawColumns(['name', 'check', 'action']);

        return $table->make(true);
    }
}