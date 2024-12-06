<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthEndBalance extends Model
{
    use SoftDeletes;

    protected $table = "gudang_month_end_balances";
    protected $fillable = 
    [
        'warehouse_id', 
        'product_packaging_id', 
        'balance', 
        'month'
    ];
}