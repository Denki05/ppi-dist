<?php

namespace App\Console\Commands;

use App\Entities\Finance\Invoicing;
use App\Entities\Master\ProductPack;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\SalesOrderItem;
use App\Helper\CustomHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixNota6k057 extends Command
{
    protected $signature = 'nota:fix-6k057 {--apply : Terapkan koreksi (tanpa flag hanya pratinjau)}';

    protected $description = 'Koreksi sekali pakai nota 6K057: Fairytale 0.1 kg menjadi 0.5 kg beserta harga, total, dan stok';

    const DO_CODE = '6K057';
    const SO_ITEM_ID = 23460;
    const DO_ITEM_ID = 33747;
    const WRONG_PACK = '15207-3';
    const RIGHT_PACK = '15207-4';

    public function handle()
    {
        $do = PackingOrder::where('do_code', self::DO_CODE)->first();
        if (!$do) {
            $this->error('DO ' . self::DO_CODE . ' tidak ditemukan.');
            return 1;
        }

        $soItem = SalesOrderItem::find(self::SO_ITEM_ID);
        $doItem = PackingOrderItem::find(self::DO_ITEM_ID);
        if (!$soItem || (int) $soItem->so_id !== (int) $do->so_id) {
            $this->error('SO item ' . self::SO_ITEM_ID . ' tidak ditemukan atau bukan milik SO DO ini.');
            return 1;
        }
        if (!$doItem || (int) $doItem->do_id !== (int) $do->id) {
            $this->error('DO item ' . self::DO_ITEM_ID . ' tidak ditemukan atau bukan milik DO ' . self::DO_CODE . '.');
            return 1;
        }

        $rightPack = ProductPack::where('id', self::RIGHT_PACK)->whereNull('deleted_at')->first();
        if (!$rightPack) {
            $this->error('Pack ' . self::RIGHT_PACK . ' tidak ditemukan.');
            return 1;
        }
        $rightPrice = (float) $rightPack->price;

        $alreadyCorrect = ((string) $doItem->product_packaging_id === self::RIGHT_PACK);
        if (!$alreadyCorrect) {
            if ((string) $doItem->product_packaging_id !== self::WRONG_PACK
                || (string) $soItem->product_packaging_id !== self::WRONG_PACK) {
                $this->error('Kondisi item tidak sesuai skenario koreksi (SO: ' . $soItem->product_packaging_id
                    . ', DO: ' . $doItem->product_packaging_id . '). Dibatalkan.');
                return 1;
            }

            $hasStockMove = DB::table('gudang_move_stock')
                ->where('code_transaction', self::DO_CODE)
                ->exists();
            if ($hasStockMove) {
                $this->error('Kartu stok ' . self::DO_CODE . ' sudah terbentuk. Koreksi stok harus manual agar saldo kartu stok tetap konsisten.');
                return 1;
            }
        }

        $detail = PackingOrderDetail::where('do_id', $do->id)->first();
        if (!$detail) {
            $this->error('Detail biaya DO ' . self::DO_CODE . ' tidak ditemukan.');
            return 1;
        }

        $rate = (float) $do->idr_rate;
        if ($rate <= 0) {
            $this->error('Kurs DO tidak valid (' . $do->idr_rate . ').');
            return 1;
        }

        $qty = (float) $doItem->qty;
        $newItemTotal = $qty * $rightPrice - (float) $doItem->total_disc;

        $currentRows = PackingOrderItem::where('do_id', $do->id)->get();
        $currentTotals = $this->computeTotals($rate, $currentRows, $detail);

        $rows = $currentRows->map(function ($row) use ($doItem, $rightPrice, $newItemTotal) {
            if ((int) $row->id === (int) $doItem->id) {
                $row->price = $rightPrice;
                $row->total = $newItemTotal;
            }
            return $row;
        });

        $totals = $this->computeTotals($rate, $rows, $detail);
        $rightPackagingId = (int) DB::table('master_products_packaging')->where('id', self::RIGHT_PACK)->value('packaging_id');
        $stockWrong = $this->stockRow($do->warehouse_id, self::WRONG_PACK);
        $stockRight = $this->stockRow($do->warehouse_id, self::RIGHT_PACK);
        if (!$stockWrong || !$stockRight) {
            $this->error('Baris stok ' . self::WRONG_PACK . ' / ' . self::RIGHT_PACK . ' tidak lengkap. Dibatalkan.');
            return 1;
        }

        $invoice = Invoicing::where('do_id', $do->id)
            ->where('status', Invoicing::STATUS['ACTIVE'])
            ->orderBy('id', 'desc')
            ->first();

        $this->info('DO ' . self::DO_CODE . ' (id ' . $do->id . '), SO item ' . $soItem->id . ', DO item ' . $doItem->id . ', qty ' . $qty);
        $this->table(
            ['Field', 'Sekarang', 'Setelah koreksi'],
            [
                ['product_packaging_id (SO)', $soItem->product_packaging_id, self::RIGHT_PACK],
                ['product_packaging_id (DO)', $doItem->product_packaging_id, self::RIGHT_PACK],
                ['kemasan', optional($doItem->packaging)->pack_name, optional($rightPack->packaging)->pack_name],
                ['price', number_format((float) $doItem->price, 2, ',', '.'), number_format($rightPrice, 2, ',', '.')],
                ['total baris', number_format((float) $doItem->total, 2, ',', '.'), number_format($newItemTotal, 2, ',', '.')],
                ['sub total', number_format($currentTotals['idr_total'], 0, ',', '.'), number_format($totals['idr_total'], 0, ',', '.')],
                ['discount_1_idr', number_format((float) $detail->discount_1_idr, 0, ',', '.'), number_format($totals['discount_1_idr'], 0, ',', '.')],
                ['grand_total_idr', number_format((float) $detail->grand_total_idr, 0, ',', '.'), number_format($totals['grand_total_idr'], 0, ',', '.')],
                ['stok ' . self::WRONG_PACK, number_format((float) $stockWrong->quantity, 2, ',', '.'), number_format((float) $stockWrong->quantity + ($alreadyCorrect ? 0 : $qty), 2, ',', '.')],
                ['stok ' . self::RIGHT_PACK, number_format((float) $stockRight->quantity, 2, ',', '.'), number_format((float) $stockRight->quantity - ($alreadyCorrect ? 0 : $qty), 2, ',', '.')],
                ['invoice', $invoice ? $invoice->code . ' = ' . number_format((float) $invoice->grand_total_idr, 0, ',', '.') : '-', $invoice ? number_format($totals['grand_total_idr'], 0, ',', '.') : '-'],
            ]
        );

        if (!$this->option('apply')) {
            $this->comment('Pratinjau saja. Jalankan ulang dengan --apply untuk menerapkan.');
            return 0;
        }

        DB::transaction(function () use ($soItem, $doItem, $detail, $invoice, $do, $rightPrice, $rightPackagingId, $newItemTotal, $qty, $alreadyCorrect, $totals) {
            $soItem->update([
                'product_packaging_id' => self::RIGHT_PACK,
                'packaging_id' => $rightPackagingId,
                'price' => $rightPrice,
                'updated_by' => 1,
            ]);

            $doItem->update([
                'product_packaging_id' => self::RIGHT_PACK,
                'packaging_id' => $rightPackagingId,
                'price' => $rightPrice,
                'total' => $newItemTotal,
            ]);

            if (!$alreadyCorrect) {
                DB::table('master_product_min_stocks')
                    ->where('warehouse_id', $do->warehouse_id)
                    ->where('product_packaging_id', self::WRONG_PACK)
                    ->increment('quantity', $qty);

                DB::table('master_product_min_stocks')
                    ->where('warehouse_id', $do->warehouse_id)
                    ->where('product_packaging_id', self::RIGHT_PACK)
                    ->decrement('quantity', $qty);

                DB::table('do_stock_deduction_logs')
                    ->where('do_id', $do->id)
                    ->where('product_packaging_id', self::WRONG_PACK)
                    ->update(['product_packaging_id' => self::RIGHT_PACK]);
            }

            $detail->update([
                'discount_1_idr' => $totals['discount_1_idr'],
                'discount_2_idr' => $totals['discount_2_idr'],
                'total_discount_idr' => $totals['total_discount_idr'],
                'ppn_idr' => $totals['ppn_idr'],
                'purchase_total_idr' => $totals['purchase_total_idr'],
                'grand_total_idr' => $totals['grand_total_idr'],
                'terbilang' => CustomHelper::terbilang($totals['grand_total_idr']),
                'updated_by' => 1,
            ]);

            if ($invoice) {
                $invoice->update(['grand_total_idr' => $totals['grand_total_idr']]);
            }
        });

        $this->info('Selesai. Nota ' . self::DO_CODE . ' sudah dikoreksi. Silakan cetak ulang nota.');
        return 0;
    }

    protected function stockRow($warehouseId, $productPackagingId)
    {
        return DB::table('master_product_min_stocks')
            ->where('warehouse_id', $warehouseId)
            ->where('product_packaging_id', $productPackagingId)
            ->first();
    }

    protected function computeTotals($rate, $items, $detail)
    {
        $idrTotal = 0;
        foreach ($items as $row) {
            $idrTotal += ceil((((float) $row->price * $rate) * (float) $row->qty) - ((float) $row->total_disc * $rate));
        }

        $discount1Idr = ceil($idrTotal * ((float) $detail->discount_1 / 100));
        $discount2Idr = ceil(($idrTotal - $discount1Idr) * ((float) $detail->discount_2 / 100));
        $totalDiscIdr = ceil($discount1Idr + $discount2Idr + (float) $detail->discount_idr);
        $ppn = (float) $detail->ppn_percent > 0
            ? ceil(($idrTotal - $totalDiscIdr) * ((float) $detail->ppn_percent / 100))
            : 0;
        $purchase = ceil($idrTotal - $totalDiscIdr - (float) $detail->voucher_idr - (float) $detail->cashback_idr + $ppn);
        $grand = ceil($purchase + (float) $detail->delivery_cost_idr + (float) $detail->other_cost_idr);

        return [
            'idr_total' => $idrTotal,
            'discount_1_idr' => $discount1Idr,
            'discount_2_idr' => $discount2Idr,
            'total_discount_idr' => $totalDiscIdr,
            'ppn_idr' => $ppn,
            'purchase_total_idr' => $purchase,
            'grand_total_idr' => $grand,
        ];
    }
}
