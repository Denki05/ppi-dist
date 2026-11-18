<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Finance\Invoicing;
use App\Entities\Gudang\StockMove;
use App\Helper\CustomHelper;

/**
 * Koreksi nota 6K059: revisi internal yang seharusnya hanya mengubah kurs
 * ikut merusak qty item desimal 0.5 -> 5 (parseCurrency membuang titik).
 * Mengembalikan qty DO ke qty SO, hitung ulang nominal, mengembalikan
 * kelebihan potong stok fisik, dan (jika DO sudah status 6) membetulkan
 * stock_out kartu stok beserta saldo baris sesudahnya.
 *
 *   php artisan nota:fix-6k059            # dry-run (tampil selisih)
 *   php artisan nota:fix-6k059 --apply    # terapkan
 */
class FixNota6k059 extends Command
{
    protected $signature = 'nota:fix-6k059
                            {--apply : Terapkan koreksi (tanpa flag ini hanya dry-run)}';

    protected $description = 'Koreksi qty DO 6K059 (0.5 -> tertulis 5), nominal, invoice, stok fisik, dan kartu stok';

    const DO_CODE = '6K059';

    public function handle()
    {
        $apply = (bool) $this->option('apply');

        $do = PackingOrder::where('do_code', self::DO_CODE)->first();
        if (!$do) {
            $this->error('DO ' . self::DO_CODE . ' tidak ditemukan.');
            return 1;
        }
        $status = (int) $do->status;
        if (!in_array($status, [5, 6], true)) {
            $this->error('DO ' . self::DO_CODE . ' berstatus ' . $status . ', bukan 5/6. Koreksi dibatalkan.');
            return 1;
        }
        $moves = StockMove::where('code_transaction', self::DO_CODE)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
        if ($status === 5 && $moves->isNotEmpty()) {
            $this->error('DO masih status 5 tetapi kartu stok ' . self::DO_CODE . ' sudah ada. Periksa manual.');
            return 1;
        }
        if ($status === 6 && $moves->isEmpty()) {
            $this->error('DO status 6 tetapi kartu stok ' . self::DO_CODE . ' tidak ditemukan. Periksa manual.');
            return 1;
        }
        $activeLogs = DB::table('do_stock_deduction_logs')->where('do_id', $do->id)->where('status', 1)->count();
        if ($activeLogs > 0) {
            $this->error('Masih ada ' . $activeLogs . ' log booking stok aktif untuk DO ini. Periksa manual sebelum koreksi.');
            return 1;
        }

        $cost = PackingOrderDetail::where('do_id', $do->id)->first();
        if (!$cost) {
            $this->error('DO cost ' . self::DO_CODE . ' tidak ditemukan.');
            return 1;
        }

        $invoice = Invoicing::where('do_id', $do->id)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->first();

        $items = PackingOrderItem::where('do_id', $do->id)->orderBy('id')->get();
        if ($items->isEmpty()) {
            $this->error('DO ' . self::DO_CODE . ' tidak punya item.');
            return 1;
        }

        $rate = (float) $do->idr_rate;
        if ($rate <= 0) {
            $this->error('Kurs DO tidak valid (' . $do->idr_rate . ').');
            return 1;
        }

        $rows = [];
        $needsQtyFix = false;

        foreach ($items as $item) {
            $soItem = SalesOrderItem::where('id', $item->so_item_id)->first();
            if (!$soItem) {
                $this->error('SO item #' . $item->so_item_id . ' untuk DO item #' . $item->id . ' tidak ditemukan.');
                return 1;
            }

            $soQty = (float) $soItem->qty;
            $doQty = (float) $item->qty;
            $qtyFix = abs($doQty - $soQty) > 0.00001;

            if ($qtyFix) {
                $isTenfold = $soQty > 0 && $soQty < 1 && abs($doQty - ($soQty * 10)) < 0.00001;
                if (!$isTenfold) {
                    $this->error('Pola qty DO item #' . $item->id . ' (DO ' . $doQty . ' vs SO ' . $soQty . ') tidak dikenali. Dibatalkan.');
                    return 1;
                }
                $needsQtyFix = true;
            }

            $qty = $qtyFix ? $soQty : $doQty;
            $discPerUnit = (float) $item->usd_disc + (((float) $item->price - (float) $item->usd_disc) * ((float) $item->percent_disc / 100));
            $totalDisc = $discPerUnit * $qty;
            $total = $qty * (float) $item->price - $totalDisc;
            $totalOld = $doQty * (float) $item->price - ($discPerUnit * $doQty);

            $rows[] = [
                'item_id' => $item->id,
                'pack_id' => $item->product_packaging_id,
                'so_qty' => $soQty,
                'do_qty' => $doQty,
                'qty' => $qty,
                'price' => (float) $item->price,
                'usd_disc' => (float) $item->usd_disc,
                'percent_disc' => (float) $item->percent_disc,
                'total_disc' => $totalDisc,
                'total' => $total,
                'total_old' => $totalOld,
                'qty_fix' => $qtyFix,
            ];
        }

        // Stok fisik memakai kunci tanpa suffix varian (sama dengan deductPhysicalStock).
        $stockPlan = [];
        foreach ($rows as $row) {
            if (!$row['qty_fix']) {
                continue;
            }
            $base = preg_replace('/_\d+$/', '', (string) $row['pack_id']);
            if (!isset($stockPlan[$base])) {
                $stockPlan[$base] = ['pack_id' => $base, 'restore' => 0];
            }
            $stockPlan[$base]['restore'] += $row['do_qty'] - $row['qty'];
        }
        $stockPlan = array_values($stockPlan);

        $usdSubtotal = 0;
        foreach ($rows as $row) {
            $usdSubtotal += $row['total'];
        }
        $subtotalIdr = round($usdSubtotal * $rate);
        $discAgen = round($subtotalIdr * ((float) $cost->discount_1 / 100));
        $discKemasan = round(($subtotalIdr - $discAgen) * ((float) $cost->discount_2 / 100));
        $grand = round(
            $subtotalIdr - $discAgen - $discKemasan - (float) $cost->discount_idr
            - (float) $cost->voucher_idr + (float) $cost->delivery_cost_idr + (float) $cost->other_cost_idr
        );

        // Kartu stok memakai kunci produk tanpa suffix varian (sama dengan grouping sent()).
        $groupQty = [];
        foreach ($rows as $row) {
            $key = preg_replace('/_\d+$/', '', (string) $row['pack_id']);
            if (!isset($groupQty[$key])) {
                $groupQty[$key] = ['do' => 0, 'fix' => 0];
            }
            $groupQty[$key]['do'] += $row['do_qty'];
            $groupQty[$key]['fix'] += $row['qty'];
        }

        $cardPlan = [];
        $needsCardFix = false;
        if ($status === 6) {
            $seen = [];
            foreach ($moves as $move) {
                $key = (string) $move->product_packaging_id;
                if (!isset($groupQty[$key])) {
                    $this->error('Kartu stok ' . self::DO_CODE . ' memuat produk ' . $key . ' yang tidak ada di item DO. Periksa manual.');
                    return 1;
                }
                if (isset($seen[$key])) {
                    $this->error('Kartu stok ' . self::DO_CODE . ' punya lebih dari satu baris untuk produk ' . $key . '. Periksa manual.');
                    return 1;
                }
                $seen[$key] = true;

                $oldOut = (float) $move->stock_out;
                $newOut = round($groupQty[$key]['fix'], 2);
                if (abs($oldOut - $newOut) < 0.00001) {
                    continue;
                }
                if (abs($oldOut - round($groupQty[$key]['do'], 2)) > 0.00001) {
                    $this->error('Kartu stok ' . $key . ' stock_out ' . $oldOut . ' tidak sama dengan qty DO saat ini (' . $groupQty[$key]['do'] . '). Pola tidak dikenali.');
                    return 1;
                }

                $delta = $oldOut - $newOut;
                $laterCount = DB::table('gudang_move_stock')
                    ->where('warehouse_id', $move->warehouse_id)
                    ->where('product_packaging_id', $key)
                    ->where(function ($q) use ($move) {
                        $q->where('created_at', '>', $move->created_at)
                          ->orWhere(function ($q2) use ($move) {
                              $q2->where('created_at', '=', $move->created_at)
                                 ->where('id', '>', $move->id);
                          });
                    })
                    ->count();

                $cardPlan[] = [
                    'id' => $move->id,
                    'pack' => $key,
                    'warehouse_id' => $move->warehouse_id,
                    'created_at' => $move->created_at,
                    'old_out' => $oldOut,
                    'new_out' => $newOut,
                    'old_balance' => (float) $move->stock_balance,
                    'new_balance' => (float) $move->stock_balance + $delta,
                    'delta' => $delta,
                    'later_count' => $laterCount,
                ];
                $needsCardFix = true;
            }
        }

        $this->info('DO ' . self::DO_CODE . ' (id ' . $do->id . ', status ' . $do->status . ', kurs ' . number_format($rate, 0, ',', '.') . ')');
        if ($invoice) {
            $this->info('Invoice: ' . $invoice->code . ' (status ' . $invoice->status . ')');
        }

        $packNames = \App\Entities\Master\ProductPack::whereIn('id', $items->pluck('product_packaging_id')->all())
            ->pluck('name', 'id');

        $this->table(
            ['Produk', 'Qty SO', 'Qty DO', 'Qty benar', 'Total USD lama', 'Total USD baru'],
            array_map(function ($row) use ($packNames) {
                return [
                    $row['pack_id'] . ' - ' . ($packNames[$row['pack_id']] ?? '-'),
                    number_format($row['so_qty'], 2, ',', '.'),
                    number_format($row['do_qty'], 2, ',', '.') . ($row['qty_fix'] ? ' *' : ''),
                    number_format($row['qty'], 2, ',', '.'),
                    number_format($row['total_old'], 2, ',', '.'),
                    number_format($row['total'], 2, ',', '.'),
                ];
            }, $rows)
        );

        $this->table(
            ['Field', 'Tersimpan', 'Hitungan baru'],
            [
                ['Subtotal IDR', number_format((float) $cost->purchase_total_idr, 0, ',', '.'), number_format($subtotalIdr, 0, ',', '.')],
                ['discount_1_idr (' . $cost->discount_1 . '%)', number_format((float) $cost->discount_1_idr, 0, ',', '.'), number_format($discAgen, 0, ',', '.')],
                ['discount_2_idr (' . $cost->discount_2 . '%)', number_format((float) $cost->discount_2_idr, 0, ',', '.'), number_format($discKemasan, 0, ',', '.')],
                ['grand_total_idr', number_format((float) $cost->grand_total_idr, 0, ',', '.'), number_format($grand, 0, ',', '.')],
                ['invoice grand_total_idr', $invoice ? number_format((float) $invoice->grand_total_idr, 0, ',', '.') : '-', number_format($grand, 0, ',', '.')],
                ['terbilang', $cost->terbilang, CustomHelper::terbilang($grand)],
            ]
        );

        if ($stockPlan) {
            $stockRows = [];
            foreach ($stockPlan as $plan) {
                $stock = DB::table('master_product_min_stocks')
                    ->where('warehouse_id', $do->warehouse_id)
                    ->where('product_packaging_id', $plan['pack_id'])
                    ->first();
                $stockRows[] = [
                    $plan['pack_id'],
                    $stock ? number_format((float) $stock->quantity, 2, ',', '.') : 'tidak ada',
                    number_format($plan['restore'], 2, ',', '.'),
                    $stock ? number_format((float) $stock->quantity + $plan['restore'], 2, ',', '.') : '-',
                ];
            }
            $this->table(['Produk', 'Fisik sekarang', 'Dikembalikan', 'Fisik setelah'], $stockRows);
        }

        if ($status === 5) {
            $this->warn('Kartu stok: ' . $moves->count()
                . ' baris. Tidak dibuat oleh command ini; nanti ditulis saat DO status 6 dari item yang sudah benar.');
        } elseif ($cardPlan) {
            $this->table(
                ['Produk', 'stock_out lama', 'stock_out baru', 'saldo lama', 'saldo baru', 'Baris sesudahnya'],
                array_map(function ($plan) {
                    return [
                        $plan['pack'],
                        number_format($plan['old_out'], 2, ',', '.'),
                        number_format($plan['new_out'], 2, ',', '.'),
                        number_format($plan['old_balance'], 2, ',', '.'),
                        number_format($plan['new_balance'], 2, ',', '.'),
                        $plan['later_count'],
                    ];
                }, $cardPlan)
            );
            $this->comment('Baris sesudahnya (produk sama) ikut digeser sebesar selisih saldo.');
        } else {
            $this->info('Kartu stok: ' . $moves->count() . ' baris, qty dan saldo sudah sesuai.');
        }

        if (!$needsQtyFix && !$needsCardFix && abs((float) $cost->grand_total_idr - $grand) < 0.01 && abs($subtotalIdr - (float) $cost->purchase_total_idr) < 0.01) {
            $this->info('Tidak ada qty/nominal yang perlu diperbaiki (sudah sesuai).');
            return 0;
        }

        if (!$apply) {
            $this->comment('Dry-run: belum ada yang diubah. Tambahkan --apply untuk menerapkan.');
            return 0;
        }

        if (!$this->confirm('Terapkan koreksi qty, nominal, invoice, stok fisik, dan kartu stok ' . self::DO_CODE . '?', false)) {
            $this->comment('Dibatalkan.');
            return 0;
        }

        DB::transaction(function () use ($do, $rows, $stockPlan, $cardPlan, $cost, $invoice, $discAgen, $discKemasan, $subtotalIdr, $grand) {
            foreach ($rows as $row) {
                if (!$row['qty_fix']) {
                    continue;
                }
                PackingOrderItem::where('id', $row['item_id'])->update([
                    'qty' => $row['qty'],
                    'total_disc' => $row['total_disc'],
                    'total' => $row['total'],
                    'updated_by' => 1,
                ]);
            }

            foreach ($cardPlan as $plan) {
                DB::table('gudang_move_stock')
                    ->where('id', $plan['id'])
                    ->update([
                        'stock_out' => $plan['new_out'],
                        'stock_balance' => $plan['new_balance'],
                        'updated_at' => now(),
                    ]);

                $later = DB::table('gudang_move_stock')
                    ->where('warehouse_id', $plan['warehouse_id'])
                    ->where('product_packaging_id', $plan['pack'])
                    ->where('id', '!=', $plan['id'])
                    ->where(function ($q) use ($plan) {
                        $q->where('created_at', '>', $plan['created_at'])
                          ->orWhere(function ($q2) use ($plan) {
                              $q2->where('created_at', '=', $plan['created_at'])
                                 ->where('id', '>', $plan['id']);
                          });
                    })
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->get();

                foreach ($later as $row) {
                    DB::table('gudang_move_stock')
                        ->where('id', $row->id)
                        ->update([
                            'stock_balance' => (float) $row->stock_balance + $plan['delta'],
                            'updated_at' => now(),
                        ]);
                }
            }

            foreach ($stockPlan as $plan) {
                $stock = DB::table('master_product_min_stocks')
                    ->where('warehouse_id', $do->warehouse_id)
                    ->where('product_packaging_id', $plan['pack_id'])
                    ->lockForUpdate()
                    ->first();
                if (!$stock) {
                    throw new \RuntimeException('Stok fisik ' . $plan['pack_id'] . ' tidak ditemukan.');
                }
                DB::table('master_product_min_stocks')
                    ->where('id', $stock->id)
                    ->update([
                        'quantity' => (float) $stock->quantity + $plan['restore'],
                        'updated_at' => now(),
                    ]);
            }

            $cost->update([
                'discount_1_idr' => $discAgen,
                'discount_2_idr' => $discKemasan,
                'total_discount_idr' => $discAgen + $discKemasan + (float) $cost->discount_idr,
                'purchase_total_idr' => $subtotalIdr,
                'grand_total_idr' => $grand,
                'terbilang' => CustomHelper::terbilang($grand),
                'updated_by' => 1,
            ]);

            if ($invoice) {
                $invoice->update([
                    'grand_total_idr' => $grand,
                    'updated_by' => 1,
                ]);
            }
        });

        $this->info('Koreksi diterapkan (qty, nominal, fisik, kartu stok). Cetak ulang nota, surat jalan, dan packing plan ' . self::DO_CODE . '.');
        return 0;
    }
}
