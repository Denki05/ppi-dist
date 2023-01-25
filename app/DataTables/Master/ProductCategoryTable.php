<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\ProductCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;

class ProductCategoryTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    public function query(Request $request)
    {  
        $model = ProductCategory::select(
                'master_product_category.id AS id', 
                'master_product_category.code AS code', 
                'master_brand_lokal.brand_name AS brandName', 
                'master_brand_lokal.id AS brand_id', 
                'master_product_category.name AS name', 
                'master_product_category.type AS type',
                'master_product_category.status AS status', 
                'master_product_category.created_at AS category_date', 
                'master_packaging.pack AS packaging',
        );

        $model = $model->leftJoin('master_brand_lokal', 'master_product_category.brand_lokal_id', '=', 'master_brand_lokal.id');

        $model = $model->leftJoin('master_packaging', 'master_product_category.packaging_id', '=', 'master_packaging.id');

        if($request->brand_ppi != 'all') {
            $model = $model->where('master_brand_lokal.id', $request->brand_ppi);
        }
        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->setRowClass(function (ProductCategory $model) {
            return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        });

        $table->editColumn('status', function (ProductCategory $model) {
            return $model->status();
        });

        $table->editColumn('category_date', function (ProductCategory $model) {
            return [
              'display' => Carbon::parse($model->category_date)->format('j F Y H:i:s'),
              'timestamp' => $model->category_date
            ];
        });

        $table->addColumn('action', function (ProductCategory $model) {
            $view = route('superuser.master.product_category.show', $model);
            $destroy = route('superuser.master.product_category.destroy', $model);

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
                <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                        <i class=\"fa fa-times\"></i>
                    </button>
                </a>
            ";
        });

        return $table->make(true);
    }
}