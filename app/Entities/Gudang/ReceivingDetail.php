<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceivingDetail extends Model
{
	use SoftDeletes;
    protected $table = "receiving_detail";
    protected $fillable = [
    	'receiving_id',
        'product_id', 
        'unit_id',
        'qty', 
        'description', 
    	
    ];

    public function receiving(){
    	return $this->hasMany('App\Entities\Gudang\Receiving','receiving_id','id');
    }

    public function product(){
    	return $this->BelongsTo('App\Entities\Master\Product','product_id','id');
    }

    public function unit(){
    	return $this->BelongsTo('App\Entities\Master\Product','unit_id','id');
    }
}
