<?php

namespace App\Repositories;

use App\Entities\Master\Ekspedisi;

class EkspedisiRepo
{
    public static function generateCode()
    {
        $count = Ekspedisi::count() + 1;
        
        $code = sprintf('%03d', $count);

        return $code;
    }
}