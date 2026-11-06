<?php

namespace App\Http\Controllers\Api;

use App\Entities\Account\Superuser;
use App\Entities\Gudang\PurchaseOrderSummary;
use App\Entities\Gudang\Receiving;
use App\Entities\Gudang\ReceivingDetail;
use App\Entities\Gudang\ReceivingQcLogs;
use App\Entities\Gudang\StockMove;
use App\Entities\Master\ProductMinStock;
use App\Entities\Master\ProductPack;
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
        $canUpdate = $this->canUpdate($userId);

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
            $qcTotal = (float) $logs->sum('qty_qc');

            $qcLogs = [];
            foreach ($logs as $log) {
                $qcLogs[] = [
                    'id'          => (int) $log->id,
                    'qty'         => (float) $log->qty_qc,
                    'status'      => $log->status_qc(),
                    'status_code' => (int) $log->status_qc,
                    'is_sellable' => (int) $log->is_sellable,
                    'is_approved' => (int) $log->is_approved,
                ];
            }

            $stats['items']++;
            $stats['qty_po'] += $qtyPo;
            $stats['qty_ri'] += $qtyRi;
            $stats['selisih'] += $selisih;
            $stats['qc_ok'] += $qcOk;
            $stats['qc_not_ok'] += $qcNotOk;
            $stats['qc_hold'] += $qcHold;
            $stats['qc_approved'] += $qcApproved;

            $rows[] = [
                'id'          => (int) $detail->id,
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
                'qc_total'    => $qcTotal,
                'remaining'   => max($qtyPo - $qcTotal, 0),
                'qc_logs'     => $qcLogs,
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
                'can_update'   => $canUpdate,
                'can_publish'  => $canUpdate && in_array((int) $receiving->status, [Receiving::STATUS['ACTIVE'], Receiving::STATUS['QC']]),
                'can_cancel'   => $canUpdate && (int) $receiving->status === Receiving::STATUS['QC'],
                'can_rollback' => $canUpdate && (int) $receiving->status === Receiving::STATUS['READY'],
                'can_qc'         => $canUpdate && (int) $receiving->status === Receiving::STATUS['QC'],
                'can_approve_qc' => $canApprove && (int) $receiving->status === Receiving::STATUS['QC'],
                'can_delete_qc'  => $this->menuRight($userId, 'superuser.gudang.receiving.detail', 'can_delete')
                    && (int) $receiving->status === Receiving::STATUS['QC'],
            ],
        ]);
    }

    /**
     * ACC Receiving diproses Admin di modul Trans, bukan dari WMS.
     */
    public function acc(Request $request, $id)
    {
        return $this->fail('ACC Receiving dilakukan di modul Trans oleh Admin.', 403);
    }

    /**
     * Publish bertahap ACTIVE -> QC -> READY.
     */
    public function publish(Request $request, $id)
    {
        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        if (!$this->canUpdate($userId)) {
            return $this->fail('Akun ini tidak punya hak ubah Receiving di Trans.', 403);
        }

        $receiving = Receiving::where('type', Receiving::TYPE['INBOUND'])->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving tidak ditemukan.', 404);
        }

        $result = app(ReceivingService::class)->publishReceiving($receiving->id);

        if (!empty($result['error'])) {
            return $this->fail($result['error'], 422);
        }

        return response()->json([
            'status'  => 'success',
            'message' => $result['message'],
            'data'    => [
                'id'     => (int) $receiving->id,
                'status' => $receiving->fresh()->status(),
            ],
        ]);
    }

    /**
     * Batalkan QC kembali ke ACTIVE.
     */
    public function cancel(Request $request, $id)
    {
        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        if (!$this->canUpdate($userId)) {
            return $this->fail('Akun ini tidak punya hak ubah Receiving di Trans.', 403);
        }

        $receiving = Receiving::where('type', Receiving::TYPE['INBOUND'])->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving tidak ditemukan.', 404);
        }

        if ((int) $receiving->status !== Receiving::STATUS['QC']) {
            return $this->fail('Hanya receiving berstatus QC yang bisa dibatalkan.', 409);
        }

        $result = app(ReceivingService::class)->cancelReceiving($receiving->id);

        if ($result['status'] !== 'ok') {
            return $this->fail($result['message'], $result['status'] === 'has_qc' ? 409 : 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => $result['message'],
            'data'    => [
                'id'     => (int) $receiving->id,
                'status' => $receiving->fresh()->status(),
            ],
        ]);
    }

    /**
     * Kembalikan READY ke QC untuk revisi.
     */
    public function rollback(Request $request, $id)
    {
        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        if (!$this->canUpdate($userId)) {
            return $this->fail('Akun ini tidak punya hak ubah Receiving di Trans.', 403);
        }

        $receiving = Receiving::where('type', Receiving::TYPE['INBOUND'])->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving tidak ditemukan.', 404);
        }

        $result = app(ReceivingService::class)->rollbackToQc($receiving->id);

        if ($result['status'] !== 'ok') {
            return $this->fail($result['message'], $result['status'] === 'invalid' ? 409 : 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => $result['message'],
            'data'    => [
                'id'     => (int) $receiving->id,
                'status' => $receiving->fresh()->status(),
            ],
        ]);
    }

    /**
     * Simpan satu log QC untuk detail receiving inbound (status harus QC).
     */
    public function storeQc(Request $request, $id, $detailId)
    {
        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        if (!$this->canUpdate($userId)) {
            return $this->fail('Akun ini tidak punya hak ubah Receiving di Trans.', 403);
        }

        $receiving = Receiving::where('type', Receiving::TYPE['INBOUND'])->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving tidak ditemukan.', 404);
        }

        if ((int) $receiving->status !== Receiving::STATUS['QC']) {
            return $this->fail('QC hanya bisa diinput saat status QC.', 409);
        }

        $detail = ReceivingDetail::where('receiving_id', $receiving->id)->find((int) $detailId);

        if (!$detail) {
            return $this->fail('Detail receiving tidak ditemukan.', 404);
        }

        $validator = \Validator::make($request->all(), [
            'qty_qc'      => 'required|numeric|min:0.1',
            'status_qc'   => 'required|in:OK,NOT OK,Not OK',
            'is_sellable' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->fail(implode(' ', $validator->errors()->all()), 422);
        }

        $qty = (float) $request->input('qty_qc');
        $existing = (float) $detail->qcLogs()->sum('qty_qc');

        if ($existing + $qty > (float) $detail->quantity_po) {
            $sisa = (float) $detail->quantity_po - $existing;

            return $this->fail('Total QC melebihi qty PO. Sisa maksimal: ' . rtrim(rtrim(number_format($sisa, 2, ',', '.'), '0'), ',') . '.', 422);
        }

        $attributes = [
            'receiving_details_id' => $detail->id,
            'product_packaging_id' => $detail->product_packaging_id,
            'qty_qc'               => $qty,
            'status_qc'            => strtoupper($request->input('status_qc')) === 'OK' ? 1 : 0,
            'is_sellable'          => $request->boolean('is_sellable') ? 1 : 0,
            'is_approved'          => 0,
        ];

        // receiving_qc_logs.id adalah PRIMARY KEY tanpa AUTO_INCREMENT di Trans;
        // id diisi manual agar baris kedua dan seterusnya tidak gagal duplicate.
        $log = null;
        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                $log = new ReceivingQcLogs($attributes);
                $log->id = (int) ReceivingQcLogs::max('id') + 1;
                $log->save();
                break;
            } catch (\Illuminate\Database\QueryException $e) {
                if ($attempt === 2) {
                    return $this->fail('Simpan QC gagal: ' . $e->getMessage(), 500);
                }
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'QC berhasil disimpan.',
            'data'    => ['id' => (int) $log->id],
        ]);
    }

    /**
     * Approve log QC saleable: potong PO summary dan tambahkan stok gudang.
     * Aturan disamakan dengan approveQc di ReceivingDetailController Trans.
     */
    public function approveQc(Request $request, $id, $detailId, $logId)
    {
        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        if (!$this->canApprove($userId)) {
            return $this->fail('Akun ini tidak punya hak ACC Receiving di Trans.', 403);
        }

        $receiving = Receiving::where('type', Receiving::TYPE['INBOUND'])->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving tidak ditemukan.', 404);
        }

        if ((int) $receiving->status !== Receiving::STATUS['QC']) {
            return $this->fail('Approve QC hanya tersedia saat status QC.', 409);
        }

        $detail = ReceivingDetail::where('receiving_id', $receiving->id)->find((int) $detailId);

        if (!$detail) {
            return $this->fail('Detail receiving tidak ditemukan.', 404);
        }

        $log = $this->findQcLog($detail, $logId, $request);

        if (!$log) {
            return $this->fail('Log QC tidak ditemukan pada detail ini.', 404);
        }

        if ($log->is_approved) {
            return $this->fail('Log QC ini sudah pernah di-approve.', 409);
        }

        if ((int) $log->status_qc !== 1 || !$log->is_sellable) {
            return $this->fail('Hanya log QC OK + Saleable yang bisa di-approve.', 422);
        }

        try {
            DB::transaction(function () use ($detail, $receiving, $log, $userId) {
                $sisaToCut = (float) $log->qty_qc;

                $summaries = PurchaseOrderSummary::where([
                    ['product_packaging_id', $detail->product_packaging_id],
                    ['status', 2],
                ])->orderBy('id')->lockForUpdate()->get();

                foreach ($summaries as $sum) {
                    if ($sisaToCut <= 0) {
                        break;
                    }

                    $ambil = min($sisaToCut, $sum->quantity);
                    $sum->quantity -= $ambil;
                    $sisaToCut -= $ambil;

                    if ($sum->quantity <= 0) {
                        $sum->status = 1;
                    }

                    $sum->save();
                }

                if ($sisaToCut > 0) {
                    throw new \RuntimeException('Stok PO Summary tidak mencukupi untuk produk ini.');
                }

                $minStock = ProductMinStock::firstOrNew([
                    'product_packaging_id' => $detail->product_packaging_id,
                    'warehouse_id'         => $receiving->warehouse_id,
                ]);

                if (!$minStock->exists) {
                    $prodPack = ProductPack::find($detail->product_packaging_id);
                    $minStock->unit_id       = $prodPack->unit_id ?? 1;
                    $minStock->selling_price = 0;
                    $minStock->quantity      = 0;
                }

                $minStock->quantity += (float) $log->qty_qc;
                $minStock->save();

                StockMove::create([
                    'warehouse_id'         => $receiving->warehouse_id,
                    'product_packaging_id' => $detail->product_packaging_id,
                    'code_transaction'     => 'RI-' . $receiving->code,
                    'stock_in'             => (float) $log->qty_qc,
                    'stock_out'            => 0,
                    'stock_balance'        => $minStock->quantity,
                    'created_by'           => (int) $userId,
                ]);

                $log->is_approved = 1;
                $log->save();
            });
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->fail('Approve QC gagal: ' . $e->getMessage(), 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'QC berhasil di-approve dan masuk ke stok.',
            'data'    => ['id' => (int) $log->id],
        ]);
    }

    /**
     * Hapus log QC yang belum masuk stok (belum approved).
     */
    public function deleteQc(Request $request, $id, $detailId, $logId)
    {
        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        if (!$this->menuRight($userId, 'superuser.gudang.receiving.detail', 'can_delete')) {
            return $this->fail('Akun ini tidak punya hak hapus QC Receiving di Trans.', 403);
        }

        $receiving = Receiving::where('type', Receiving::TYPE['INBOUND'])->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving tidak ditemukan.', 404);
        }

        if ((int) $receiving->status !== Receiving::STATUS['QC']) {
            return $this->fail('Log QC hanya bisa dihapus saat status QC.', 409);
        }

        $detail = ReceivingDetail::where('receiving_id', $receiving->id)->find((int) $detailId);

        if (!$detail) {
            return $this->fail('Detail receiving tidak ditemukan.', 404);
        }

        $log = $this->findQcLog($detail, $logId, $request);

        if (!$log) {
            return $this->fail('Log QC tidak ditemukan pada detail ini.', 404);
        }

        if ($log->is_sellable && $log->is_approved) {
            return $this->fail('Tidak dapat menghapus log QC yang sudah masuk ke stok.', 409);
        }

        $log->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Log QC berhasil dihapus.',
        ]);
    }

    /**
     * Cari baris log QC. receiving_qc_logs di Trans tidak memakai AUTO_INCREMENT,
     * sehingga baris baru bisa ber-id 0; untuk itu pencocokan dilengkapi
     * qty/status/sellable/approved agar baris yang dipilih benar.
     */
    private function findQcLog(ReceivingDetail $detail, $logId, Request $request)
    {
        $query = ReceivingQcLogs::where('receiving_details_id', $detail->id);

        if ((int) $logId > 0) {
            $query->where('id', (int) $logId);
        } else {
            if ($request->filled('qty_qc')) {
                $query->where('qty_qc', (float) $request->input('qty_qc'));
            }
            if ($request->filled('status_qc')) {
                $query->where('status_qc', (int) $request->input('status_qc'));
            }
            if ($request->filled('is_sellable')) {
                $query->where('is_sellable', (int) $request->input('is_sellable'));
            }
            if ($request->filled('is_approved')) {
                $query->where('is_approved', (int) $request->input('is_approved'));
            }
        }

        return $query->orderByDesc('created_at')->first();
    }

    /**
     * Detail receiving type RETURN (menu Receiving - Transaksi) beserta
     * baris QC dan produk retur yang masih bisa ditambahkan.
     */
    public function transaksiShow(Request $request, $id)
    {
        $receiving = Receiving::with([
                'warehouse',
                'details.product_pack.packaging',
                'details.qcLogs',
            ])
            ->where('type', Receiving::TYPE['RETURN'])
            ->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving - Transaksi tidak ditemukan.', 404);
        }

        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');
        $status = array_search((int) $receiving->status, $this->statuses) ?: 'ACTIVE';
        $canUpdate = $this->menuRight($userId, 'superuser.gudang.quality_control', 'can_update');
        $canApprove = $this->menuRight($userId, 'superuser.gudang.quality_control', 'can_approve');
        $canDelete = $this->menuRight($userId, 'superuser.gudang.quality_control', 'can_delete');

        $details = [];
        $saleReturnId = null;

        foreach ($receiving->details as $i => $detail) {
            $logs = [];
            $qcTotal = 0.0;

            foreach ($detail->qcLogs as $log) {
                $qcTotal += (float) $log->qty_qc;
                $logs[] = [
                    'id'          => (int) $log->id,
                    'qty'         => (float) $log->qty_qc,
                    'status'      => $log->status_qc(),
                    'is_sellable' => (int) $log->is_sellable,
                    'is_approved' => (int) $log->is_approved,
                ];
            }

            $saleReturnId = $saleReturnId ?: ($detail->po_id ? (int) $detail->po_id : null);

            $details[] = [
                'no'        => $i + 1,
                'id'        => (int) $detail->id,
                'product'   => $detail->product_pack
                    ? trim($detail->product_pack->code . ' - ' . $detail->product_pack->name, ' -')
                    : '-',
                'pack'      => $detail->product_pack && $detail->product_pack->packaging
                    ? $detail->product_pack->packaging->pack_name
                    : '-',
                'qty_po'    => (float) $detail->quantity_po,
                'qty_ri'    => $detail->quantity_ri !== null ? (float) $detail->quantity_ri : null,
                'selisih'   => $detail->selisih !== null ? (float) $detail->selisih : null,
                'batch'     => (string) ($detail->no_batch ?? ''),
                'note'      => (string) ($detail->note ?? ''),
                'qc_total'  => $qcTotal,
                'remaining' => max((float) $detail->quantity_po - $qcTotal, 0),
                'qc_logs'   => $logs,
            ];
        }

        $available = [];
        if ((int) $receiving->status === Receiving::STATUS['ACTIVE']) {
            $used = DB::table('receiving_detail')
                ->where('receiving_id', $receiving->id)
                ->select('product_packaging_id', DB::raw('SUM(quantity_po) as qty'))
                ->groupBy('product_packaging_id')
                ->pluck('qty', 'product_packaging_id');

            $rows = DB::table('penjualan_retur_detail as d')
                ->join('penjualan_retur as r', 'r.id', '=', 'd.retur_id')
                ->join('master_products_packaging as p', 'p.id', '=', 'd.product_packaging_id')
                ->leftJoin('master_packaging as pk', 'pk.id', '=', 'p.packaging_id')
                ->where('r.status', 1)
                ->whereNull('r.deleted_at')
                ->where('r.type', 1)
                ->select(
                    'd.product_packaging_id as pack_id',
                    'p.code',
                    'p.name',
                    'pk.pack_name',
                    DB::raw('SUM(d.qty) as qty')
                )
                ->groupBy('d.product_packaging_id', 'p.code', 'p.name', 'pk.pack_name')
                ->orderBy('p.code')
                ->get();

            foreach ($rows as $row) {
                $remaining = (float) $row->qty - (float) ($used[$row->pack_id] ?? 0);
                if ($remaining <= 0.005) {
                    continue;
                }

                $available[] = [
                    'pack_id'       => $row->pack_id,
                    'code'          => $row->code,
                    'name'          => $row->name,
                    'pack'          => $row->pack_name ?: '-',
                    'qty_available' => round($remaining, 2),
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'generated_at' => date('c'),
                'header'       => [
                    'id'         => (int) $receiving->id,
                    'code'       => $receiving->code,
                    'status'     => $status,
                    'type'       => $receiving->type(),
                    'warehouse'  => $receiving->warehouse ? $receiving->warehouse->name : '-',
                    'pbm_date'   => $receiving->pbm_date ? date('d/m/Y', strtotime($receiving->pbm_date)) : '-',
                    'created_at' => $receiving->created_at ? date('d/m/Y H:i', strtotime($receiving->created_at)) : '-',
                    'note'       => (string) ($receiving->note ?? ''),
                    'created_by' => $receiving->createdBySuperuser() ?: '-',
                    'acc_by'     => $receiving->acc_by ? ($receiving->accBySuperuser() ?: '-') : null,
                    'acc_at'     => $receiving->acc_at ? date('d/m/Y H:i', strtotime($receiving->acc_at)) : null,
                ],
                'details'      => $details,
                'available'    => $available,
                'sale_return_id' => $saleReturnId,
                'can_update'   => $canUpdate,
                'can_delete'   => $canDelete,
                'can_publish'  => $canUpdate && in_array((int) $receiving->status, [Receiving::STATUS['ACTIVE'], Receiving::STATUS['QC']]),
                'can_acc'      => $canApprove && (int) $receiving->status === Receiving::STATUS['READY'],
                'can_pdf'      => (int) $receiving->status === Receiving::STATUS['ACC'] && $saleReturnId !== null,
            ],
        ]);
    }

    public function transaksiAddDetail(Request $request, $id)
    {
        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        if (!$this->menuRight($userId, 'superuser.gudang.quality_control', 'can_update')) {
            return $this->fail('Akun ini tidak punya hak ubah Receiving - Transaksi di Trans.', 403);
        }

        $receiving = Receiving::where('type', Receiving::TYPE['RETURN'])->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving - Transaksi tidak ditemukan.', 404);
        }

        if ((int) $receiving->status !== Receiving::STATUS['ACTIVE']) {
            return $this->fail('Detail hanya bisa ditambah saat status ACTIVE.', 409);
        }

        $validator = \Validator::make($request->all(), [
            'product_pack_id' => 'required|exists:master_products_packaging,id',
            'quantity'        => 'required|numeric|min:0.01',
            'no_batch'        => 'nullable|string|max:50',
            'note'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->fail(implode(' ', $validator->errors()->all()), 422);
        }

        $packId = (string) $request->input('product_pack_id');
        $quantity = (float) $request->input('quantity');

        $available = (float) DB::table('penjualan_retur_detail as d')
            ->join('penjualan_retur as r', 'r.id', '=', 'd.retur_id')
            ->where('r.status', 1)
            ->whereNull('r.deleted_at')
            ->where('r.type', 1)
            ->where('d.product_packaging_id', $packId)
            ->sum('d.qty');

        $used = (float) DB::table('receiving_detail')
            ->where('receiving_id', $receiving->id)
            ->where('product_packaging_id', $packId)
            ->sum('quantity_po');

        $remaining = $available - $used;

        if ($quantity > $remaining) {
            return $this->fail('Jumlah melebihi qty retur yang tersedia (' . rtrim(rtrim(number_format($remaining, 2, ',', '.'), '0'), ',') . ').', 422);
        }

        $returId = DB::table('penjualan_retur_detail as d')
            ->join('penjualan_retur as r', 'r.id', '=', 'd.retur_id')
            ->where('r.status', 1)
            ->whereNull('r.deleted_at')
            ->where('r.type', 1)
            ->where('d.product_packaging_id', $packId)
            ->orderBy('r.id')
            ->value('r.id');

        $detail = new \App\Entities\Gudang\QualityControlDetail;
        $detail->receiving_id = $receiving->id;
        $detail->po_id = $returId;
        $detail->product_packaging_id = $packId;
        $detail->quantity_po = $quantity;
        $detail->no_batch = $request->input('no_batch');
        $detail->note = $request->input('note');
        $detail->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail berhasil ditambahkan.',
            'data'    => ['id' => (int) $detail->id],
        ]);
    }

    public function transaksiDeleteDetail(Request $request, $id, $detailId)
    {
        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        if (!$this->menuRight($userId, 'superuser.gudang.quality_control', 'can_delete')) {
            return $this->fail('Akun ini tidak punya hak hapus Receiving - Transaksi di Trans.', 403);
        }

        $receiving = Receiving::where('type', Receiving::TYPE['RETURN'])->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving - Transaksi tidak ditemukan.', 404);
        }

        if ((int) $receiving->status !== Receiving::STATUS['ACTIVE']) {
            return $this->fail('Detail hanya bisa dihapus saat status ACTIVE.', 409);
        }

        $detail = \App\Entities\Gudang\QualityControlDetail::where('receiving_id', $receiving->id)->find((int) $detailId);

        if (!$detail) {
            return $this->fail('Detail tidak ditemukan.', 404);
        }

        $detail->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail berhasil dihapus.',
        ]);
    }

    public function transaksiStoreQc(Request $request, $id, $detailId)
    {
        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        if (!$this->menuRight($userId, 'superuser.gudang.quality_control', 'can_update')) {
            return $this->fail('Akun ini tidak punya hak ubah Receiving - Transaksi di Trans.', 403);
        }

        $receiving = Receiving::where('type', Receiving::TYPE['RETURN'])->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving - Transaksi tidak ditemukan.', 404);
        }

        if ((int) $receiving->status !== Receiving::STATUS['QC']) {
            return $this->fail('QC hanya bisa diinput saat status QC.', 409);
        }

        $detail = \App\Entities\Gudang\QualityControlDetail::where('receiving_id', $receiving->id)->find((int) $detailId);

        if (!$detail) {
            return $this->fail('Detail tidak ditemukan.', 404);
        }

        $validator = \Validator::make($request->all(), [
            'qty_qc'    => 'required|numeric|min:0.1',
            'status_qc' => 'required|in:OK,NOT OK,Not OK',
        ]);

        if ($validator->fails()) {
            return $this->fail(implode(' ', $validator->errors()->all()), 422);
        }

        $qty = (float) $request->input('qty_qc');
        $existing = (float) $detail->qcLogs()->sum('qty_qc');

        if ($existing + $qty > (float) $detail->quantity_po) {
            $sisa = (float) $detail->quantity_po - $existing;

            return $this->fail('Total QC melebihi qty retur. Sisa maksimal: ' . rtrim(rtrim(number_format($sisa, 2, ',', '.'), '0'), ',') . '.', 422);
        }

        $log = \App\Entities\Gudang\QualityControlLogs::create([
            'receiving_details_id' => $detail->id,
            'product_packaging_id' => $detail->product_packaging_id,
            'qty_qc'               => $qty,
            'status_qc'            => strtoupper($request->input('status_qc')) === 'OK' ? 1 : 0,
            'is_sellable'          => 0,
            'is_approved'          => 0,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'QC berhasil disimpan.',
            'data'    => ['id' => (int) $log->id],
        ]);
    }

    public function transaksiDeleteQc(Request $request, $id, $detailId, $logId)
    {
        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        if (!$this->menuRight($userId, 'superuser.gudang.quality_control', 'can_delete')) {
            return $this->fail('Akun ini tidak punya hak hapus Receiving - Transaksi di Trans.', 403);
        }

        $receiving = Receiving::where('type', Receiving::TYPE['RETURN'])->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving - Transaksi tidak ditemukan.', 404);
        }

        if ((int) $receiving->status !== Receiving::STATUS['QC']) {
            return $this->fail('Log QC hanya bisa dihapus saat status QC.', 409);
        }

        $detail = \App\Entities\Gudang\QualityControlDetail::where('receiving_id', $receiving->id)->find((int) $detailId);

        if (!$detail) {
            return $this->fail('Detail tidak ditemukan.', 404);
        }

        // Tabel receiving_qc_logs di Trans tidak memakai AUTO_INCREMENT sehingga id
        // banyak bernilai 0; pencocokan dilengkapi qty agar baris yang dihapus benar.
        $query = \App\Entities\Gudang\QualityControlLogs::where('receiving_details_id', $detail->id)
            ->where('id', (int) $logId);

        if ($request->has('qty_qc')) {
            $query->where('qty_qc', (float) $request->input('qty_qc'));
        }

        $log = $query->orderByDesc('created_at')->first();

        if (!$log) {
            return $this->fail('Log QC tidak ditemukan.', 404);
        }

        if ($log->is_sellable && $log->is_approved) {
            return $this->fail('Tidak dapat menghapus log QC yang sudah masuk ke stok.', 409);
        }

        $log->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Log QC berhasil dihapus.',
        ]);
    }

    public function transaksiPublish(Request $request, $id)
    {
        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');

        if (!$this->menuRight($userId, 'superuser.gudang.quality_control', 'can_update')) {
            return $this->fail('Akun ini tidak punya hak ubah Receiving - Transaksi di Trans.', 403);
        }

        $receiving = Receiving::where('type', Receiving::TYPE['RETURN'])->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving - Transaksi tidak ditemukan.', 404);
        }

        $result = app(ReceivingService::class)->publishReceiving($receiving->id);

        if (!empty($result['error'])) {
            return $this->fail($result['error'], 422);
        }

        return response()->json([
            'status'  => 'success',
            'message' => $result['message'],
            'data'    => ['id' => (int) $receiving->id, 'status' => $receiving->fresh()->status()],
        ]);
    }

    public function transaksiAcc(Request $request, $id)
    {
        return $this->fail('ACC Receiving - Transaksi dilakukan di modul Trans oleh Admin.', 403);
    }

    /**
     * Surat Jalan komplain (PDF) untuk receiving retur yang sudah ACC.
     */
    public function transaksiPdf(Request $request, $id)
    {
        $receiving = Receiving::where('type', Receiving::TYPE['RETURN'])->find((int) $id);

        if (!$receiving) {
            return $this->fail('Receiving - Transaksi tidak ditemukan.', 404);
        }

        $saleReturnId = DB::table('receiving_detail')->where('receiving_id', $receiving->id)->value('po_id');
        $saleReturn = $saleReturnId ? \App\Entities\Penjualan\SaleReturn::find($saleReturnId) : null;

        if (!$saleReturn) {
            return $this->fail('Data retur untuk surat jalan tidak ditemukan.', 404);
        }

        $pdf = \PDF::loadView('superuser.penjualan.sale_return.pdf_sj', ['result' => $saleReturn])
            ->setPaper('a5', 'landscape');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $saleReturn->code . '.pdf"',
        ]);
    }

    private function menuRight($userId, $routeName, $column)
    {
        $user = Superuser::find((int) $userId);

        if (!$user) {
            return false;
        }

        if ((int) $user->is_superuser === 1) {
            return true;
        }

        return UserMenu::where('user_id', $user->id)
            ->where($column, 1)
            ->whereHas('menu', function ($query) use ($routeName) {
                $query->where('route_name', $routeName);
            })
            ->exists();
    }

    private function canUpdate($userId)
    {
        $user = Superuser::find((int) $userId);

        if (!$user) {
            return false;
        }

        if ((int) $user->is_superuser === 1) {
            return true;
        }

        return UserMenu::where('user_id', $user->id)
            ->where('can_update', 1)
            ->whereHas('menu', function ($query) {
                $query->where('route_name', 'superuser.gudang.receiving');
            })
            ->exists();
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

        // Daftar penerimaan: hanya tahun berjalan, default bulan berjalan.
        $month = trim((string) $request->query('month'));
        $current = date('Y-m');

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) || substr($month, 0, 4) !== substr($current, 0, 4)) {
            $month = $current;
        }

        $monthStart = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $counts = [];
        foreach ($this->statuses as $label => $value) {
            $query = Receiving::where('receiving.type', $type)
                ->where('receiving.status', $value)
                ->leftJoin('master_warehouses', 'master_warehouses.id', '=', 'receiving.warehouse_id');
            $query->whereBetween('receiving.pbm_date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
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

        $paginator->whereBetween('receiving.pbm_date', [$monthStart->toDateString(), $monthEnd->toDateString()]);

        $this->applySearch($paginator, $search);

        $paginator = $paginator->orderBy('receiving.pbm_date', 'desc')
            ->orderBy('receiving.id', 'desc')
            ->paginate(10);

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

        $userId = (string) ($request->attributes->get('wms_user')['sub'] ?? '');
        $menuRoute = $type === Receiving::TYPE['RETURN']
            ? 'superuser.gudang.quality_control'
            : 'superuser.gudang.receiving';

        return response()->json([
            'status' => 'success',
            'data'   => [
                'generated_at' => date('c'),
                'status'       => $status,
                'search'       => $search,
                'month'        => $month,
                'tabs'         => $tabs,
                'rows'         => $rows,
                'permissions'  => [
                    'can_update'  => $this->menuRight($userId, $menuRoute, 'can_update'),
                    'can_approve' => $this->menuRight($userId, $menuRoute, 'can_approve'),
                    'can_delete'  => $this->menuRight($userId, $menuRoute, 'can_delete'),
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
