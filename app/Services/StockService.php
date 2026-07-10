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
        // Tambahkan validasi keamanan
        if (empty($warehouseId) || empty($productId)) {
            \Log::error("Gagal deduct stok: Warehouse ID atau Product ID kosong.", [
                'warehouse_id' => $warehouseId,
                'product_id' => $productId
            ]);
            throw new \Exception("Data stok tidak lengkap (Warehouse/Product ID kosong).");
        }

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
    public function recordAdministrativeLog(
        $warehouseId, 
        $productId, 
        $qty, 
        $transactionCode, 
        $note,
        $transactionDate = null  // ✅ TAMBAHAN PARAMETER
    ) {
        return DB::transaction(function () use ($warehouseId, $productId, $qty, $transactionCode, $note, $transactionDate) {
            $stock = ProductMinStock::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)
                ->lockForUpdate()
                ->first();

            // ✅ Get current balance
            $currentBalance = $stock ? $stock->quantity : 0;

            // ✅ Calculate new balance setelah transaksi
            $newBalance = $currentBalance - $qty;
    
            // ✅ Gunakan insert() agar bisa override created_at
            StockMove::insert([
                'code_transaction'     => $transactionCode,
                'warehouse_id'         => $warehouseId,
                'product_packaging_id' => $productId,
                'stock_in'             => 0,
                'stock_out'            => $qty,
                'stock_balance'        => $newBalance,
                'note'                 => $note,
                'created_by'           => auth()->id() ?? 1,
                'created_at'           => $transactionDate ?? now(), // ✅ backdate jika lintas bulan
                'updated_at'           => now(),
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
                'code_transaction' => $transactionCode, 
                'warehouse_id' => $warehouseId,
                'product_packaging_id' => $productId, 
                'stock_in' => ($type === 'IN') ? $qty : 0,
                'stock_out' => ($type === 'OUT') ? $qty : 0, 
                'stock_balance' => $stock->quantity,
                'note' => $note, 
                'created_by' => auth()->id() ?? 1,
                'created_at' => $historicalDate, 
                'updated_at' => now(),
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
     * 5. FUNGSI BOOKING STOK (DIPANGGIL SAAT ACC PROFORMA / TUTUP SO)
     * Hanya menambah reserved_quantity, tidak memotong fisik.
     * Menggunakan logika SMART MINUS.
     */
    public function reserveStock($warehouseId, $productId, $qty)
    {
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

            // 1. Hitung sisa stok yang benar-benar bisa di-booking
            $available = $quantity - $current_reserved;

            // 2. Jika fisik rak dari awal memang sudah minus (bug/selisih opname), tolak!
            if ($quantity < 0) {
                throw new \Exception("Stok fisik sudah minus. Product ID: {$productId}");
            }

            // 3. ATURAN SMART MINUS
            // Jika sisa stok SEBELUM order ini masuk sudah minus, TOLAK!
            // Ini memastikan stok tidak bertambah minus berkali-kali.
            if ($available < 0) {
                throw new \Exception("Stock untuk product {$productId} sedang kosong / minus (Sisa: {$available}). Harap tunggu restock.");
            }

            // Jika lolos dari validasi di atas, orderan ini diizinkan lewat
            // meskipun akan membuat sisa stok (available) menjadi negatif.
            $stock->reserved_quantity = $current_reserved + $qty;
            
            $stock->save();
            return true;
        }, 5); 
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
     */
    public function cancelDoRevisi($warehouseId, $productId, $qty, $isProforma, $doStatus)
    {
        return DB::transaction(function () use ($warehouseId, $productId, $qty, $isProforma, $doStatus) {
            $stock = ProductMinStock::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)->lockForUpdate()->first();

            if ($stock) {
                // UPDATE ATURAN BARU:
                // Proforma sekarang sama dengan SO Normal. Pemotongan fisik HANYA terjadi di checker.
                // Jadi, fisik rak dikembalikan HANYA JIKA DO sudah masuk tahap packing/checker (Status 3 / 4).
                $isPhysicalCut = in_array($doStatus, [3, 4]);

                if ($isPhysicalCut) {
                    // Kembalikan fisik ke rak
                    $stock->quantity += $qty; 
                } else {
                    // Jika fisik belum terpotong (DO masih Draft/List PO Status 2), 
                    // maka yang ter-booking hanya reserved_quantity. Lepaskan booking-nya!
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

    public function undoDeductPhysicalStock($warehouseId, $productId, $qty)
    {
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

            $qty = (float)$qty;

            // 🔁 restore fisik
            $stock->quantity += $qty;

            // 🔁 restore reserved (dengan batas)
            $stock->reserved_quantity = min(
                $stock->quantity,
                (float)$stock->reserved_quantity + $qty
            );

            $stock->save();

            return true;
        });
    }
}