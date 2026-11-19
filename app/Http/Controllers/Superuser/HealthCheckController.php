<?php

namespace App\Http\Controllers\Superuser;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Diagnostics\HealthCheckRunner;

class HealthCheckController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'checks');
        if (!in_array($tab, ['checks', 'errors'], true)) {
            $tab = 'checks';
        }

        $sinceDays = (int) $request->query('since', 180);
        $sinceDays = max(0, min(3650, $sinceDays));

        $result = null;
        if ($tab === 'checks' && $request->boolean('run')) {
            $result = (new HealthCheckRunner())->run([], $sinceDays);
        }

        $errorStats = [
            'unresolved' => DB::table('dev_error_events')->where('is_resolved', 0)->count(),
            'last24' => DB::table('dev_error_events')->where('last_seen_at', '>=', now()->subDay())->count(),
        ];

        $errors = DB::table('dev_error_events')
            ->orderBy('is_resolved')
            ->orderBy('last_seen_at', 'desc')
            ->paginate(10)
            ->appends($request->only('tab'));

        return view('superuser.developer.health.index', compact('tab', 'result', 'errorStats', 'errors', 'sinceDays'));
    }

    public function resolve($id)
    {
        DB::table('dev_error_events')->where('id', $id)->update([
            'is_resolved' => 1,
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('superuser.health_check.index', ['tab' => 'errors'])
            ->with('success', 'Error ditandai selesai.');
    }

    public function resolveAll()
    {
        DB::table('dev_error_events')->where('is_resolved', 0)->update([
            'is_resolved' => 1,
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('superuser.health_check.index', ['tab' => 'errors'])
            ->with('success', 'Semua error ditandai selesai.');
    }
}
