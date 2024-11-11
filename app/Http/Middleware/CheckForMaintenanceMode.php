<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as Middleware;
use Illuminate\Support\Facades\App;
use Closure;
use Auth;

class CheckForMaintenanceMode extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array
     */
    protected $except = [
        '/auth/superuser',
        '/auth/superuser/*',
        '/superuser',
        '/superuser/*',
    ];

    public function handle($request, Closure $next)
    {
        if (App::isDownForMaintenance() && !$this->inExceptArray($request)) {
            return abort(503, 'Situs sedang dalam pemeliharaan. Silakan coba lagi nanti!');
        }

        // read IP from config, or use another codition you need
        // if ($request->getClientIp() === '127.0.0.1') {
        //     return $next($request);
        // }

        return $next($request);
    }

    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }

    protected function isAdmin($request)
    {
        if ($request->user() && $request->user()->is_superuser == 1){
            return true;
        }

        // dd($request);

        return false;
    }
}