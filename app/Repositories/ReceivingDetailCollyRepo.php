<?php

namespace App\Repositories;

use Illuminate\Support\Str;
use Carbon\Carbon;

class ReceivingDetailCollyRepo
{
    public static function generateCode()
    {
        $pre = 10;
        $today = Carbon::today();
        $today_formatted = $today->isoFormat('YYMMDD');
        $rand = rand ( 10000 , 99999 );

        $code = $pre.$today_formatted.$rand;
        return $code;
    }
}