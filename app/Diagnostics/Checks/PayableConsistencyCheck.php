<?php

namespace App\Diagnostics\Checks;

use Illuminate\Support\Facades\DB;
use App\Diagnostics\Check;

class PayableConsistencyCheck extends Check
{
    public function key()
    {
        return 'payable_consistency';
    }

    public function label()
    {
        return 'Payment vs invoice (overpay, total detail)';
    }

    public function run()
    {
        $overpay = DB::table('finance_payable_detail as pd')
            ->join('finance_payable as p', 'p.id', '=', 'pd.payable_id')
            ->join('finance_invoicing as f', 'f.id', '=', 'pd.invoice_id')
            ->whereNull('pd.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('f.deleted_at')
            ->whereIn('p.status', [1, 2])
            ->when($this->hasSince(), function ($q) {
                $q->where('f.created_at', '>=', $this->since());
            })
            ->groupBy('pd.invoice_id', 'f.code', 'f.grand_total_idr')
            ->havingRaw('SUM(pd.total) > f.grand_total_idr + 1')
            ->select('pd.invoice_id', 'f.code', 'f.grand_total_idr', DB::raw('SUM(pd.total) as total_bayar'))
            ->limit($this->limit + 1)
            ->get();

        foreach ($overpay as $row) {
            $this->add(
                'critical',
                'Total pembayaran ' . number_format((float) $row->total_bayar, 0, ',', '.')
                . ' melebihi grand invoice ' . number_format((float) $row->grand_total_idr, 0, ',', '.') . '.',
                $row->code
            );
        }

        // Catatan: total header payable sengaja TIDAK dibandingkan dengan
        // SUM(detail). Header bisa berupa pembayaran gabungan tingkat customer
        // sehingga selisihnya sah, dan perbandingan itu selalu berisik.
    }
}
