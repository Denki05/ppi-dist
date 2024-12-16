<?php

namespace App\Http\Controllers\Superuser\Penjualan;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;

class NotificationController extends Controller
{
    public function getNotifData()
    {
        $user = Auth::id(); // Get the currently authenticated user ID

        $deliveryOrderNotifications = DB::table('notifications')
            ->where('notifiable_id', $user)
            ->where('type', 'App\Notifications\DoNotification')
            ->where('read_at', null)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $salesOrderNotifications = DB::table('notifications')
            ->where('notifiable_id', $user)
            ->where('type', 'App\Notifications\SoNotification')
            ->where('read_at', null)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $payableNotifications = DB::table('notifications')
            ->where('notifiable_id', $user)
            ->where('type', 'App\Notifications\PayableNotification')
            ->where('read_at', null)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $receivingNotifications = DB::table('notifications')
            ->where('notifiable_id', $user)
            ->where('type', 'App\Notifications\ReceivingNotification')
            ->where('read_at', null)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Merge all notifications into one collection
        $notifications = $deliveryOrderNotifications
            ->merge($salesOrderNotifications)
            ->merge($payableNotifications)
            ->merge($receivingNotifications);

        $notifCount = DB::table('notifications')
            ->where('notifiable_id', $user)
            ->where('read_at', null)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'notifCount' => $notifCount,
        ]);
    }

    public function unread_notif_do(Request $request, $id, $do)
    {
        $notification = DB::table('notifications')->where('id', $id)->first();
        $notifData = json_decode($notification->data, true);

        if ($notification && $notification->notifiable_id == Auth::id()) {
            DB::table('notifications')->where('id', $id)->update(['read_at' => now()]);

            // Check the status
            if (isset($notifData['status'])) {
                if ($notifData['status'] == 2) {
                    return redirect()->route('superuser.penjualan.delivery_order.detail', ['id' => $do])
                        ->with('success', 'Notif ditandai sebagai telah dibaca.');
                } elseif ($notifData['status'] == 6) {
                    return back()->with('success', 'Notif ditandai sebagai telah dibaca.');
                }
            }
        }

        return back()->with('error', 'Notif tidak ditemukan atau Anda tidak berhak.');
    }

    public function unread_notif_so(Request $request, $id, $do)
    {
        $notification = DB::table('notifications')->where('id', $id)->first();

        if ($notification && $notification->notifiable_id == Auth::id()) {
            DB::table('notifications')->where('id', $id)->update(['read_at' => now()]);
        }

        return redirect()->route('superuser.penjualan.sales_order.index_lanjutan')
            ->with('success', 'Notif ditandai sebagai telah dibaca.');
    }

    public function unread_notif_payable(Request $request, $id)
    {
        $notification = DB::table('notifications')->where('id', $id)->first();

        if ($notification && $notification->notifiable_id == Auth::id()) {
            DB::table('notifications')->where('id', $id)->update(['read_at' => now()]);
        }

        return redirect()->route('superuser.finance.payable.index')
            ->with('success', 'Notif ditandai sebagai telah dibaca.');
    }

    public function mark_as_read_only(Request $request, $id)
    {
        $notification = DB::table('notifications')->where('id', $id)->first();

        if ($notification && $notification->notifiable_id == Auth::id()) {
            DB::table('notifications')->where('id', $id)->update(['read_at' => now()]);
            return back()->with('success', 'Notif ditandai sebagai telah dibaca.');
        }

        return back()->with('error', 'Notif tidak ditemukan atau Anda tidak berhak.');
    }

    public function unread_all_notif(Request $request)
    {
        $user = Auth::id(); // Get the currently authenticated user ID

        // Update all notifications for the user, marking them as read
        DB::table('notifications')
            ->where('notifiable_id', $user)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai telah dibaca.');
    }
}