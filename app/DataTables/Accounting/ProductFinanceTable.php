<?php

namespace App\DataTables\Accounting;

use App\DataTables\Table;
use App\Entities\Master\ProductFinance;
use Illuminate\Http\Request;

class ProductFinanceTable extends Table
{
    /**
     * Kembalikan query builder (tanpa ->get()) agar Yajra bisa
     * paging / sorting / searching di SQL, tidak load semua ke memori.
     */
    private function query(Request $request)
    {
        $mitra = $request->input('mitra');

        return ProductFinance::leftJoin('master_packaging', 'master_product_finance.packaging_id', '=', 'master_packaging.id')
            ->leftJoin('master_products', 'master_product_finance.product_id', '=', 'master_products.id')
            ->leftJoin('master_mitra', 'master_product_finance.mitra_id', '=', 'master_mitra.id')
            ->when(empty($mitra), function ($q) {
                // Belum pilih mitra: kembalikan kosong cepat, jangan load semua.
                $q->whereRaw('1 = 0');
            }, function ($q) use ($mitra) {
                $q->where('master_mitra.id', $mitra);
            })
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
                'master_mitra.name as mitra_name'
            );
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->editColumn('status', function ($model) {
            return $model->status == ProductFinance::STATUS['ACTIVE'] ? 'ACTIVE' : 'DELETED';
        });

        $table->editColumn('uv_jual', function ($model) {
            return is_null($model->uv_jual) ? null : number_format((float) $model->uv_jual, 2);
        });

        $table->editColumn('uv_beli', function ($model) {
            return is_null($model->uv_beli) ? null : number_format((float) $model->uv_beli, 2);
        });

        $table->addColumn('action', function ($model) {
            return '<button class="btn btn-warning btn-sm edit-price" 
                            data-id="'.htmlspecialchars($model->id, ENT_QUOTES, 'UTF-8').'" 
                            data-buy="'.$model->uv_beli.'" 
                            data-sell="'.$model->uv_jual.'" 
                            data-name="'.htmlspecialchars($model->name, ENT_QUOTES, 'UTF-8').'">
                        <i class="fa fa-edit"></i>
                    </button>';
        });

        $table->rawColumns(['action']);

        return $table->make(true);
    }
}
