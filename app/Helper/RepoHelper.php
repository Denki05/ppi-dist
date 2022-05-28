<?php

namespace App\Helper;

use App\Traits\Responder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RepoHelper
{
    public static function id(Request $request)
    {
        // check if encoded array
        if (Str::containsAll($request->id, ['[', ']'])) {
            $arr = json_decode($request->id);

            if (is_array($arr) AND count($arr) > 0) {
                return $arr;
            } else {
                return false;
            }
        } else {
            return $request->id;
        }
    }

    public static function condition(Request $request)
    {
        return $request->except(['id']);
    }
}