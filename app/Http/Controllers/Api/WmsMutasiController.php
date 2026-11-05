<?php

namespace App\Http\Controllers\Api;

use App\Entities\Gudang\MutasiOut;
use App\Entities\Gudang\MutasiShowroom;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Checker Mutasi untuk modul WMS (read-only).
 * Jenis: showroom (default) dan gudang, mengikuti tab di Trans.
 */
class WmsMutasiController extends Controller
{
    private $tabs = ['AKTIF', 'BELUM', 'SELESAI'];

    public function index(Request $request)
    {
        $type = strtolower((string) $request->query('type', 'showroom'));
        if (!in_array($type, ['showroom', 'gudang'], true)) {
            $type = 'showroom';
        }

        $tab = strtoupper((string) $request->query('tab', 'AKTIF'));
        if (!in_array($tab, $this->tabs, true)) {
            $tab = 'AKTIF';
        }

        $search = trim((string) $request->query('search'));

        $counts = [];
        foreach ($this->tabs as $item) {
            $counts[$item] = $this->buildQuery($type, $item, $search)->count();
        }

        $paginator = $this->buildQuery($type, $tab, $search);
        $paginator = $type === 'showroom'
            ? $paginator->orderBy('penjualan_showroom.tanggal', 'desc')->orderBy('penjualan_showroom.id', 'desc')->paginate(20)
            : $paginator->orderBy('gudang_mutasi_out.date', 'desc')->orderBy('gudang_mutasi_out.id', 'desc')->paginate(20);

        $rows = [];
        $no = ($paginator->currentPage() - 1) * $paginator->perPage();

        foreach ($paginator as $row) {
            if ($type === 'showroom') {
                $rows[] = [
                    'no'       => ++$no,
                    'id'       => (int) $row->id,
                    'tanggal'  => $row->tanggal ? date('d/m/Y', strtotime($row->tanggal)) : '-',
                    'code'     => $row->kode,
                    'so'       => $row->so_code ?: '-',
                    'customer' => trim(($row->customer_name ?: 'SHOWROOM') . ' ' . (string) $row->customer_city),
                    'type'     => $this->showroomTypeLabel($row->type),
                    'status'   => 'PUBLISH',
                    'barang'   => $this->barangLabel((int) $row->status_barang),
                ];
                continue;
            }

            $rows[] = [
                'no'       => ++$no,
                'id'       => (int) $row->id,
                'tanggal'  => $row->date ? date('d/m/Y', strtotime($row->date)) : '-',
                'code'     => $row->code,
                'so'       => '-',
                'customer' => ($row->warehouse_from ?: '-') . ' → ' . ($row->warehouse_to ?: '-'),
                'type'     => 'Mutasi Gudang',
                'status'   => $this->statusLabel((int) $row->status),
                'barang'   => $this->barangLabel((int) $row->status_barang),
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
                'type'         => $type,
                'tab'          => $tab,
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

    private function buildQuery($type, $tab, $search)
    {
        if ($type === 'showroom') {
            $query = MutasiShowroom::select(
                    'penjualan_showroom.id',
                    'penjualan_showroom.kode',
                    'penjualan_showroom.tanggal',
                    'penjualan_showroom.type',
                    'penjualan_showroom.status',
                    'penjualan_showroom.status_barang',
                    'penjualan_showroom.print_count',
                    'customer.name as customer_name',
                    'customer.text_kota as customer_city',
                    'so.code as so_code'
                )
                ->leftJoin('master_customer_other_addresses as customer', 'customer.id', '=', 'penjualan_showroom.customer_other_address_id')
                ->leftJoin('penjualan_so as so', 'so.id', '=', 'penjualan_showroom.so_id')
                ->where('penjualan_showroom.status', 2);

            if ($tab === 'AKTIF') {
                $query->where('penjualan_showroom.status_barang', 0);
            } elseif ($tab === 'BELUM') {
                $query->where('penjualan_showroom.status_barang', 1)
                    ->where('penjualan_showroom.print_count', '>', 0);
            } else {
                $query->where('penjualan_showroom.status_barang', 2);
            }

            $this->applySearch($query, $search, 'showroom');

            return $query;
        }

        $query = MutasiOut::select(
                'gudang_mutasi_out.id',
                'gudang_mutasi_out.code',
                'gudang_mutasi_out.date',
                'gudang_mutasi_out.status',
                'gudang_mutasi_out.status_barang',
                'wf.name as warehouse_from',
                'wt.name as warehouse_to'
            )
            ->leftJoin('master_warehouses as wf', 'wf.id', '=', 'gudang_mutasi_out.warehouse_from')
            ->leftJoin('master_warehouses as wt', 'wt.id', '=', 'gudang_mutasi_out.warehouse_to');

        if ($tab === 'AKTIF') {
            $query->where('gudang_mutasi_out.status', MutasiOut::STATUS['PUBLISH'])
                ->where('gudang_mutasi_out.status_barang', 0);
        } elseif ($tab === 'BELUM') {
            $query->whereIn('gudang_mutasi_out.status', [
                    MutasiOut::STATUS['PUBLISH'],
                    MutasiOut::STATUS['ACC'],
                ])
                ->where('gudang_mutasi_out.status_barang', 1);
        } else {
            $query->where('gudang_mutasi_out.status', MutasiOut::STATUS['ACC']);
        }

        $this->applySearch($query, $search, 'gudang');

        return $query;
    }

    private function applySearch($query, $search, $type)
    {
        if ($search === '') {
            return;
        }

        if ($type === 'showroom') {
            $query->where(function ($sub) use ($search) {
                $sub->where('penjualan_showroom.kode', 'like', '%' . $search . '%')
                    ->orWhere('customer.name', 'like', '%' . $search . '%')
                    ->orWhere('so.code', 'like', '%' . $search . '%');
            });

            return;
        }

        $query->where(function ($sub) use ($search) {
            $sub->where('gudang_mutasi_out.code', 'like', '%' . $search . '%')
                ->orWhere('gudang_mutasi_out.note', 'like', '%' . $search . '%')
                ->orWhere('wf.name', 'like', '%' . $search . '%')
                ->orWhere('wt.name', 'like', '%' . $search . '%');
        });
    }

    private function statusLabel($status)
    {
        $map = [
            MutasiOut::STATUS['DELETED'] => 'Draft',
            MutasiOut::STATUS['ACTIVE']  => 'Aktif',
            MutasiOut::STATUS['PUBLISH'] => 'Publish',
            MutasiOut::STATUS['ACC']     => 'ACC',
        ];

        return $map[$status] ?? '-';
    }

    private function barangLabel($status)
    {
        $map = [
            MutasiOut::STATUS_BARANG['PENDING']        => 'Pending',
            MutasiOut::STATUS_BARANG['BELUM_DIAMBIL']  => 'Belum Diambil',
            MutasiOut::STATUS_BARANG['DIAMBIL']        => 'Diambil',
        ];

        return $map[$status] ?? '-';
    }

    private function showroomTypeLabel($type)
    {
        $map = [
            1 => 'SHOWROOM',
            2 => 'FREE PRODUCT',
            3 => 'KLAIM',
            4 => 'BUNDLING',
            5 => 'PROMOSI',
        ];

        return $map[(int) $type] ?? '-';
    }
}
