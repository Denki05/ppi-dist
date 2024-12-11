<?php

namespace App\Entities\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceSimulationPriceDetail extends Model
{
    use SoftDeletes;

    protected $table = "finance_simulation_price_detail";
    protected $fillable = [
    	'finance_simulation_id',
    	'product_packaging_id',
        'price_buying',
        'price_selling',
        'qty',
        'subtotal_harga_beli',
        'subtotal_harga_jual',
    ];

    public function simulation_price(){
    	return $this->belongsTo('App\Entities\Accounting\FinanceSimulationPrice', 'finance_simulation_id', 'id');
    }

    public function product_tax(){
    	return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
    }
}
