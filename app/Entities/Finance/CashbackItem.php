<?php

namespace App\Entities\Finance;

use Illuminate\Database\Eloquent\Model;

class CashbackItem extends Model
{
    protected $table = "finance_cashback_detail";
    protected $fillable = [
        'cashback_id', 
        'product_packaging_id', 
        'price', 
        'price_cashback', 
        'qty', 
        'subtotal_item_idr', 
        'amount_cashback', 
    ];

    public function cashback(){
    	return $this->BelongsTo('App\Entities\Finance\Cashback', 'cahsback_id', 'id');
    }

    public function product_pack(){
    	return $this->BelongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
    }
}
