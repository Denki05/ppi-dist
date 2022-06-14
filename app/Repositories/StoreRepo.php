<?php

namespace App\Repositories;

use App\Entities\Master\Store;

class StoreRepo
{
    public static function generateCode()
    {
        $pre = 'STR-';
        $count = Store::count() + 1;
        
        $code = $pre . sprintf('%03d', $count);

        return $code;
    }

    // public static function generateCode()
    // {
    //     $pre = 'CUST-';
    //     $count = Customer::count() + 1;
        
    //     $code = $pre . sprintf('%04d', $count);

    //     return $code;
    // }
}