<?php

namespace App\Services\Finance;

use App\Helper\CustomHelper;
use Illuminate\Support\Facades\DB;

class InvoiceDiscountAuditService
{
    /**
     * Scan invoice aktif dan hitung tagihan yang seharusnya (nett setelah diskon).
     *
     * @param  array       $codes       Batasi ke kode nota tertentu
     * @param  int|null    $year        Batasi tahun invoice
     * @param  string|null $customer    Filter nama customer/address (LIKE)
     * @param  bool        $includePaid Ikutkan nota yang sudah dibayar bila nominalnya pas
     * @return array
     */
    public function scan(array $codes = [], ?int $year = null, ?string $customer = null, bool $includePaid = true): array
    {
        $query = DB::table('finance_invoicing as fi')
            ->join('penjualan_do as po', 'po.id', '=', 'fi.do_id')
            ->leftJoin('master_customer_other_addresses as addr', 'addr.id', '=', 'po.customer_other_address_id')
            ->where('fi.status', 1)
            ->whereNull('fi.deleted_at')
            ->select(
                'fi.id as invoice_id',
                'fi.code',
                'fi.do_id',
                'fi.grand_total_idr as inv_grand',
                'fi.status',
                'po.do_code',
                'po.idr_rate',
                'addr.name as addr_name'
            );

        if ($codes) {
            $query->whereIn('fi.code', $codes);
        } elseif ($customer) {
            $query->where('addr.name', 'LIKE', '%' . $customer . '%');
        }

        if ($year) {
            $query->whereYear('fi.created_at', $year);
        }

        $rows = [];

        foreach ($query->orderBy('fi.code')->get() as $invoice) {
            $rows[] = $this->evaluate((array) $invoice, $includePaid);
        }

        return $rows;
    }

    public function evaluate(array $inv, bool $includePaid = true): array
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
            'paid' => 0,
        ];

        $paid = (float) DB::table('finance_payable_detail')->where('invoice_id', $inv['invoice_id'])->sum('total');
        $base['paid'] = $paid;

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

        // Tipe A: purchase tersimpan = kotor (diskon belum dikurang).
        // Tipe B: purchase tersimpan = nominal diskonnya sendiri -> rekonstruksi dari persen.
        // Tipe C: purchase tersimpan sudah nett.
        if ($discSum <= 0) {
            $base['action'] = 'OK';
            $base['note'] = 'tidak ada diskon tersimpan';
            $base['expected'] = (float) $inv['inv_grand'];
            $base['selisih'] = 0;
            return $base;
        }

        $tol = max(10000, $grossItem * 0.005);

        if (abs($purch - $grossItem) <= $tol) {
            $gross = $purch;
            $grossNote = '';
            $grossType = 'A';
        } elseif (abs($purch - ($grossItem - $discSum)) <= $tol) {
            $gross = $grossItem;
            $grossNote = '';
            $grossType = 'C';
        } elseif ($purch <= $discSum && $d1 > 0 && $d2 == 0 && $didr == 0) {
            $gross = round($d1idr / ($d1 / 100), 2);
            if (abs($gross - $grossItem) > max(10000, $grossItem * 0.005)) {
                $base['note'] = 'rekonstruksi subtotal tidak cocok dgn item, perlu manual';
                return $base;
            }
            $grossNote = 'gross direkonstruksi dari persen';
            $grossType = 'B';
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
        $base['purchase_stored'] = $purch;
        $base['gross'] = $gross;
        $base['discSum'] = $discSum;
        $base['expectedPurchase'] = $expectedPurchase;
        $base['grossNote'] = $grossNote;
        $base['grossType'] = $grossType;

        // Yang menentukan OK/FIX adalah INVOICE (basis payable). Selisih kecil
        // (<= Rp 100) = noise pembulatan. Bedanya grand di detail saja (misal
        // ketambahan other_cost saat update resi) hanya jadi catatan info.
        if (abs($base['selisih']) <= 100) {
            $base['action'] = 'OK';
            $base['note'] = abs($base['selisih']) > 1 ? 'sudah benar (noise rounding)' : 'sudah benar';
            if (abs((float) $det->grand_total_idr - $expectedGrand) > 100) {
                $base['note'] .= '; info: grand di detail beda ' . number_format((float) $det->grand_total_idr - $expectedGrand, 2, '.', ',');
            }
            return $base;
        }

        // Sudah ada pembayaran: hanya boleh ikut bila total bayar PAS sama dengan
        // seharusnya (bayar nett benar, grand rusak belakangan -> sisa hantu jadi nol).
        if ($paid > 0) {
            $paidLabel = number_format($paid, 2, '.', ',');
            if (!$includePaid || abs($paid - $expectedGrand) > 1) {
                $base['action'] = 'SKIP';
                $base['note'] = 'sudah ada pembayaran ' . $paidLabel . ', perlu keputusan manual'
                    . (!$includePaid ? ' (atau ulangi dgn --include-paid)' : '');
                return $base;
            }
            $base['action'] = 'FIX';
            $notes = ['sudah dibayar pas nett ' . $paidLabel . '; sisa hantu jadi nol'];
            if ($grossNote) {
                $notes[] = $grossNote;
            }
            if ((float) $det->other_cost_idr != 0) {
                $notes[] = 'other_cost ' . number_format((float) $det->other_cost_idr, 2, '.', ',') . ' tidak ikut grand';
            }
            $base['note'] = implode('; ', $notes);
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

    public function applyFix(array $row): void
    {
        DB::transaction(function () use ($row) {
            // Tipe C: purchase tersimpan sudah nett -> jangan ubah purchase,
            // cukup selaraskan grand (+ total_discount bila masih nol).
            $purchase = ($row['grossType'] ?? '') === 'C' ? $row['purchase_stored'] : $row['gross'];

            DB::table('penjualan_do_details')->where('id', $row['det_id'])->update([
                'purchase_total_idr' => $purchase,
                'total_discount_idr' => $row['discSum'],
                'grand_total_idr' => $row['expected'],
                'terbilang' => CustomHelper::terbilang($row['expected']),
                'updated_at' => now(),
            ]);

            DB::table('finance_invoicing')->where('id', $row['invoice_id'])->update([
                'grand_total_idr' => $row['expected'],
                'updated_at' => now(),
            ]);
        });
    }
}
