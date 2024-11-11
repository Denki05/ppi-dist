<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrderMutationItem extends Model
{
    	use SoftDeletes;
        protected $table = "penjualan_do_mutation_item";
        protected $fillable =[
        	'do_id',
        	'do_mutation_id',
        	'product_packaging_id',
        	'so_item_id',
            'packaging_id',
        	'qty',
        	'price',
        	'note',
        	'updated_by',
        	'created_by',
        	'deleted_by'
        ];

       	public function do_mutation(){
       		return $this->BelongsTo('App\Entities\Penjualan\DeliveryOrderMutation','do_mutation_id','id');
       	}
        public function product_pack(){
        	return $this->BelongsTo('App\Entities\Master\ProductPack','product_packaging_id','id');
        }
        public function so_item(){
        	return $this->BelongsTo('App\Entities\Penjualan\SalesOrderItem','so_item_id','id');
        }
        public function packaging(){
        	return $this->BelongsTo('App\Entities\Master\Packaging','packaging_id','id');
        }
        public function getPriceAttribute($value)
        {
            return floatval($value);
        }
        public function getQtyAttribute($value)
        {
            return floatval($value);
        }
        public function packaging_txt(){
            return (object) self::PACKAGING[$this->packaging];
        }
        public function packaging_val(){
            return (object) self::PACKAGING_VALUE[$this->packaging];
        }
}
