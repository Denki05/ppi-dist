<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Finance\Invoicing;
use App\Helper\CustomHelper;

/**
 * Hitung ulang total DO dari item x kurs DO tersimpan (otoritatif, backend).
 * Dipakai untuk memperbaiki nota yang nominal diskonnya 100x lipat
 * (kasus 6I083: kurs "18025.00" terbaca 1802500 saat tutup_so).
 *
 *   php artisan do:recalc-totals 6I083            # dry-run (tampil selisih)
 *   php artisan do:recalc-totals 6I083 --apply    # terapkan ke details + invoice
 */
class RecalcDoTotals extends Command
{
    protected $signature = 'do:recalc-totals
                            {do_code : Kode DO (cth: 6I083)}
                            {--apply : Terapkan perbaikan (tanpa flag ini hanya dry-run)}';

    protected $description = 'Hitung ulang discount/purchase/grand DO dari item x kurs + sinkron invoice (perbaiki nota 100x)';

    public function handle()
    {
        $code = trim((string) $this->argument('do_code'));
        $apply = (bool) $this->option('apply');

        $do = PackingOrder::where('do_code', $code)->first();
        if (!$do) {
            $this->error("DO {$code} tidak ditemukan.");
            return 1;
        }

        $rate = (float) $do->idr_rate;
        if ($rate <= 0) {
            $this->error("Kurs DO {$code} tidak valid ({$do->idr_rate}). Betulkan kurs dulu via Update Kurs.");
            return 1;
        }

        $items = PackingOrderItem::where('do_id', $do->id)->get();
        if ($items->isEmpty()) {
            $this->error("DO {$code} tidak punya item (mungkin status revisi). Tidak ada yang dihitung.");
            return 1;
        }

        $detail = PackingOrderDetail::where('do_id', $do->id)->first();
        if (!$detail) {
            $this->error("Detail DO {$code} tidak ditemukan.");
            return 1;
        }

        // Rumus sama seperti reset_cost_if_change_idr_rate().
        $idrTotal = 0;
        foreach ($items as $row) {
            $idrTotal += ceil(((($row->price * $rate) * $row->qty) - ($row->total_disc * $rate)));
        }

        $discount1Idr = ceil($idrTotal * ((float) $detail->discount_1 / 100));
        $discount2Idr = ceil(($idrTotal - $discount1Idr) * ((float) $detail->discount_2 / 100));
        $totalDiscIdr = ceil($discount1Idr + $discount2Idr + (float) $detail->discount_idr);

        if ((float) $detail->ppn_percent > 0) {
            $ppn = ceil(($idrTotal - $totalDiscIdr) * ((float) $detail->ppn_percent / 100));
        } else {
            $ppn = 0;
        }

        $purchase = ceil($idrTotal - $totalDiscIdr - (float) $detail->voucher_idr - (float) $detail->cashback_idr + $ppn);
        $grand = ceil($purchase + (float) $detail->delivery_cost_idr + (float) $detail->other_cost_idr);

        $this->info("DO {$code} (id {$do->id}, kurs {$rate}, status {$do->status})");
        $this->table(
            ['Field', 'Tersimpan', 'Hitungan ulang'],
            [
                ['Subtotal (item x kurs)', number_format($idrTotal, 0, ',', '.'), ''],
                ['discount_1_idr (' . $detail->discount_1 . '%)', number_format((float) $detail->discount_1_idr, 0, ',', '.'), number_format($discount1Idr, 0, ',', '.')],
                ['discount_2_idr (' . $detail->discount_2 . '%)', number_format((float) $detail->discount_2_idr, 0, ',', '.'), number_format($discount2Idr, 0, ',', '.')],
                ['total_discount_idr', number_format((float) $detail->total_discount_idr, 0, ',', '.'), number_format($totalDiscIdr, 0, ',', '.')],
                ['purchase_total_idr', number_format((float) $detail->purchase_total_idr, 0, ',', '.'), number_format($purchase, 0, ',', '.')],
                ['grand_total_idr', number_format((float) $detail->grand_total_idr, 0, ',', '.'), number_format($grand, 0, ',', '.')],
            ]
        );

        if ($grand <= 0) {
            $this->error('Grand total hitungan tidak valid (minus/nol). Dibatalkan, periksa diskon & item DO.');
            return 1;
        }

        $invoice = Invoicing::where('do_id', $do->id)
            ->where('status', Invoicing::STATUS['ACTIVE'])
            ->orderBy('id', 'desc')
            ->first();
        if ($invoice) {
            $this->info('Invoice aktif: ' . $invoice->code . ' = ' . number_format((float) $invoice->grand_total_idr, 0, ',', '.')
                . ' -> ' . number_format($grand, 0, ',', '.'));
        } else {
            $this->warn('Tidak ada invoice aktif untuk DO ini (hanya details yang diperbaiki).');
        }

        if (!$apply) {
            $this->comment('Dry-run: belum ada yang diubah. Tambahkan --apply untuk menerapkan.');
            return 0;
        }

        if (!$this->confirm('Terapkan hitungan ulang ke details' . ($invoice ? ' + invoice' : '') . '?', false)) {
            $this->comment('Dibatalkan.');
            return 0;
        }

        DB::transaction(function () use ($do, $detail, $invoice, $discount1Idr, $discount2Idr, $totalDiscIdr, $ppn, $purchase, $grand) {
            $detail->update([
                'discount_1_idr' => $discount1Idr,
                'discount_2_idr' => $discount2Idr,
                'total_discount_idr' => $totalDiscIdr,
                'ppn_idr' => $ppn,
                'purchase_total_idr' => $purchase,
                'grand_total_idr' => $grand,
                'terbilang' => CustomHelper::terbilang($grand),
                'updated_by' => 1,
            ]);

            if ($invoice) {
                $invoice->update(['grand_total_idr' => $grand]);
            }
        });

        $this->info('Berhasil diterapkan. Silakan cetak ulang nota ' . $code . '.');
        return 0;
    }
}
