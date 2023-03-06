<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoProformaDetail extends Model
{
	use SoftDeletes;
    protected $table = "so_proforma_detail";
    protected $fillable =[
    	'so_proforma_id',
    	'product_id',
    	'qty',
    	'updated_at',
    	'created_at',
    	'deleted_at'
    ];

    
    public function detail(){
    	return $this->BelongsTo('App\Entities\Penjualan\SoProforma','so_proforma_id','id');
    }

    public function product(){
    	return $this->BelongsTo('App\Entities\Master\Product','product_id','id');
    }
    
}
