<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Mencatat user yang sedang aktif memakai aplikasi ke tabel user_presence.
 *
 * Penulisan dibatasi sekali per menit per sesi agar tidak menambah satu
 * query tulis di setiap request.
 */
class TrackUserPresence
{
    public function handle($request, Closure $next)
    {
        $this->touch($request);

        return $next($request);
    }

    protected function touch($request)
    {
        if (!$request->hasSession()) {
            return;
        }

        $user = Auth::guard('superuser')->user();

        if (!$user || (int) ($user->is_active ?? 0) !== 1) {
            return;
        }

        $session = $request->session();
        $last = (int) $session->get('_presence_at', 0);

        if ($last >= time() - 50) {
            return;
        }

        $session->put('_presence_at', time());

        try {
            $now = now();

            DB::table('user_presence')->updateOrInsert(
                ['session_id' => $session->getId()],
                [
                    'user_id' => $user->id,
                    'ip' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'last_seen_at' => $now,
                    'updated_at' => $now,
                ]
            );

            if (random_int(1, 100) === 1) {
                DB::table('user_presence')
                    ->where('last_seen_at', '<', $now->copy()->subHours(2))
                    ->delete();
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
