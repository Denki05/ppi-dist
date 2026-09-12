<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'App\Notifications\PayableNotification',
            'App\Notifications\ReceivingNotification',
            'App\Notifications\InternalRevisionOtpNotification',
        ];
    }

    /**
     * Ambil data notifikasi (maksimal 5 per tipe)
     */
    public function getNotifData()
    {
        $userId = Auth::id();

        // Ambil semua notif unread sesuai tipe sekaligus
        $notifications = DB::table('notifications')
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->whereIn('type', $this->visibleTypes())
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('type')
            ->map(function ($items) {
                return $items->take(5);
            })
            ->flatten(1)
            ->sortByDesc('created_at')
            ->values();

        $notifCount = DB::table('notifications')
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'notifCount'    => $notifCount,
        ]);
    }

    /**
     * Halaman View All Notifications dengan pagination
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $typeFilter = $request->get('type');

        $query = DB::table('notifications')
            ->where('notifiable_id', $userId)
            ->whereIn('type', $this->visibleTypes())
            ->orderBy('created_at', 'desc');

        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        $notifications = $query->paginate(20)->withQueryString();

        // Statistik
        $stats = [
            'total' => DB::table('notifications')
                ->where('notifiable_id', $userId)
                ->whereIn('type', $this->visibleTypes())
                ->count(),
            'unread' => DB::table('notifications')
                ->where('notifiable_id', $userId)
                ->whereNull('read_at')
                ->whereIn('type', $this->visibleTypes())
                ->count(),
        ];

        return view('superuser.penjualan.notification.index', compact('notifications', 'stats', 'typeFilter'));
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
     * Rute POST lama tidak diubah (dipakai form halaman + flows lain).
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

        if (str_contains($type, 'DoNotification')) {
            if (($data['status'] ?? null) == 2 && !empty($data['id'])) {
                return redirect()->route('superuser.penjualan.delivery_order.detail', ['id' => $data['id']]);
            }
            return redirect()->route('superuser.penjualan.delivery_order.index');
        }

        if (str_contains($type, 'SoNotification')) {
            return redirect()->route('superuser.penjualan.sales_order.index_lanjutan');
        }

        if (str_contains($type, 'PayableNotification')) {
            return redirect()->route('superuser.finance.payable.index');
        }

        if (str_contains($type, 'InternalRevisionOtp')) {
            return redirect()->route('superuser.penjualan.internal_revision.index');
        }

        return redirect()->route('superuser.penjualan.notification.index');
    }

    /**
     * Mark semua notif sebagai read
     */
    public function unread_all_notif(Request $request)
    {
        $userId = Auth::id();

        try {
            DB::table('notifications')
                ->where('notifiable_id', $userId)
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