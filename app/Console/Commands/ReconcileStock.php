<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\StockService;

class ReconcileStock extends Command
{
    protected $signature = 'stock:reconcile
                            {--fix : Terapkan perbaikan (tanpa flag ini hanya laporan dry-run)}
                            {--product= : Batasi ke satu product_packaging_id}
                            {--warehouse= : Batasi ke satu warehouse_id}';

    protected $description = 'Rekonsiliasi fisik (master_product_min_stocks) vs KS (gudang_move_stock) vs booking (do_stock_deduction_logs)';

    public function handle()
    {
        $stockService = new StockService();
        $isFix = (bool) $this->option('fix');
        $filterProduct = $this->option('product');
        $filterWarehouse = $this->option('warehouse');

        $stocks = DB::table('master_product_min_stocks')
            ->when($filterProduct, function ($q) use ($filterProduct) {
                $q->where('product_packaging_id', $filterProduct);
            })
            ->when($filterWarehouse, function ($q) use ($filterWarehouse) {
                $q->where('warehouse_id', $filterWarehouse);
            })
            ->orderBy('warehouse_id')
            ->orderBy('product_packaging_id')
            ->get();

        $rows = [];
        $fixedBooking = 0;
        $fixedPhysical = 0;
        $needManual = 0;

        foreach ($stocks as $stock) {
            $ks = DB::table('gudang_move_stock')
                ->where('warehouse_id', $stock->warehouse_id)
                ->where('product_packaging_id', $stock->product_packaging_id)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $activeLog = (float) DB::table('do_stock_deduction_logs')
                ->where('warehouse_id', $stock->warehouse_id)
                ->where('product_packaging_id', $stock->product_packaging_id)
                ->where('status', 1)
                ->sum('qty');

            $quantity = (float) $stock->quantity;
            $reserved = (float) $stock->reserved_quantity;
            $ksBalance = $ks ? (float) $ks->stock_balance : null;

            $selisihFisik = $ksBalance === null ? null : round($quantity - $ksBalance, 2);
            $selisihBooking = round($reserved - $activeLog, 2);

            $hasFisikIssue = $selisihFisik !== null && abs($selisihFisik) > 0.01;
            $hasBookingIssue = abs($selisihBooking) > 0.01;

            if (!$hasFisikIssue && !$hasBookingIssue) {
                continue;
            }

            $action = '-';

            if ($isFix) {
                // 1) Selaraskan booking dengan log aktif (aman: hanya angka reservasi).
                if ($hasBookingIssue) {
                    DB::table('master_product_min_stocks')
                        ->where('warehouse_id', $stock->warehouse_id)
                        ->where('product_packaging_id', $stock->product_packaging_id)
                        ->update([
                            'reserved_quantity' => $activeLog,
                            'updated_at' => now(),
                        ]);
                    $fixedBooking++;
                    $action = 'reserved:=' . $activeLog;
                    $reserved = $activeLog;
                    $hasBookingIssue = false;
                    $selisihBooking = 0;
                }

                // 2) Tipe A: fisik berlebih tepat sebesar booking hantu yang baru
                // dinolkan + tidak ada log aktif tersisa. Pola ini = DO lompat
                // ke status 6 tanpa delivering (fisik tak terpotong). Satu
                // deductPhysicalStock memperbaiki quantity (reserved sudah 0).
                if ($hasFisikIssue && $selisihFisik > 0 && $activeLog == 0
                    && abs($selisihFisik - (float) $stock->reserved_quantity) < 0.01
                ) {
                    try {
                        $stockService->deductPhysicalStock(
                            $stock->warehouse_id,
                            $stock->product_packaging_id,
                            $selisihFisik
                        );
                        $fixedPhysical++;
                        $action .= ($action === '-' ? '' : ' + ') . 'fisik -' . $selisihFisik;
                        $hasFisikIssue = false;
                    } catch (\Exception $e) {
                        $action .= ' GAGAL deduct: ' . $e->getMessage();
                    }
                }
            }

            if ($hasFisikIssue || $hasBookingIssue) {
                $needManual++;
                if ($isFix) {
                    $action .= ' (PERLU MANUAL)';
                }
            }

            $rows[] = [
                $stock->warehouse_id,
                $stock->product_packaging_id,
                $quantity,
                $reserved,
                $ksBalance === null ? 'tanpa KS' : $ksBalance,
                $selisihFisik === null ? '-' : $selisihFisik,
                $activeLog,
                $selisihBooking,
                $action,
            ];
        }

        $this->table(
            ['Gdg', 'Produk', 'Fisik', 'Reserved', 'KS', 'Selisih Fisik-KS', 'LogAktif', 'Selisih Booking', $isFix ? 'Aksi' : 'Ket'],
            $rows
        );

        $this->info('Total baris bermasalah: ' . count($rows));
        if ($isFix) {
            $this->info("Booking diselaraskan: {$fixedBooking}, fisik dikoreksi (Tipe A): {$fixedPhysical}, perlu manual: {$needManual}");
        } else {
            $this->info("Dry-run selesai (tanpa perubahan). Perlu manual/review: {$needManual}. Jalankan dengan --fix untuk auto-perbaikan aman.");
        }

        return 0;
    }
}
