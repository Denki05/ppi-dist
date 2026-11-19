<?php

namespace App\Diagnostics\Checks;

use Illuminate\Support\Facades\DB;
use App\Diagnostics\Check;

class DoNominalCheck extends Check
{
    public function key()
    {
        return 'do_nominal';
    }

    public function label()
    {
        return 'Nominal item DO (total & diskon)';
    }

    public function run()
    {
        // Dua konvensi penyimpanan yang sah di data historis:
        // A) total = qty x harga - diskon_per_unit x qty (kolom total_disc = diskon per unit)
        // B) total = qty x harga - total_disc (kolom total_disc = total diskon baris)
        // Baris dianggap sehat kalau salah satu cocok; kalau dua-duanya gagal,
        // besar kemungkinan total tersimpan hasil parsing yang salah (kasus 6K036).
        $query = DB::table('penjualan_do_item as i')
            ->join('penjualan_do as d', 'd.id', '=', 'i.do_id')
            ->whereNull('d.deleted_at')
            ->whereNull('i.deleted_at')
            ->whereIn('d.status', [4, 5, 6])
            ->whereRaw('ABS(i.total - ((i.qty * i.price) - ((i.usd_disc + ((i.price - i.usd_disc) * i.percent_disc / 100)) * i.qty))) > 0.01')
            ->whereRaw('ABS(i.total - ((i.qty * i.price) - i.total_disc)) > 0.01');

        if ($this->hasSince()) {
            $query->where('d.created_at', '>=', $this->since());
        }

        $rows = $query
            ->select('d.do_code', 'i.id as item_id', 'i.product_packaging_id', 'i.qty', 'i.price', 'i.total', 'i.total_disc')
            ->orderBy('d.id')
            ->limit($this->limit + 1)
            ->get();

        foreach ($rows as $row) {
            $expected = ((float) $row->qty * (float) $row->price) - (float) $row->total_disc;

            $this->add(
                'critical',
                'Total item tersimpan ' . number_format((float) $row->total, 2, ',', '.')
                . ', seharusnya qty x harga - diskon = ' . number_format($expected, 2, ',', '.') . '.',
                $row->do_code . ' / item #' . $row->item_id . ' / ' . $row->product_packaging_id
            );
        }
    }
}
