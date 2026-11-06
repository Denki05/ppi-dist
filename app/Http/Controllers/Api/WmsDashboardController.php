<?php

namespace App\Http\Controllers\Api;

use App\Entities\Gudang\MutasiOut;
use App\Entities\Gudang\MutasiShowroom;
use App\Entities\Gudang\QualityControl2;
use App\Entities\Gudang\Receiving;
use App\Entities\Master\ProductMinStock;
use App\Entities\Penjualan\PackingOrder;
use App\Http\Controllers\Controller;

class WmsDashboardController extends Controller
{
    /**
     * Ringkasan pekerjaan gudang untuk layar awal WMS.
     * Satu request, hanya count — agar ringan untuk Trans.
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data'   => [
                'generated_at' => date('c'),
                'do'           => [
                    'belum_print' => PackingOrder::where('status', 3)
                        ->where(function ($query) {
                            $query->whereNull('print_count')->orWhere('print_count', 0);
                        })->count(),
                    'siap_kirim'  => PackingOrder::where('status', 4)->count(),
                    'belum_resi'  => PackingOrder::where('status', 5)->count(),
                ],
                // Dipisah per tipe agar pengingat dashboard bisa membuka
                // tab tipe yang benar-benar punya pekerjaan.
                'mutasi'       => [
                    'showroom' => [
                        'perlu_cek'     => MutasiShowroom::where('status', 2)
                            ->where('status_barang', 0)->count(),
                        'belum_diambil' => MutasiShowroom::where('status', 2)
                            ->where('status_barang', 1)->count(),
                    ],
                    'gudang'   => [
                        'perlu_cek'     => MutasiOut::where('status', MutasiOut::STATUS['PUBLISH'])
                            ->where('status_barang', 0)->count(),
                        'belum_diambil' => MutasiOut::whereIn('status', [
                                MutasiOut::STATUS['PUBLISH'],
                                MutasiOut::STATUS['ACC'],
                            ])->where('status_barang', 1)->count(),
                    ],
                    'perlu_cek'     => MutasiOut::where('status', MutasiOut::STATUS['PUBLISH'])
                            ->where('status_barang', 0)->count()
                        + MutasiShowroom::where('status', 2)
                            ->where('status_barang', 0)->count(),
                    'belum_diambil' => MutasiOut::whereIn('status', [
                            MutasiOut::STATUS['PUBLISH'],
                            MutasiOut::STATUS['ACC'],
                        ])->where('status_barang', 1)->count()
                        + MutasiShowroom::where('status', 2)
                            ->where('status_barang', 1)->count(),
                ],
                'receiving'    => [
                    // Status 2 lama (>90 hari) adalah backlog legacy, bukan pekerjaan aktif.
                    'qc'           => Receiving::where('status', Receiving::STATUS['QC'])
                        ->where('pbm_date', '>=', date('Y-m-d', strtotime('-90 days')))
                        ->count(),
                    'menunggu_acc' => Receiving::where('status', Receiving::STATUS['READY'])->count(),
                ],
                'komplain'     => [
                    'menunggu_acc' => QualityControl2::where('status', QualityControl2::STATUS['ACTIVE'])->count(),
                ],
                'stock'        => [
                    'minus' => ProductMinStock::where('quantity', '<', 0)->count(),
                ],
            ],
        ]);
    }
}
