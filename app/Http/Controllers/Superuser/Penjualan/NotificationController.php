<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
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
            ->whereIn('type', [
                'App\Notifications\DoNotification',
                'App\Notifications\SoNotification',
                'App\Notifications\PayableNotification',
                'App\Notifications\ReceivingNotification'
            ])
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