<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Finance\Invoicing;
use App\Entities\Finance\PayableDetail;
use App\Services\StockService;
use PDF;

class FinanceVoidController extends Controller
{
    /**
     * Guard akses: cuma user tertentu (SPV Finance & Accounting) atau
     * superuser yang boleh approve/reject void.
     */
    private function guardFinance()
    {
        // SPV Finance & Accounting yang berwenang approve/reject void
        // TODO: pastikan nama role ini sesuai yang terdaftar di Spatie Permission
        // (cek di tabel `roles` atau penggunaan @role() lain di menu.blade.php)
        if (Auth::user()->is_superuser == 0 && !Auth::user()->hasRole(['SPV Finance', 'Finance', 'Developer'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak punya akses untuk approval void.',
            ]);
        }
        return null;
    }

    /**
     * List semua pengajuan void yang masih pending, buat ditampilkan
     * di halaman Approval Void.
     */
    public function index()
    {
        $voidRequests = DB::table('do_void_requests')
            ->where('status', 1) // 1 = pending
            ->orderBy('requested_at', 'asc')
            ->get();

        $requests = $voidRequests->map(function ($vr) {
            $packing = PackingOrder::with('member')->find($vr->do_id);
            $invoicing = Invoicing::where('do_id', $vr->do_id)->first();

            // TODO: sesuaikan nama tabel/kolom kalau bukan users.name
            $requestedByName = DB::table('superusers')->where('id', $vr->requested_by)->value('username');

            // ⚠️ Info tambahan untuk Finance - seharusnya sudah aman (kurs & payment
            // sudah digerbang di ready()/sending()), tapi tetap ditampilkan sebagai
            // jaga-jaga kalau ada anomali data.
            $kursWarning = optional($packing)->is_kurs_hold == 1;
            $paymentWarning = optional($packing)->type_transaction == 'CASH' && optional($packing)->has_payment != 1;

            return (object) [
                'id' => $vr->id,
                'do_id' => $vr->do_id,
                'do_code' => optional($packing)->do_code,
                'customer_name' => $packing->member->name ?? optional($packing->customer)->name ?? '-',
                'customer_city' => $packing->member->text_kota ?? optional($packing->customer)->text_kota ?? '-',
                'invoice_code' => optional($invoicing)->code,
                'grand_total_idr' => optional($invoicing)->grand_total_idr,
                'request_reason' => $vr->request_reason,
                'requested_at' => $vr->requested_at,
                'requested_by_name' => $requestedByName,
                'kurs_warning' => $kursWarning,
                'payment_warning' => $paymentWarning,
            ];
        });

        return view('superuser.finance.void.index', compact('requests'));
    }

    /**
     * Approve pengajuan void: stok fisik dikembalikan, invoice di-void
     * (soft delete), SO ditandai batal (condition = DELETED), DO jadi
     * status 8 (Void). Ditolak kalau invoice sudah ada pembayaran.
     */
    public function approve(Request $request, $id)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        if ($guard = $this->guardFinance()) {
            return $guard;
        }

        $request->validate([
            'approval_reason' => 'required|string|min:5',
        ]);

        try {
            $voidRequest = DB::table('do_void_requests')->where('id', $id)->where('status', 1)->first();

            if (!$voidRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengajuan void tidak ditemukan atau sudah diproses.',
                ]);
            }

            $packing = PackingOrder::where('id', $voidRequest->do_id)->lockForUpdate()->first();

            if (!$packing || (int) $packing->status !== 5) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'DO ini sudah tidak dalam status yang bisa di-void (mungkin sudah Update Resi, gunakan Sale Retur untuk proses pengembalian).',
                ]);
            }

            $invoicing = Invoicing::where('do_id', $packing->id)->first();

            // ======================================================
            // 🔴 GUARD DEFENSIF: kurs & payment seharusnya sudah aman
            // (sudah digerbang di sending()/ready()), tapi tetap dicek ulang
            // di sini untuk jaga-jaga kalau ada anomali data.
            // ======================================================
            if ($packing->is_kurs_hold == 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'DO ini terdeteksi belum punya kurs valid (is_kurs_hold masih aktif) - seharusnya tidak mungkin sampai status 5. Mohon cek data ini sebelum melanjutkan void.',
                ]);
            }

            if ($packing->type_transaction == 'CASH' && $packing->has_payment != 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'DO CASH ini terdeteksi belum ada konfirmasi pembayaran (has_payment belum aktif) - seharusnya tidak mungkin sampai status 5. Mohon cek data ini sebelum melanjutkan void.',
                ]);
            }

            // ======================================================
            // 🔴 GUARD PENTING: tolak kalau invoice sudah ada pembayaran
            // ======================================================
            if ($invoicing) {
                $hasPayable = PayableDetail::where('invoice_id', $invoicing->id)->exists();
                if ($hasPayable) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invoice ini sudah punya catatan pembayaran (Payable). Void tidak bisa dilakukan dari sini, koordinasikan dulu dengan tim AR/Payable.',
                    ]);
                }
            }

            DB::transaction(function () use ($packing, $invoicing, $voidRequest, $request) {

                $stockService = new StockService();

                $activeLogs = DB::table('do_stock_deduction_logs')
                    ->where('do_id', $packing->id)
                    ->where('status', 1)
                    ->get();

                foreach ($activeLogs as $log) {
                    if ($log->qty > 0) {
                        $stockService->executeSmartCancel(
                            $log->warehouse_id,
                            $log->product_packaging_id,
                            (float) $log->qty,
                            $packing->status, // 5, jadi tidak bikin jurnal StockMove
                            $packing->do_code,
                            'Void approved oleh Finance'
                        );
                    }
                }

                // 2) Nonaktifkan log kuota
                DB::table('do_stock_deduction_logs')
                    ->where('do_id', $packing->id)
                    ->where('status', 1)
                    ->update([
                        'status' => 0,
                        'note' => 'Dibatalkan karena Void (approved Finance)',
                        'updated_at' => now(),
                    ]);

                // 3) Void invoice (soft delete, bukan force delete - butuh audit trail)
                //    + tandai kodenya dengan suffix -VOID untuk audit yang jelas.
                if ($invoicing) {
                    $invoicing->update([
                        'status' => Invoicing::STATUS['VOID'],
                        'code' => $invoicing->code . '-VOID',
                        'updated_by' => Auth::id(),
                    ]);
                    $invoicing->delete(); // soft delete
                }

                // 4) SO: tandai batal final (tidak direset supaya bisa dipakai lagi)
                //    + tandai kodenya dengan suffix -VOID.
                $so = SalesOrder::where('id', $packing->so_id)->lockForUpdate()->first();
                if ($so) {
                    $so->update([
                        'status' => 7, // VOID (STEP)
                        'condition' => 3, // VOID (CONDITION)
                        'code' => $so->code . '-VOID',
                        'updated_by' => Auth::id(),
                    ]);
                }

                // 5) DO -> status Void, kode DO juga ditandai -VOID.
                $packing->update([
                    'status' => 8, // Void
                    'void_status' => 2, // approved/executed
                    'do_code' => $packing->do_code . '-VOID',
                    'updated_by' => Auth::id(),
                ]);

                // 6) Tutup request void ini (jadi audit log-nya)
                DB::table('do_void_requests')->where('id', $voidRequest->id)->update([
                    'status' => 2, // approved
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'approval_reason' => trim(htmlentities($request->approval_reason)),
                    'updated_at' => now(),
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Void berhasil diproses. Stok, invoice, dan SO sudah dikembalikan.',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * List semua pengajuan void yang SUDAH diproses (approved/rejected),
     * buat tab History di halaman Approval Void.
     */
    public function history()
    {
        $voidRequests = DB::table('do_void_requests')
            ->whereIn('status', [2, 3]) // 2 = approved, 3 = rejected
            ->orderBy('approved_at', 'desc')
            ->get();

        $requests = $voidRequests->map(function ($vr) {
            $packing = PackingOrder::with('member')->find($vr->do_id);
            // Invoicing sudah soft-deleted kalau approved -> pakai withTrashed()
            $invoicing = Invoicing::withTrashed()->where('do_id', $vr->do_id)->first();

            $requestedByName = DB::table('superusers')->where('id', $vr->requested_by)->value('username');
            $approvedByName = $vr->approved_by ? DB::table('superusers')->where('id', $vr->approved_by)->value('username') : null;

            return (object) [
                'id' => $vr->id,
                'do_id' => $vr->do_id,
                'do_code' => optional($packing)->do_code,
                'customer_name' => optional($packing->member ?? null)->name ?? '-',
                'invoice_code' => optional($invoicing)->code,
                'invoice_id' => optional($invoicing)->id,
                'grand_total_idr' => optional($invoicing)->grand_total_idr,
                'request_reason' => $vr->request_reason,
                'requested_at' => $vr->requested_at,
                'requested_by_name' => $requestedByName,
                'status' => (int) $vr->status, // 2 approved, 3 rejected
                'approval_reason' => $vr->approval_reason,
                'approved_by_name' => $approvedByName,
                'approved_at' => $vr->approved_at,
            ];
        });

        return view('superuser.finance.void.history', compact('requests'));
    }

    /**
     * Cetak/download PDF invoice yang sudah di-void (soft-deleted).
     * Pakai view print invoice yang sama dengan invoice aktif, cuma
     * query-nya withTrashed() supaya invoice yang sudah di-void tetap ketemu.
     */
    public function printVoided($do_id, $returnBinary = false)
    {
        $invoicing = Invoicing::withTrashed()->where('do_id', $do_id)->first();

        if (!$invoicing) {
            abort(404, 'Invoice untuk DO ini tidak ditemukan.');
        }

        $data = [
            'result' => $invoicing,
            'watermark' => 'VOID',
        ];

        // Reuse view print invoice yang sudah stabil (print_new), variabelnya
        // tetap $result supaya kompatibel sama template yang sudah jadi.
        // $watermark wajib diisi karena view ini butuh variabel itu.
        $pdf = PDF::loadView('superuser.finance.invoicing.print_new', $data)
                ->setPaper('a5', 'landscape');

        if ($returnBinary) {
            return $pdf->output(); // hasil biner PDF
        }

        return $pdf->stream("{$invoicing->code}-FULL.pdf");
    }

    /**
     * Reject pengajuan void: DO kembali normal (void_status dilepas),
     * tidak ada stok/invoice/SO yang diubah sama sekali.
     */
    public function reject(Request $request, $id)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        if ($guard = $this->guardFinance()) {
            return $guard;
        }

        $request->validate([
            'approval_reason' => 'required|string|min:5',
        ]);

        try {
            $voidRequest = DB::table('do_void_requests')->where('id', $id)->where('status', 1)->first();

            if (!$voidRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengajuan void tidak ditemukan atau sudah diproses.',
                ]);
            }

            DB::transaction(function () use ($voidRequest, $request) {
                DB::table('do_void_requests')->where('id', $voidRequest->id)->update([
                    'status' => 3, // rejected
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'approval_reason' => trim(htmlentities($request->approval_reason)),
                    'updated_at' => now(),
                ]);

                PackingOrder::where('id', $voidRequest->do_id)->update([
                    'void_status' => null,
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan void ditolak, DO kembali normal.',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}