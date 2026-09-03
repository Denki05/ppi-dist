<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\DoInternalRevision;
use App\Entities\Finance\Invoicing;
use App\Entities\Setting\UserMenu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\StockService;
use Illuminate\Support\Facades\Hash;

class InternalRevisionController extends Controller
{
    public function __construct()
    {
        $this->view = "superuser.penjualan.sales_order.";
        $this->user_menu = new UserMenu;
    }

    // Helper: sama persis pola parseCurrency di DeliveryOrderController,
    // biar format angka '1.800.000,50' konsisten diparse jadi 1800000.50
    private function parseCurrency($value)
    {
        if (empty($value)) {
            return 0;
        }
        $value = (string) $value;
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        return floatval($value);
    }

    /**
     * Ambil nama customer dengan fallback berjenjang, supaya tidak blank
     * kalau salah satu sumber datanya kosong:
     * 1) alamat pengiriman DO (customer_other_address_id)
     * 2) alamat pengiriman di SO induk
     * 3) master customer utama
     */
    private function resolveCustomerName($packingOrder)
    {
        if (!$packingOrder) {
            return '-';
        }

        return optional($packingOrder->member)->name
            ?? optional(optional($packingOrder->so)->member)->name
            ?? optional($packingOrder->customer)->name
            ?? '-';
    }

    /**
     * Form pengajuan revisi internal. Ambil data DO + item existing,
     * mirip pola create_lanjutan.
     */
    public function create($do_id)
    {
        $do = PackingOrder::with(['do_detail.product_pack', 'do_detail_cost', 'so', 'member'])
            ->findOrFail($do_id);

        if (!in_array((int) $do->status, [5, 6])) {
            return redirect()->back()->with('error', 'Revisi internal hanya bisa diajukan untuk DO status Delivering (5) atau Delivered (6).');
        }

        if (!empty($do->void_status)) {
            return redirect()->back()->with('error', 'DO ini sedang dalam pengajuan Void, tidak bisa diajukan revisi internal bersamaan.');
        }

        if (!empty($do->internal_revision_status) && (int) $do->internal_revision_status === 1) {
            return redirect()->back()->with('error', 'DO ini sudah punya pengajuan revisi internal yang masih pending.');
        }

        if (!empty($do->internal_revision_count) && (int) $do->internal_revision_count > 0) {
            return redirect()->back()->with('error', 'DO ini sudah pernah di-revisi internal sebanyak ' . $do->internal_revision_count . ' kali. Pengajuan ulang tidak diperbolehkan.');
        }

        $rekening = DB::table('rekening')->get();

        $data = [
            'result' => $do,
            'rekening' => $rekening,
        ];

        return view($this->view . 'internal_revision_create', $data);
    }

    /**
     * Simpan pengajuan revisi (DRAFT). Belum menyentuh stok/invoice sama sekali -
     * itu baru dieksekusi nanti pas approve().
     */
    public function store(Request $request)
    {
        try {
            $debugLog = storage_path('logs/debug_store.txt');
            $debugDir = dirname($debugLog);
            if (!is_dir($debugDir)) {
                @mkdir($debugDir, 0775, true);
            }
            @file_put_contents($debugLog, 'HIT store() at ' . now()->toDateTimeString() . ' method=' . $request->method() . ' url=' . $request->fullUrl() . "\n", FILE_APPEND);
            Log::info('=== InternalRevisionController@store DIPANGGIL ===', $request->except('_token'));
        } catch (\Throwable $diagnosticException) {
            // Abaikan kegagalan diagnostik; proses utama harus tetap berjalan.
        }

        try {
            $request->validate([
                'do_id' => 'required|integer',
                'request_reason' => 'required|string|min:10',
                'items' => 'required|array|min:1',
                'items.*.product_packaging_id' => 'required|string',
                'items.*.qty' => 'required',
                'items.*.price' => 'required',
                'sales_senior_id' => 'required|integer',
                'sales_id' => 'required|integer',
                'rekening_id' => 'required|integer',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            @file_put_contents(storage_path('logs/debug_store.txt'), 'VALIDASI GAGAL: ' . json_encode($ve->errors()) . "\n", FILE_APPEND);
            throw $ve;
        }

            @file_put_contents(storage_path('logs/debug_store.txt'), 'LOLOS VALIDASI' . PHP_EOL, FILE_APPEND);

        DB::beginTransaction();
        try {
            $do = PackingOrder::with(['do_detail', 'do_detail_cost'])
                ->lockForUpdate()
                ->findOrFail($request->do_id);

            if (!in_array((int) $do->status, [5, 6])) {
                throw new \Exception('Status DO sudah berubah, silakan refresh halaman.');
            }
            if (!empty($do->void_status)) {
                throw new \Exception('DO ini sedang dalam pengajuan Void.');
            }
            if (!empty($do->internal_revision_status) && (int) $do->internal_revision_status === 1) {
                throw new \Exception('DO ini sudah punya pengajuan revisi internal yang masih pending.');
            }
            if (!empty($do->internal_revision_count) && (int) $do->internal_revision_count > 0) {
                throw new \Exception('DO ini sudah pernah di-revisi internal sebanyak ' . $do->internal_revision_count . ' kali. Pengajuan ulang tidak diperbolehkan.');
            }

            // ==========================================
            // SNAPSHOT "BEFORE" - dari data existing di DB
            // ==========================================
            $beforeItems = $do->do_detail->map(function ($item) {
                return [
                    'do_item_id' => $item->id,
                    'product_packaging_id' => $item->product_packaging_id,
                    'qty' => (float) $item->qty,
                    'price' => (float) $item->price,
                    'usd_disc' => (float) $item->usd_disc,
                    'percent_disc' => (float) $item->percent_disc,
                ];
            })->values()->toArray();

            $detailCost = $do->do_detail_cost;

            $before = [
                'items' => $beforeItems,
                'idr_rate' => (float) $do->idr_rate,
                'disc_agen_percent' => (float) optional($detailCost)->discount_1,
                'disc_kemasan_percent' => (float) optional($detailCost)->discount_2,
                'disc_tambahan_idr' => (float) optional($detailCost)->discount_idr,
                'voucher_idr' => (float) optional($detailCost)->voucher_idr,
                'delivery_cost_idr' => (float) optional($detailCost)->delivery_cost_idr,
                'other_cost_idr' => (float) optional($detailCost)->other_cost_idr,
                'grand_total_idr' => (float) optional($detailCost)->grand_total_idr,
                'sales_senior_id' => optional($do->so)->sales_senior_id ?? null,
                'sales_id' => optional($do->so)->sales_id ?? null,
                'rekening_id' => optional($do->so)->rekening ?? null,
            ];

            // ==========================================
            // SNAPSHOT "AFTER" - dari input form (BELUM dieksekusi, cuma disimpan)
            // ==========================================
            $afterItems = [];
            foreach ($request->items as $reqItem) {
                $afterItems[] = [
                    'do_item_id' => $reqItem['do_item_id'] ?? null, // null = produk baru
                    'product_packaging_id' => $reqItem['product_packaging_id'],
                    'qty' => $this->parseCurrency($reqItem['qty']),
                    'price' => $this->parseCurrency($reqItem['price']),
                    'usd_disc' => $this->parseCurrency($reqItem['usd_disc'] ?? 0),
                    'percent_disc' => $this->parseCurrency($reqItem['percent_disc'] ?? 0),
                    'is_new_product' => empty($reqItem['do_item_id']),
                ];
            }

            $after = [
                'items' => $afterItems,
                'idr_rate' => $this->parseCurrency($request->idr_rate ?? $before['idr_rate']),
                'disc_agen_percent' => $this->parseCurrency($request->disc_agen_percent ?? 0),
                'disc_kemasan_percent' => $this->parseCurrency($request->disc_kemasan_percent ?? 0),
                'disc_tambahan_idr' => $this->parseCurrency($request->disc_tambahan_idr ?? 0),
                'voucher_idr' => $this->parseCurrency($request->voucher_idr ?? 0),
                'delivery_cost_idr' => $this->parseCurrency($request->delivery_cost_idr ?? 0),
                'other_cost_idr' => $this->parseCurrency($request->other_cost_idr ?? 0),
                'sales_senior_id' => $request->sales_senior_id ?? $before['sales_senior_id'],
                'sales_id' => $request->sales_id ?? $before['sales_id'],
                'rekening_id' => $request->rekening_id ?? $before['rekening_id'],
                // grand_total_idr sengaja TIDAK dihitung di sini - akan dihitung ULANG
                // dari nol pas approve(), supaya tidak percaya angka dari form (anti-tampering).
            ];

            // ==========================================
            // DETEKSI items_changed (buat nentuin wajib reprint SJ atau tidak)
            // ==========================================
            $beforeMap = collect($beforeItems)->keyBy('product_packaging_id')
                ->map(function ($i) { return (float) $i['qty']; });
            $afterMap = collect($afterItems)->keyBy('product_packaging_id')
                ->map(function ($i) { return (float) $i['qty']; });

            $itemsChanged = $beforeMap->count() !== $afterMap->count();
            if (!$itemsChanged) {
                foreach ($afterMap as $pid => $qty) {
                    if (!$beforeMap->has($pid) || (float) $beforeMap->get($pid) !== (float) $qty) {
                        $itemsChanged = true;
                        break;
                    }
                }
            }

            // ==========================================
            // SIMPAN PENGAJUAN (DRAFT)
            // ==========================================
            $revision = DoInternalRevision::create([
                'do_id' => $do->id,
                'so_id' => $do->so_id,
                'origin_status' => $do->status,
                'requested_by' => Auth::id(),
                'requested_at' => now(),
                'request_reason' => trim($request->request_reason),
                'revision_detail' => ['before' => $before, 'after' => $after],
                'items_changed' => $itemsChanged,
                'status' => 1,
            ]);

            // Lock DO dari aksi lain
            $do->update(['internal_revision_status' => 1]);

            // Hold invoice kalau sudah ada, biar gak bisa diproses/cetak selagi pending
            Invoicing::where('do_id', $do->id)->update([
                'status' => Invoicing::STATUS['PENDING'],
            ]);

            DB::commit();

            @file_put_contents(storage_path('logs/debug_store.txt'), 'SUCCESS - redirecting to internal_revision.index' . PHP_EOL, FILE_APPEND);

            $successMsg = "Pengajuan revisi internal DO {$do->do_code} berhasil dikirim, menunggu approval Management/Developer.";

            if ($request->ajax()) {
                return response()->json([
                    'IsError' => false,
                    'Notification' => [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => $successMsg,
                    ],
                    'redirect_to' => route('superuser.penjualan.internal_revision.index'),
                ]);
            }

            return redirect()
                ->route('superuser.penjualan.internal_revision.index')
                ->with('success', $successMsg);

        } catch (\Throwable $e) {
            DB::rollBack();

            @file_put_contents(storage_path('logs/debug_store.txt'), 'EXCEPTION: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine() . "\n", FILE_APPEND);

            try {
                Log::error('InternalRevisionController@store GAGAL: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            } catch (\Throwable $logException) {
                // Jangan menggagalkan response hanya karena log tidak writable.
            }

            if ($request->ajax()) {
                return response()->json([
                    'IsError' => true,
                    'Notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $e->getMessage(),
                    ],
                ], 500);
            }

            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

        /**
     * List pengajuan revisi yang masih pending, buat halaman approval.
     */
    public function index(Request $request)
    {
        $statusFilter = $request->get('status');

        $query = DoInternalRevision::with([
                'packingOrder.member',
                'packingOrder.so.member',
                'packingOrder.customer',
                'packingOrder.so',
                'requestedBy',
                'approvedBy',
            ])
            ->orderBy('requested_at', 'desc');

        if ($statusFilter && in_array((int) $statusFilter, [1, 2, 3])) {
            $query->where('status', (int) $statusFilter);
        }

        $revisions = $query->get();

        // Statistik
        $stats = [
            'pending'  => DoInternalRevision::where('status', 1)->count(),
            'approved' => DoInternalRevision::where('status', 2)->count(),
            'rejected' => DoInternalRevision::where('status', 3)->count(),
        ];

        return view($this->view . 'internal_revision_index', compact('revisions', 'statusFilter', 'stats'));
    }

    /**
     * Hitung ulang TOTAL dari nol berdasarkan revision_detail->after.
     * Ini SATU-SATUNYA tempat yang menentukan angka final - form/JS di frontend
     * cuma preview, tidak dipercaya.
     */
    private function calculateTotals(array $after)
    {
        $subTotalItem = 0;
        foreach ($after['items'] as $item) {
            if ($item['qty'] <= 0) {
                throw new \Exception('Qty produk harus lebih dari 0.');
            }
            if ($item['price'] < 0 || $item['usd_disc'] < 0) {
                throw new \Exception('Harga/disc tidak boleh negatif.');
            }
            $totalDiscItem = ($item['usd_disc'] + (($item['price'] - $item['usd_disc']) * ($item['percent_disc'] / 100))) * $item['qty'];
            $subTotalItemUsd = ($item['qty'] * $item['price']) - $totalDiscItem;
            $subTotalItem += $subTotalItemUsd * $after['idr_rate'];
        }

        $discAgenIdr = round($subTotalItem * ($after['disc_agen_percent'] / 100));
        $discKemasanIdr = round(($subTotalItem - $discAgenIdr) * ($after['disc_kemasan_percent'] / 100));
        $subtotal2 = $subTotalItem - $discAgenIdr - $discKemasanIdr;

        if ($after['disc_tambahan_idr'] > $subtotal2) {
            throw new \Exception('Disc tambahan tidak boleh melebihi subtotal.');
        }

        $grandTotal = $subtotal2 - $after['disc_tambahan_idr'] - $after['voucher_idr']
            + $after['delivery_cost_idr'] + $after['other_cost_idr'];

        if ($grandTotal <= 0) {
            throw new \Exception('Grand total hasil revisi tidak valid (kurang dari atau sama dengan 0).');
        }

        return [
            'sub_total_item' => round($subTotalItem),
            'disc_agen_idr' => $discAgenIdr,
            'disc_kemasan_idr' => $discKemasanIdr,
            'grand_total_idr' => round($grandTotal),
        ];
    }

    /**
     * Cek stok tersedia buat semua produk yang qty-nya NAIK (termasuk produk baru).
     * Dipanggil SEBELUM eksekusi apapun - kalau gagal, approve() batal total.
     */
    private function checkStockAvailability($warehouseId, array $beforeItems, array $afterItems)
    {
        $beforeMap = collect($beforeItems)->keyBy('product_packaging_id')->map(function ($i) { return (float) $i['qty']; });

        foreach ($afterItems as $item) {
            $qtyLama = (float) ($beforeMap->get($item['product_packaging_id']) ?? 0);
            $delta = (float) $item['qty'] - $qtyLama;

            if ($delta > 0) {
                $stock = \App\Entities\Master\ProductMinStock::where('warehouse_id', $warehouseId)
                    ->where('product_packaging_id', $item['product_packaging_id'])
                    ->lockForUpdate()
                    ->first();

                $available = $stock ? ((float) $stock->quantity - (float) $stock->reserved_quantity) : 0;

                if ($available < $delta) {
                    throw new \Exception("Stok tidak cukup untuk produk {$item['product_packaging_id']} (butuh tambahan {$delta}, tersedia {$available}).");
                }
            }
        }
    }

    /**
     * Generate & kirim OTP ke approver. Dipanggil approver SEBELUM approve().
     */
    public function request_otp($id)
    {
        $revision = DoInternalRevision::where('status', 1)->findOrFail($id);

        if (!Auth::user()->is_superuser && !Auth::user()->hasRole(['Manajemen', 'Developer'])) {
            // TODO: pastikan nama role 'Manajemen' sesuai yang terdaftar di tabel roles
            return response()->json(['status' => 'error', 'message' => 'Anda tidak punya akses approval revisi internal.']);
        }

        if ((int) $revision->requested_by === (int) Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Pengaju dan approver tidak boleh orang yang sama.']);
        }

        $otp = (string) random_int(100000, 999999);

        $revision->update([
            'otp_hash' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(5),
            'otp_attempts' => 0,
        ]);

        Auth::user()->notify(new \App\Notifications\InternalRevisionOtpNotification($revision, $otp));

        return response()->json([
            'status' => 'success',
            'message' => 'Kode OTP telah dikirim. Silakan masukkan kode di bawah ini.',
            'otp' => $otp,
            'expires_in' => 5,
        ]);
    }

    /**
     * Approve pengajuan: validasi OTP -> cek stok -> hitung ulang total ->
     * eksekusi stok sesuai origin_status -> sync invoice -> tandai items_changed.
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'otp' => 'required|string',
            'approval_reason' => 'required|string|min:5',
        ]);

        DB::beginTransaction();
        try {
            $revision = DoInternalRevision::where('status', 1)->lockForUpdate()->findOrFail($id);

            if (!Auth::user()->is_superuser && !Auth::user()->hasRole(['Manajemen', 'Developer'])) {
                throw new \Exception('Anda tidak punya akses approval revisi internal.');
            }
            if ((int) $revision->requested_by === (int) Auth::id()) {
                throw new \Exception('Pengaju dan approver tidak boleh orang yang sama.');
            }
            if (empty($revision->otp_hash) || !$revision->otp_expires_at || now()->greaterThan($revision->otp_expires_at)) {
                throw new \Exception('OTP tidak ditemukan atau sudah kedaluwarsa, silakan generate ulang.');
            }
            if ($revision->otp_attempts >= 3) {
                throw new \Exception('OTP salah 3x, silakan generate ulang.');
            }
            if (!Hash::check($request->otp, $revision->otp_hash)) {
                $revision->increment('otp_attempts');
                throw new \Exception('Kode OTP salah.');
            }

            $do = PackingOrder::where('id', $revision->do_id)->lockForUpdate()->first();
            if (!$do || (int) $do->status !== (int) $revision->origin_status) {
                throw new \Exception('Status DO sudah berubah sejak pengajuan dibuat, tidak bisa diapprove. Silakan reject dan ajukan ulang.');
            }

            $detail = $revision->revision_detail;
            $before = $detail['before'];
            $after = $detail['after'];

            $this->checkStockAvailability($do->warehouse_id, $before['items'], $after['items']);
            $totals = $this->calculateTotals($after);

            $stockService = new StockService();
            $beforeMap = collect($before['items'])->keyBy('product_packaging_id');

            foreach ($after['items'] as $item) {
                $qtyLama = (float) optional($beforeMap->get($item['product_packaging_id']))['qty'] ?? 0;
                $delta = (float) $item['qty'] - $qtyLama;

                if ($delta == 0) continue;

                if ((int) $revision->origin_status === 5) {
                    if ($delta > 0) {
                        $stockService->deductPhysicalStock($do->warehouse_id, $item['product_packaging_id'], $delta);
                    } else {
                        $stockService->undoDeductPhysicalStock($do->warehouse_id, $item['product_packaging_id'], abs($delta));
                    }
                } else {
                    $stockService->recordCorrectionLog(
                        $do->warehouse_id,
                        $item['product_packaging_id'],
                        $delta,
                        'REVISI-' . $do->do_code,
                        'Koreksi Revisi Internal DO #' . $revision->id
                    );
                }
            }

            $afterIds = collect($after['items'])->pluck('product_packaging_id');
            foreach ($before['items'] as $item) {
                if (!$afterIds->contains($item['product_packaging_id'])) {
                    if ((int) $revision->origin_status === 5) {
                        $stockService->undoDeductPhysicalStock($do->warehouse_id, $item['product_packaging_id'], $item['qty']);
                    } else {
                        $stockService->recordCorrectionLog(
                            $do->warehouse_id,
                            $item['product_packaging_id'],
                            -$item['qty'],
                            'REVISI-' . $do->do_code,
                            'Koreksi Revisi Internal DO #' . $revision->id . ' (produk dihapus)'
                        );
                    }
                }
            }

            foreach ($after['items'] as $item) {
                $totalDiscItem = ($item['usd_disc'] + (($item['price'] - $item['usd_disc']) * ($item['percent_disc'] / 100))) * $item['qty'];
                $subTotalItemUsd = ($item['qty'] * $item['price']) - $totalDiscItem;
                $totalItemIdr = $subTotalItemUsd * $after['idr_rate'];

                if (!empty($item['do_item_id'])) {
                    PackingOrderItem::where('id', $item['do_item_id'])->update([
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'usd_disc' => $item['usd_disc'],
                        'percent_disc' => $item['percent_disc'],
                        'total_disc' => $totalDiscItem,
                        'total' => $totalItemIdr,
                        'updated_by' => Auth::id(),
                    ]);
                } else {
                    PackingOrderItem::create([
                        'do_id' => $do->id,
                        'product_packaging_id' => $item['product_packaging_id'],
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'usd_disc' => $item['usd_disc'],
                        'percent_disc' => $item['percent_disc'],
                        'total_disc' => $totalDiscItem,
                        'total' => $totalItemIdr,
                        'created_by' => Auth::id(),
                    ]);
                }
            }
            foreach ($before['items'] as $item) {
                if (!$afterIds->contains($item['product_packaging_id']) && !empty($item['do_item_id'])) {
                    PackingOrderItem::where('id', $item['do_item_id'])->delete();
                }
            }

            Invoicing::where('do_id', $do->id)->update([
                'grand_total_idr' => $totals['grand_total_idr'],
                'status' => Invoicing::STATUS['ACTIVE'],
                'updated_by' => Auth::id(),
            ]);

            // ==========================================
            // UPDATE PackingOrderDetail (DO Detail Cost)
            // Supaya diskon, biaya, & grand total tersimpan
            // dan konsisten dengan invoice yang dihitung ulang.
            // ==========================================
            \App\Entities\Penjualan\PackingOrderDetail::where('do_id', $do->id)->update([
                'discount_1'         => $after['disc_agen_percent'],
                'discount_2'         => $after['disc_kemasan_percent'],
                'discount_idr'       => $after['disc_tambahan_idr'],
                'voucher_idr'        => $after['voucher_idr'],
                'delivery_cost_idr'  => $after['delivery_cost_idr'],
                'other_cost_idr'     => $after['other_cost_idr'],
                'purchase_total_idr' => $totals['sub_total_item'],
                'total_discount_idr' => $totals['disc_agen_idr'] + $totals['disc_kemasan_idr'] + $after['disc_tambahan_idr'],
                'grand_total_idr'    => $totals['grand_total_idr'],
                'updated_by'         => Auth::id(),
            ]);

            $do->update(['idr_rate' => $after['idr_rate']]);

            // ==========================================
            // UPDATE SO - Sales & Rekening jika berubah
            // ==========================================
            if ($do->so_id) {
                $soUpdate = [];
                if (isset($after['sales_senior_id']) && $after['sales_senior_id'] != $before['sales_senior_id']) {
                    $soUpdate['sales_senior_id'] = $after['sales_senior_id'];
                }
                if (isset($after['sales_id']) && $after['sales_id'] != $before['sales_id']) {
                    $soUpdate['sales_id'] = $after['sales_id'];
                }
                if (isset($after['rekening_id']) && $after['rekening_id'] != $before['rekening_id']) {
                    $soUpdate['rekening'] = $after['rekening_id'];
                }
                if (!empty($soUpdate)) {
                    SalesOrder::where('id', $do->so_id)->update($soUpdate);
                }
            }

            $do->update([
                'internal_revision_status' => null,
                'internal_revision_count' => $do->internal_revision_count + 1,
            ]);

            $revision->update([
                'status' => 2,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_reason' => trim($request->approval_reason),
                'otp_hash' => null,
                'otp_expires_at' => null,
                'revision_detail' => array_merge($detail, ['calculated_totals' => $totals]),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Revisi internal berhasil diapprove dan dieksekusi.' . ($revision->items_changed ? ' Item berubah - wajib cetak ulang Surat Jalan.' : ''),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Reject pengajuan: lepas lock DO, lepas hold invoice, tidak ada eksekusi stok.
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['approval_reason' => 'required|string|min:5']);

        DB::beginTransaction();
        try {
            $revision = DoInternalRevision::where('status', 1)->lockForUpdate()->findOrFail($id);

            if (!Auth::user()->is_superuser && !Auth::user()->hasRole(['Manajemen', 'Developer'])) {
                throw new \Exception('Anda tidak punya akses approval revisi internal.');
            }
            if ((int) $revision->requested_by === (int) Auth::id()) {
                throw new \Exception('Pengaju dan approver tidak boleh orang yang sama.');
            }

            $revision->update([
                'status' => 3,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_reason' => trim($request->approval_reason),
                'otp_hash' => null,
                'otp_expires_at' => null,
            ]);

            PackingOrder::where('id', $revision->do_id)->update(['internal_revision_status' => null]);

            Invoicing::where('do_id', $revision->do_id)->update(['status' => Invoicing::STATUS['ACTIVE']]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Pengajuan revisi ditolak, DO kembali normal.']);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function detail($id)
    {
        // Eager-load semua jalur nama customer, sama seperti index(), supaya
        // detailCustomer di modal juga tidak pernah blank.
        $revision = DoInternalRevision::with([
            'packingOrder.member',
            'packingOrder.so.member',
            'packingOrder.customer',
        ])->find($id);
        $detail = $revision->revision_detail;

        $resolveItems = function ($items, $idrRate) {
            return collect($items)->map(function ($item) use ($idrRate) {
                $product = \App\Entities\Master\ProductPack::find($item['product_packaging_id']);
                $item['product_code'] = $product->code ?? '-';
                $item['product_name'] = $product->name ?? '(produk tidak ditemukan)';
                $item['price_idr'] = round($item['price'] * $idrRate);
                $item['usd_disc_idr'] = round($item['usd_disc'] * $idrRate);
                $totalDiscUsd = ($item['usd_disc'] + (($item['price'] - $item['usd_disc']) * ($item['percent_disc'] / 100))) * $item['qty'];
                $item['total_idr'] = round((($item['qty'] * $item['price']) - $totalDiscUsd) * $idrRate);
                return $item;
            });
        };

        $before = $detail['before'];
        $after = $detail['after'];

        $before['items'] = $resolveItems($before['items'], $before['idr_rate']);
        $after['items'] = $resolveItems($after['items'], $after['idr_rate']);

        // Total akhir before/after, biar approver bisa langsung banding sama invoice tercetak
        try {
            $before['calculated_totals'] = $this->calculateTotals(array_merge($before, ['items' => $before['items']->toArray()]));
        } catch (\Throwable $e) {
            $before['calculated_totals'] = null;
        }
        try {
            $after['calculated_totals'] = $this->calculateTotals(array_merge($after, ['items' => $after['items']->toArray()]));
        } catch (\Throwable $e) {
            $after['calculated_totals'] = null;
        }

        return response()->json([
            'do_code' => optional($revision->packingOrder)->do_code,
            'customer' => $this->resolveCustomerName($revision->packingOrder),
            'origin_status' => $revision->origin_status,
            'request_reason' => $revision->request_reason,
            'items_changed' => $revision->items_changed,
            'status' => $revision->status,
            'approved_by' => optional($revision->approvedBy)->name ?? null,
            'approval_reason' => $revision->approval_reason ?? null,
            'approved_at' => $revision->approved_at ? $revision->approved_at->format('d/m/Y H:i') : null,
            'before' => $before,
            'after' => $after,
        ]);
    }
}