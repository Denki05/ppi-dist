<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use App\Entities\Penjualan\PackingOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class DeliveryCostReportTable extends Table
{
    private function query(Request $request)
    {
        $model = PackingOrder::select(
                    'penjualan_do.id AS doID',
                    'penjualan_so.id AS soID', 
                    'penjualan_do.do_code AS code', 
                    'master_customer_other_addresses.name AS customer_name', 
                    'master_customer_other_addresses.text_kota AS customer_kota', 
                    DB::raw("
                        CASE
                            WHEN master_customer_other_addresses.free_shipping = 0 THEN 'NO'
                            WHEN master_customer_other_addresses.free_shipping = 1 THEN 'YES'
                        END AS free_ongkir
                    "),
                    'penjualan_do_details.delivery_cost_idr AS ongkir_idr', 
                    'penjualan_do_details.other_cost_idr AS resi_idr', 
                    'penjualan_do_details.delivery_cost_note AS ekspedisi', 
                    'penjualan_do_details.other_cost_note AS other_ekspedisi', 
                    'penjualan_do.image AS image_resi1', 
                    'penjualan_do.image2 AS image_resi2',
                    'penjualan_do.date_sent AS date_sent'
                )
                ->leftJoin('penjualan_do_details', 'penjualan_do.id', '=', 'penjualan_do_details.do_id')
                ->leftJoin('penjualan_so', 'penjualan_do.so_id', '=', 'penjualan_so.id')
                ->leftJoin('master_customer_other_addresses', 'penjualan_so.customer_other_address_id', '=', 'master_customer_other_addresses.id')
                ->where('penjualan_do.status', 6)
                ->where(function ($query) use ($request) {
                    if ($request->customer_name != 'all') {
                        $query->where('penjualan_do.customer_other_address_id', $request->customer_name);
                    }
                })
                ->whereBetween('penjualan_so.so_date', [$request->start_date . " 00:00:00", $request->end_date . " 23:59:59"])
                ->get();

                    if($request->do_code != 'all') {
                        $model = $model->where('penjualan_do.do_code', $request->do_code);
                    }

        return $model;
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->editColumn('date_sent', function (PackingOrder $model) {
            return $model->date_sent ? Carbon::parse($model->date_sent)->format('d/m/Y') : '-';
        });

        $table->addColumn('customer', function (PackingOrder $model) {
            return $model->customer_name . ' ' . $model->customer_kota;
        });

        $table->addColumn('image_resi1', function (PackingOrder $model) {
            return '<a href="' . asset($model->image_resi1) . '"><img src="' . asset($model->image_resi1) . '" width="100" height="100" class="img-fluid img-show-small"></a>';
        });

        $table->addColumn('image_resi2', function (PackingOrder $model) {
            return '<img src="' . asset($model->image_resi2) . '" width="100" height="100" class="img-fluid img-show-small img-lightbox">';
        });

        $table->rawColumns(['image_resi1', 'image_resi2', 'customer']);
        // asset
        return $table->make(true);
    }
}