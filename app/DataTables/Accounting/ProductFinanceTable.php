<?php

namespace App\DataTables\Accounting;

use App\DataTables\Table;
use App\Entities\Master\ProductFinance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class ProductFinanceTable extends Table
{
    private function query(Request $request)
    {
        $mitra = $request->input('mitra');

        $model = ProductFinance::LeftJoin('master_packaging', 'master_product_finance.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('master_products', 'master_product_finance.product_id', '=', 'master_products.id')
            ->leftJoin('master_mitra', 'master_product_finance.mitra_id', '=', 'master_mitra.id')
            ->select(
                'master_product_finance.id as id', 
                'master_product_finance.brand_name as brand',
                'master_product_finance.code_product as kode',
                'master_product_finance.name_product as name',
                'master_product_finance.selling_price_usd_unit as uv_jual',
                'master_product_finance.buying_price_usd_unit as uv_beli',
                'master_product_finance.status as status',
                'master_packaging.pack_name as packaging_name', 
                'master_mitra.id as id_mitra',
                'master_mitra.name as mitra_name',
            )
            ->where('master_mitra.id', $mitra)
            ->get();

            return $model;
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->editColumn('status', function (ProductFinance $model) {
            return $model->status();
        });

        $table->editColumn('uv_jual', function (ProductFinance $model) {
            return number_format($model->uv_jual, 2);
        });

        $table->editColumn('uv_beli', function (ProductFinance $model) {
            return number_format($model->uv_beli, 2);
        });

        $table->addColumn('action', function (ProductFinance $model) {
            if ($model->status == ProductFinance::STATUS['ACTIVE']) {
                return '<button type="button" class="btn bg-gd-leaf border-0 text-black openModalUpdateCost" 
                            data-toggle="modal" 
                            data-target="#updateCost" 
                            data-id="' . base64_encode($model->id) . '">
                            <i class="fa fa-money ml-10"></i>
                        </button>';
            }
        });
        
        // Mark the column as raw to allow HTML rendering
        $table->rawColumns(['action']);

        return $table->make(true);
    }
}