<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Notifikasi Trans yang relevan untuk gudang (WMS).
 * User mengikuti akun WMS yang login (payload token), bukan user hardcode.
 */
class WmsNotificationController extends Controller
{
    /**
     * Tipe yang diteruskan ke WMS. Saat ini hanya DoNotification:
     * DO masuk antrean SPK (status 3) dan DO selesai dikirim (status 6).
     */
    private $types = [
        'App\Notifications\DoNotification',
    ];

    public function index(Request $request)
    {
        $base = $this->baseQuery($request);

        $unread = (clone $base)->whereNull('read_at')->count();

        $query = clone $base;
        if ($request->query('filter') === 'unread') {
            $query->whereNull('read_at');
        }

        $limit = min(max((int) $request->query('limit', 10), 1), 50);
        $page = max(1, (int) $request->query('page', 1));
        $total = (clone $query)->count();

        $rows = $query->orderBy('created_at', 'desc')
            ->forPage($page, $limit)
            ->get(['id', 'type', 'data', 'read_at', 'created_at']);

        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->formatRow($row);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'generated_at' => date('c'),
                'unread'       => $unread,
                'total'        => $total,
                'page'         => $page,
                'per_page'     => $limit,
                'last_page'    => max(1, (int) ceil($total / $limit)),
                'items'        => $items,
            ],
        ]);
    }

    public function markRead(Request $request, $id)
    {
        $notification = $this->baseQuery($request)->where('id', $id)->first();

        if (!$notification) {
            return $this->fail('Notifikasi tidak ditemukan.', 404);
        }

        if (is_null($notification->read_at)) {
            DB::table('notifications')->where('id', $id)->update(['read_at' => now()]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'     => $id,
                'unread' => $this->baseQuery($request)->whereNull('read_at')->count(),
            ],
        ]);
    }

    public function markAllRead(Request $request)
    {
        $updated = $this->baseQuery($request)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'updated' => $updated,
                'unread'  => 0,
            ],
        ]);
    }

    private function baseQuery(Request $request)
    {
        $payload = $request->attributes->get('wms_user');

        return DB::table('notifications')
            ->where('notifiable_type', 'App\Entities\Account\User')
            ->where('notifiable_id', (int) ($payload['sub'] ?? 0))
            ->whereIn('type', $this->types);
    }

    private function formatRow($row)
    {
        $data = json_decode($row->data, true);
        if (!is_array($data)) {
            $data = [];
        }

        $created = $row->created_at ? Carbon::parse($row->created_at) : null;
        $status = isset($data['status']) ? (int) $data['status'] : null;

        return [
            'id'         => $row->id,
            'kind'       => 'do',
            'label'      => 'Delivery Order',
            'code'       => $data['code'] ?? null,
            'customer'   => $data['customer'] ?? null,
            'city'       => $data['customer_kota'] ?? null,
            'do_id'      => isset($data['id']) ? (int) $data['id'] : null,
            'do_status'  => $status,
            'priority'   => $status === 3 ? 'action' : 'info',
            'is_unread'  => is_null($row->read_at),
            'created_at' => $created ? $created->toIso8601String() : null,
            'human'      => $created ? $created->locale('id')->diffForHumans() : '',
        ];
    }

    private function fail($message, $code = 422)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], $code);
    }
}
