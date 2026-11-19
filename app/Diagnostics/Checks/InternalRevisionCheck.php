<?php

namespace App\Diagnostics\Checks;

use Illuminate\Support\Facades\DB;
use App\Diagnostics\Check;

class InternalRevisionCheck extends Check
{
    public function key()
    {
        return 'internal_revision';
    }

    public function label()
    {
        return 'Revisi internal vs status DO';
    }

    public function run()
    {
        $orphanPending = DB::table('do_internal_revisions as r')
            ->join('penjualan_do as d', 'd.id', '=', 'r.do_id')
            ->where('r.status', 1)
            ->where(function ($q) {
                $q->whereNull('d.internal_revision_status')
                    ->orWhere('d.internal_revision_status', '!=', 1)
                    ->orWhereNotNull('d.deleted_at');
            })
            ->select('r.id as revision_id', 'd.do_code', 'd.internal_revision_status')
            ->limit($this->limit + 1)
            ->get();

        foreach ($orphanPending as $row) {
            $this->add(
                'critical',
                'Revisi #' . $row->revision_id . ' masih pending tetapi flag DO tidak sinkron'
                . ' (flag: ' . ($row->internal_revision_status === null ? 'null' : $row->internal_revision_status) . ').',
                $row->do_code
            );
        }

        $flagWithoutPending = DB::table('penjualan_do as d')
            ->whereNull('d.deleted_at')
            ->where('d.internal_revision_status', 1)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('do_internal_revisions as r')
                    ->whereRaw('r.do_id = d.id')
                    ->where('r.status', 1);
            })
            ->select('d.do_code')
            ->limit($this->limit + 1)
            ->get();

        foreach ($flagWithoutPending as $row) {
            $this->add(
                'warning',
                'DO ditandai sedang revisi, tetapi tidak ada revisi pending.',
                $row->do_code
            );
        }

        $statusDrift = DB::table('do_internal_revisions as r')
            ->join('penjualan_do as d', 'd.id', '=', 'r.do_id')
            ->where('r.status', 1)
            ->whereRaw('d.status != r.origin_status')
            ->select('r.id as revision_id', 'd.do_code', 'r.origin_status', 'd.status')
            ->limit($this->limit + 1)
            ->get();

        foreach ($statusDrift as $row) {
            $this->add(
                'warning',
                'Revisi #' . $row->revision_id . ' diajukan saat DO status ' . $row->origin_status
                . ', sekarang status DO ' . $row->status . '.',
                $row->do_code
            );
        }
    }
}
