<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Entities\Penjualan\SalesOrderProforma;

/**
 * Koreksi nominal proforma yang menggelembung karena parser lama membuang
 * titik desimal (nilai "1157625.00" terbaca 115762500).
 *
 * Hanya baris yang selisihnya berpola tepat x10, x100, ... yang diperbaiki;
 * pola lain dianggap data lain dan dilaporkan untuk diperiksa manual.
 * Idempoten: dijalankan dua kali tidak mengubah nilai yang sudah benar.
 *
 *   php artisan proforma:fix-totals --id=232          # dry-run
 *   php artisan proforma:fix-totals --id=232 --apply  # terapkan
 */
class FixProformaTotals extends Command
{
    protected $signature = 'proforma:fix-totals
                            {--id= : Hanya proses satu id proforma}
                            {--include-accepted : Ikut proses proforma yang sudah ACC (status 4)}
                            {--apply : Terapkan koreksi (tanpa flag ini hanya dry-run)}';

    protected $description = 'Koreksi total item/detail cost proforma yang terlanjur dikali 10/100/1000 oleh parsing desimal, plus tipe transaksi yang tertimpa 0';

    private $scales = [10, 100, 1000, 10000, 100000];

    private $typeMap = ['CASH' => 1, 'TEMPO' => 2, 'MARKETPLACE' => 3, 'COD' => 4];

    // Selisih pembulatan sen (0,01) bukan kerusakan data.
    const TOLERANCE = 0.02;

    public function handle()
    {
        $query = SalesOrderProforma::with(['items', 'details_cost', 'salesOrder']);
        if ($this->option('id')) {
            $query->where('id', (int) $this->option('id'));
        }

        $proformas = $query->orderBy('id')->get();
        if ($proformas->isEmpty()) {
            $this->error('Proforma tidak ditemukan.');
            return 1;
        }

        $plans = [];
        $fixable = 0;
        $manual = 0;
        $skipped = 0;
        $ok = 0;

        foreach ($proformas as $pf) {
            $plan = $this->buildPlan($pf);

            switch ($plan['action']) {
                case 'fix':
                    $fixable++;
                    break;
                case 'manual':
                    $manual++;
                    break;
                case 'skip':
                    $skipped++;
                    break;
                default:
                    $ok++;
            }

            if ($plan['action'] === 'manual') {
                $this->warn($pf->code . ' (id ' . $pf->id . '): ' . $plan['note']);
            } elseif ($plan['action'] === 'skip') {
                $this->line($pf->code . ' (id ' . $pf->id . '): dilewati — ' . $plan['note']);
            } elseif ($plan['action'] === 'fix') {
                $plans[] = $plan;
            }
        }

        foreach ($plans as $plan) {
            $this->renderPlan($plan);
        }

        // Tipe transaksi proforma ikut tertimpa 0 oleh halaman Edit lama;
        // pulihkan dari SO-nya bila masih kosong/0.
        $headerFixes = [];
        foreach ($proformas as $pf) {
            // Pakai nilai mentah: accessor so_type_transaction mengembalikan label.
            $rawType = $pf->getAttributes()['so_type_transaction'] ?? null;
            if ($rawType !== null && (int) $rawType !== 0) {
                continue;
            }
            $soType = strtoupper(trim((string) optional($pf->salesOrder)->type_transaction));
            if (!isset($this->typeMap[$soType])) {
                continue;
            }
            $headerFixes[] = ['proforma' => $pf, 'value' => $this->typeMap[$soType], 'label' => $soType];
        }

        if ($headerFixes) {
            $this->table(
                ['Kode', 'Tipe SO', 'Akan diisi'],
                array_map(function ($fix) {
                    return [$fix['proforma']->code, $fix['label'], $fix['value'] . ' (' . $fix['label'] . ')'];
                }, $headerFixes)
            );
        }

        $this->info('Ringkasan: ' . $fixable . ' perlu dikoreksi, ' . $manual . ' perlu manual, '
            . $skipped . ' dilewati, ' . $ok . ' sudah sesuai, '
            . count($headerFixes) . ' tipe transaksi kosong.');

        if (!$plans && !$headerFixes) {
            return 0;
        }

        if (!$this->option('apply')) {
            $this->comment('Dry-run: belum ada yang diubah. Tambahkan --apply untuk menerapkan.');
            return 0;
        }

        if (!$this->confirm('Terapkan koreksi pada ' . count($plans) . ' proforma dan '
            . count($headerFixes) . ' tipe transaksi?', false)) {
            $this->comment('Dibatalkan.');
            return 0;
        }

        DB::transaction(function () use ($plans, $headerFixes) {
            foreach ($plans as $plan) {
                foreach ($plan['items'] as $row) {
                    if ($row['changed']) {
                        $row['item']->total_item = $row['new'];
                        $row['item']->save();
                    }
                }

                $plan['detail']->discount_1_percent = $plan['p1'];
                $plan['detail']->discount_1 = $plan['disc_1'];
                $plan['detail']->discount_2_percent = $plan['p2'];
                $plan['detail']->discount_2 = $plan['disc_2'];
                $plan['detail']->purchase_total_idr = $plan['subtotal'];
                $plan['detail']->grand_total_idr = $plan['grand'];
                $plan['detail']->save();
            }

            foreach ($headerFixes as $fix) {
                $fix['proforma']->so_type_transaction = $fix['value'];
                $fix['proforma']->save();
            }
        });

        $this->info('Koreksi diterapkan pada ' . count($plans) . ' proforma dan '
            . count($headerFixes) . ' tipe transaksi. Cetak ulang proforma terkait.');
        return 0;
    }

    private function buildPlan(SalesOrderProforma $pf)
    {
        $status = (int) $pf->status;
        if ($status === 4 && !$this->option('include-accepted')) {
            return ['proforma' => $pf, 'action' => 'skip', 'note' => 'sudah ACC (pakai --include-accepted bila tetap ingin dikoreksi)'];
        }

        $rate = (float) $pf->so_idr_rate;
        if ($rate <= 1) {
            return ['proforma' => $pf, 'action' => 'manual', 'note' => 'kurs tidak valid (' . $pf->so_idr_rate . ')'];
        }

        $detail = $pf->details_cost;
        if (!$detail) {
            return ['proforma' => $pf, 'action' => 'skip', 'note' => 'belum punya detail cost'];
        }

        $itemRows = [];
        $needsItemFix = false;

        foreach ($pf->items as $item) {
            $qty = (float) $item->qty;
            if ($qty < 0.01) {
                continue;
            }

            $new = round(((float) $item->price - (float) $item->disc_usd) * $qty * $rate, 2);
            $old = (float) $item->total_item;
            $changed = abs($old - $new) > self::TOLERANCE;

            if ($changed && !$this->isScalePattern($old, $new)) {
                return [
                    'proforma' => $pf,
                    'action' => 'manual',
                    'note' => 'item #' . $item->id . ' total ' . $this->fmt($old) . ' vs hitung ' . $this->fmt($new) . ' bukan pola x10^n',
                ];
            }

            $needsItemFix = $needsItemFix || $changed;
            $itemRows[] = ['item' => $item, 'old' => $old, 'new' => $new, 'changed' => $changed];
        }

        if (!$itemRows) {
            return ['proforma' => $pf, 'action' => 'skip', 'note' => 'tidak ada item ber-qty'];
        }

        $subtotal = round(array_sum(array_column($itemRows, 'new')), 2);
        $p1 = (float) $detail->discount_1_percent;
        $p2 = (float) $detail->discount_2_percent;
        // Data lama bisa menyimpan persen kemasan tanpa nominal (5% tapi IDR 0).
        // Jangan menciptakan potongan yang belum pernah ditagihkan.
        if ($p2 > 0 && (float) $detail->discount_2 <= 0) {
            $p2 = 0;
        }
        $disc1 = round($subtotal * $p1 / 100, 2);
        $disc2 = round(($subtotal - $disc1) * $p2 / 100, 2);
        $grand = round(
            $subtotal - $disc1 - $disc2
            - (float) $detail->discount_idr
            - (float) $detail->voucher_idr
            + (float) $detail->delivery_cost_idr,
            2
        );

        $fields = [
            'purchase_total_idr' => [(float) $detail->purchase_total_idr, $subtotal],
            'discount_1' => [(float) $detail->discount_1, $disc1],
            'discount_2' => [(float) $detail->discount_2, $disc2],
            'grand_total_idr' => [(float) $detail->grand_total_idr, $grand],
        ];

        $needsDetailFix = false;
        foreach ($fields as $name => $pair) {
            if (abs($pair[0] - $pair[1]) <= self::TOLERANCE) {
                continue;
            }
            if (!$this->isScalePattern($pair[0], $pair[1])) {
                return [
                    'proforma' => $pf,
                    'action' => 'manual',
                    'note' => $name . ' ' . $this->fmt($pair[0]) . ' vs hitung ' . $this->fmt($pair[1]) . ' bukan pola x10^n (periksa Disc IDR/Voucher/Ongkir)',
                ];
            }
            $needsDetailFix = true;
        }

        if (!$needsItemFix && !$needsDetailFix) {
            return ['proforma' => $pf, 'action' => 'ok', 'note' => 'sudah sesuai'];
        }

        return [
            'proforma' => $pf,
            'action' => 'fix',
            'note' => '',
            'items' => $itemRows,
            'detail' => $detail,
            'subtotal' => $subtotal,
            'p1' => $p1,
            'p2' => $p2,
            'disc_1' => $disc1,
            'disc_2' => $disc2,
            'grand' => $grand,
            'fields' => $fields,
        ];
    }

    private function isScalePattern($old, $new)
    {
        if (abs($new) < 0.005) {
            return abs($old) < 0.005;
        }
        if (abs($old) < 0.005) {
            // Belum pernah dihitung (mis. proforma dari AO), aman diisi.
            return true;
        }

        foreach ($this->scales as $scale) {
            if (abs($old - $new * $scale) <= max(0.01, abs($new * $scale) * 1e-6)) {
                return true;
            }
        }

        return false;
    }

    private function renderPlan(array $plan)
    {
        $pf = $plan['proforma'];
        $this->info('');
        $this->info($pf->code . ' (id ' . $pf->id . ', status ' . $pf->status . ', kurs ' . $this->fmt((float) $pf->so_idr_rate) . ')');

        $this->table(
            ['Item', 'Total tersimpan', 'Total benar'],
            array_map(function ($row) {
                return [
                    $row['item']->product_packaging_id . ' (qty ' . $row['item']->qty . ')',
                    $this->fmt($row['old']),
                    $this->fmt($row['new']) . ($row['changed'] ? ' *' : ''),
                ];
            }, $plan['items'])
        );

        $rows = [];
        foreach ($plan['fields'] as $name => $pair) {
            if (abs($pair[0] - $pair[1]) <= self::TOLERANCE) {
                continue;
            }
            $rows[] = [$name, $this->fmt($pair[0]), $this->fmt($pair[1])];
        }

        $this->table(['Field', 'Tersimpan', 'Hitungan baru'], $rows);
    }

    private function fmt($value)
    {
        return number_format((float) $value, 2, ',', '.');
    }
}
