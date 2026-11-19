<?php

namespace App\Diagnostics\Checks;

use Illuminate\Support\Facades\DB;
use App\Diagnostics\Check;

class FatNotaTtCheck extends Check
{
    public function key()
    {
        return 'fat_nota_tt';
    }

    public function label()
    {
        return 'Nota TT & refund retur';
    }

    public function run()
    {
        $duplicates = DB::table('finance_retur')
            ->whereNull('deleted_at')
            ->when($this->hasSince(), function ($q) {
                $q->where('created_at', '>=', $this->since());
            })
            ->groupBy('retur_id')
            ->havingRaw('COUNT(*) > 1')
            ->select('retur_id', DB::raw('COUNT(*) as jumlah'))
            ->limit($this->limit + 1)
            ->get();

        foreach ($duplicates as $row) {
            $this->add(
                'critical',
                'Retur #' . $row->retur_id . ' punya ' . $row->jumlah . ' baris finance_retur.',
                'retur #' . $row->retur_id
            );
        }

        $nominalMismatch = DB::table('finance_retur')
            ->whereNull('deleted_at')
            ->when($this->hasSince(), function ($q) {
                $q->where('created_at', '>=', $this->since());
            })
            ->whereRaw('ABS(total_final - (total_nota - total_retur)) > 1')
            ->select('id', 'retur_id', 'total_nota', 'total_retur', 'total_final')
            ->limit($this->limit + 1)
            ->get();

        foreach ($nominalMismatch as $row) {
            $expected = (float) $row->total_nota - (float) $row->total_retur;
            $this->add(
                'warning',
                'Total final ' . number_format((float) $row->total_final, 0, ',', '.')
                . ' != total nota - total retur (' . number_format($expected, 0, ',', '.') . ').',
                'retur #' . $row->retur_id
            );
        }

        $verifiedWithoutRow = DB::table('penjualan_retur as r')
            ->whereNull('r.deleted_at')
            ->where('r.fat_status', 2)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('finance_retur as f')
                    ->whereRaw('f.retur_id = r.id')
                    ->whereNull('f.deleted_at');
            })
            ->select('r.id', 'r.code')
            ->limit($this->limit + 1)
            ->get();

        foreach ($verifiedWithoutRow as $row) {
            $this->add(
                'warning',
                'Retur sudah diverifikasi FAT tetapi belum punya baris finance_retur.',
                $row->code
            );
        }

        $refundWithoutProof = DB::table('penjualan_retur')
            ->whereNull('deleted_at')
            ->where('fat_status', 3)
            ->where(function ($q) {
                $q->whereNull('bukti_refund')->orWhere('bukti_refund', '');
            })
            ->select('id', 'code')
            ->limit($this->limit + 1)
            ->get();

        foreach ($refundWithoutProof as $row) {
            $this->add(
                'warning',
                'Status refund selesai tetapi bukti refund kosong.',
                $row->code
            );
        }

        $refundOnUnpaid = DB::table('penjualan_retur')
            ->whereNull('deleted_at')
            ->where('payment_status', 0)
            ->whereNotNull('bukti_refund')
            ->where('bukti_refund', '!=', '')
            ->select('id', 'code')
            ->limit($this->limit + 1)
            ->get();

        foreach ($refundOnUnpaid as $row) {
            $this->add(
                'info',
                'Ada bukti refund pada retur yang belum lunas.',
                $row->code
            );
        }
    }
}
