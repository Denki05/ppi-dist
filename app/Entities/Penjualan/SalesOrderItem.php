<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderItem extends Model
{
    	use SoftDeletes;
        protected $table = "penjualan_so_item";
        protected $fillable =[
        	'so_id',
        	'product_id',
        	'qty',
        	'qty_worked',
            'packaging_id',
            'free_product',
        	'updated_by',
        	'created_by',
        	'deleted_by'
        ];

        public function so(){
        	return $this->BelongsTo('App\Entities\Penjualan\SalesOrder','so_id','id');
        }
        public function product(){
        	return $this->BelongsTo('App\Entities\Master\Product','product_id','id');
        }
        
        // public function packaging_txt(){
        //     return (object) self::PACKAGING[$this->packaging];
        // }
        // public function packaging_val(){
        //     return (object) self::PACKAGING_VALUE[$this->packaging];
        // }

        public function packaging(){
            return $this->BelongsTo('App\Entities\Master\Packaging', 'packaging_id', 'id');
        }

        public function getQtyAttribute($value)
        {
            return floatval($value);
        }

        public function do_item(){
            return $this->hasMany('App\Entities\Penjualan\PackingOrderItem','so_item_id');
        }
}
