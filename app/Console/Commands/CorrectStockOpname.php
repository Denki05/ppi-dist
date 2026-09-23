<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\StockService;

class CorrectStockOpname extends Command
{
    protected $signature = 'stock:correct
                            {--product= : product_packaging_id (wajib)}
                            {--warehouse=2 : warehouse_id}
                            {--opname= : hasil hitung fisik (wajib)}
                            {--note= : catatan audit}';

    protected $description = 'Koreksi satu sisi (fisik ATAU KS) berdasarkan hasil opname. Sisi yang sama dengan opname dianggap benar.';

    public function handle()
    {
        $pid = $this->option('product');
        $warehouse = $this->option('warehouse');
        $opname = $this->option('opname');
        $note = $this->option('note') ?: 'Koreksi opname';

        if (!$pid || $opname === null || !is_numeric($opname)) {
            $this->error('--product dan --opname (angka) wajib diisi.');
            return 1;
        }
        $opname = (float) $opname;

        $stock = DB::table('master_product_min_stocks')
            ->where('warehouse_id', $warehouse)
            ->where('product_packaging_id', $pid)
            ->first();

        if (!$stock) {
            $this->error("Baris min_stock tidak ditemukan: {$pid} @ gudang {$warehouse}.");
            return 1;
        }

        $ks = DB::table('gudang_move_stock')
            ->where('warehouse_id', $warehouse)
            ->where('product_packaging_id', $pid)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $quantity = (float) $stock->quantity;
        $reserved = (float) $stock->reserved_quantity;
        $ksBalance = $ks ? (float) $ks->stock_balance : null;

        $this->info("Fisik={$quantity}, Reserved={$reserved}, KS=" . ($ksBalance === null ? '-' : $ksBalance) . ", Opname={$opname}");

        if (abs($quantity - $opname) < 0.01 && ($ksBalance === null || abs($ksBalance - $opname) < 0.01)) {
            $this->info('Sudah selaras, tidak ada koreksi.');
            return 0;
        }

        // Kasus 1: opname == KS -> fisik yang salah. Koreksi quantity saja,
        // reserved TIDAK disentuh (milik order aktif).
        if ($ksBalance !== null && abs($ksBalance - $opname) < 0.01) {
            DB::table('master_product_min_stocks')
                ->where('warehouse_id', $warehouse)
                ->where('product_packaging_id', $pid)
                ->update(['quantity' => $opname, 'updated_at' => now()]);
            $this->info("OK: fisik {$quantity} -> {$opname} (reserved tetap {$reserved}). Catatan: {$note}");
            return 0;
        }

        // Kasus 2: opname == fisik -> KS yang salah. Tulis jurnal KS satu sisi
        // (tidak menyentuh quantity/reserved).
        if (abs($quantity - $opname) < 0.01 && $ksBalance !== null) {
            $diff = round($ksBalance - $opname, 2); // >0: KS berlebih -> OUT
            $code = 'KOREKSI-' . date('ymd-His') . '-' . substr(str_replace(['/', '-'], '', $pid), 0, 8);

            if ($diff > 0) {
                (new StockService())->recordAdministrativeLog(
                    $warehouse, $pid, $diff, $code, $note . " (KS {$ksBalance} -> {$opname})"
                );
            } else {
                $lastId = DB::table('gudang_move_stock')
                    ->where('warehouse_id', $warehouse)
                    ->where('product_packaging_id', $pid)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->value('id');
                DB::table('gudang_move_stock')->insert([
                    'code_transaction' => $code,
                    'warehouse_id' => $warehouse,
                    'product_packaging_id' => $pid,
                    'stock_in' => abs($diff),
                    'stock_out' => 0,
                    'stock_balance' => $opname,
                    'note' => $note . " (KS {$ksBalance} -> {$opname})",
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->info("OK: jurnal KS (" . ($diff > 0 ? "OUT {$diff}" : 'IN ' . abs($diff)) . ") kode {$code}. Fisik tetap {$quantity}.");
            return 0;
        }

        // Kasus 3: opname beda dari keduanya -> tolak, minta verifikasi ulang.
        $this->error("Opname ({$opname}) beda dari fisik ({$quantity}) MAUPUN KS ({$ksBalance}). Ada mutasi berjalan — hitung ulang / cek DO open dulu.");
        return 1;
    }
}
