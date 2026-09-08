<?php

namespace App\Services\Receiving;

use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\PurchaseOrderSummary;
use App\Entities\Gudang\Receiving;
use App\Entities\Master\ProductPack;
use App\Entities\Master\Warehouse;
use DB;
use Validator;

/**
 * Logika bisnis Receiving (header + workflow).
 *
 * Controller hanya mengurus HTTP (request/response + hak akses),
 * semua validasi, query, dan transaksi database terpusat di sini.
 */
class ReceivingService
{
    /**
     * Data dropdown untuk form create.
     */
    public function formData()
    {
        return [
            'warehouses' => Warehouse::get(),
        ];
    }

    /**
     * Sisa real per produk yang masih bisa diterima ke receiving ini.
     *
     * Dihitung live (BUKAN dari tabel summary yang korup oleh data transisi):
     *   per PO (type PO, status SENT/ACC): detail.qty
     *     - sudah diterima (quantity_ri receiving ACC)
     *     - sudah dipesan receiving terbuka (quantity_po receiving ACTIVE/QC/READY, termasuk receiving ini)
     *   Sisa per (PO, produk) dijaga >= 0 lalu dijumlah per produk.
     *
     * @return \Illuminate\Support\Collection [{product_pack_id, code, name, pack_name, qty_available, po_id}]
     */
    public function liveAvailability($receiving_id)
    {
        $map = $this->liveRemainingMap($receiving_id);

        $result = collect();
        foreach ($map as $pack_id => $row) {
            if ($row['total'] <= 0.005) continue;
            $pack = ProductPack::with('packaging')->find($pack_id);
            if (!$pack) continue;
            $result->push((object) [
                'product_pack_id' => $pack_id,
                'code'            => $pack->code,
                'name'            => $pack->name,
                'pack_name'       => $pack->packaging ? $pack->packaging->pack_name : null,
                'qty_available'   => round($row['total'], 2),
                'po_id'           => $row['po_id'],
            ]);
        }

        return $result->values();
    }

    /**
     * Total sisa live 1 produk untuk 1 receiving (dipakai validasi).
     */
    public function livePackRemaining($pack_id, $receiving_id)
    {
        $map = $this->liveRemainingMap($receiving_id);

        return isset($map[$pack_id]) ? round($map[$pack_id]['total'], 2) : 0;
    }

    /**
     * PO pertama (terlama) yang masih punya sisa untuk 1 produk.
     */
    public function liveFirstPo($pack_id, $receiving_id)
    {
        $map = $this->liveRemainingMap($receiving_id);

        return isset($map[$pack_id]) ? $map[$pack_id]['po_id'] : null;
    }

    /**
     * Peta sisa [product_packaging_id => [total, po_id]].
     */
    private function liveRemainingMap($receiving_id)
    {
        $poDetails = DB::table('purchase_order_detail as d')
            ->join('purchase_order as po', 'po.id', '=', 'd.po_id')
            ->where('po.type', 1)
            ->whereIn('po.status', [2, 4]) // ACC, SENT
            ->select('po.id as po_id', 'd.product_packaging_id as pack_id', 'd.quantity as qty')
            ->orderBy('po.id')
            ->get();

        $received = DB::table('receiving_detail as rd')
            ->join('receiving as r', 'r.id', '=', 'rd.receiving_id')
            ->where('r.status', 4) // ACC
            ->select('rd.po_id', 'rd.product_packaging_id as pack_id', DB::raw('COALESCE(SUM(rd.quantity_ri),0) as q'))
            ->groupBy('rd.po_id', 'rd.product_packaging_id')
            ->get()
            ->keyBy(function ($x) { return $x->po_id . '|' . $x->pack_id; });

        $reserved = DB::table('receiving_detail as rd')
            ->join('receiving as r', 'r.id', '=', 'rd.receiving_id')
            ->whereIn('r.status', [1, 2, 3]) // ACTIVE, QC, READY (termasuk receiving ini)
            ->select('rd.po_id', 'rd.product_packaging_id as pack_id', DB::raw('COALESCE(SUM(rd.quantity_po),0) as q'))
            ->groupBy('rd.po_id', 'rd.product_packaging_id')
            ->get()
            ->keyBy(function ($x) { return $x->po_id . '|' . $x->pack_id; });

        $out = [];
        foreach ($poDetails as $d) {
            if (empty($d->pack_id)) continue;
            $k = $d->po_id . '|' . $d->pack_id;
            $rem = (float) $d->qty
                - (float) (isset($received[$k]) ? $received[$k]->q : 0)
                - (float) (isset($reserved[$k]) ? $reserved[$k]->q : 0);
            if ($rem > 0.005) {
                if (!isset($out[$d->pack_id])) {
                    $out[$d->pack_id] = ['total' => 0, 'po_id' => $d->po_id];
                }
                $out[$d->pack_id]['total'] += $rem;
                $out[$d->pack_id]['po_id'] = min($out[$d->pack_id]['po_id'], $d->po_id);
            }
        }

        return $out;
    }

    /**
     * Buat receiving baru (status ACTIVE/draft admin).
     *
     * @return array [status => ok|validation, errors => [], receiving => Receiving|null]
     */
    public function createReceiving(array $input, $user_id)
    {
        $validator = Validator::make($input, [
            'code'      => 'required|string|unique:receiving,code',
            'warehouse' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return ["status" => "validation", "errors" => $validator->errors()->all(), "receiving" => null];
        }

        $receiving               = new Receiving;
        $receiving->code         = $input["code"];
        $receiving->type         = isset($input["type"]) ? $input["type"] : Receiving::TYPE['INBOUND'];
        $receiving->warehouse_id = $input["warehouse"];
        $receiving->pbm_date     = isset($input["pbm_date"]) ? $input["pbm_date"] : null;
        $receiving->note         = isset($input["note"]) ? $input["note"] : null;
        $receiving->status       = Receiving::STATUS['ACTIVE'];

        if ($receiving->save()) {
            return ["status" => "ok", "errors" => [], "receiving" => $receiving];
        }

        return ["status" => "validation", "errors" => ["Gagal menyimpan receiving"], "receiving" => null];
    }

    /**
     * Update header receiving.
     *
     * @return array [status => ok|validation|not_found, errors => [], receiving => Receiving|null]
     */
    public function updateReceiving($id, array $input)
    {
        $receiving = Receiving::find($id);

        if ($receiving == null) {
            return ["status" => "not_found", "errors" => [], "receiving" => null];
        }

        $validator = Validator::make($input, [
            'code' => 'required|string|unique:receiving,code,' . $receiving->id,
        ]);

        if ($validator->fails()) {
            return ["status" => "validation", "errors" => $validator->errors()->all(), "receiving" => null];
        }

        $receiving->code = $input["code"];
        $receiving->pbm_date = isset($input["pbm_date"]) ? $input["pbm_date"] : $receiving->pbm_date;
        $receiving->note = isset($input["note"]) ? $input["note"] : $receiving->note;

        if ($receiving->save()) {
            return ["status" => "ok", "errors" => [], "receiving" => $receiving];
        }

        return ["status" => "validation", "errors" => ["Gagal menyimpan receiving"], "receiving" => null];
    }

    /**
     * Hapus receiving (soft delete via status DELETED).
     *
     * @return array [status => ok|not_found]
     */
    public function deleteReceiving($id)
    {
        $receiving = Receiving::find($id);

        if ($receiving === null) {
            return ["status" => "not_found"];
        }

        $receiving->status = Receiving::STATUS['DELETED'];

        if ($receiving->save()) {
            return ["status" => "ok"];
        }

        return ["status" => "not_found"];
    }

    /**
     * Publish bertahap: ACTIVE -> QC -> READY.
     * Return dipakai controller untuk redirect (step / back + pesan).
     *
     * @return array [ok => bool, step_id => int|null, message => string, error => string|null]
     */
    public function publishReceiving($id)
    {
        try {
            $receiving = Receiving::findOrFail($id);

            // Publish to QC
            if ($receiving->status == Receiving::STATUS['ACTIVE']) {
                if ($receiving->details->isEmpty()) {
                    return ["ok" => false, "step_id" => null, "message" => "", "error" => "Receiving tidak memiliki list Product"];
                }

                $receiving->status = Receiving::STATUS['QC'];
                $receiving->save();

                return ["ok" => true, "step_id" => $receiving->id, "message" => "Receiving berhasil dipindah ke tahap QC", "error" => null];
            }

            // Publish to Ready
            if ($receiving->status == Receiving::STATUS['QC']) {

                // 1. Re-kalkulasi quantity_ri dan selisih
                foreach ($receiving->details as $detail) {
                    // Akumulasi semua log QC
                    $totalQtyQc = $detail->qcLogs()->sum('qty_qc');

                    $detail->quantity_ri = $totalQtyQc;
                    $detail->selisih     = $detail->quantity_po - $totalQtyQc;
                    $detail->save();

                    // Validasi: cek apakah ada log QC yang belum di-approve
                    $hasUnapprovedSellable = $detail->qcLogs()
                        ->where('is_sellable', 1)
                        ->where('is_approved', 0)
                        ->exists();

                    if ($hasUnapprovedSellable) {
                        return ["ok" => false, "step_id" => null, "message" => "", "error" => "Terdapat item QC yang sudah ditandai saleable namun belum di-approve pada produk: {$detail->product_pack->name}"];
                    }
                }

                // 2. Validasi: setiap detail harus memiliki quantity_ri > 0
                $hasInvalidRi = $receiving->details->contains(function ($d) {
                    return is_null($d->quantity_ri) || $d->quantity_ri <= 0;
                });

                if ($hasInvalidRi) {
                    return ["ok" => false, "step_id" => null, "message" => "", "error" => "Semua detail Receiving harus memiliki data QC yang valid (jumlah yang diterima > 0)."];
                }

                // 3. Update status menjadi READY
                $receiving->status = Receiving::STATUS['READY'];
                $receiving->save();

                return ["ok" => true, "step_id" => $receiving->id, "message" => "Receiving berhasil dipindah ke tahap Ready", "error" => null];
            }

            return ["ok" => false, "step_id" => null, "message" => "", "error" => "Status tidak valid untuk diproses"];
        } catch (\Exception $e) {
            return ["ok" => false, "step_id" => null, "message" => "", "error" => "Terjadi kesalahan: " . $e->getMessage()];
        }
    }

    /**
     * ACC receiving READY: masuk stok + potong PO summary (INBOND).
     *
     * @return array [status => ok|not_ready|error, message => string]
     */
    public function accReceiving($id, $user_id)
    {
        DB::beginTransaction();

        try {
            $receiving = Receiving::findOrFail($id);

            // Validasi status
            if ($receiving->status != Receiving::STATUS['READY']) {
                DB::rollBack();

                return ["status" => "not_ready", "message" => "Receiving tidak dalam status READY"];
            }

            // Lock receiving untuk prevent concurrent ACC
            $receiving = Receiving::where('id', $id)
                ->lockForUpdate()
                ->first();

            // Initialize StockService
            $stockService = app(\App\Services\StockService::class);

            // Track total per product & PO untuk smart deduction
            $poDeductionMap = []; // Format: [po_id => [product_id => qty_to_deduct]]

            foreach ($receiving->details as $detail) {
                // Hanya proses yang sellable (is_sellable = 0)
                $qtyToStock = $detail->qcLogs()
                                    ->where('is_sellable', 0)
                                    ->sum('qty_qc');

                if ($qtyToStock <= 0) {
                    continue;
                }

                $poId = $detail->po_id;
                $productId = $detail->product_packaging_id;

                // Track untuk deduction PO nanti
                if (!isset($poDeductionMap[$poId])) {
                    $poDeductionMap[$poId] = [];
                }
                if (!isset($poDeductionMap[$poId][$productId])) {
                    $poDeductionMap[$poId][$productId] = 0;
                }
                $poDeductionMap[$poId][$productId] += $qtyToStock;

                // Input stock pakai StockService (atomic + balance accurate)
                $stockService->replayHistoricalLog(
                    $receiving->warehouse_id,
                    $productId,
                    $qtyToStock,
                    'IN',  // IN karena receiving
                    'RI-' . $receiving->code,
                    now(),  // transactionDate
                    now(),  // transactionDate
                    'Receiving ACC - ' . $detail->product_pack->name
                );
            }

            // POTONG PO SUMMARY (hanya jika tipe INBOUND)
            if ($receiving->type == 0) { // INBOUND
                foreach ($poDeductionMap as $poId => $productMap) {
                    foreach ($productMap as $productId => $qtyToCut) {
                        $summaries = PurchaseOrderSummary::where([
                                ['product_packaging_id', $productId],
                                ['po_id', $poId],
                                ['status', 2]  // Status = Active/Open
                            ])
                            ->orderBy('id')
                            ->lockForUpdate()
                            ->get();

                        $sisaToCut = (float) $qtyToCut;
                        $deductedQty = 0;

                        foreach ($summaries as $sum) {
                            if ($sisaToCut <= 0) break;

                            $ambil = min($sisaToCut, (float) $sum->quantity);
                            $sum->quantity -= $ambil;
                            $sisaToCut -= $ambil;
                            $deductedQty += $ambil;

                            if ($sum->quantity == 0) {
                                $sum->status = 1; // Mark as done
                            }

                            $sum->save();
                        }

                        if ($sisaToCut > 0) {
                            \Log::warning('PO deduction incomplete', [
                                'receiving_id' => $receiving->id,
                                'po_id' => $poId,
                                'product_id' => $productId,
                                'qty_to_cut' => $qtyToCut,
                                'deducted' => $deductedQty,
                                'remaining' => $sisaToCut,
                                'summaries_count' => $summaries->count(),
                            ]);
                        }
                    }
                }
            } elseif ($receiving->type == 1) { // RETUR
                // Update warehouse_id pada retur
                foreach ($receiving->details as $detail) {
                    DB::table('penjualan_retur')
                        ->where('id', $detail->po_id)
                        ->update(['warehouse_id' => $receiving->warehouse_id]);
                }
            }

            // Update receiving status
            $receiving->status = Receiving::STATUS['ACC'];
            $receiving->acc_by = $user_id;
            $receiving->acc_at = now();
            $receiving->save();

            DB::commit();

            return ["status" => "ok", "message" => "Receiving berhasil di-ACC"];
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('ACC Receiving error', [
                'receiving_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ["status" => "error", "message" => "Terjadi kesalahan: " . $e->getMessage()];
        }
    }

    /**
     * Kembalikan receiving READY ke QC (revisi human error).
     * Log QC dan quantity_ri/selisih dibiarkan — dihitung ulang saat publish berikutnya.
     *
     * @return array [status => ok|invalid|error, message => string]
     */
    public function rollbackToQc($id)
    {
        try {
            $receiving = Receiving::findOrFail($id);

            if ($receiving->status != Receiving::STATUS['READY']) {
                return ["status" => "invalid", "message" => "Hanya receiving READY yang bisa dikembalikan ke QC."];
            }

            $receiving->status = Receiving::STATUS['QC'];
            $receiving->save();

            return ["status" => "ok", "message" => "Receiving dikembalikan ke tahap QC."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => "Terjadi kesalahan: " . $e->getMessage()];
        }
    }

    /**
     * Batalkan receiving kembali ke ACTIVE (ditolak bila sudah ada log QC).
     *
     * @return array [status => ok|has_qc|error, message => string]
     */
    public function cancelReceiving($id)
    {
        DB::beginTransaction();

        try {
            $receiving = Receiving::with('details.qcLogs')->findOrFail($id);

            // Cek apakah ada log QC
            $hasQcLogs = $receiving->details->pluck('qcLogs')->flatten()->isNotEmpty();
            if ($hasQcLogs) {
                DB::rollBack();

                return ["status" => "has_qc", "message" => "Receiving tidak bisa dibatalkan karena sudah ada item QC."];
            }

            $receiving->status = Receiving::STATUS['ACTIVE'];
            $receiving->save();

            DB::commit();

            return ["status" => "ok", "message" => "Receiving berhasil di batalkan"];
        } catch (\Exception $e) {
            DB::rollBack();

            return ["status" => "error", "message" => "Terjadi kesalahan: " . $e->getMessage()];
        }
    }
}
