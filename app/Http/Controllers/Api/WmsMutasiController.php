<?php

namespace App\Http\Controllers\Api;

use App\Entities\Account\Superuser;
use App\Entities\Gudang\MutasiOut;
use App\Entities\Gudang\MutasiShowroom;
use App\Entities\Setting\UserMenu;
use App\Http\Controllers\Controller;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ? $paginator->orderBy('penjualan_showroom.tanggal', 'desc')->orderBy('penjualan_showroom.id', 'desc')->paginate(10)
            : $paginator->orderBy('gudang_mutasi_out.date', 'desc')->orderBy('gudang_mutasi_out.id', 'desc')->paginate(10);

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

    /**
     * Detail checker mutasi + langkah aktif (checklist / print / barang).
     */
    public function show(Request $request, $type, $id)
    {
        $mutasi = $this->findMutasi($type, $id);

        if (!$mutasi) {
            return $this->fail('Mutasi tidak ditemukan.', 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $this->detailPayload($type, $mutasi, $this->userId($request)),
        ]);
    }

    /**
     * Step 1: checklist lengkap lalu potong stok fisik gudang asal.
     */
    public function checklist(Request $request, $type, $id)
    {
        $mutasi = $this->findMutasi($type, $id);

        if (!$mutasi) {
            return $this->fail('Mutasi tidak ditemukan.', 404);
        }

        if ((int) $mutasi->status_checked === 1) {
            return $this->fail('Mutasi sudah pernah dichecklist.', 409);
        }

        $details = $type === 'showroom' ? $mutasi->details : $mutasi->mutasiOutDetails;
        $items   = array_map('intval', (array) $request->input('items', []));

        $detailIds = $details->pluck('id')->map(function ($value) {
            return (int) $value;
        })->sort()->values()->all();
        $checkedIds = collect($items)->unique()->sort()->values()->all();

        if ($detailIds !== $checkedIds) {
            return $this->fail('Checklist harus lengkap. Semua produk dalam mutasi wajib dicentang.', 422);
        }

        $userId = $this->userId($request);

        DB::beginTransaction();

        try {
            $stockService = app(StockService::class);
            $warehouseId  = $type === 'showroom' ? $mutasi->warehouse_from_id : $mutasi->warehouse_from;

            foreach ($details as $detail) {
                $detail->is_checked = 1;
                $detail->save();

                $qty = $type === 'showroom' ? (float) ($detail->qty ?? 0) : (float) ($detail->quantity ?? 0);

                if ($qty > 0 && $detail->product_packaging_id) {
                    $stockService->deductPhysicalStock($warehouseId, $detail->product_packaging_id, $qty);
                }
            }

            $mutasi->status_checked = 1;
            $mutasi->updated_by     = $userId;
            $mutasi->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->fail($e->getMessage() ?: 'Gagal menyimpan checklist.', 422);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Checklist tersimpan. Lanjut cetak surat jalan.',
        ]);
    }

    /**
     * Batalkan checklist: kembalikan stok fisik lalu tandai CANCELED.
     */
    public function cancel(Request $request, $type, $id)
    {
        $mutasi = $this->findMutasi($type, $id);

        if (!$mutasi) {
            return $this->fail('Mutasi tidak ditemukan.', 404);
        }

        if ((int) $mutasi->status_checked !== 1) {
            return $this->fail('Status tidak valid untuk pembatalan.', 409);
        }

        $details = $type === 'showroom' ? $mutasi->details : $mutasi->mutasiOutDetails;
        $userId  = $this->userId($request);

        DB::beginTransaction();

        try {
            $stockService = app(StockService::class);
            $warehouseId  = $type === 'showroom' ? $mutasi->warehouse_from_id : $mutasi->warehouse_from;

            foreach ($details as $detail) {
                if (!$detail->is_checked) {
                    continue;
                }

                $qty = $type === 'showroom' ? (float) ($detail->qty ?? 0) : (float) ($detail->quantity ?? 0);

                if ($qty > 0) {
                    $stockService->undoDeductPhysicalStock($warehouseId, $detail->product_packaging_id, $qty);
                }

                $detail->is_checked = 0;
                $detail->save();
            }

            $mutasi->status_checked = 2;
            $mutasi->print_count    = 0;
            $mutasi->updated_by     = $userId;
            $mutasi->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->fail($e->getMessage() ?: 'Gagal membatalkan checklist.', 422);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Checklist dibatalkan dan stok dikembalikan.',
        ]);
    }

    /**
     * Step 2 -> Step 3: surat jalan wajib sudah dicetak dan ada item tercentang.
     */
    public function next(Request $request, $type, $id)
    {
        $mutasi = $this->findMutasi($type, $id);

        if (!$mutasi) {
            return $this->fail('Mutasi tidak ditemukan.', 404);
        }

        if ((int) $mutasi->print_count === 0) {
            return $this->fail('Surat Jalan belum dicetak.', 422);
        }

        $details = $type === 'showroom' ? $mutasi->details : $mutasi->mutasiOutDetails;

        if ($details->where('is_checked', 1)->count() === 0) {
            return $this->fail('Belum ada produk yang dichecklist.', 422);
        }

        if ((int) $mutasi->status_barang === 3) {
            return $this->fail('Mutasi sudah selesai diproses.', 422);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Lanjut ke update status barang.',
        ]);
    }

    /**
     * Step 3: update status barang (belum diambil / diambil + bukti foto).
     */
    public function updateBarang(Request $request, $type, $id)
    {
        $mutasi = $this->findMutasi($type, $id);

        if (!$mutasi) {
            return $this->fail('Mutasi tidak ditemukan.', 404);
        }

        $newStatus = (int) $request->input('status_barang');
        if (!in_array($newStatus, [1, 2], true)) {
            return $this->fail('Status barang tidak valid.', 422);
        }

        $oldStatus = (int) $mutasi->status_barang;

        if ($oldStatus === 2) {
            return $this->fail('Mutasi sudah DIAMBIL dan tidak bisa diubah.', 409);
        }

        if ($oldStatus === 1 && $newStatus !== 2) {
            return $this->fail('Status hanya bisa diubah ke DIAMBIL.', 422);
        }

        if ($newStatus === 2) {
            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);
        }

        $details = $type === 'showroom' ? $mutasi->details : $mutasi->mutasiOutDetails;
        $userId  = $this->userId($request);
        $folder  = $type === 'showroom' ? 'mutasi_showroom' : 'mutasi_out';

        DB::beginTransaction();

        try {
            if ($newStatus === 2 && $request->hasFile('image')) {
                $destination = storage_path('app/public/' . $folder);

                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                $file = $request->file('image');
                $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($destination, $filename);

                $mutasi->image    = $folder . '/' . $filename;
                $mutasi->taken_at = now();
            }

            $mutasi->status_barang = $newStatus;

            if ($type === 'gudang') {
                if ($newStatus === 1) {
                    $mutasi->status = 2;
                }
                if ($newStatus === 2) {
                    $mutasi->status = 3;
                }
            }

            $mutasi->save();

            if ($newStatus === 2 && $oldStatus !== 2) {
                $stockService = app(StockService::class);
                $warehouseId  = $type === 'showroom' ? $mutasi->warehouse_from_id : $mutasi->warehouse_from;

                foreach ($details as $detail) {
                    $quantity = $type === 'showroom' ? $detail->qty : $detail->quantity;
                    $code     = $type === 'showroom' ? $mutasi->kode : $mutasi->code;
                    $note     = $type === 'showroom'
                        ? 'Mutasi Showroom - DIAMBIL'
                        : 'Mutasi Out - ' . (optional($mutasi->warehouse_to_attribute)->name ?? '-') . ' - DIAMBIL';

                    $stockService->recordAdministrativeLog(
                        $warehouseId,
                        $detail->product_packaging_id,
                        (float) $quantity,
                        $code,
                        $note,
                        now()
                    );
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->fail($e->getMessage() ?: 'Gagal update status barang.', 422);
        }

        return response()->json([
            'status'     => 'success',
            'message'    => $newStatus === 2 ? 'Barang sudah DIAMBIL dan stok tercatat.' : 'Status barang: BELUM DIAMBIL.',
            'to_selesai' => $newStatus === 2,
        ]);
    }

    /**
     * Surat jalan (PDF) mutasi; menaikkan print_count seperti di Trans.
     */
    public function pdf(Request $request, $type, $id)
    {
        $printMenu = $type === 'showroom'
            ? 'superuser.gudang.mutasi_showroom'
            : 'superuser.gudang.mutasi_out';

        if (!$this->menuRight($request, 'can_print', $printMenu)
            && !$this->menuRight($request, 'can_print', 'superuser.gudang.sj_mutasi_internal')) {
            return $this->fail('Akun ini tidak punya hak cetak Mutasi di Trans.', 403);
        }

        $mutasi = $this->findMutasi($type, $id);

        if (!$mutasi) {
            return $this->fail('Mutasi tidak ditemukan.', 404);
        }

        if ((int) $mutasi->print_count >= 2 && $this->userId($request) !== 1) {
            return $this->fail('Dokumen ini sudah dicetak maksimal 2 kali.', 403);
        }

        $mutasi->increment('print_count');

        if (empty($mutasi->printed_at)) {
            $mutasi->updated_by = $this->userId($request);
            $mutasi->printed_at = now();
            $mutasi->save();
        }

        $view = $type === 'showroom'
            ? 'superuser.gudang.mutasi_showroom.print_pdf'
            : 'superuser.gudang.mutasi_out.print_pdf';

        $pdf = \PDF::loadView($view, compact('mutasi'))->setPaper('A5', 'landscape');

        $code = $type === 'showroom' ? $mutasi->kode : $mutasi->code;

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="mutasi-' . $code . '.pdf"',
        ]);
    }

    /**
     * Bukti foto barang diambil.
     */
    public function image(Request $request, $type, $id)
    {
        $mutasi = $this->findMutasi($type, $id);

        if (!$mutasi || empty($mutasi->image)) {
            return $this->fail('Bukti foto tidak ditemukan.', 404);
        }

        $path = storage_path('app/public/' . $mutasi->image);

        if (!file_exists($path)) {
            return $this->fail('Berkas bukti tidak ditemukan.', 404);
        }

        return response()->file($path);
    }

    private function findMutasi($type, $id)
    {
        if ($type === 'showroom') {
            return MutasiShowroom::with([
                'details.product_packaging.product',
                'details.product_packaging.packaging',
                'customer_other_address',
                'so',
                'warehouse_from',
            ])->find((int) $id);
        }

        if ($type === 'gudang') {
            return MutasiOut::with([
                'mutasiOutDetails.product_pack.product',
                'mutasiOutDetails.product_pack.packaging',
                'warehouse_from_attribute',
                'warehouse_to_attribute',
            ])->find((int) $id);
        }

        return null;
    }

    private function detailPayload($type, $mutasi, $userId)
    {
        $details = $type === 'showroom' ? $mutasi->details : $mutasi->mutasiOutDetails;

        if ((int) $mutasi->status_checked === 0 || (int) $mutasi->status_checked === 2) {
            $step = 1;
        } elseif ((int) $mutasi->status_checked === 1 && (int) $mutasi->print_count === 0) {
            $step = 2;
        } else {
            $step = 3;
        }

        $rows = [];
        $no = 0;

        foreach ($details as $detail) {
            $pack = $type === 'showroom' ? $detail->product_packaging : $detail->product_pack;

            $rows[] = [
                'no'          => ++$no,
                'id'          => (int) $detail->id,
                'product'     => trim(($pack->code ?? '-') . ' - ' . (optional($pack->product)->name ?? '-'), ' -'),
                'packaging'   => optional($pack->packaging)->pack_name ?? '-',
                'qty'         => (float) ($type === 'showroom' ? $detail->qty : $detail->quantity),
                'is_checked'  => (int) $detail->is_checked === 1,
            ];
        }

        if ($type === 'showroom') {
            $customer = optional($mutasi->customer_other_address);
            $typeLabel = $mutasi->type();

            $header = [
                'code'       => $mutasi->kode,
                'date'       => $mutasi->tanggal ? date('d/m/Y', strtotime($mutasi->tanggal)) : '-',
                'type_label' => is_string($typeLabel) ? $typeLabel : '-',
                'customer'   => trim(($customer->name ?: 'SHOWROOM') . ' ' . (string) $customer->text_kota),
                'so'         => optional($mutasi->so)->code ?: '-',
                'warehouse'  => optional($mutasi->warehouse_from)->name ?: '-',
                'warehouse_to' => null,
            ];
        } else {
            $header = [
                'code'       => $mutasi->code,
                'date'       => $mutasi->date ? date('d/m/Y', strtotime($mutasi->date)) : '-',
                'type_label' => 'Mutasi Gudang',
                'customer'   => '-',
                'so'         => '-',
                'warehouse'  => optional($mutasi->warehouse_from_attribute)->name ?: '-',
                'warehouse_to' => optional($mutasi->warehouse_to_attribute)->name ?: '-',
            ];
        }

        $header['id']            = (int) $mutasi->id;
        $header['status_checked'] = (int) $mutasi->status_checked;
        $header['status_barang']  = (int) $mutasi->status_barang;
        $header['print_count']    = (int) $mutasi->print_count;
        $header['has_image']      = !empty($mutasi->image);
        $header['image_url']      = !empty($mutasi->image)
            ? '/api/wms/mutasi/' . $type . '/' . (int) $mutasi->id . '/image'
            : null;

        // Setara modul Trans: aksi checker tidak dibatasi hak per-menu di controller,
        // akses halaman yang membatasi.
        return [
            'type'    => $type,
            'step'    => $step,
            'header'  => $header,
            'details' => $rows,
            'flags'   => [
                'can_checklist'  => $step === 1,
                'can_print'      => $step === 2,
                'can_cancel'     => $step === 2,
                'can_next'       => $step === 2,
                'can_update'     => $step === 3 && (int) $mutasi->status_barang !== 2,
                'can_view_image' => !empty($mutasi->image),
                'print_blocked'  => (int) $mutasi->print_count >= 2 && (int) $userId !== 1,
            ],
        ];
    }

    private function menuRight(Request $request, $column, $routeName)
    {
        return $this->menuRightByUser($this->userId($request), $column, $routeName);
    }

    private function menuRightByUser($userId, $column, $routeName)
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

    private function userId(Request $request)
    {
        $payload = $request->attributes->get('wms_user', []);

        return (int) ($payload['sub'] ?? 0);
    }

    private function fail($message, $code)
    {
        return response()->json(['status' => 'error', 'message' => $message], $code);
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
