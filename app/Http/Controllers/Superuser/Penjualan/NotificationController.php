<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    /**
     * Daftar tipe notifikasi yang ditampilkan (satu sumber supaya bell,
     * halaman, dan statistik tidak pernah beda isi — OTP revisi wajib ikut).
     */
    private function visibleTypes()
    {
        return [
            'App\Notifications\DoNotification',
            'App\Notifications\SoNotification',
            'App\Notifications\PengajuanProformaNotification',
            'App\Notifications\PayableNotification',
            'App\Notifications\ReceivingNotification',
            'App\Notifications\InternalRevisionOtpNotification',
        ];
    }

    /**
     * Filter chip memakai kunci grup (mis. "so") supaya SO Lanjutan/Proforma
     * dan Pengajuan Proforma bisa tampil di satu tombol yang sama.
     */
    private function typeFilterGroups()
    {
        return [
            'so' => [
                'App\Notifications\SoNotification',
                'App\Notifications\PengajuanProformaNotification',
            ],
            'do' => ['App\Notifications\DoNotification'],
            'pay' => ['App\Notifications\PayableNotification'],
            'otp' => ['App\Notifications\InternalRevisionOtpNotification'],
        ];
    }

    /**
     * Terima kunci grup maupun FQCN lama (bookmark), kembalikan daftar FQCN.
     */
    private function resolveTypeFilter($type)
    {
        if (!$type) {
            return null;
        }
        if (Str::contains($type, '\\') && in_array($type, $this->visibleTypes(), true)) {
            return [$type];
        }
        $groups = $this->typeFilterGroups();

        return $groups[$type] ?? null;
    }

    /**
     * Label/ikon/warna per tipe. Dipakai bell dan halaman daftar supaya
     * tidak ada dua tempat yang bisa berbeda.
     */
    private function metaFor($type)
    {
        if (Str::contains($type, 'PengajuanProforma')) {
            return ['label' => 'Pengajuan Proforma', 'short' => 'Pengajuan', 'icon' => 'bi-file-earmark-plus', 'color' => 'primary'];
        }
        if (Str::contains($type, 'DoNotification')) {
            return ['label' => 'Delivery Order', 'short' => 'DO', 'icon' => 'bi-truck', 'color' => 'info'];
        }
        if (Str::contains($type, 'SoNotification')) {
            return ['label' => 'Sales Order', 'short' => 'SO', 'icon' => 'bi-cart', 'color' => 'success'];
        }
        if (Str::contains($type, 'PayableNotification')) {
            return ['label' => 'Payment', 'short' => 'Payment', 'icon' => 'bi-credit-card', 'color' => 'secondary'];
        }
        if (Str::contains($type, 'ReceivingNotification')) {
            return ['label' => 'Receiving', 'short' => 'Receiving', 'icon' => 'bi-box-seam', 'color' => 'primary'];
        }
        if (Str::contains($type, 'InternalRevisionOtp')) {
            return ['label' => 'OTP Revisi', 'short' => 'OTP', 'icon' => 'bi-key', 'color' => 'warning'];
        }

        return ['label' => 'Notifikasi', 'short' => 'Notif', 'icon' => 'bi-bell', 'color' => 'primary'];
    }

    /**
     * Tingkat prioritas notifikasi. Satu sumber warna di seluruh tampilan:
     * Mendesak = butuh aksi cepat, Perlu tindakan = menunggu diproses,
     * Informasi = sekadar pemberitahuan.
     */
    private function priorityFor($type, array $data = [])
    {
        $override = $data['priority'] ?? null;
        if (in_array($override, ['urgent', 'action', 'info'], true)) {
            return $override;
        }

        if (Str::contains($type, 'InternalRevisionOtp')) {
            return 'urgent';
        }
        if (Str::contains($type, 'PengajuanProforma')) {
            return 'action';
        }
        if (Str::contains($type, 'SoNotification') || Str::contains($type, 'PayableNotification')) {
            return 'action';
        }

        return 'info';
    }

    private function priorityLabel($key)
    {
        return [
            'urgent' => 'Mendesak',
            'action' => 'Perlu tindakan',
            'info' => 'Informasi',
        ][$key] ?? 'Informasi';
    }

    /**
     * Bentuk satu baris notifikasi untuk JSON maupun halaman.
     */
    private function formatRow($row)
    {
        $data = json_decode($row->data, true);
        if (!is_array($data)) {
            $data = [];
        }

        $created = $row->created_at ? Carbon::parse($row->created_at) : null;
        $priority = $this->priorityFor($row->type, $data);

        return [
            'id' => $row->id,
            'type' => $row->type,
            'label' => $this->metaFor($row->type)['label'],
            'short' => $this->metaFor($row->type)['short'],
            'icon' => $this->metaFor($row->type)['icon'],
            'color' => $this->metaFor($row->type)['color'],
            'priority' => $priority,
            'priority_label' => $this->priorityLabel($priority),
            'data' => $data,
            'is_unread' => is_null($row->read_at),
            'created_at' => $row->created_at,
            'human' => $created ? $created->locale('id')->diffForHumans() : '',
            'date_key' => $created ? $created->toDateString() : '',
        ];
    }

    /**
     * Data untuk dropdown lonceng.
     *
     * Riwayat ikut dikirim (bukan hanya yang belum dibaca) supaya admin tetap
     * bisa melihat notifikasi lama setelah menandai semua dibaca. Badge tetap
     * menghitung unread saja.
     */
    public function getNotifData(Request $request)
    {
        $userId = Auth::id();

        $base = DB::table('notifications')
            ->where('notifiable_id', $userId)
            ->whereIn('type', $this->visibleTypes());

        // Badge selalu menghitung total unread, tidak ikut filter chip.
        $unread = (clone $base)->whereNull('read_at')->count();

        // Hitungan per prioritas dipakai warna badge, banner mendesak, dan
        // ringkasan di popup. Dihitung dari type (bukan per baris) supaya
        // tetap murah walau unread ribuan.
        $unreadByType = (clone $base)
            ->whereNull('read_at')
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        $priorityCounts = ['urgent' => 0, 'action' => 0, 'info' => 0];
        foreach ($unreadByType as $type => $total) {
            $priorityCounts[$this->priorityFor($type)] += (int) $total;
        }

        // Pengingat hanya memuat notifikasi hari ini & kemarin (sejak 00:00
        // kemarin). Sisanya cukup dihitung supaya modal bisa memberi tahu
        // bahwa masih ada notifikasi lain di lonceng/arsip.
        $recentFrom = Carbon::now()->subDay()->startOfDay();
        $grandTotal = (clone $base)->count();

        $recent = (clone $base)
            ->where('created_at', '>=', $recentFrom)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return $this->formatRow($row);
            })
            ->values();

        $recentTotal = (clone $base)->where('created_at', '>=', $recentFrom)->count();
        $recentUnread = (clone $base)->whereNull('read_at')->where('created_at', '>=', $recentFrom)->count();

        $listQuery = clone $base;
        $filterTypes = $this->resolveTypeFilter($request->query('type'));
        if ($filterTypes) {
            $listQuery->whereIn('type', $filterTypes);
        }

        $total = (clone $listQuery)->count();
        $limit = 10;
        $page = max(1, (int) $request->query('page', 1));

        $notifications = $listQuery
            ->orderBy('created_at', 'desc')
            ->forPage($page, $limit)
            ->get()
            ->map(function ($row) {
                return $this->formatRow($row);
            })
            ->values();

        return response()->json([
            'notifications' => $notifications,
            'notifCount' => $unread,
            'priorityCounts' => $priorityCounts,
            'page' => $page,
            'total' => $total,
            'hasMore' => $page * $limit < $total,
            // Untuk modal pengingat: hanya jendela hari ini & kemarin.
            'recent' => $recent,
            'recentTotal' => $recentTotal,
            'recentUnread' => $recentUnread,
            'olderTotal' => max(0, $grandTotal - $recentTotal),
            'olderUnread' => max(0, $unread - $recentUnread),
        ]);
    }

    /**
     * Halaman View All Notifications dengan pagination.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $typeFilter = $request->get('type');
        $statusFilter = $request->get('status');

        $base = DB::table('notifications')
            ->where('notifiable_id', $userId)
            ->whereIn('type', $this->visibleTypes());

        $filterTypes = $this->resolveTypeFilter($typeFilter);
        if ($filterTypes) {
            $base->whereIn('type', $filterTypes);
        }
        if ($statusFilter === 'unread') {
            $base->whereNull('read_at');
        }

        $stats = [
            'total' => (clone $base)->count(),
            'unread' => (clone $base)->whereNull('read_at')->count(),
        ];

        $notifications = $base->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $notifications->getCollection()->transform(function ($row) {
            return $this->formatRow($row);
        });

        $types = $this->visibleTypes();

        return view('superuser.penjualan.notification.index', compact('notifications', 'stats', 'typeFilter', 'statusFilter', 'types'));
    }

    /**
     * Mark as read & redirect Delivery Order
     */
    public function unread_notif_do(Request $request, $id, $do)
    {
        $notification = $this->getUserNotification($id);

        if (!$notification) {
            return back()->with('error', 'Notif tidak ditemukan atau Anda tidak berhak.');
        }

        $this->markAsRead($id);

        $notifData = json_decode($notification->data, true);

        if (isset($notifData['status'])) {
            if ($notifData['status'] == 2) {
                return redirect()->route(
                    'superuser.penjualan.delivery_order.detail',
                    ['id' => $do]
                )->with('success', 'Notif ditandai sebagai telah dibaca.');
            }

            if ($notifData['status'] == 6) {
                return back()->with('success', 'Notif ditandai sebagai telah dibaca.');
            }
        }

        return back()->with('success', 'Notif ditandai sebagai telah dibaca.');
    }

    /**
     * Mark as read & redirect Sales Order
     */
    public function unread_notif_so(Request $request, $id)
    {
        if (!$this->getUserNotification($id)) {
            return back()->with('error', 'Notif tidak ditemukan atau Anda tidak berhak.');
        }

        $this->markAsRead($id);

        return redirect()->route('superuser.penjualan.sales_order.index_lanjutan')
            ->with('success', 'Notif ditandai sebagai telah dibaca.');
    }

    /**
     * Mark as read & redirect Payable
     */
    public function unread_notif_payable(Request $request, $id)
    {
        if (!$this->getUserNotification($id)) {
            return back()->with('error', 'Notif tidak ditemukan atau Anda tidak berhak.');
        }

        $this->markAsRead($id);

        return redirect()->route('superuser.finance.payable.index')
            ->with('success', 'Notif ditandai sebagai telah dibaca.');
    }

    /**
     * Mark as read only (tanpa redirect khusus)
     */
    public function mark_as_read_only(Request $request, $id)
    {
        if (!$this->getUserNotification($id)) {
            return back()->with('error', 'Notif tidak ditemukan atau Anda tidak berhak.');
        }

        $this->markAsRead($id);

        return back()->with('success', 'Notif ditandai sebagai telah dibaca.');
    }

    /**
     * Baca via klik lonceng (GET): tandai dibaca lalu redirect sesuai tipe.
     */
    public function read($id)
    {
        $notification = $this->getUserNotification($id);

        if (!$notification) {
            return redirect()->route('superuser.penjualan.notification.index')
                ->with('error', 'Notif tidak ditemukan atau Anda tidak berhak.');
        }

        $this->markAsRead($id);

        $data = json_decode($notification->data, true) ?? [];
        $type = $notification->type ?? '';

        if (Str::contains($type, 'PengajuanProforma')) {
            if (!empty($data['id'])) {
                return redirect()->route('superuser.penjualan.pengajuan_proforma.show', ['id' => $data['id']]);
            }
            return redirect()->route('superuser.penjualan.pengajuan_proforma.index');
        }

        if (Str::contains($type, 'DoNotification')) {
            if (($data['status'] ?? null) == 2 && !empty($data['id'])) {
                return redirect()->route('superuser.penjualan.delivery_order.detail', ['id' => $data['id']]);
            }
            return redirect()->route('superuser.penjualan.delivery_order.index');
        }

        if (Str::contains($type, 'SoNotification')) {
            if (($data['kind'] ?? '') === 'proforma') {
                return redirect()->route('superuser.penjualan.so_proforma.index');
            }
            if (!empty($data['id'])) {
                return redirect()->route('superuser.penjualan.sales_order.detail', ['id' => $data['id']]);
            }
            return redirect()->route('superuser.penjualan.sales_order.index_lanjutan');
        }

        if (Str::contains($type, 'PayableNotification')) {
            return redirect()->route('superuser.finance.payable.index');
        }

        if (Str::contains($type, 'InternalRevisionOtp')) {
            return redirect()->route('superuser.penjualan.internal_revision.index');
        }

        return redirect()->route('superuser.penjualan.notification.index');
    }

    /**
     * Mark semua notif sebagai read (hanya tipe yang tampil di menu ini).
     */
    public function unread_all_notif(Request $request)
    {
        $userId = Auth::id();

        try {
            DB::table('notifications')
                ->where('notifiable_id', $userId)
                ->whereIn('type', $this->visibleTypes())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi telah ditandai sebagai telah dibaca.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai semua notifikasi.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * Helper: ambil notif milik user
     */
    private function getUserNotification($id)
    {
        return DB::table('notifications')
            ->where('id', $id)
            ->where('notifiable_id', Auth::id())
            ->first();
    }

    /**
     * Helper: update read_at
     */
    private function markAsRead($id)
    {
        DB::table('notifications')
            ->where('id', $id)
            ->update(['read_at' => now()]);
    }
}
