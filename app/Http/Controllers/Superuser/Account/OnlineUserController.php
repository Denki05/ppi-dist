<?php

namespace App\Http\Controllers\Superuser\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OnlineUserController extends Controller
{
    const ONLINE_MINUTES = 5;

    public function index(Request $request)
    {
        $this->authorizeDeveloper();

        $users = DB::table('user_presence as p')
            ->join('superusers as u', 'u.id', '=', 'p.user_id')
            ->where('p.last_seen_at', '>=', now()->subMinutes(self::ONLINE_MINUTES))
            ->groupBy('u.id', 'u.username', 'u.name', 'u.email', 'u.is_active')
            ->orderByRaw('MAX(p.last_seen_at) DESC')
            ->select([
                'u.id',
                'u.username',
                'u.name',
                'u.email',
                'u.is_active',
                DB::raw('COUNT(*) as session_count'),
                DB::raw('MAX(p.last_seen_at) as last_seen_at'),
                DB::raw('MAX(p.ip) as last_ip'),
            ])
            ->get();

        return view('superuser.account.online.index', [
            'users' => $users,
            'windowMinutes' => self::ONLINE_MINUTES,
            'totalSessions' => $users->sum('session_count'),
            'currentUserId' => (int) Auth::guard('superuser')->id(),
        ]);
    }

    protected function authorizeDeveloper()
    {
        $user = Auth::guard('superuser')->user();

        if (!$user || !$user->hasAnyRole(['Developer', 'SuperAdmin'])) {
            abort(403, 'Halaman ini hanya untuk Developer/SuperAdmin.');
        }
    }
}
