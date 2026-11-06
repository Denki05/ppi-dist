<?php

namespace App\Http\Controllers\Api;

use App\Entities\Account\Superuser;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Setting\UserMenu;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Superuser\Penjualan\DeliveryOrderController as TransDeliveryOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Checker Transaksi (DO) untuk modul WMS.
 * Daftar & aksi mengikuti tab Trans: List SPK, Cetak SJ, Update Resi, History Resi.
 *
 * Aksi (print SPK, packed, sending, sent) dieksekusi oleh controller Trans
 * yang sama supaya validasi stok/kurs/log tetap identik dengan UI Trans.
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

        // History Resi dibatasi per bulan; bulan berjalan jadi default.
        $month = trim((string) $request->query('month'));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = '';
        }
        $month = $tab === 'HISTORY' ? ($month ?: date('Y-m')) : '';
        $monthStart = $month !== '' ? \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth() : null;
        $monthEnd   = $monthStart ? $monthStart->copy()->endOfMonth() : null;

        $counts = [];
        foreach ($this->tabs as $label => $status) {
            $query = $this->baseQuery($search, $status);
            if ($label === 'SPK' && $onlyUnprinted) {
                $query->where(function ($sub) {
                    $sub->whereNull('print_count')->orWhere('print_count', 0);
                });
            }
            if ($label === 'HISTORY' && $monthStart) {
                $query->whereBetween('penjualan_do.created_at', [$monthStart, $monthEnd]);
            }
            $counts[$label] = $query->count();
        }

        $paginator = $this->baseQuery($search, $this->tabs[$tab]);

        if ($onlyUnprinted) {
            $paginator->where(function ($sub) {
                $sub->whereNull('print_count')->orWhere('print_count', 0);
            });
        }

        if ($monthStart) {
            $paginator->whereBetween('penjualan_do.created_at', [$monthStart, $monthEnd]);
        }

        $paginator = $paginator->orderBy('penjualan_do.id', 'desc')->paginate(10);

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
                'has_payment' => (bool) ($row->has_payment ?? false),
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
                'month'        => $month !== '' ? $month : null,
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
     * Detail DO + hak aksi sesuai status.
     */
    public function show(Request $request, $id)
    {
        // 'member' sengaja tidak di-eager load: primary key alamat kirim
        // berupa string desimal (mis. 37.1) dan eager load Eloquent
        // membulatkannya menjadi 37 sehingga relasinya kosong.
        $do = PackingOrder::with([
            'customer',
            'warehouse',
            'vendor',
            'so',
            'do_detail_cost',
            'do_detail.product_pack.packaging.unit',
        ])->find($id);

        if (!$do) {
            return $this->fail('DO tidak ditemukan.', 404);
        }

        $status = (int) $do->status;
        $cost = $do->do_detail_cost;
        $items = [];

        foreach ($do->do_detail as $index => $row) {
            $pack = $row->product_pack;
            $packValue = (float) ($pack->packaging->pack_value ?? 0);

            $items[] = [
                'no'            => $index + 1,
                'id'            => (int) $row->id,
                'product_code'  => $pack->code ?? '-',
                'product_name'  => $pack->name ?? '-',
                'packaging'     => $pack->packaging->pack_name ?? '-',
                'qty'           => (float) $row->qty,
                'total_packing' => $packValue > 0 ? round((float) $row->qty / $packValue, 2) : 0,
                'note'          => (string) ($row->note ?? ''),
            ];
        }

        $isKursHold = (bool) $do->is_kurs_hold;
        $voidStatus = (bool) $do->void_status;

        $actions = [
            'can_print_spk' => $status === 3,
            'can_pack'      => $status === 3,
            'can_deliver'   => $status === 4 && !$isKursHold,
            'can_sent'      => $status === 5 && !$voidStatus,
            'deliver_blocked' => $status === 4 && $isKursHold
                ? 'Kurs IDR belum di-set. Update kurs di SO Progress sebelum membuat surat jalan.'
                : '',
            'sent_blocked'  => $status === 5 && $voidStatus
                ? 'DO ini sedang dalam pengajuan void. Koordinasi dengan Finance sebelum update resi.'
                : '',
        ];

        if ($status === 5 && $do->type_transaction === 'CASH' && !$this->hasPayment($do)) {
            $actions['can_sent'] = false;
            $actions['sent_blocked'] = 'Pembayaran CASH belum tercatat. Update resi dikunci.';
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'generated_at' => date('c'),
                'header' => [
                    'id'                => (int) $do->id,
                    'do_code'           => $do->do_code ?: $do->code,
                    'so_code'           => $do->so->code ?? '-',
                    'status_code'       => $status,
                    'status'            => $this->statusLabel($status),
                    'warehouse'         => $do->warehouse->name ?? '-',
                    // Trans menampilkan customer dari alamat kirim (member),
                    // bukan nama master customer.
                    'customer'          => $do->member->name ?? ($do->customer->name ?? '-'),
                    'customer_address'  => $do->customer->address ?? '-',
                    'contact_label'     => $do->member->contact_person ?: ($do->member->pic ?: '-'),
                    'contact_address'   => $do->member->address ?? '-',
                    'contact_phone'     => $do->member->phone ?? '-',
                    'ekspedisi'         => $do->vendor->name ?? '-',
                    'type_transaction'  => $do->type_transaction,
                    'created_at'        => $do->created_at ? date('d/m/Y H:i', strtotime($do->created_at)) : '-',
                    'date_sent'         => $do->date_sent ? date('d/m/Y', strtotime($do->date_sent)) : '-',
                    'print_count'       => (int) $do->print_count,
                    'is_kurs_hold'      => $isKursHold,
                    'void_status'       => $voidStatus,
                    'image_url'         => $this->fileUrl($do->image),
                    'image2_url'        => $this->fileUrl($do->image2),
                ],
                'items'   => $items,
                'cost'    => [
                    'delivery_cost_note' => (string) ($cost->delivery_cost_note ?? ''),
                    'delivery_cost_idr'  => (float) ($cost->delivery_cost_idr ?? 0),
                    'other_cost_note'    => (string) ($cost->other_cost_note ?? ''),
                    'other_cost_idr'     => (float) ($cost->other_cost_idr ?? 0),
                    'grand_total_idr'    => (float) ($cost->grand_total_idr ?? 0),
                ],
                'actions' => $actions,
            ],
        ]);
    }

    /**
     * Print SPK (PDF) — sama dengan tombol Print SPK di Trans,
     * termasuk menaikkan print_count.
     */
    public function printManifest(Request $request, $id)
    {
        if (!$this->canDo($request, 'can_print')) {
            return $this->fail('Akun ini tidak punya hak cetak SPK di Trans.', 403);
        }

        if (!PackingOrder::where('id', $id)->exists()) {
            return $this->fail('DO tidak ditemukan.', 404);
        }

        $this->loginTransUser($request);

        try {
            return $this->transController()->print_manifest($request, $id);
        } catch (\Throwable $e) {
            return $this->fail('Print SPK gagal: ' . $e->getMessage());
        }
    }

    /**
     * Cetak SJ — sama dengan tombol Print DO di Trans (Crystal do.rpt).
     * File hasil export dibaca ulang dan dikirim sebagai application/pdf.
     */
    public function printSuratJalan(Request $request, $id)
    {
        if (!$this->canDo($request, 'can_print')) {
            return $this->fail('Akun ini tidak punya hak cetak SJ di Trans.', 403);
        }

        $do = PackingOrder::find($id);

        if (!$do) {
            return $this->fail('DO tidak ditemukan.', 404);
        }

        $this->loginTransUser($request);

        try {
            $path = $this->transController()->exportSuratJalanPdf($id);
        } catch (\Throwable $e) {
            return $this->fail('Cetak SJ gagal: ' . $e->getMessage());
        }

        if (!is_file($path)) {
            return $this->fail('Cetak SJ gagal: file export tidak terbentuk.');
        }

        $filename = 'SJ-' . ($do->do_code ?: $do->code ?: $id) . '.pdf';

        return response(file_get_contents($path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Kerjakan: konfirmasi barang lalu status 3 -> 4 (Siap Kirim).
     */
    public function packed(Request $request, $id)
    {
        if (!$this->canDo($request, 'can_approve')) {
            return $this->fail('Akun ini tidak punya hak proses DO di Trans.', 403);
        }

        $do = PackingOrder::with('do_detail')->find($id);

        if (!$do) {
            return $this->fail('DO tidak ditemukan.', 404);
        }

        $itemIds = $do->do_detail->pluck('id')->map(function ($value) {
            return (int) $value;
        })->all();

        if (count($itemIds) === 0) {
            return $this->fail('Tidak ada item untuk diproses.');
        }

        // Server-side memastikan seluruh item ikut dikonfirmasi, sama seperti
        // validasi JS Trans yang mewajibkan semua checklist dicentang.
        $request->merge([
            'id'              => (int) $id,
            'confirmed_items' => $itemIds,
        ]);

        $this->loginTransUser($request);

        try {
            $response = $this->transController()->packed(
                $request,
                app(\App\Services\SalesOrder\SalesOrderClosingService::class)
            );
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }

        return $this->actionResult($response, $id, 4, 'DO berhasil diubah ke Siap Kirim.');
    }

    /**
     * Surat Jalan: status 4 -> 5 (Delivering), potong stok fisik di Trans.
     */
    public function sending(Request $request, $id)
    {
        if (!$this->canDo($request, 'can_approve')) {
            return $this->fail('Akun ini tidak punya hak proses DO di Trans.', 403);
        }

        if (!PackingOrder::where('id', $id)->exists()) {
            return $this->fail('DO tidak ditemukan.', 404);
        }

        $request->merge(['id' => (int) $id]);
        $this->loginTransUser($request);

        try {
            $response = $this->transController()->sending($request);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }

        return $this->actionResult($response, $id, 5, 'DO berhasil diubah ke Delivering.');
    }

    /**
     * Update Resi: status 5 -> 6 (Delivered), simpan bukti kirim & biaya.
     */
    public function sent(Request $request, $id)
    {
        if (!$this->canDo($request, 'can_approve')) {
            return $this->fail('Akun ini tidak punya hak proses DO di Trans.', 403);
        }

        $do = PackingOrder::with(['do_detail_cost', 'vendor'])->find($id);

        if (!$do) {
            return $this->fail('DO tidak ditemukan.', 404);
        }

        $cost = $do->do_detail_cost;

        // Nilai ongkir & catatan ongkir mengikuti data DO (readonly di UI),
        // supaya tidak bisa ditimpa dari request.
        $request->merge([
            'do_id'              => (int) $id,
            'delivery_cost_note' => (string) ($cost->delivery_cost_note ?? ($do->vendor->name ?? '')),
            'delivery_cost_idr'  => (float) ($cost->delivery_cost_idr ?? 0),
            'other_cost_idr'     => $request->input('other_cost_idr', 0),
            'other_cost_note'    => (string) $request->input('other_cost_note', ''),
        ]);

        $this->loginTransUser($request);

        try {
            $response = $this->transController()->sent($request);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }

        return $this->actionResult($response, $id, 6, 'DO berhasil update resi.');
    }

    /**
     * Kinerja logistik untuk dashboard WMS, per bulan.
     * Bulan berjalan (sejak tanggal 1) dibandingkan dengan bulan lalu.
     * Kecepatan = DO dibuat sampai berangkat (date_sent, status >= 5);
     * pergeseran update resi (status 6) memakai updated_at record.
     * Ditambah aging: dokumen yang belum selesai terlama (status 3/4/5)
     * dan DO berangkat > 5 hari tanpa resi.
     */
    public function performance(Request $request)
    {
        $now = now();

        // Riwayat 6 bulan terakhir (bulan berjalan paling baru). Satu respons
        // berisi semuanya supaya filter bulan di dashboard bisa dipilih tanpa reload.
        $months = [];
        $startBase = $now->copy()->startOfMonth();
        for ($i = 0; $i < 6; $i++) {
            $start = $startBase->copy()->subMonths($i);
            $metrik = $this->metricsPeriode($start, $start->copy()->endOfMonth());
            $metrik['month'] = $start->format('Y-m');
            $months[$start->format('Y-m')] = $metrik;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'generated_at' => date('c'),
                'months'       => $months,
                'aging'        => $this->agingPending(),
            ],
        ]);
    }

    /**
     * Metrik kinerja untuk satu periode berangkat DO (partial compare by date_sent).
     */
    private function metricsPeriode($start, $end)
    {
        $berangkat = DB::table('penjualan_do')
            ->whereNotNull('date_sent')
            ->where('date_sent', '!=', '0000-00-00')
            ->where('date_sent', '>=', $start->toDateString())
            ->where('date_sent', '<=', $end->toDateString())
            ->get(['id', 'do_code', 'created_at', 'date_sent', 'updated_at', 'status']);

        $created = DB::table('penjualan_do')
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->count();

        $resiDone = 0;
        $within1Day = 0;
        $sumBerangkat = 0.0;
        $sumSampaiResi = 0.0;
        $nSampaiResi = 0;
        $best = null;

        foreach ($berangkat as $row) {
            $tsBerangkat = strtotime($row->date_sent);
            $tsDibuat    = $row->created_at ? strtotime($row->created_at) : $tsBerangkat;
            $tsResi      = $row->updated_at ? strtotime($row->updated_at) : null;

            $durasi = max(0.0, ($tsBerangkat - $tsDibuat) / 86400);

            $sumBerangkat += $durasi;
            if ($durasi <= 1) {
                $within1Day++;
            }

            if ((int) $row->status === 6 && $tsResi !== null) {
                $resiDone++;
                $sumSampaiResi += max(0.0, ($tsResi - $tsBerangkat) / 86400);
                $nSampaiResi++;
            }

            if ($best === null || $durasi < $best['hari']) {
                $best = [
                    'code' => $row->do_code ?: ('DO-' . $row->id),
                    'hari' => round($durasi, 1),
                ];
            }
        }

        $berangkatBase = $berangkat->count();

        return [
            'created'            => (int) $created,
            'depart'             => $berangkatBase,
            'resi_done'          => $resiDone,
            'avg_days_to_depart' => $berangkatBase ? round($sumBerangkat / $berangkatBase, 2) : null,
            'within_1day'        => $within1Day,
            'within_1day_pct'    => $berangkatBase ? (int) round(100 * $within1Day / $berangkatBase) : null,
            'avg_days_resi_lag'  => $nSampaiResi ? round($sumSampaiResi / $nSampaiResi, 2) : null,
            'fastest'            => $best,
        ];
    }

    /**
     * Aging dokumen pending: status 3 (List SPK), 4 (Cetak SJ), 5 (Update Resi),
     * plus DO berangkat > 5 hari tanpa resi.
     */
    private function agingPending()
    {
        $now = now();

        $aging = [];
        foreach ([3 => 'list_spk', 4 => 'cetak_sj', 5 => 'update_resi'] as $status => $key) {
            $table = DB::table('penjualan_do')->where('status', $status)
                ->whereNotNull('created_at')
                ->where('created_at', '!=', '0000-00-00 00:00:00');

            $count = (clone $table)->count();
            $oldest = (clone $table)->orderBy('created_at')->first(['id', 'do_code', 'created_at']);

            $aging[$key] = [
                'count'       => (int) $count,
                'oldest_days' => $oldest ? max(0, $now->diffInDays(\Carbon\Carbon::parse($oldest->created_at))) : null,
                'oldest_code' => $oldest ? ($oldest->do_code ?: ('DO-' . $oldest->id)) : null,
            ];
        }

        // DO sudah berangkat tetapi update resi molor.
        $late = DB::table('penjualan_do')
            ->where('status', 5)
            ->whereNotNull('date_sent')
            ->where('date_sent', '!=', '0000-00-00')
            ->where('date_sent', '<', $now->copy()->subDays(5)->toDateString())
            ->orderBy('date_sent')
            ->get(['id', 'do_code', 'date_sent']);

        $aging['resi_late'] = [
            'count'       => $late->count(),
            'oldest_days' => $late->count() ? max(0, $now->diffInDays(\Carbon\Carbon::parse($late->first()->date_sent))) : null,
            'oldest_code' => $late->count() ? ($late->first()->do_code ?: ('DO-' . $late->first()->id)) : null,
        ];

        return $aging;
    }

    private function baseQuery($search, $status)
    {
        $columns = [
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
            'customer.text_kota as customer_city',
        ];

        // Kolom bisa belum ada di DB produksi (schema drift); jangan sampai
        // seluruh daftar DO gagal hanya karena kolom opsional.
        if ($this->hasColumn('penjualan_do', 'has_payment')) {
            $columns[] = 'penjualan_do.has_payment';
        }

        $query = PackingOrder::select($columns)
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

    private function transController()
    {
        return app(TransDeliveryOrderController::class)->allowWms();
    }

    /**
     * Hak menu DO milik user token WMS, disamakan dengan guard Trans.
     */
    private function canDo(Request $request, $column)
    {
        $payload = $request->attributes->get('wms_user', []);
        $user = Superuser::find((int) ($payload['sub'] ?? 0));

        if (!$user) {
            return false;
        }

        if ((int) $user->is_superuser === 1) {
            return true;
        }

        return UserMenu::where('user_id', $user->id)
            ->where($column, 1)
            ->whereHas('menu', function ($query) {
                $query->where('route_name', 'superuser.penjualan.delivery_order');
            })
            ->exists();
    }

    /**
     * Aksi Trans memakai Auth::user(); API WMS hanya punya token, jadi user
     * superuser Trans di-set untuk request ini saja.
     */
    private function loginTransUser(Request $request)
    {
        $payload = $request->attributes->get('wms_user');
        $user = Superuser::find($payload['sub'] ?? null);

        if ($user) {
            Auth::guard('superuser')->setUser($user);
            Auth::shouldUse('superuser');
        }
    }

    private function actionResult($response, $id, $expectedStatus, $successMessage)
    {
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $body = $response->getData(true);

            if (!empty($body['IsError'])) {
                return $this->fail($body['Message'] ?? 'Aksi gagal.');
            }
        }

        $success = session()->pull('success');
        $error = session()->pull('error');

        // Error dari guard Trans (mis. DO sudah diproses / kurs belum di-set)
        // harus menang walau status kebetulan sudah sama dengan target.
        if (!empty($error)) {
            return $this->fail($error, 422);
        }

        $do = PackingOrder::find($id);
        $status = $do ? (int) $do->status : null;

        if ($status === $expectedStatus) {
            return response()->json([
                'status'  => 'success',
                'message' => $success ?: $successMessage,
                'data'    => [
                    'status_code' => $status,
                    'status'      => $this->statusLabel($status),
                ],
            ]);
        }

        return $this->fail($success ?: 'Aksi gagal diproses.', 422);
    }

    private function fileUrl($path)
    {
        if (empty($path)) {
            return null;
        }

        $path = ltrim($path, '/');

        if (strpos($path, 'images/') !== 0) {
            $path = 'images/delivery_order/expedition_receipt/' . $path;
        }

        return rtrim(config('app.url'), '/') . '/' . $path;
    }

    private function fail($message, $code = 422)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], $code);
    }

    /**
     * Kolom opsional (mis. has_payment) belum tentu ada di DB produksi.
     */
    private function hasColumn($table, $column)
    {
        static $cache = [];

        $key = $table . '.' . $column;

        if (!array_key_exists($key, $cache)) {
            $cache[$key] = \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        }

        return $cache[$key];
    }

    private function hasPayment($do)
    {
        if (!$this->hasColumn('penjualan_do', 'has_payment')) {
            return true;
        }

        return (bool) $do->has_payment;
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
