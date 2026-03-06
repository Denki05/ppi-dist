<?php

namespace App\Services;

use App\Entities\Gudang\MutasiShowroom;
use App\Entities\Gudang\MutasiShowroomDetail;
use App\Repositories\CodeRepo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MutasiShowroomFreeSOService
{
    public function generate($startDate, $endDate)
    {
        $created = 0;

        DB::transaction(function () use ($startDate, $endDate, &$created) {

            $soList = DB::table('penjualan_so')
                ->join('penjualan_so_item', 'penjualan_so.id', '=', 'penjualan_so_item.so_id')
                ->whereBetween('penjualan_so.so_date', [$startDate, $endDate])
                ->where('penjualan_so.status', 4)
                ->where('penjualan_so_item.free_product', 1)
                ->select(
                    'penjualan_so.id',
                    'penjualan_so.code',
                    'penjualan_so.brand_name',
                    'penjualan_so.so_date',
                    'penjualan_so.customer_id',
                    'penjualan_so.customer_other_address_id'
                )
                ->groupBy('penjualan_so.id')
                ->get();

            foreach ($soList as $so) {

                $exists = MutasiShowroom::where('so_id', $so->id)
                    ->where('type', MutasiShowroom::TYPE_SYSTEM_FREE_SO)
                    ->where('status', 3)
                    ->where('status_barang', 2)
                    ->where('status_checked', 1)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $kode = CodeRepo::generateMutasiShowroom(
                    MutasiShowroom::TYPE_SYSTEM_FREE_SO
                );

                $mutasi = MutasiShowroom::create([
                    'kode'           => $kode,
                    'type'           => MutasiShowroom::TYPE_SYSTEM_FREE_SO,
                    'so_id'          => $so->id,
                    'tanggal'        => Carbon::parse($so->so_date),

                    'brand_name' => $so->brand_name,

                    'warehouse_from_id' => 2,
                    // khusus type promosi → warehouse_to_id menyimpan customer_id
                    'warehouse_to_id'   => $so->customer_id,
                    'customer_other_address_id' => $so->customer_other_address_id,

                    'status'         => 3, // Settle
                    'status_barang'  => 2, // Diambil
                    'status_checked' => 1, // Checked

                    'created_by'     => auth()->id(),
                ]);

                $items = DB::table('penjualan_so_item')
                    ->where('so_id', $so->id)
                    ->where('free_product', 1)
                    ->get();

                foreach ($items as $item) {

                    MutasiShowroomDetail::create([
                        'penjualan_showroom_id' => $mutasi->id,
                        'product_packaging_id'  => $item->product_packaging_id,
                        'qty'                   => $item->qty,
                        'price_usd'             => 0,
                        'price_idr'             => 0,
                        'total_price'           => 0,
                        'note'                  => 'Auto generate from SO Free Product',
                    ]);
                }

                $created++;
            }
        });

        return $created;
    }
}