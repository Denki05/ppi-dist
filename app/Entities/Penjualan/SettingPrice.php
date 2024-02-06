<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;

class SettingPrice extends Model
{
    protected $table = "master_products_packaging";
    protected $fillable =[
    	'price',
    ];
}
