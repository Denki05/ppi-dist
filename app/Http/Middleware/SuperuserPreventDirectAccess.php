<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class SuperuserPreventDirectAccess
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
        $blacklistUrl = [
            '*edit*',
        ];

        // in blacklist url
        // is not ajax
        // is get
        if  ($request->is($blacklistUrl) AND !$request->ajax() AND $request->isMethod('get')) {
            $token = $request->token;

            // debug for developer
            if ($token === '?' OR env('SUPERUSER_PREVENT_DIRECT_ACCESS') === false) {
                return $next($request);
            }
            
            $forbidden = false;

            // try to decrypt the token, if error, abort.
            try {
                $decrypted = Crypt::decryptString($token);

                $link = Str::before($decrypted, '###');
                $username = Str::after($decrypted, '###');

                $url = url()->current();
                $loginUsername = Auth::guard('superuser')->user()->username;

                // if current url is not same as the requested token, abort.
                // if login username is not same as the requested token, abort.
                if ($url !== $link OR $loginUsername !== $username) {
                    $forbidden = true;
                }
            } catch (\Throwable $th) {
                $forbidden = true;
            }

            // if forbidden & old request is empty (bypass redirect with input)
            if ($forbidden && empty(old())) {
                abort(403);
            }
        }

        return $next($request);
    }
}
