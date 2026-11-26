<?php

namespace App\Console\Commands;

use App\Entities\Finance\Invoicing;
use App\Entities\Gudang\StockMove;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\ProductPack;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\SalesOrderItem;
use App\Helper\CustomHelper;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixNota6k057 extends Command
{
    protected $signature = 'nota:fix-6k057 {--apply : Terapkan koreksi (tanpa flag hanya pratinjau)}';

    protected $description = 'Koreksi nota 6K057: Fairytale 0.1 kg -> 0.5 kg, hitung ulang total nota, dan koreksi stok';

    const DO_CODE = '6K057';
    const WRONG_PACK = '15207-3'; // 0.1 kg (salah)
    const RIGHT_PACK = '15207-4'; // 0.5 kg (seharusnya)

    public function handle()
    {
        $do = PackingOrder::where('do_code', self::DO_CODE)->first();
        if (!$do) {
            $this->error('DO ' . self::DO_CODE . ' tidak ditemukan.');
            return 1;
        }
        if ((int) $do->status !== 6) {
            $this->error('DO ' . self::DO_CODE . ' berstatus ' . $do->status . ', bukan 6 (resi sudah update). Command ini khusus koreksi pasca-resi.');
            return 1;
        }
        if (!empty($do->void_status)) {
            $this->error('DO ' . self::DO_CODE . ' sedang dalam proses void. Dibatalkan.');
            return 1;
        }

        $wrongPack = ProductPack::where('id', self::WRONG_PACK)->whereNull('deleted_at')->first();
        $rightPack = ProductPack::where('id', self::RIGHT_PACK)->whereNull('deleted_at')->first();
        if (!$rightPack || !$wrongPack) {
            $this->error('Master pack ' . self::WRONG_PACK . ' / ' . self::RIGHT_PACK . ' tidak lengkap.');
            return 1;
        }
        if ((string) $wrongPack->product_id !== (string) $rightPack->product_id) {
            $this->error('Pack pengganti bukan produk yang sama (product_id berbeda). Dibatalkan.');
            return 1;
        }
        $rightPrice = (float) $rightPack->price;
        if ($rightPrice <= 0) {
            $this->error('Harga master ' . self::RIGHT_PACK . ' tidak valid (' . $rightPack->price . ').');
            return 1;
        }

        $doItems = PackingOrderItem::where('do_id', $do->id)
            ->where('product_packaging_id', self::WRONG_PACK)
            ->get();
        $alreadyNew = PackingOrderItem::where('do_id', $do->id)
            ->where('product_packaging_id', self::RIGHT_PACK)
            ->where('so_item_id', '>', 0)
            ->exists();

        if ($doItems->isEmpty() && !$alreadyNew) {
            $this->error('Item Fairytale ' . self::WRONG_PACK . ' tidak ditemukan di DO ' . self::DO_CODE . '.');
            return 1;
        }
        if ($doItems->isEmpty() && $alreadyNew) {
            $this->warn('Kemasan item sudah ' . self::RIGHT_PACK . ' (0.5 kg). Tidak ada perubahan pack; total tetap dihitung ulang.');
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

        $currentRows = PackingOrderItem::where('do_id', $do->id)->get();
        $currentTotals = $this->computeTotals($rate, $currentRows, $detail);

        $plannedRows = $currentRows->map(function ($row) use ($doItems, $rightPack, $rightPrice) {
            $changed = $doItems->firstWhere('id', $row->id);
            if (!$changed) {
                return $row;
            }
            $copy = clone $row;
            $qty = (float) $changed->qty;
            $usdDisc = (float) $changed->usd_disc;
            $percentDisc = (float) $changed->percent_disc;
            $totalDisc = ($usdDisc + (($rightPrice - $usdDisc) * ($percentDisc / 100))) * $qty;
            $copy->product_packaging_id = self::RIGHT_PACK;
            $copy->packaging_id = $rightPack->packaging_id;
            $copy->price = $rightPrice;
            $copy->total_disc = $totalDisc;
            $copy->total = ($qty * $rightPrice) - $totalDisc;
            return $copy;
        });
        $newTotals = $this->computeTotals($rate, $plannedRows, $detail);

        $invoice = Invoicing::where('do_id', $do->id)
            ->where('status', Invoicing::STATUS['ACTIVE'])
            ->orderBy('id', 'desc')
            ->first();
        $paid = $invoice
            ? (float) DB::table('finance_payable_detail')->where('invoice_id', $invoice->id)->sum('total')
            : 0.0;
        $newOutstanding = $newTotals['grand_total_idr'] - $paid;
        if ($newOutstanding < -0.5) {
            $this->error('Grand total baru (' . number_format($newTotals['grand_total_idr'], 0, ',', '.')
                . ') lebih kecil dari pembayaran (' . number_format($paid, 0, ',', '.') . '). Dibatalkan.');
            return 1;
        }

        $bookingRows = DB::table('do_stock_deduction_logs')
            ->where('do_id', $do->id)
            ->where('product_packaging_id', self::WRONG_PACK)
            ->get();
        $origMoves = StockMove::where('code_transaction', self::DO_CODE)
            ->where('product_packaging_id', self::WRONG_PACK)
            ->orderBy('created_at')
            ->get();
        $hasStockData = $bookingRows->isNotEmpty() || $origMoves->isNotEmpty();
        $effectiveDate = $origMoves->isNotEmpty()
            ? Carbon::parse($origMoves->last()->created_at)
            : ($do->date_sent ? Carbon::parse($do->date_sent)->endOfDay() : now());
        $stockOld = (float) ProductMinStock::where('warehouse_id', $do->warehouse_id)
            ->where('product_packaging_id', self::WRONG_PACK)->value('quantity');
        $stockNew = (float) ProductMinStock::where('warehouse_id', $do->warehouse_id)
            ->where('product_packaging_id', self::RIGHT_PACK)->value('quantity');
        $qtyFix = (float) $doItems->sum('qty');
        $stockCorrected = StockMove::where('code_transaction', 'KOREKSI-' . self::DO_CODE)
            ->whereIn('product_packaging_id', [self::WRONG_PACK, self::RIGHT_PACK])
            ->exists();
        $needStock = $hasStockData && !$stockCorrected && $qtyFix > 0;

        $currentPack = $doItems->isNotEmpty()
            ? self::WRONG_PACK . ' (' . $wrongPack->packaging_id . ')'
            : ($alreadyNew ? self::RIGHT_PACK . ' (' . $rightPack->packaging_id . ') [sudah benar]' : '-');

        $this->info('DO ' . self::DO_CODE . ' (id ' . $do->id . '), status ' . $do->status . ', kurs ' . $rate);
        $this->table(
            ['Field', 'Sekarang', 'Setelah koreksi'],
            [
                ['product_packaging_id', $currentPack, self::RIGHT_PACK . ' (' . $rightPack->packaging_id . ')'],
                ['harga item', number_format((float) ($doItems->first()->price ?? $rightPrice), 2, ',', '.'), number_format($rightPrice, 2, ',', '.')],
                ['sub total', number_format($currentTotals['idr_total'], 0, ',', '.'), number_format($newTotals['idr_total'], 0, ',', '.')],
                ['discount_1_idr (' . $detail->discount_1 . '%)', number_format((float) $detail->discount_1_idr, 0, ',', '.'), number_format($newTotals['discount_1_idr'], 0, ',', '.')],
                ['purchase_total_idr', number_format((float) $detail->purchase_total_idr, 0, ',', '.'), number_format($newTotals['purchase_total_idr'], 0, ',', '.')],
                ['grand_total_idr (DO)', number_format((float) $detail->grand_total_idr, 0, ',', '.'), number_format($newTotals['grand_total_idr'], 0, ',', '.')],
                ['invoice', $invoice ? $invoice->code . ' = ' . number_format((float) $invoice->grand_total_idr, 0, ',', '.') : '-', $invoice ? number_format($newTotals['grand_total_idr'], 0, ',', '.') : '-'],
                ['pembayaran', number_format($paid, 0, ',', '.'), number_format($paid, 0, ',', '.')],
                ['sisa setelah koreksi', '-', number_format($newOutstanding, 0, ',', '.')],
                ['stok ' . self::WRONG_PACK . ' (0.1)', number_format($stockOld, 2, ',', '.'), $needStock ? number_format($stockOld + $qtyFix, 2, ',', '.') : 'tidak berubah'],
                ['stok ' . self::RIGHT_PACK . ' (0.5)', number_format($stockNew, 2, ',', '.'), $needStock ? number_format($stockNew - $qtyFix, 2, ',', '.') : 'tidak berubah'],
                ['kartu stok ' . self::DO_CODE, $origMoves->count() . ' baris', $needStock ? 'tambah IN 0.1 / OUT 0.5' : ($stockCorrected ? 'sudah pernah dikoreksi' : 'tidak ada data stok, dilewati')],
                ['log booking', $bookingRows->count() . ' baris', $bookingRows->isNotEmpty() ? 'pindah ke ' . self::RIGHT_PACK : 'tidak ada, dilewati'],
            ]
        );

        if (!$this->option('apply')) {
            $this->comment('Pratinjau saja. Jalankan ulang dengan --apply untuk menerapkan.');
            return 0;
        }

        DB::transaction(function () use ($do, $doItems, $detail, $invoice, $rightPack, $rightPrice, $newTotals, $needStock, $effectiveDate, $qtyFix) {
            foreach ($doItems as $item) {
                $qty = (float) $item->qty;
                $usdDisc = (float) $item->usd_disc;
                $percentDisc = (float) $item->percent_disc;
                $totalDisc = ($usdDisc + (($rightPrice - $usdDisc) * ($percentDisc / 100))) * $qty;

                $item->update([
                    'product_packaging_id' => self::RIGHT_PACK,
                    'packaging_id' => $rightPack->packaging_id,
                    'price' => $rightPrice,
                    'total_disc' => $totalDisc,
                    'total' => ($qty * $rightPrice) - $totalDisc,
                ]);

                $soItem = SalesOrderItem::find($item->so_item_id);
                if ($soItem && (string) $soItem->product_packaging_id === self::WRONG_PACK) {
                    $soItem->update([
                        'product_packaging_id' => self::RIGHT_PACK,
                        'packaging_id' => $rightPack->packaging_id,
                        'price' => $rightPrice,
                    ]);
                }
            }

            $detail->update([
                'discount_1_idr' => $newTotals['discount_1_idr'],
                'discount_2_idr' => $newTotals['discount_2_idr'],
                'total_discount_idr' => $newTotals['total_discount_idr'],
                'ppn_idr' => $newTotals['ppn_idr'],
                'purchase_total_idr' => $newTotals['purchase_total_idr'],
                'grand_total_idr' => $newTotals['grand_total_idr'],
                'terbilang' => CustomHelper::terbilang($newTotals['grand_total_idr']),
                'updated_by' => 1,
            ]);

            if ($invoice) {
                $invoice->update(['grand_total_idr' => $newTotals['grand_total_idr']]);
            }

            if ($needStock) {
                $stockService = new StockService();
                $stockService->replayHistoricalLog(
                    $do->warehouse_id,
                    self::WRONG_PACK,
                    $qtyFix,
                    'IN',
                    'KOREKSI-' . self::DO_CODE,
                    $effectiveDate,
                    'Koreksi nota ' . self::DO_CODE . ': batal potong 0.1 kg'
                );
                $stockService->replayHistoricalLog(
                    $do->warehouse_id,
                    self::RIGHT_PACK,
                    $qtyFix,
                    'OUT',
                    'KOREKSI-' . self::DO_CODE,
                    $effectiveDate,
                    'Koreksi nota ' . self::DO_CODE . ': potong 0.5 kg'
                );
            }

            DB::table('do_stock_deduction_logs')
                ->where('do_id', $do->id)
                ->where('product_packaging_id', self::WRONG_PACK)
                ->update([
                    'product_packaging_id' => self::RIGHT_PACK,
                    'note' => 'Koreksi kemasan ke 0.5 kg',
                    'updated_at' => now(),
                ]);
        });

        foreach ([self::DO_CODE . '-FULL.pdf', self::DO_CODE . '.pdf'] as $file) {
            $path = public_path('cr/invoice/export/' . $file);
            if (is_file($path)) {
                $backup = $path . '.bak-' . date('YmdHis');
                @rename($path, $backup);
                $this->line('Cache PDF dipindah: ' . basename($path) . ' -> ' . basename($backup));
            }
        }

        $this->info('Selesai. Nota ' . self::DO_CODE . ' sudah dikoreksi. Silakan cetak ulang nota.');
        return 0;
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
