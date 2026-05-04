<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Entities\Master\ProductMinStock;
use App\Entities\Gudang\StockMove;

class StockService
{
    /**
     * 1. FUNGSI POTONG FISIK RAK (DIPANGGIL SAAT DO PACKED / MUTASI CHECKER)
     * Hanya memotong stok fisik tanpa mencatat kartu stok.
     */
    public function deductPhysicalStock($warehouseId, $productId, $qty)
    {
        return DB::transaction(function () use ($warehouseId, $productId, $qty) {
            $stock = ProductMinStock::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)
                ->lockForUpdate()
                ->first();

            // Jika barang tidak ditemukan di master, buatkan nol dulu agar tidak error
            if (!$stock) {
                $stock = ProductMinStock::create([
                    'warehouse_id' => $warehouseId,
                    'product_packaging_id' => $productId,
                    'quantity' => 0,
                    'reserved_quantity' => 0
                ]);
            }

            // Kurangi fisik
            $stock->quantity -= $qty;
            
            // Kurangi status 'booking' (reserved) jika ada
            if ($stock->reserved_quantity >= $qty) {
                $stock->reserved_quantity -= $qty;
            }

            $stock->save();
            return true;
        });
    }

    /**
     * 2. FUNGSI PENCATATAN ADMINISTRASI (DIPANGGIL SAAT UPDATE RESI / MUTASI SETTLE)
     * Hanya mencatat histori pergerakan tanpa memotong fisik lagi.
     */
    public function recordAdministrativeLog($warehouseId, $productId, $qty, $transactionCode, $note)
    {
        return DB::transaction(function () use ($warehouseId, $productId, $qty, $transactionCode, $note) {
            
            // Ambil master stok HANYA untuk nyontek saldo akhir
            $stock = ProductMinStock::where('warehouse_id', $warehouseId)
                ->where('product_packaging_id', $productId)
                ->lockForUpdate()
                ->first();

            // Insert ke StockMove
            StockMove::create([
                'code_transaction'     => $transactionCode,
                'warehouse_id'         => $warehouseId,
                'product_packaging_id' => $productId,
                'stock_in'             => 0,
                'stock_out'            => $qty,
                'stock_balance'        => $stock ? $stock->quantity : 0, // Saldo nyontek dari master
                'note'                 => $note,
                'created_by'           => auth()->id() ?? 1,
            ]);

            return true;
        });
    }

    /**
     * 3. FUNGSI MEMUTAR ULANG WAKTU (LEDGER REPLAY - UNTUK REBUILD DATA)
     * Digunakan untuk sinkronisasi data dari Januari - Maret.
     */
    public function replayHistoricalLog($warehouseId, $productId, $qty, $type, $transactionCode, $historicalDate, $note)
    {
        return DB::transaction(function () use ($warehouseId, $productId, $qty, $type, $transactionCode, $historicalDate, $note) {
            
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

            // Sesuaikan fisik berdasarkan IN / OUT
            if ($type === 'IN') {
                $stock->quantity += $qty;
            } else {
                $stock->quantity -= $qty;
            }
            $stock->save();

            // Insert log dengan tanggal kejadian masa lalu
            StockMove::insert([
                'code_transaction'     => $transactionCode,
                'warehouse_id'         => $warehouseId,
                'product_packaging_id' => $productId,
                'stock_in'             => ($type === 'IN') ? $qty : 0,
                'stock_out'            => ($type === 'OUT') ? $qty : 0,
                'stock_balance'        => $stock->quantity,
                'note'                 => $note,
                'created_by'           => auth()->id() ?? 1,
                'created_at'           => $historicalDate, // Waktu masa lalu
                'updated_at'           => now(),
            ]);

            return true;
        });
    }
}