<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CanvasingItem extends Model
{
    	use SoftDeletes;
        protected $table = "penjualan_canvasing_item";
        protected $fillable =[
        	'canvasing_id',
        	'product_id',
        	'qty',
        	'updated_by',
        	'created_by',
        	'deleted_by'
        ];

        public function canvasing(){
        	return $this->BelongsTo('App\Entities\Penjualan\Canvasing','canvasing_id','id');
        }
        public function product(){
        	return $this->BelongsTo('App\Entities\Master\Product','product_id','id');
        }
        public function getQtyAttribute($value)
        {
            return floatval($value);
        }
}
