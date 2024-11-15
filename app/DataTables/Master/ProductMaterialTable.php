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
        $model = Product::leftJoin('master_products_packaging', 'master_products.id', '=', 'master_products_packaging.product_id')
            ->leftJoin('master_vendors', 'master_products.vendor_id', '=', 'master_vendors.id')
            ->select(
                'master_vendors.name as vendor_name',
                DB::raw("CONCAT(master_products.material_code, ' - ', master_products.material_name) as material"),
                'master_products.brand_name as brand',
                DB::raw("MIN(CONCAT(master_products_packaging.code, ' - ', master_products_packaging.name)) as produk"),
                DB::raw("MIN(master_products_packaging.price) as harga")
            )
            ->groupBy('master_vendors.name', 'master_products.material_code', 'master_products.material_name', 'master_products.brand_name')
            ->orderBy('master_vendors.name', 'asc');

        // Apply vendor filter if necessary
        if ($request->vendor && $request->vendor != 'all') {
            $multiple_vendor = explode(',', $request->vendor);  // If multiple vendors are selected
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