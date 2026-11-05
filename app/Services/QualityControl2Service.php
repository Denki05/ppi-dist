<?php

namespace App\Services;

use App\Entities\Gudang\QualityControl2;
use App\Entities\Gudang\StockMove;
use App\Entities\Master\ProductMinStock;
use DB;

/**
 * Logika ACC Receiving - Komplain.
 *
 * Barang masuk ke Gudang Araya lalu ditransfer ke gudang QC tujuan,
 * sehingga Trans dan WMS memakai alur stok yang sama.
 */
class QualityControl2Service
{
    /**
     * @return array [status => ok|already|error, message => string]
     */
    public function acc($id, $userId)
    {
        DB::beginTransaction();

        try {
            $qc = QualityControl2::with('details')->findOrFail($id);

            if ($qc->status == QualityControl2::STATUS['ACC']) {
                DB::rollBack();

                return ['status' => 'already', 'message' => 'Data sudah di ACC.'];
            }

            foreach ($qc->details as $detail) {
                $qty = floatval($detail->qty);
                $productId = $detail->product_packaging_id;

                // 1. IN ke Gudang Araya
                $stockAraya = ProductMinStock::where('product_packaging_id', $productId)
                    ->where('warehouse_id', 2)
                    ->lockForUpdate()
                    ->first();

                if (!$stockAraya) {
                    throw new \Exception('Stok Gudang Araya tidak ditemukan');
                }

                $stockAraya->quantity += $qty;
                $stockAraya->save();

                StockMove::create([
                    'code_transaction'     => $qc->code,
                    'warehouse_id'         => 2,
                    'product_packaging_id' => $productId,
                    'stock_in'             => $qty,
                    'stock_out'            => 0,
                    'stock_balance'        => $stockAraya->quantity,
                    'note'                 => 'Receiving komplain - masuk gudang Araya',
                    'created_by'           => $userId,
                ]);

                // 2. OUT dari Gudang Araya (transfer ke QC)
                $stockAraya->quantity -= $qty;
                $stockAraya->save();

                StockMove::create([
                    'code_transaction'     => $qc->code,
                    'warehouse_id'         => 2,
                    'product_packaging_id' => $productId,
                    'stock_in'             => 0,
                    'stock_out'            => $qty,
                    'stock_balance'        => $stockAraya->quantity,
                    'note'                 => 'Transfer ke gudang QC',
                    'created_by'           => $userId,
                ]);

                // 3. IN ke Gudang QC
                $stockQc = ProductMinStock::where('product_packaging_id', $productId)
                    ->where('warehouse_id', $qc->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stockQc) {
                    $stockQc = ProductMinStock::create([
                        'product_packaging_id' => $productId,
                        'warehouse_id'         => $qc->warehouse_id,
                        'unit_id'              => $stockAraya->unit_id ?? 1,
                        'quantity'             => 0,
                        'selling_price'        => 0,
                    ]);
                }

                $stockQc->quantity += $qty;
                $stockQc->save();

                StockMove::create([
                    'code_transaction'     => $qc->code,
                    'warehouse_id'         => $qc->warehouse_id,
                    'product_packaging_id' => $productId,
                    'stock_in'             => $qty,
                    'stock_out'            => 0,
                    'stock_balance'        => $stockQc->quantity,
                    'note'                 => 'Receiving komplain - masuk gudang QC',
                    'created_by'           => $userId,
                ]);
            }

            $qc->update(['status' => QualityControl2::STATUS['ACC']]);

            DB::commit();

            return ['status' => 'ok', 'message' => 'Komplain berhasil di-ACC.'];
        } catch (\Exception $e) {
            DB::rollBack();

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
