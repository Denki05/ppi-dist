<?php

namespace App\Http\Controllers\Api;

use App\Entities\Account\Superuser;
use App\Entities\Gudang\QualityControl2;
use App\Entities\Setting\UserMenu;
use App\Http\Controllers\Controller;
use App\Services\QualityControl2Service;
use Illuminate\Http\Request;

/**
 * Daftar Receiving - Komplain untuk modul WMS (read-only).
 */
class WmsKomplainController extends Controller
{
    private $statuses = [
        'ACTIVE' => QualityControl2::STATUS['ACTIVE'],
        'ACC'    => QualityControl2::STATUS['ACC'],
    ];

    public function index(Request $request)
    {
        $status = strtoupper((string) $request->query('status', 'ACTIVE'));
        if (!isset($this->statuses[$status])) {
            $status = 'ACTIVE';
        }

        $search = trim((string) $request->query('search'));

        $counts = [];
        foreach ($this->statuses as $label => $value) {
            $query = QualityControl2::where('receiving_komplain.status', $value)
                ->leftJoin('master_customer_other_addresses as customer', 'customer.id', '=', 'receiving_komplain.customer_id')
                ->leftJoin('master_warehouses as warehouse', 'warehouse.id', '=', 'receiving_komplain.warehouse_id');
            $this->applySearch($query, $search);
            $counts[$label] = $query->count();
        }

        $paginator = QualityControl2::select(
                'receiving_komplain.id',
                'receiving_komplain.code',
                'receiving_komplain.tanggal',
                'receiving_komplain.status',
                'receiving_komplain.note',
                'customer.name as customer_name',
                'customer.text_kota as customer_city',
                'warehouse.name as warehouse'
            )
            ->leftJoin('master_customer_other_addresses as customer', 'customer.id', '=', 'receiving_komplain.customer_id')
            ->leftJoin('master_warehouses as warehouse', 'warehouse.id', '=', 'receiving_komplain.warehouse_id')
            ->where('receiving_komplain.status', $this->statuses[$status]);

        $this->applySearch($paginator, $search);

        $paginator = $paginator->orderBy('receiving_komplain.tanggal', 'desc')
            ->orderBy('receiving_komplain.id', 'desc')
            ->paginate(20);

        // Total qty per komplain untuk halaman ini (hindari N+1).
        $ids = collect($paginator->items())->pluck('id')->all();
        $qtyMap = collect();
        if (!empty($ids)) {
            $qtyMap = \Illuminate\Support\Facades\DB::table('receiving_komplain_detail')
                ->whereIn('receving_komplain_id', $ids)
                ->select('receving_komplain_id', \Illuminate\Support\Facades\DB::raw('SUM(qty) as total_qty'))
                ->groupBy('receving_komplain_id')
                ->pluck('total_qty', 'receving_komplain_id');
        }

        $rows = [];
        $no = ($paginator->currentPage() - 1) * $paginator->perPage();

        foreach ($paginator as $row) {
            $rows[] = [
                'no'         => ++$no,
                'id'         => (int) $row->id,
                'code'       => $row->code,
                'tanggal'    => $row->tanggal ? date('d/m/Y', strtotime($row->tanggal)) : '-',
                'customer'   => trim(($row->customer_name ?: 'SHOWROOM') . ' ' . (string) $row->customer_city),
                'warehouse'  => $row->warehouse ?: '-',
                'qty'        => (float) ($qtyMap[$row->id] ?? 0),
                'note'       => (string) $row->note,
                'status'     => array_search((int) $row->status, $this->statuses),
            ];
        }

        $tabs = [];
        foreach ($counts as $label => $count) {
            $tabs[] = ['status' => $label, 'count' => $count];
        }

        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'generated_at' => date('c'),
                'status'       => $status,
                'search'       => $search,
                'tabs'         => $tabs,
                'rows'         => $rows,
                'permissions'  => [
                    'can_approve' => $this->canApprove($userId),
                ],
                'meta'         => [
                    'page'      => $paginator->currentPage(),
                    'per_page'  => $paginator->perPage(),
                    'total'     => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Detail satu receiving komplain: header, produk, dan hak ACC.
     */
    public function show(Request $request, $id)
    {
        $qc = QualityControl2::with([
                'warehouse',
                'customer',
                'retur',
                'details.product_pack.packaging',
            ])
            ->find((int) $id);

        if (!$qc) {
            return $this->fail('Komplain tidak ditemukan.', 404);
        }

        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');
        $canApprove = $this->canApprove($userId);

        $customer = 'SHOWROOM';
        if ($qc->customer) {
            $customer = trim(($qc->customer->name ?: 'SHOWROOM') . ' ' . (string) $qc->customer->text_kota);
        }

        $creator = $qc->created_by ? Superuser::find($qc->created_by) : null;

        $rows = [];
        $totalQty = 0.0;

        foreach ($qc->details as $i => $detail) {
            $qty = (float) $detail->qty;
            $totalQty += $qty;

            $rows[] = [
                'no'      => $i + 1,
                'product' => $detail->product_pack
                    ? trim($detail->product_pack->code . ' - ' . $detail->product_pack->name, ' -')
                    : '-',
                'pack'    => $detail->product_pack && $detail->product_pack->packaging
                    ? $detail->product_pack->packaging->pack_name
                    : '-',
                'qty'     => $qty,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'generated_at' => date('c'),
                'header'       => [
                    'id'         => (int) $qc->id,
                    'code'       => $qc->code,
                    'tanggal'    => $qc->tanggal ? date('d/m/Y', strtotime($qc->tanggal)) : '-',
                    'status'     => array_search((int) $qc->status, $this->statuses) ?: 'ACTIVE',
                    'customer'   => $customer,
                    'warehouse'  => $qc->warehouse ? $qc->warehouse->name : '-',
                    'note'       => (string) ($qc->note ?? ''),
                    'retur'      => $qc->retur ? $qc->retur->code : null,
                    'created_at' => $qc->created_at ? date('d/m/Y H:i', strtotime($qc->created_at)) : '-',
                    'created_by' => $creator ? ($creator->name ?: $creator->username) : '-',
                ],
                'stats'        => [
                    'items'     => count($rows),
                    'total_qty' => $totalQty,
                ],
                'rows'         => $rows,
                'can_acc'      => $qc->status == QualityControl2::STATUS['ACTIVE'] && $canApprove,
                'acc_blocked'  => $this->accBlockedReason($qc, $canApprove),
                'can_pdf'      => (int) $qc->status === QualityControl2::STATUS['ACC'],
            ],
        ]);
    }

    /**
     * ACC komplain; stok Araya -> gudang QC diproses lewat service Trans.
     */
    public function acc(Request $request, $id)
    {
        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        if (!$this->canApprove($userId)) {
            return $this->fail('Akun ini tidak punya hak ACC Receiving - Komplain di Trans.', 403);
        }

        $qc = QualityControl2::find((int) $id);

        if (!$qc) {
            return $this->fail('Komplain tidak ditemukan.', 404);
        }

        if ($qc->status == QualityControl2::STATUS['ACC']) {
            return $this->fail('Data sudah di ACC.', 409);
        }

        $result = app(QualityControl2Service::class)->acc($qc->id, (int) $userId);

        if ($result['status'] !== 'ok') {
            return $this->fail($result['message'], $result['status'] === 'already' ? 409 : 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => $result['message'],
            'data'    => [
                'id'     => (int) $qc->id,
                'status' => 'ACC',
            ],
        ]);
    }

    /**
     * Surat Jalan komplain (PDF) untuk komplain yang sudah ACC.
     */
    public function pdf(Request $request, $id)
    {
        $qc = QualityControl2::find((int) $id);

        if (!$qc) {
            return $this->fail('Komplain tidak ditemukan.', 404);
        }

        $pdf = \PDF::loadView('superuser.gudang.quality_control_2.pdf_sj_komplain', ['result' => $qc])
            ->setPaper('a5', 'landscape');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $qc->code . '.pdf"',
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
                $query->where('route_name', 'superuser.gudang.quality_control_2');
            })
            ->exists();
    }

    private function accBlockedReason(QualityControl2 $qc, $canApprove)
    {
        if ($qc->status != QualityControl2::STATUS['ACTIVE']) {
            return 'ACC hanya tersedia untuk komplain berstatus ACTIVE.';
        }

        if (!$canApprove) {
            return 'Akun ini tidak punya hak ACC Receiving - Komplain di Trans.';
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

    private function applySearch($query, $search)
    {
        if ($search === '') {
            return;
        }

        $query->where(function ($sub) use ($search) {
            $sub->where('receiving_komplain.code', 'like', '%' . $search . '%')
                ->orWhere('receiving_komplain.note', 'like', '%' . $search . '%')
                ->orWhere('customer.name', 'like', '%' . $search . '%')
                ->orWhere('warehouse.name', 'like', '%' . $search . '%');
        });
    }
}
