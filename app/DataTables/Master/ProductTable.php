<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $model = Product::select(
            'master_products.id', 
            'master_products.code', 
            'master_products.name', 
            'master_products.brand_name as brand_name', 
            'master_product_categories.name as category_name', 
            'master_products.name', 'master_products.status', 
            'master_products.created_at'
        )
        ->where(function ($query) use ($request) {
            if ($request->product_name != 'all') {
                $query->where('master_products.name', $request->product_name);
            } else {
                $query;
            }
        })
        ->where(function ($query) use ($request) {
            if ($request->brand_lokal != 'all') {
                $query->where('master_products.brand_name', $request->brand_lokal);
            } else {
                $query;
            }
        })
        ->where(function ($query) use ($request) {
            if ($request->kategori != 'all') {
                $query->where('master_product_categories.id', $request->kategori);
            } else {
                $query;
            }
        })
        ->where(function ($query) use ($request) {
            if ($request->status != 'all') {
                $query->where('master_products.status', $request->status);
            } else {
                $query;
            }
        })
        ->join('master_product_categories', 'master_product_categories.id', '=', 'master_products.category_id')
        ->get();

        // dd($model);

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        // $table->addIndexColumn();

        $table->setRowClass(function (Product $model) {
            if ($model->status == $model::STATUS['DELETED'] OR $model->status == $model::STATUS['DISABLE']) {
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

            $view = route('superuser.master.product.show', base64_encode($model->id));
            $edit = route('superuser.master.product.edit', base64_encode($model->id));
            $destroy = route('superuser.master.product.destroy', $model);

            if ($model->status == $model::STATUS['DELETED']) {
                return "
                        <a href=\"{$view}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"View\">
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
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Edit\">
                            <i class=\"fa fa-pencil\"></i>
                        </button>
                    </a>
                    
                    <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                            <i class=\"fa fa-trash\"></i>
                        </button>
                    </a>
                ";

            
        });

        $table->addColumn('action2', function (Product $model) {
            $inactive = route('superuser.master.product.inactiveStatus', base64_encode($model->id));
            $active = route('superuser.master.product.activeStatus', base64_encode($model->id));

            if ($model->status == $model::STATUS['ACTIVE']) {
                return "
                    <a href=\"javascript:saveConfirmation('{$inactive}')\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Inactive Status\">
                            <i class=\"fa fa-unlock-alt\" aria-hidden=\"true\"></i>
                        </button>
                    </a>
                ";
            }

            if ($model->status == $model::STATUS['INACTIVE']) {
                return "
                    <a href=\"javascript:saveConfirmation('{$active}')\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Active Status\">
                            <i class=\"fa fa-lock\" aria-hidden=\"true\"></i>
                        </button>
                    </a>
                ";
            }
        });

        $table->rawColumns(['name', 'check', 'action', 'action2']);

        return $table->make(true);
    }
}