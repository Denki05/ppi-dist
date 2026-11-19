<?php

namespace App\Diagnostics\Checks;

use Illuminate\Support\Facades\DB;
use App\Diagnostics\Check;

class StockLedgerCheck extends Check
{
    public function key()
    {
        return 'stock_ledger';
    }

    public function label()
    {
        return 'Stok fisik vs kartu stok vs booking';
    }

    public function run()
    {
        $stocks = DB::table('master_product_min_stocks')
            ->orderBy('warehouse_id')
            ->orderBy('product_packaging_id')
            ->get();

        foreach ($stocks as $stock) {
            $lastMove = DB::table('gudang_move_stock')
                ->where('warehouse_id', $stock->warehouse_id)
                ->where('product_packaging_id', $stock->product_packaging_id)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $activeQty = (float) DB::table('do_stock_deduction_logs')
                ->where('warehouse_id', $stock->warehouse_id)
                ->where('product_packaging_id', $stock->product_packaging_id)
                ->where('status', 1)
                ->sum('qty');

            $quantity = (float) $stock->quantity;
            $reserved = (float) $stock->reserved_quantity;
            $ref = 'Gudang ' . $stock->warehouse_id . ' / ' . $stock->product_packaging_id;

            if ($lastMove && abs($quantity - (float) $lastMove->stock_balance) > 0.01) {
                $this->add(
                    'warning',
                    'Fisik ' . number_format($quantity, 2, ',', '.')
                    . ' != saldo kartu stok terakhir ' . number_format((float) $lastMove->stock_balance, 2, ',', '.') . '.',
                    $ref
                );
            }

            if (abs($reserved - $activeQty) > 0.01) {
                $this->add(
                    'warning',
                    'Reserved ' . number_format($reserved, 2, ',', '.')
                    . ' != total log booking aktif ' . number_format($activeQty, 2, ',', '.') . '.',
                    $ref
                );
            }

            if ($quantity < 0) {
                $this->add('warning', 'Stok fisik negatif: ' . number_format($quantity, 2, ',', '.') . '.', $ref);
            }
        }
    }
}
