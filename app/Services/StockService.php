<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Entities\Master\ProductMinStock;
use App\Entities\Gudang\StockMove;

class StockService
{
    // 1. POTONG FISIK RAK (DIPANGGIL: DO Packed & Mutasi Step 1)
    public function deductPhysicalStock($warehouseId, $productId, $qty)
    {
        return DB::transaction(function () use ($warehouseId, $productId, $qty) {
            $stock = ProductMinStock::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)->lockForUpdate()->first();

            if (!$stock) {
                $stock = ProductMinStock::create([
                    'warehouse_id' => $warehouseId, 'product_packaging_id' => $productId,
                    'quantity' => 0, 'reserved_quantity' => 0
                ]);
            }

            $stock->quantity -= $qty;
            if ($stock->reserved_quantity >= $qty) $stock->reserved_quantity -= $qty;
            $stock->save();
            return true;
        });
    }

    // 2. CETAK LOG ADMINISTRASI (DIPANGGIL: DO Sent & Mutasi Step 3)
    public function recordAdministrativeLog($warehouseId, $productId, $qty, $transactionCode, $note)
    {
        return DB::transaction(function () use ($warehouseId, $productId, $qty, $transactionCode, $note) {
            $stock = ProductMinStock::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)->lockForUpdate()->first();

            StockMove::create([
                'code_transaction' => $transactionCode, 'warehouse_id' => $warehouseId,
                'product_packaging_id' => $productId, 'stock_in' => 0, 'stock_out' => $qty,
                'stock_balance' => $stock ? $stock->quantity : 0,
                'note' => $note, 'created_by' => auth()->id() ?? 1,
            ]);
            return true;
        });
    }

    // 3. MESIN WAKTU / REBUILD (DIPANGGIL: StockRebuildService)
    public function replayHistoricalLog($warehouseId, $productId, $qty, $type, $transactionCode, $historicalDate, $note)
    {
        return DB::transaction(function () use ($warehouseId, $productId, $qty, $type, $transactionCode, $historicalDate, $note) {
            $stock = ProductMinStock::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)->lockForUpdate()->first();

            if (!$stock) {
                $stock = ProductMinStock::create([
                    'warehouse_id' => $warehouseId, 'product_packaging_id' => $productId,
                    'quantity' => 0, 'reserved_quantity' => 0
                ]);
            }

            if ($type === 'IN') $stock->quantity += $qty; else $stock->quantity -= $qty;
            $stock->save();

            StockMove::insert([
                'code_transaction' => $transactionCode, 'warehouse_id' => $warehouseId,
                'product_packaging_id' => $productId, 'stock_in' => ($type === 'IN') ? $qty : 0,
                'stock_out' => ($type === 'OUT') ? $qty : 0, 'stock_balance' => $stock->quantity,
                'note' => $note, 'created_by' => auth()->id() ?? 1,
                'created_at' => $historicalDate, 'updated_at' => now(),
            ]);
            return true;
        });
    }

    // 4. SMART CANCEL (DIPANGGIL: Batal DO & Batal Mutasi)
    public function executeSmartCancel($warehouseId, $productId, $qty, $status, $transactionCode, $note)
    {
        return DB::transaction(function () use ($warehouseId, $productId, $qty, $status, $transactionCode, $note) {
            $stock = ProductMinStock::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)->lockForUpdate()->first();

            if ($stock) {
                $stock->quantity += $qty;
                $stock->save();

                // Hanya cetak jurnal pembalik jika DO sudah Update Resi (Status 6)
                if ($status == 6) {
                    StockMove::create([
                        'code_transaction' => 'CANCEL-' . $transactionCode, 'warehouse_id' => $warehouseId,
                        'product_packaging_id' => $productId, 'stock_in' => $qty, 'stock_out' => 0,
                        'stock_balance' => $stock->quantity, 'note' => $note,
                        'created_by' => auth()->id() ?? 1,
                    ]);
                }
            }
            return true;
        });
    }
    
    /**
     * 5. FUNGSI BOOKING STOK (DIPANGGIL SAAT ACC PROFORMA)
     * Hanya menambah reserved_quantity, tidak memotong fisik.
     */
    public function reserveStock($warehouseId, $productId, $qty)
    {
        // Angka 5 di bawah ini adalah fitur bawaan Laravel untuk mengulang (retry) 
        // transaksi hingga 5 kali jika terjadi deadlock database.
        return DB::transaction(function () use ($warehouseId, $productId, $qty) {
            $stock = ProductMinStock::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                $stock = ProductMinStock::create([
                    'warehouse_id' => $warehouseId,
                    'product_packaging_id' => $productId,
                    'quantity' => 0,
                    'reserved_quantity' => 0
                ]);
            }

            $current_reserved = (float) $stock->reserved_quantity;
            $quantity = (float) $stock->quantity;
            
            $available = $quantity - $current_reserved;
            $new_available = $available - $qty;

            if ($available >= $qty) {
                $stock->reserved_quantity = $current_reserved + $qty;
            } elseif ($available >= 0 && $new_available < 0) {
                $stock->reserved_quantity = $current_reserved + $qty;
            } else {
                throw new \Exception("Stock packaging ID {$productId} sudah minus sebelumnya. Available: {$available}, Request: {$qty}");
            }

            $stock->save();
            return true;
        }, 5); // <-- Otomatis retry 5x jika deadlock
    }
    
    /**
     * 6. FUNGSI RELEASE BOOKING STOK (DIPANGGIL SAAT TUTUP SO / BARANG REJECT)
     * Hanya mengurangi reserved_quantity karena barang batal dikirim.
     */
    public function releaseReservedStock($warehouseId, $productId, $qty)
    {
        return DB::transaction(function () use ($warehouseId, $productId, $qty) {
            $stock = ProductMinStock::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)
                ->lockForUpdate()
                ->first();

            if ($stock) {
                // Lepaskan kuota booking dengan aman
                if ($stock->reserved_quantity >= $qty) {
                    $stock->reserved_quantity -= $qty;
                } else {
                    $stock->reserved_quantity = 0;
                }
                $stock->save();
            }
            return true;
        });
    }

    /**
     * 7. FUNGSI CANCEL / REVISI DO
     * Tahu persis kapan harus mengembalikan Fisik Rak atau sekadar melepas Booking.
     */
    public function cancelDoRevisi($warehouseId, $productId, $qty, $isProforma, $doStatus)
    {
        return DB::transaction(function () use ($warehouseId, $productId, $qty, $isProforma, $doStatus) {
            $stock = ProductMinStock::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)->lockForUpdate()->first();

            if ($stock) {
                // ATURAN CERDAS 1 PINTU:
                // - Jika Proforma: Fisik SUDAH PASTI terpotong di ACC, wajib dikembalikan!
                // - Jika Normal SO: Fisik terpotong JIKA status DO >= 3 (Sudah dipacking).
                $isPhysicalCut = $isProforma || in_array($doStatus, [3, 4]);

                if ($isPhysicalCut) {
                    $stock->quantity += $qty; // Kembalikan fisik ke rak
                } else {
                    // Jika fisik belum terpotong (DO Normal masih Draft/Status 2), 
                    // maka yang ter-booking hanya reserved_quantity. Lepaskan itu!
                    if ($stock->reserved_quantity >= $qty) {
                        $stock->reserved_quantity -= $qty;
                    } else {
                        $stock->reserved_quantity = 0;
                    }
                }
                $stock->save();
            }
            return true;
        });
    }
}