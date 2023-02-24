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
            'packaging',
        	'updated_by',
        	'created_by',
        	'deleted_by'
        ];

        const PACKAGING = [
            1 => '100gr (0.1)',
            2 => '500gr (0.5)',
            3 => '500gr (0.5)',
            4 => 'Jerigen 5kg (5)',
            5 => 'Alumunium 5kg (5)',
            6 => 'Jerigen 25kg (25)',
            7 => 'Drum 25kg (25)',
            8 => 'Free'
        ];

        const PACKAGING_VALUE = [
            1 => 0.1,
            2 => 0.5,
            3 => 2.5,
            4 => 5,
            5 => 5,
            6 => 25,
            7 => 25,
            8 => 'Free'
        ];

        public function so(){
        	return $this->BelongsTo('App\Entities\Penjualan\SalesOrder','so_id','id');
        }
        public function product(){
        	return $this->BelongsTo('App\Entities\Master\Product','product_id','id');
        }
        
        public function packaging_txt(){
            return (object) self::PACKAGING[$this->packaging];
        }
        public function packaging_val(){
            return (object) self::PACKAGING_VALUE[$this->packaging];
        }

        public function getQtyAttribute($value)
        {
            return floatval($value);
        }

        public function do_item(){
            return $this->hasMany('App\Entities\Penjualan\PackingOrderItem','so_item_id');
        }
}
