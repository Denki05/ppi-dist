<?php

namespace App\Entities\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackingOrderItemUv extends Model
{
    protected $table = "penjualan_do_item_uv";
    protected $fillable = [
    	'do_uv_id',
    	'product_packaging_id',
    	'qty',
    	'free',
    	'price_jual',
    	'price_beli',
    	'usd_disc',
    	'total',
    ];

	public function simulation_price()
	{
		return $this->belongsTo('App\Entities\Accounting\FinanceSimulationPrice', 'do_uv_id', 'id');
	}

	public function product()
	{
		return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
	}
}
