<?php

namespace App\Entities\Finance;

use Illuminate\Database\Eloquent\Model;

class CashbackItemUv extends Model
{
    protected $table = "finance_cashback_detail_uv";
    protected $fillable = [
        'cashback_uv_id', 
        'product_packaging_id', 
        'price', 
        'price_cashback', 
        'qty', 
        'subtotal_item_idr', 
        'amount_cashback_idr', 
    ];

    public function cashback()
    {
        return $this->belongsTo('App\Entities\Finance\CashbackUv', 'cashback_uv_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
    }
}
