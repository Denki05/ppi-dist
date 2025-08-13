<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class ProductMaterialTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
{
    // Subquery ambil 1 packaging per product_id (berdasarkan id terkecil)
    $sub = DB::table('master_products_packaging as mpp1')
        ->select('mpp1.*')
        ->join(DB::raw('
            (
                SELECT MIN(id) as id
                FROM master_products_packaging
                GROUP BY product_id
            ) as mpp2
        '), function ($join) {
            $join->on('mpp1.id', '=', 'mpp2.id');
        });

    // Main query
    $model = Product::joinSub($sub, 'best_packaging', function ($join) {
            $join->on('master_products.id', '=', 'best_packaging.product_id');
        })
        ->leftJoin('master_vendors', 'master_products.vendor_id', '=', 'master_vendors.id')
        ->leftJoin('master_sub_brand_references', 'master_products.sub_brand_reference_id', '=', 'master_sub_brand_references.id')
        ->select(
            'master_vendors.name as vendor_name',
            DB::raw("CONCAT(master_products.material_code, ' - ', master_products.material_name) as material"),
            'master_products.brand_name as brand',
            DB::raw("CONCAT(best_packaging.code, ' - ', best_packaging.name) as produk"),
            'best_packaging.price as harga',
            'master_sub_brand_references.name as searah_name'
        )
        ->orderBy('master_vendors.name', 'asc');

    // Filter vendor
    if ($request->vendor && $request->vendor != 'all') {
        $multiple_vendor = explode(',', $request->vendor);
        $model->whereIn('master_vendors.id', $multiple_vendor);
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

        return $table->make(true);
    }
}