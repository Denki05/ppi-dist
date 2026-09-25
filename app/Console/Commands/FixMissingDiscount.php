<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Helper\CustomHelper;

class FixMissingDiscount extends Command
{
    protected $signature = 'payable:fix-missing-discount
                            {--fix : Terapkan perbaikan (tanpa flag ini hanya laporan dry-run)}
                            {--codes= : Batasi ke kode nota, dipisah koma (cth: 6H077,6H078)}
                            {--customer=Depo Aroma : Filter nama customer/address (LIKE)}';

    protected $description = 'Betulkan invoice & DO yang diskon persennya tersimpan tapi tidak dikurangkan dari grand total (kasus Depo Aroma)';

    public function handle()
    {
        $isFix = (bool) $this->option('fix');
        $codesOpt = $this->option('codes');
        $customerOpt = $this->option('customer');

        $codes = $codesOpt
            ? array_values(array_filter(array_map('trim', explode(',', $codesOpt))))
            : [];

        $query = DB::table('finance_invoicing as fi')
            ->join('penjualan_do as po', 'po.id', '=', 'fi.do_id')
            ->leftJoin('master_customer_other_addresses as addr', 'addr.id', '=', 'po.customer_other_address_id')
            ->where('fi.status', 1) // ACTIVE saja
            ->select('fi.id as invoice_id', 'fi.code', 'fi.do_id', 'fi.grand_total_idr as inv_grand', 'fi.status',
                'po.do_code', 'po.idr_rate', 'addr.name as addr_name');

        if ($codes) {
            $query->whereIn('fi.code', $codes);
        } elseif ($customerOpt) {
            $query->where('addr.name', 'LIKE', '%' . $customerOpt . '%');
        }

        $invoices = $query->orderBy('fi.code')->get();

        if ($invoices->isEmpty()) {
            $this->warn('Tidak ada invoice yang cocok dengan filter.');
            return 0;
        }

        $rows = [];
        $nFix = 0;
        $nOk = 0;
        $nSkip = 0;
        $totalSelisih = 0;

        foreach ($invoices as $inv) {
            $result = $this->evaluate((array) $inv);

            if ($result['action'] === 'OK') {
                $nOk++;
            } elseif ($result['action'] === 'SKIP') {
                $nSkip++;
            } elseif ($result['action'] === 'FIX' && $isFix) {
                $this->applyFix($result);
                $nFix++;
                $totalSelisih += $result['selisih'];
            } elseif ($result['action'] === 'FIX') {
                $totalSelisih += $result['selisih'];
            }

            $rows[] = [
                $result['code'],
                $result['addr'],
                number_format((float) $result['inv_grand'], 2, '.', ','),
                $result['expected'] === null ? '-' : number_format((float) $result['expected'], 2, '.', ','),
                $result['selisih'] === null ? '-' : number_format((float) $result['selisih'], 2, '.', ','),
                $result['action'] . ($result['note'] ? ' (' . $result['note'] . ')' : ''),
            ];
        }

        $this->table(['Nota', 'Customer', 'Tagihan DB', 'Seharusnya', 'Selisih', 'Aksi'], $rows);
        $this->info("OK: $nOk, Perlu FIX: " . count(array_filter($rows, function ($r) { return strpos($r[5], 'FIX') === 0; })) . ", SKIP: $nSkip"
            . ($isFix ? ", Diperbaiki: $nFix" : ' (dry-run, tambah --fix untuk menerapkan)'));
        $this->info('Total selisih tagihan: ' . number_format((float) $totalSelisih, 2, '.', ','));

        return 0;
    }

    protected function evaluate(array $inv)
    {
        $base = [
            'invoice_id' => $inv['invoice_id'],
            'code' => $inv['code'],
            'do_id' => $inv['do_id'],
            'addr' => $inv['addr_name'],
            'inv_grand' => $inv['inv_grand'],
            'expected' => null,
            'selisih' => null,
            'action' => 'SKIP',
            'note' => '',
        ];

        // Jangan sentuh invoice yang sudah ada pembayaran / payable masuk.
        $paid = (float) DB::table('finance_payable_detail')->where('invoice_id', $inv['invoice_id'])->sum('total');
        if ($paid > 0) {
            $base['note'] = 'sudah ada pembayaran ' . number_format($paid, 2, '.', ',');
            return $base;
        }

        $det = DB::table('penjualan_do_details')->where('do_id', $inv['do_id'])->first();
        if (!$det) {
            $base['note'] = 'detail DO tidak ketemu';
            return $base;
        }

        $kurs = (float) $inv['idr_rate'];
        if ($kurs <= 0) {
            $base['note'] = 'kurs DO tidak valid';
            return $base;
        }

        // Subtotal kotor versi sistem = jumlah item (USD nett per baris) x kurs.
        $items = DB::table('penjualan_do_item')->where('do_id', $inv['do_id'])->get();
        if ($items->isEmpty()) {
            $base['note'] = 'item DO kosong';
            return $base;
        }
        $grossItem = 0;
        foreach ($items as $it) {
            $grossItem += ((float) $it->total) * $kurs;
        }

        $purch = (float) $det->purchase_total_idr;
        $d1 = (float) $det->discount_1;
        $d2 = (float) $det->discount_2;
        $d1idr = (float) $det->discount_1_idr;
        $d2idr = (float) $det->discount_2_idr;
        $didr = (float) $det->discount_idr;
        $discSum = $d1idr + $d2idr + $didr;
        $voucher = (float) $det->voucher_idr;
        $cashback = (float) $det->cashback_idr;
        $ppn = (float) $det->ppn_idr;
        $delivery = (float) $det->delivery_cost_idr;

        // Tentukan subtotal kotor yang dipakai:
        // Tipe A: purchase tersimpan = kotor (diskon belum dikurang).
        // Tipe B: purchase tersimpan = nominal diskonnya sendiri (kasus 6H077) -> rekonstruksi dari persen.
        if ($discSum <= 0) {
            $base['action'] = 'OK';
            $base['note'] = 'tidak ada diskon tersimpan';
            $base['expected'] = (float) $inv['inv_grand'];
            $base['selisih'] = 0;
            return $base;
        }

        $tol = max(10000, $grossItem * 0.005);
        if (abs($purch - $grossItem) <= $tol) {
            $gross = $purch; // Tipe A: purchase tersimpan = kotor
            $grossNote = '';
        } elseif (abs($purch - ($grossItem - $discSum)) <= $tol) {
            $gross = $grossItem; // Tipe C: purchase tersimpan sudah nett, tinggal validasi grand
            $grossNote = '';
        } elseif ($purch <= $discSum && $d1 > 0 && $d2 == 0 && $didr == 0) {
            $gross = round($d1idr / ($d1 / 100), 2); // Tipe B
            if (abs($gross - $grossItem) > max(10000, $grossItem * 0.005)) {
                $base['note'] = 'rekonstruksi subtotal tidak cocok dgn item, perlu manual';
                return $base;
            }
            $grossNote = 'gross direkonstruksi dari persen';
        } else {
            $base['note'] = 'pola purchase vs diskon tidak dikenal, perlu manual';
            return $base;
        }

        // Validasi silang: nominal diskon tersimpan harus konsisten dgn persen x gross.
        $expectD1 = round($gross * ($d1 / 100), 2);
        $afterD1 = $gross - $expectD1;
        $expectD2 = round($afterD1 * ($d2 / 100), 2);
        if (abs($expectD1 - $d1idr) > max(1, $gross * 0.001) || abs($expectD2 - $d2idr) > max(1, $gross * 0.001)) {
            $base['note'] = 'nominal diskon tidak konsisten dgn persen, perlu manual';
            return $base;
        }

        $expectedPurchase = $gross - $discSum - $voucher - $cashback + $ppn;
        // Rumus baku: ongkos lain TIDAK masuk grand (sesuai nota cetak).
        $expectedGrand = $expectedPurchase + $delivery;

        if ($expectedGrand <= 0 || $discSum > $gross) {
            $base['note'] = 'hasil hitungan tidak wajar, perlu manual';
            return $base;
        }

        $base['expected'] = $expectedGrand;
        $base['selisih'] = $expectedGrand - (float) $inv['inv_grand'];
        $base['det_id'] = $det->id;
        $base['gross'] = $gross;
        $base['discSum'] = $discSum;
        $base['expectedPurchase'] = $expectedPurchase;
        $base['grossNote'] = $grossNote;

        if (abs($base['selisih']) <= 1 && abs((float) $det->grand_total_idr - $expectedGrand) <= 1) {
            $base['action'] = 'OK';
            $base['note'] = 'sudah benar';
            return $base;
        }

        $base['action'] = 'FIX';
        $notes = [];
        if ($grossNote) {
            $notes[] = $grossNote;
        }
        if ((float) $det->other_cost_idr != 0) {
            $notes[] = 'other_cost ' . number_format((float) $det->other_cost_idr, 2, '.', ',') . ' tidak ikut grand';
        }
        if (abs($purch - $grossItem) > 1) {
            $notes[] = 'gross item selisih ' . number_format($purch - $grossItem, 2, '.', ',');
        }
        $base['note'] = implode('; ', $notes);

        return $base;
    }

    protected function applyFix(array $r)
    {
        DB::transaction(function () use ($r) {
            DB::table('penjualan_do_details')->where('id', $r['det_id'])->update([
                'purchase_total_idr' => $r['gross'],
                'total_discount_idr' => $r['discSum'],
                'grand_total_idr' => $r['expected'],
                'terbilang' => CustomHelper::terbilang($r['expected']),
                'updated_at' => now(),
            ]);
            DB::table('finance_invoicing')->where('id', $r['invoice_id'])->update([
                'grand_total_idr' => $r['expected'],
                'updated_at' => now(),
            ]);
        });
        $this->line('  -> ' . $r['code'] . ' diperbaiki: ' . number_format((float) $r['inv_grand'], 2, '.', ',') . ' => ' . number_format((float) $r['expected'], 2, '.', ','));
    }
}
