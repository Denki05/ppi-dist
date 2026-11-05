<?php

namespace App\Http\Controllers\Api;

use App\Entities\Account\Superuser;
use App\Entities\Gudang\Receiving;
use App\Entities\Setting\UserMenu;
use App\Http\Controllers\Controller;
use App\Services\Receiving\ReceivingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Daftar penerimaan untuk modul WMS (read-only).
 * Satu request mengembalikan count per status, baris, dan paginasi.
 */
class WmsReceivingController extends Controller
{
    private $statuses = [
        'ACTIVE' => Receiving::STATUS['ACTIVE'],
        'QC'     => Receiving::STATUS['QC'],
        'READY'  => Receiving::STATUS['READY'],
        'ACC'    => Receiving::STATUS['ACC'],
    ];

    public function index(Request $request)
    {
        return $this->listResponse($request, Receiving::TYPE['INBOUND']);
    }

    public function transaksi(Request $request)
    {
        return $this->listResponse($request, Receiving::TYPE['RETURN']);
    }

    /**
     * Detail satu penerimaan inbound: header, produk, ringkasan QC, dan hak ACC.
     */
    public function show(Request $request, $id)
    {
        $receiving = Receiving::with([
                'warehouse',
                'details.product_pack.packaging',
                'details.qcLogs',
            ])
            ->where('type', Receiving::TYPE['INBOUND'])
            ->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving tidak ditemukan.', 404);
        }

        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');
        $status = array_search((int) $receiving->status, $this->statuses) ?: 'ACTIVE';
        $canApprove = $this->canApprove($userId);

        $stats = [
            'items'       => 0,
            'qty_po'      => 0.0,
            'qty_ri'      => 0.0,
            'selisih'     => 0.0,
            'qc_ok'       => 0.0,
            'qc_not_ok'   => 0.0,
            'qc_hold'     => 0.0,
            'qc_approved' => 0.0,
        ];

        $rows = [];
        foreach ($receiving->details as $i => $detail) {
            $logs = $detail->qcLogs;
            $qcOk = (float) $logs->where('status_qc', 1)->sum('qty_qc');
            $qcNotOk = (float) $logs->where('status_qc', 0)->sum('qty_qc');
            $qcHold = (float) $logs->where('status_qc', 2)->sum('qty_qc');
            $qcApproved = (float) $logs->where('is_approved', 1)->sum('qty_qc');
            $qtyPo = (float) $detail->quantity_po;
            $qtyRi = (float) ($detail->quantity_ri ?? 0);
            $selisih = (float) ($detail->selisih ?? ($qtyPo - $qtyRi));

            $stats['items']++;
            $stats['qty_po'] += $qtyPo;
            $stats['qty_ri'] += $qtyRi;
            $stats['selisih'] += $selisih;
            $stats['qc_ok'] += $qcOk;
            $stats['qc_not_ok'] += $qcNotOk;
            $stats['qc_hold'] += $qcHold;
            $stats['qc_approved'] += $qcApproved;

            $rows[] = [
                'no'          => $i + 1,
                'product'     => $detail->product_pack
                    ? trim($detail->product_pack->code . ' - ' . $detail->product_pack->name, ' -')
                    : '-',
                'pack'        => $detail->product_pack && $detail->product_pack->packaging
                    ? $detail->product_pack->packaging->pack_name
                    : '-',
                'qty_po'      => $qtyPo,
                'qty_ri'      => $qtyRi,
                'selisih'     => $selisih,
                'batch'       => (string) ($detail->no_batch ?? ''),
                'note'        => (string) ($detail->note ?? ''),
                'qc_ok'       => $qcOk,
                'qc_not_ok'   => $qcNotOk,
                'qc_hold'     => $qcHold,
                'qc_approved' => $qcApproved,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'generated_at' => date('c'),
                'header'       => [
                    'id'          => (int) $receiving->id,
                    'code'        => $receiving->code,
                    'status'      => $status,
                    'type'        => $receiving->type(),
                    'warehouse'   => $receiving->warehouse ? $receiving->warehouse->name : '-',
                    'pbm_date'    => $receiving->pbm_date ? date('d/m/Y', strtotime($receiving->pbm_date)) : '-',
                    'created_at'  => $receiving->created_at ? date('d/m/Y H:i', strtotime($receiving->created_at)) : '-',
                    'note'        => (string) ($receiving->note ?? ''),
                    'created_by'  => $receiving->createdBySuperuser() ?: '-',
                    'acc_by'      => $receiving->acc_by ? ($receiving->accBySuperuser() ?: '-') : null,
                    'acc_at'      => $receiving->acc_at ? date('d/m/Y H:i', strtotime($receiving->acc_at)) : null,
                ],
                'stats'        => $stats,
                'rows'         => $rows,
                'can_acc'      => $receiving->status == Receiving::STATUS['READY'] && $canApprove,
                'acc_blocked'  => $this->accBlockedReason($receiving, $canApprove),
            ],
        ]);
    }

    /**
     * ACC penerimaan. Memakai ReceivingService milik Trans agar stok & PO
     * diproses dengan aturan yang sama.
     */
    public function acc(Request $request, $id)
    {
        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        if (!$this->canApprove($userId)) {
            return $this->fail('Akun ini tidak punya hak ACC Receiving di Trans.', 403);
        }

        $receiving = Receiving::where('type', Receiving::TYPE['INBOUND'])->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving tidak ditemukan.', 404);
        }

        $result = app(ReceivingService::class)->accReceiving($receiving->id, (int) $userId);

        if ($result['status'] !== 'ok') {
            $code = $result['status'] === 'not_ready' ? 409 : 500;

            return $this->fail($result['message'], $code);
        }

        return response()->json([
            'status'  => 'success',
            'message' => $result['message'],
            'data'    => [
                'id'     => (int) $receiving->id,
                'status' => 'ACC',
            ],
        ]);
    }

    private function canApprove($userId)
    {
        $user = Superuser::find((int) $userId);

        if (!$user) {
            return false;
        }

        if ((int) $user->is_superuser === 1) {
            return true;
        }

        return UserMenu::where('user_id', $user->id)
            ->where('can_approve', 1)
            ->whereHas('menu', function ($query) {
                $query->where('route_name', 'superuser.gudang.receiving');
            })
            ->exists();
    }

    private function accBlockedReason(Receiving $receiving, $canApprove)
    {
        if ($receiving->status != Receiving::STATUS['READY']) {
            return 'ACC hanya tersedia untuk dokumen berstatus READY.';
        }

        if (!$canApprove) {
            return 'Akun ini tidak punya hak ACC Receiving di Trans.';
        }

        return null;
    }

    private function fail($message, $code = 400)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], $code);
    }

    private function listResponse(Request $request, $type)
    {
        $status = strtoupper((string) $request->query('status', 'ACTIVE'));
        if (!isset($this->statuses[$status])) {
            $status = 'ACTIVE';
        }

        $search = trim((string) $request->query('search'));

        $counts = [];
        foreach ($this->statuses as $label => $value) {
            $query = Receiving::where('receiving.type', $type)
                ->where('receiving.status', $value)
                ->leftJoin('master_warehouses', 'master_warehouses.id', '=', 'receiving.warehouse_id');
            $this->applySearch($query, $search);
            $counts[$label] = $query->count();
        }

        $paginator = Receiving::select(
                'receiving.id',
                'receiving.code',
                'receiving.status',
                'receiving.pbm_date',
                'receiving.created_at',
                'receiving.note',
                'master_warehouses.name as warehouse'
            )
            ->leftJoin('master_warehouses', 'master_warehouses.id', '=', 'receiving.warehouse_id')
            ->where('receiving.type', $type)
            ->where('receiving.status', $this->statuses[$status]);

        $this->applySearch($paginator, $search);

        $paginator = $paginator->orderBy('receiving.pbm_date', 'desc')
            ->orderBy('receiving.id', 'desc')
            ->paginate(20);

        // Kode PO hanya untuk Receiving - Transaksi (type 1).
        $poMap = collect();
        if ($type === Receiving::TYPE['RETURN']) {
            $ids = collect($paginator->items())->pluck('id')->all();
            if (!empty($ids)) {
                $poMap = DB::table('receiving_detail')
                    ->join('purchase_order', 'purchase_order.id', '=', 'receiving_detail.po_id')
                    ->whereIn('receiving_detail.receiving_id', $ids)
                    ->select('receiving_detail.receiving_id', 'purchase_order.code')
                    ->get()
                    ->groupBy('receiving_id');
            }
        }

        $rows = [];
        $no = ($paginator->currentPage() - 1) * $paginator->perPage();

        foreach ($paginator as $row) {
            $rows[] = [
                'no'          => ++$no,
                'id'          => (int) $row->id,
                'code'        => $row->code,
                'po'          => $poMap->has($row->id)
                    ? $poMap[$row->id]->pluck('code')->unique()->implode(', ')
                    : '-',
                'warehouse'   => $row->warehouse ?: '-',
                'pbm_date'    => $row->pbm_date ? date('d/m/Y', strtotime($row->pbm_date)) : '-',
                'created_at'  => $row->created_at ? date('d/m/Y H:i', strtotime($row->created_at)) : '-',
                'note'        => (string) $row->note,
                'status'      => array_search((int) $row->status, $this->statuses),
            ];
        }

        $tabs = [];
        foreach ($counts as $label => $count) {
            $tabs[] = ['status' => $label, 'count' => $count];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'generated_at' => date('c'),
                'status'       => $status,
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

    private function applySearch($query, $search)
    {
        if ($search === '') {
            return;
        }

        $query->where(function ($sub) use ($search) {
            $sub->where('receiving.code', 'like', '%' . $search . '%')
                ->orWhere('receiving.note', 'like', '%' . $search . '%')
                ->orWhere('master_warehouses.name', 'like', '%' . $search . '%');
        });
    }
}
