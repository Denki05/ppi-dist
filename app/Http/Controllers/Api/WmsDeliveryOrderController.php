<?php

namespace App\Http\Controllers\Api;

use App\Entities\Penjualan\PackingOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Checker Transaksi (DO) untuk modul WMS (read-only).
 * Tab mengikuti caption Trans: List SPK, Cetak SJ, Update Resi, History Resi.
 */
class WmsDeliveryOrderController extends Controller
{
    private $tabs = [
        'SPK'     => 3,
        'CETAK'   => 4,
        'RESI'    => 5,
        'HISTORY' => 6,
    ];

    public function index(Request $request)
    {
        $tab = strtoupper((string) $request->query('tab', 'SPK'));
        if (!isset($this->tabs[$tab])) {
            $tab = 'SPK';
        }

        $search = trim((string) $request->query('search'));
        $onlyUnprinted = $tab === 'SPK' && (string) $request->query('printed') === '0';

        $counts = [];
        foreach ($this->tabs as $label => $status) {
            $query = $this->baseQuery($search, $status);
            if ($label === 'SPK' && $onlyUnprinted) {
                $query->where(function ($sub) {
                    $sub->whereNull('print_count')->orWhere('print_count', 0);
                });
            }
            $counts[$label] = $query->count();
        }

        $paginator = $this->baseQuery($search, $this->tabs[$tab]);

        if ($onlyUnprinted) {
            $paginator->where(function ($sub) {
                $sub->whereNull('print_count')->orWhere('print_count', 0);
            });
        }

        $paginator = $paginator->orderBy('penjualan_do.id', 'desc')->paginate(20);

        $rows = [];
        $no = ($paginator->currentPage() - 1) * $paginator->perPage();

        foreach ($paginator as $row) {
            $rows[] = [
                'no'          => ++$no,
                'id'          => (int) $row->id,
                'do_code'     => $row->do_code,
                'so_code'     => $row->code,
                'customer'    => trim(($row->customer_name ?: '-') . ' ' . (string) $row->customer_city),
                'status'      => $this->statusLabel((int) $row->status),
                'print_count' => (int) $row->print_count,
                'created_at'  => $row->created_at ? date('d/m/Y H:i', strtotime($row->created_at)) : '-',
                'date_sent'   => $row->date_sent ? date('d/m/Y', strtotime($row->date_sent)) : '-',
                'void_status' => (bool) $row->void_status,
                'kurs_hold'   => (bool) $row->is_kurs_hold,
                'cash'        => $row->type_transaction === 'CASH',
            ];
        }

        $tabs = [];
        foreach ($counts as $label => $count) {
            $tabs[] = ['tab' => $label, 'count' => $count];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'generated_at' => date('c'),
                'tab'          => $tab,
                'only_unprinted' => $onlyUnprinted,
                'search'       => $search,
                'tabs'         => $tabs,
                'rows'         => $rows,
                'meta'         => [
                    'page'      => $paginator->currentPage(),
                    'per_page'  => $paginator->perPage(),
                    'total'     => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
        ]);
    }

    private function baseQuery($search, $status)
    {
        $query = PackingOrder::select(
                'penjualan_do.id',
                'penjualan_do.do_code',
                'penjualan_do.code',
                'penjualan_do.status',
                'penjualan_do.print_count',
                'penjualan_do.created_at',
                'penjualan_do.date_sent',
                'penjualan_do.void_status',
                'penjualan_do.is_kurs_hold',
                'penjualan_do.type_transaction',
                'customer.name as customer_name',
                'customer.text_kota as customer_city'
            )
            ->leftJoin('master_customer_other_addresses as customer', 'customer.id', '=', 'penjualan_do.customer_other_address_id')
            ->where('penjualan_do.status', $status);

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('penjualan_do.do_code', 'like', '%' . $search . '%')
                    ->orWhere('penjualan_do.code', 'like', '%' . $search . '%')
                    ->orWhere('customer.name', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    private function statusLabel($status)
    {
        $map = [
            3 => 'READY',
            4 => 'PACKED',
            5 => 'DELIVERING',
            6 => 'DELIVERED',
        ];

        return $map[$status] ?? '-';
    }
}
