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

        $notifications = $deliveryOrderNotifications->merge($salesOrderNotifications);

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

        if ($notification && $notification->notifiable_id == Auth::id()) {
            DB::table('notifications')->where('id', $id)->update(['read_at' => now()]);

        }

        Return redirect()->route('superuser.penjualan.delivery_order.detail', ['id' => $do])->with('success', 'Notif ditandai sebagai telah dibaca.');
    }

    public function unread_notif_so(Request $request, $id, $do)
    {
        $notification = DB::table('notifications')->where('id', $id)->first();

        if ($notification && $notification->notifiable_id == Auth::id()) {
            DB::table('notifications')->where('id', $id)->update(['read_at' => now()]);

        }

        Return redirect()->route('superuser.penjualan.sales_order.index_lanjutan')->with('success', 'Notif ditandai sebagai telah dibaca.');
    }
}
