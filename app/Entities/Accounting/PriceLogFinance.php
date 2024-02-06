<?php

namespace App\Entities\Accounting;

use Illuminate\Database\Eloquent\Model;

class PriceLogFinance extends Model
{
    protected $table = "master_product_finance_price_log";
    protected $fillable =[
    	'product_finance_id',
    	'selling_price_usd_drum',
    	'buying_price_usd_drum',
    	'selling_price_usd_unit',
    	'buying_price_usd_unit',
    ];

	public function product_finance(){
    	return $this->belongsTo('App\Entities\Master\ProductFinance','product_finance_id', 'id');
    }
}
