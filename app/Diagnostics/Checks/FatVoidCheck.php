<?php

namespace App\Diagnostics\Checks;

use Illuminate\Support\Facades\DB;
use App\Diagnostics\Check;

class FatVoidCheck extends Check
{
    public function key()
    {
        return 'fat_void';
    }

    public function label()
    {
        return 'Void DO (invoice, stok, log, request)';
    }

    public function run()
    {
        $voided = DB::table('penjualan_do')
            ->whereNull('deleted_at')
            ->where('status', 8)
            ->when($this->hasSince(), function ($q) {
                $q->where('created_at', '>=', $this->since());
            })
            ->select('id', 'do_code', 'void_status')
            ->orderBy('id', 'desc')
            ->limit($this->limit + 1)
            ->get();

        foreach ($voided as $do) {
            $activeInvoice = DB::table('finance_invoicing')
                ->where('do_id', $do->id)
                ->whereNull('deleted_at')
                ->value('code');

            if ($activeInvoice) {
                $this->add(
                    'critical',
                    'DO void masih punya invoice aktif ' . $activeInvoice . '.',
                    $do->do_code
                );
            }

            $originalCode = preg_replace('/-VOID$/', '', (string) $do->do_code);
            $hasMove = DB::table('gudang_move_stock')
                ->where('code_transaction', $originalCode)
                ->whereNull('deleted_at')
                ->exists();

            if ($hasMove) {
                $this->add('warning', 'DO void masih punya baris kartu stok.', $do->do_code);
            }

            $hasActiveLog = DB::table('do_stock_deduction_logs')
                ->where('do_id', $do->id)
                ->where('status', 1)
                ->exists();

            if ($hasActiveLog) {
                $this->add('warning', 'DO void masih punya log booking aktif.', $do->do_code);
            }

            $hasApprovedRequest = DB::table('do_void_requests')
                ->where('do_id', $do->id)
                ->where('status', 2)
                ->exists();

            if (!$hasApprovedRequest) {
                $this->add('warning', 'DO void tanpa request void berstatus approved.', $do->do_code);
            }

            if ((int) $do->void_status !== 2) {
                $this->add('info', 'DO status void tetapi void_status bukan 2.', $do->do_code);
            }
        }

        $flagMismatch = DB::table('penjualan_do')
            ->whereNull('deleted_at')
            ->where('void_status', 2)
            ->where('status', '!=', 8)
            ->when($this->hasSince(), function ($q) {
                $q->where('created_at', '>=', $this->since());
            })
            ->select('do_code', 'status')
            ->limit($this->limit + 1)
            ->get();

        foreach ($flagMismatch as $row) {
            $this->add(
                'warning',
                'void_status approved tetapi status DO masih ' . $row->status . '.',
                $row->do_code
            );
        }
    }
}
