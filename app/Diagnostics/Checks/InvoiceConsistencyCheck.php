<?php

namespace App\Diagnostics\Checks;

use Illuminate\Support\Facades\DB;
use App\Diagnostics\Check;

class InvoiceConsistencyCheck extends Check
{
    public function key()
    {
        return 'invoice_consistency';
    }

    public function label()
    {
        return 'Invoice vs DO (ganda, hilang, nominal)';
    }

    public function run()
    {
        $duplicates = DB::table('finance_invoicing as f')
            ->join('penjualan_do as d', 'd.id', '=', 'f.do_id')
            ->whereNull('f.deleted_at')
            ->whereNull('d.deleted_at')
            ->when($this->hasSince(), function ($q) {
                $q->where('d.created_at', '>=', $this->since());
            })
            ->groupBy('f.do_id', 'd.do_code')
            ->havingRaw('COUNT(*) > 1')
            ->select('f.do_id', 'd.do_code', DB::raw('COUNT(*) as jumlah'))
            ->limit($this->limit + 1)
            ->get();

        foreach ($duplicates as $row) {
            $this->add(
                'critical',
                'DO punya ' . $row->jumlah . ' invoice aktif. Seharusnya satu.',
                $row->do_code
            );
        }

        $voidWithInvoice = DB::table('finance_invoicing as f')
            ->join('penjualan_do as d', 'd.id', '=', 'f.do_id')
            ->whereNull('f.deleted_at')
            ->whereNull('d.deleted_at')
            ->where('d.status', 8)
            ->when($this->hasSince(), function ($q) {
                $q->where('d.created_at', '>=', $this->since());
            })
            ->select('d.do_code', 'f.code')
            ->limit($this->limit + 1)
            ->get();

        foreach ($voidWithInvoice as $row) {
            $this->add(
                'critical',
                'DO sudah void tetapi invoice ' . $row->code . ' masih aktif.',
                $row->do_code
            );
        }

        $grandMismatch = DB::table('finance_invoicing as f')
            ->join('penjualan_do as d', 'd.id', '=', 'f.do_id')
            ->join('penjualan_do_details as c', 'c.do_id', '=', 'f.do_id')
            ->whereNull('f.deleted_at')
            ->whereNull('d.deleted_at')
            ->whereNull('c.deleted_at')
            // Ongkir & biaya lain memang sering tidak ikut ditagihkan di invoice,
            // jadi selisih yang bisa dijelaskan kedua kolom itu bukan anomali.
            ->whereRaw('ABS(f.grand_total_idr - c.grand_total_idr) > 0.5')
            ->whereRaw('ABS(f.grand_total_idr - (c.grand_total_idr - COALESCE(c.delivery_cost_idr, 0))) > 0.5')
            ->whereRaw('ABS(f.grand_total_idr - (c.grand_total_idr - COALESCE(c.other_cost_idr, 0))) > 0.5')
            ->whereRaw('ABS(f.grand_total_idr - (c.grand_total_idr - COALESCE(c.delivery_cost_idr, 0) - COALESCE(c.other_cost_idr, 0))) > 0.5')
            ->when($this->hasSince(), function ($q) {
                $q->where('d.created_at', '>=', $this->since());
            })
            ->select('d.do_code', 'f.code', 'f.grand_total_idr', 'c.grand_total_idr as cost_grand_total')
            ->limit($this->limit + 1)
            ->get();

        foreach ($grandMismatch as $row) {
            $this->add(
                'warning',
                'Grand invoice ' . number_format((float) $row->grand_total_idr, 0, ',', '.')
                . ' berbeda dari grand DO cost ' . number_format((float) $row->cost_grand_total, 0, ',', '.') . '.',
                $row->do_code . ' / ' . $row->code
            );
        }

        $missing = DB::table('penjualan_do as d')
            ->whereNull('d.deleted_at')
            ->whereIn('d.status', [4, 5, 6])
            ->whereNotNull('d.do_code')
            ->when($this->hasSince(), function ($q) {
                $q->where('d.created_at', '>=', $this->since());
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('finance_invoicing as f')
                    ->whereRaw('f.do_id = d.id')
                    ->whereNull('f.deleted_at');
            })
            ->select('d.do_code', 'd.status')
            ->orderBy('d.id', 'desc')
            ->limit($this->limit + 1)
            ->get();

        foreach ($missing as $row) {
            $this->add(
                'warning',
                'DO status ' . $row->status . ' belum punya invoice aktif.',
                $row->do_code
            );
        }
    }
}
