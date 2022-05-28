<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;

class SettingPriceLog extends Model
{
    protected $table = "penjualan_setting_price_log";
    protected $fillable =[
    	'product_id',
    	'buying_price',
    	'selling_price'
    ];

  
    public function getBuyingPriceAttribute($value)
    {
        return floatval($value);
    }

    public function getSellingPriceAttribute($value)
    {
        return floatval($value);
    }

    public function getDefaultQuantityAttribute($value)
    {
        return floatval($value);
    }
}
