<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\ProductType;
use Carbon\Carbon;

class ProductTypeTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $model = ProductType::select('id', 'code', 'name', 'status', 'created_at');
        
        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        $table->setRowClass(function (ProductType $model) {
            return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        });
        
        $table->editColumn('status', function (ProductType $model) {
            return $model->status();
        });

        $table->editColumn('created_at', function (ProductType $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('check', function (ProductType $model) {
            if ($model->status == $model::STATUS['DELETED']) {
                return "";
            } else {
                return "
                    <input type='checkbox' class='check-entity' value='" . $model->id . "' />
                ";
            }
        });

        $table->addColumn('action', function (ProductType $model) {
            $view = route('superuser.master.product_type.show', $model);
            $edit = route('superuser.master.product_type.edit', $model);
            $destroy = route('superuser.master.product_type.destroy', $model);

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
                <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                        <i class=\"fa fa-times\"></i>
                    </button>
                </a>
            ";
        });

        $table->rawColumns(['check', 'action']);

        return $table->make(true);
    }
}