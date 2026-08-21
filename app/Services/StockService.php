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
    public function recordAdministrativeLog(
        $warehouseId,
        $productId,
        $qty,
        $transactionCode,
        $note,
        $transactionDate = null
    ) {
        return DB::transaction(function () use ($warehouseId, $productId, $qty, $transactionCode, $note, $transactionDate) {
 
            $effectiveDate = $transactionDate ?? now();
 
            // ✅ LOCK per produk+gudang (dipakai sebagai mutex, bukan untuk baca quantity-nya)
            // supaya proses baca-saldo-lalu-insert ini tidak bertabrakan kalau ada
            // proses lain yang nyaris bersamaan menyentuh produk yang sama.
            ProductMinStock::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)
                ->lockForUpdate()
                ->first();
 
            // ✅ Basis saldo: baris StockMove TERAKHIR secara kronologis SEBELUM
            // tanggal transaksi ini (bukan ProductMinStock.quantity).
            $lastBalance = StockMove::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)
                ->where('created_at', '<=', $effectiveDate)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->value('stock_balance');
 
            $currentBalance = $lastBalance ?? 0;
 
            // ✅ Calculate new balance setelah transaksi
            $newBalance = $currentBalance - $qty;
 
            // ✅ Insert baris baru (pakai insertGetId agar dapat id untuk tie-breaker di bawah)
            $newId = StockMove::insertGetId([
                'code_transaction'     => $transactionCode,
                'warehouse_id'         => $warehouseId,
                'product_packaging_id' => $productId,
                'stock_in'             => 0,
                'stock_out'            => $qty,
                'stock_balance'        => $newBalance,
                'note'                 => $note,
                'created_by'           => auth()->id() ?? 1,
                'created_at'           => $effectiveDate, // ✅ backdate jika lintas bulan
                'updated_at'           => now(),
            ]);
 
            // ✅ FIX BACKDATE: jika ada baris LAIN yang created_at-nya SESUDAH baris
            // baru ini (transaksi ini menyisip di tengah histori), baris-baris itu
            // harus di-recalculate ulang berantai karena saldo mereka sekarang
            // "kedahuluan" oleh sisipan baru ini.
            $subsequentMoves = StockMove::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)
                ->where(function ($q) use ($effectiveDate, $newId) {
                    $q->where('created_at', '>', $effectiveDate)
                      ->orWhere(function ($q2) use ($effectiveDate, $newId) {
                          // Tie-breaker: created_at sama persis tapi id lebih besar dari baris baru
                          $q2->where('created_at', '=', $effectiveDate)
                             ->where('id', '>', $newId);
                      });
                })
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();
 
            $runningBalance = $newBalance;
            foreach ($subsequentMoves as $move) {
                $runningBalance += ($move->stock_in - $move->stock_out);
                if ((float) $move->stock_balance !== (float) $runningBalance) {
                    StockMove::where('id', $move->id)->update([
                        'stock_balance' => $runningBalance,
                        'updated_at'    => now(),
                    ]);
                }
            }
 
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

            // ✅ Fisik rak tetap diupdate seperti sebelumnya — TIDAK DIUBAH.
            if ($type === 'IN') $stock->quantity += $qty; else $stock->quantity -= $qty;
            $stock->save();

            $effectiveDate = $historicalDate;
            $stockIn  = ($type === 'IN') ? $qty : 0;
            $stockOut = ($type === 'OUT') ? $qty : 0;

            // ✅ Basis saldo kartu stok: baris StockMove TERAKHIR secara kronologis
            // SEBELUM tanggal transaksi ini (bukan ProductMinStock.quantity).
            $lastBalance = StockMove::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)
                ->where('created_at', '<=', $effectiveDate)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->value('stock_balance');

            $currentBalance = $lastBalance ?? 0;
            $newBalance = $currentBalance + $stockIn - $stockOut;

            $newId = StockMove::insertGetId([
                'code_transaction' => $transactionCode,
                'warehouse_id' => $warehouseId,
                'product_packaging_id' => $productId,
                'stock_in' => $stockIn,
                'stock_out' => $stockOut,
                'stock_balance' => $newBalance,
                'note' => $note,
                'created_by' => auth()->id() ?? 1,
                'created_at' => $effectiveDate,
                'updated_at' => now(),
            ]);

            // ✅ FIX BACKDATE: recalculate berantai untuk baris-baris sesudahnya,
            // sama persis logika di recordAdministrativeLog().
            $subsequentMoves = StockMove::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)
                ->where(function ($q) use ($effectiveDate, $newId) {
                    $q->where('created_at', '>', $effectiveDate)
                      ->orWhere(function ($q2) use ($effectiveDate, $newId) {
                          $q2->where('created_at', '=', $effectiveDate)
                             ->where('id', '>', $newId);
                      });
                })
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $runningBalance = $newBalance;
            foreach ($subsequentMoves as $move) {
                $runningBalance += ($move->stock_in - $move->stock_out);
                if ((float) $move->stock_balance !== (float) $runningBalance) {
                    StockMove::where('id', $move->id)->update([
                        'stock_balance' => $runningBalance,
                        'updated_at'    => now(),
                    ]);
                }
            }

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