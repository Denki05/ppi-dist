<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Entities\Account\Superuser;
use App\Entities\Penjualan\PackingOrder;
use App\Services\StockService;
use PDF;

class PickerApiController extends Controller
{
    public function login(Request $request)
    {
        $user = Superuser::where('username', $request->username)->first();

        if ($user && Hash::check($request->password, $user->password)) {

            $token = Str::random(60);
            $user->api_token = $token;
            $user->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'Login berhasil',
                'data'    => [
                    'token' => $token,
                    'user'  => [
                        'id'       => $user->id,
                        'name'     => $user->name,
                        'username' => $user->username,
                    ],
                ],
            ], 200);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Username atau Password salah',
        ], 401);
    }

    /**
     * Antrian buat picker.
     *
     * PENTING: cuma DO yang SPK-nya SUDAH dicetak SPV (print_count > 0)
     * yang boleh muncul di picker-app. Ini gate baru sesuai permintaan
     * manajer: SPK dicetak SPV dulu, baru diserahkan ke picker.
     */
    public function getReadyTasks()
    {
        $tasks = PackingOrder::select('id', 'do_code', 'code', 'customer_other_address_id', 'created_at', 'status', 'print_count')
            ->where('status', 3)
            ->where('print_count', '>', 0)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->map(function ($task) {
                return [
                    'id'         => $task->id,
                    'do_code'    => $task->do_code,
                    'code'       => $task->code,
                    'customer'   => optional($task->member)->name,
                    'created_at' => $task->created_at,
                ];
            });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data task berhasil diambil',
            'data'    => $tasks,
        ], 200);
    }

    /**
     * Detail satu DO + daftar item buat di-render checklist di picker-app.
     */
    public function getTaskDetail($id)
    {
        $result = PackingOrder::where('id', $id)
            ->where('status', 3)
            ->where('print_count', '>', 0)
            ->first();
    
        if (!$result) {
            return response()->json([
                'status'  => 'error',
                'message' => 'DO tidak ditemukan atau belum siap diproses (SPK belum dicetak SPV).',
            ], 404);
        }
    
        $items = $result->do_detail->map(function ($item) {
            return [
                'do_detail_id' => $item->id,
                'sku'          => $item->product_pack->code ?? '-',
                'name'         => $item->product_pack->name ?? '-',
                'qty'          => (float) $item->qty,
                'packaging'    => $item->product_pack->packaging->pack_name ?? '-',
            ];
        })->values();
    
        return response()->json([
            'status'  => 'success',
            'data'    => [
                'id'          => $result->id,
                'do_code'     => $result->do_code,
                'code'        => $result->code,
                'warehouse'   => optional($result->warehouse)->name,
                'customer'    => optional($result->member)->name,
                'city'    => optional($result->member)->text_kota,
                'address'     => optional($result->member)->address,
                'items'       => $items,
            ],
        ], 200);
    }

    /**
     * Submit checklist konfirmasi barang dari picker-app.
     * Disamakan dengan checker web (DeliveryOrderController::packed):
     * - validasi checklist + kuota reserved (do_stock_deduction_logs)
     * - status 3 -> 4 (+ invoice kalau kurs valid)
     * - potong fisik TIDAK di sini — satu-satunya titik potong adalah
     *   sending() (4 -> 5, momen save-setelah-cetak SJ).
     *   (Dulu packTask motong di sini; itu bikin double-deduct karena
     *   sending() motong lagi untuk DO yang diproses via picker-app,
     *   plus invoice tidak pernah dibuat di jalur picker.)
     *
     * Body yang diharapkan:
     * {
     *   "confirmed_items": [12, 13, 14, ...]   // array do_detail_id yang dicentang
     * }
     */
    public function packTask(Request $request, $id)
    {
        $request->validate([
            'confirmed_items'   => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $packing = PackingOrder::with(['do_detail.product_pack', 'so'])
                ->where('print_count', '>', 0)
                ->findOrFail($id);

            if ($packing->status != 3) {
                throw new \Exception('Status DO tidak valid untuk diproses (mungkin sudah diproses picker/SPV lain).');
            }

            if ($packing->do_detail->count() == 0) {
                throw new \Exception('Tidak ada item untuk diproses.');
            }

            // ====== VALIDASI CHECKLIST (sama seperti versi web) ======
            $doItemIds = $packing->do_detail->pluck('id')->toArray();
            $confirmedIds = collect($request->confirmed_items)->map(function ($v) {
                return (int) $v;
            })->toArray();

            if (count($doItemIds) !== count($confirmedIds)) {
                throw new \Exception('Semua item harus dikonfirmasi sebelum diproses.');
            }
            foreach ($doItemIds as $itemId) {
                if (!in_array($itemId, $confirmedIds)) {
                    throw new \Exception('Checklist item tidak lengkap.');
                }
            }

            // ====== VALIDASI KUOTA RESERVED (checker tidak boleh melebihi log) ======
            // Fisik TIDAK dipotong di sini (lihat docblock di atas).
            $checkerQtys = [];
            foreach ($packing->do_detail as $item) {
                $base_id = preg_replace('/_\d+$/', '', $item->product_packaging_id);
                $checkerQtys[$base_id] = ($checkerQtys[$base_id] ?? 0) + $item->qty;
            }

            foreach ($checkerQtys as $base_id => $totalCheckerQty) {
                $logQty = DB::table('do_stock_deduction_logs')
                    ->where('do_id', $packing->id)
                    ->where('product_packaging_id', $base_id)
                    ->where('status', 1)
                    ->sum('qty');

                if ((float) $totalCheckerQty > (float) $logQty) {
                    throw new \Exception("Gagal: Total Qty untuk produk {$base_id} ({$totalCheckerQty}) melebihi kuota Pesanan di Log ({$logQty}).");
                }
            }

            // ====== CEK KURS + INVOICE (sama seperti checker web) ======
            $isKursHold = empty($packing->idr_rate) || (float) $packing->idr_rate <= 1;

            $packing->update([
                'status'       => 4,
                'is_kurs_hold' => $isKursHold,
                'updated_by'   => null, // TODO: isi ID user picker kalau mau tercatat siapa yg proses
            ]);

            if (!$isKursHold) {
                $this->createInvoiceIfNeeded($packing->id);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'DO berhasil diproses, status berubah ke Siap Kirim.',
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Buat invoice DO bila belum ada (salinan logika
     * DeliveryOrderController::createInvoiceIfNeeded agar jalur picker
     * dan web menghasilkan invoice yang sama).
     */
    private function createInvoiceIfNeeded($do_id)
    {
        $packing = PackingOrder::where('id', $do_id)->first();
        $detail  = \App\Entities\Penjualan\PackingOrderDetail::where('do_id', $do_id)->first();

        if (!$packing || !$detail) {
            return;
        }

        $existing = \App\Entities\Finance\Invoicing::withTrashed()->where('do_id', $do_id)->first();

        if ($existing) {
            if ($existing->trashed() && (int) $existing->status === \App\Entities\Finance\Invoicing::STATUS['VOID']) {
                return;
            }
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update([
                'code' => $packing->do_code,
                'customer_id' => $packing->customer_id,
                'customer_other_address_id' => $packing->customer_other_address_id,
                'grand_total_idr' => $detail->grand_total_idr,
                'status' => \App\Entities\Finance\Invoicing::STATUS['ACTIVE'],
            ]);
            return;
        }

        \App\Entities\Finance\Invoicing::create([
            'code' => $packing->do_code,
            'do_id' => $packing->id,
            'customer_id' => $packing->customer_id,
            'customer_other_address_id' => $packing->customer_other_address_id,
            'grand_total_idr' => $detail->grand_total_idr,
            'status' => \App\Entities\Finance\Invoicing::STATUS['ACTIVE'],
            'type' => 0,
            'created_by' => null,
        ]);
    }

    /**
     * Cetak Label Kirim buat picker.
     *
     * TODO PENTING: method print_label() versi lama di
     * DeliveryOrderController pakai Crystal Report via COM Object,
     * yang HANYA jalan di Windows. Kalau server trial (lssoft88.xyz)
     * itu Linux, endpoint ini WAJIB pakai dompdf (seperti packing plan),
     * bukan COM. Di bawah ini saya taruh versi dompdf sebagai placeholder
     * layout dasar (Nama, Alamat, No HP, Kode DO) - SESUAIKAN layoutnya
     * kalau ada contoh label fisik yang sudah baku (mirip contoh
     * PRE00206.pdf buat packing plan kemarin).
     */
    public function printLabel($id)
    {
        $result = PackingOrder::with(['member', 'warehouse'])
            ->where('id', $id)
            ->first();

        if (!$result) {
            return response()->json(['status' => 'error', 'message' => 'DO tidak ditemukan'], 404);
        }

        $data = [
            'do_code'   => $result->do_code ?: $result->code,
            'warehouse' => optional($result->warehouse)->name ?? '-',
            'customer'  => optional($result->member)->name ?? '-',
            'address'   => optional($result->member)->address ?? '-',
            'phone'     => optional($result->member)->phone ?? '-',
        ];

        $pdf = PDF::loadView('superuser.penjualan.delivery_order.print_label_pdf', $data);
        $pdf->setPaper([0, 0, 283, 283]); // ~10x10cm, SESUAIKAN sama ukuran label fisik kamu

        return $pdf->stream('LabelKirim-'.$data['do_code'].'.pdf');
    }
}