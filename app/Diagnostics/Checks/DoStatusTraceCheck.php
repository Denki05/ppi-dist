<?php

namespace App\Diagnostics\Checks;

use Illuminate\Support\Facades\DB;
use App\Diagnostics\Check;

class DoStatusTraceCheck extends Check
{
    public function key()
    {
        return 'do_status_trace';
    }

    public function label()
    {
        return 'Status DO vs kartu stok & log booking';
    }

    public function run()
    {
        $sentWithoutMove = DB::table('penjualan_do as d')
            ->whereNull('d.deleted_at')
            ->where('d.status', 6)
            ->whereNotNull('d.do_code')
            // DO Proforma (kode P...) memang tidak memotong stok, jadi
            // tidak wajib punya kartu stok.
            ->where('d.do_code', 'not like', 'P%')
            ->when($this->hasSince(), function ($q) {
                $q->where('d.created_at', '>=', $this->since());
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('gudang_move_stock as m')
                    ->whereRaw('m.code_transaction = d.do_code')
                    ->whereNull('m.deleted_at');
            })
            ->select('d.do_code')
            ->orderBy('d.id', 'desc')
            ->limit($this->limit + 1)
            ->get();

        foreach ($sentWithoutMove as $row) {
            $this->add('critical', 'DO status 6 tidak punya baris kartu stok.', $row->do_code);
        }

        $moveTooEarly = DB::table('penjualan_do as d')
            ->whereNull('d.deleted_at')
            ->whereIn('d.status', [1, 2, 3, 4, 5, 7, 8])
            ->whereNotNull('d.do_code')
            ->when($this->hasSince(), function ($q) {
                $q->where('d.created_at', '>=', $this->since());
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('gudang_move_stock as m')
                    ->whereRaw('m.code_transaction = d.do_code')
                    ->whereNull('m.deleted_at');
            })
            ->select('d.do_code', 'd.status')
            ->orderBy('d.id', 'desc')
            ->limit($this->limit + 1)
            ->get();

        foreach ($moveTooEarly as $row) {
            $this->add(
                'critical',
                'DO status ' . $row->status . ' sudah punya kartu stok padahal belum berstatus 6.',
                $row->do_code
            );
        }

        $activeLogOnVoid = DB::table('penjualan_do as d')
            ->whereNull('d.deleted_at')
            ->whereIn('d.status', [7, 8])
            ->when($this->hasSince(), function ($q) {
                $q->where('d.created_at', '>=', $this->since());
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('do_stock_deduction_logs as l')
                    ->whereRaw('l.do_id = d.id')
                    ->where('l.status', 1);
            })
            ->select('d.do_code', 'd.status')
            ->orderBy('d.id', 'desc')
            ->limit($this->limit + 1)
            ->get();

        foreach ($activeLogOnVoid as $row) {
            $this->add(
                'warning',
                'DO status ' . $row->status . ' masih punya log booking aktif.',
                $row->do_code
            );
        }
    }
}
