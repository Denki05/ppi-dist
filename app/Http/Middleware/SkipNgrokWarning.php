<?php

namespace App\Http\Middleware;

use Closure;

class SkipNgrokWarning
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \Log::info('Middleware SkipNgrokWarning dijalankan');

        $response = $next($request);

        $response->headers->set('ngrok-skip-browser-warning', 'true');

        return $response;
    }
}
