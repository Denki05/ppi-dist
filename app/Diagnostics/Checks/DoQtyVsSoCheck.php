<?php

namespace App\Diagnostics\Checks;

use Illuminate\Support\Facades\DB;
use App\Diagnostics\Check;

class DoQtyVsSoCheck extends Check
{
    public function key()
    {
        return 'do_qty_vs_so';
    }

    public function label()
    {
        return 'Qty DO vs qty SO per item';
    }

    public function run()
    {
        $query = DB::table('penjualan_do_item as i')
            ->join('penjualan_do as d', 'd.id', '=', 'i.do_id')
            ->join('penjualan_so_item as s', 's.id', '=', 'i.so_item_id')
            ->whereNull('d.deleted_at')
            ->whereNull('i.deleted_at')
            ->whereIn('d.status', [4, 5, 6])
            ->whereRaw('i.qty > s.qty + 0.00001');

        if ($this->hasSince()) {
            $query->where('d.created_at', '>=', $this->since());
        }

        $rows = $query
            ->select('d.id as do_id', 'd.do_code', 'd.status', 'i.id as item_id', 'i.product_packaging_id', 'i.qty as do_qty', 's.qty as so_qty')
            ->orderBy('d.id')
            ->limit($this->limit + 1)
            ->get();

        $revisedDoIds = DB::table('do_internal_revisions')
            ->whereIn('do_id', $rows->pluck('do_id')->unique()->all())
            ->where('status', 2)
            ->where('items_changed', 1)
            ->pluck('do_id')
            ->all();

        foreach ($rows as $row) {
            $doQty = (float) $row->do_qty;
            $soQty = (float) $row->so_qty;
            $ref = $row->do_code . ' / item #' . $row->item_id . ' / ' . $row->product_packaging_id;
            $tenfold = $soQty > 0 && abs($doQty - ($soQty * 10)) < 0.00001;

            if ($tenfold) {
                $this->add(
                    'critical',
                    'Qty DO ' . $doQty . ' = 10x qty SO ' . $soQty . ' (pola kerusakan parsing desimal).',
                    $ref
                );
            } elseif (in_array($row->do_id, $revisedDoIds, true)) {
                $this->add(
                    'info',
                    'Qty DO ' . $doQty . ' berbeda dari SO ' . $soQty . ', DO punya revisi internal yang mengubah item.',
                    $ref
                );
            } else {
                $this->add(
                    'warning',
                    'Qty DO ' . $doQty . ' berbeda dari qty SO ' . $soQty . '.',
                    $ref
                );
            }
        }
    }
}
