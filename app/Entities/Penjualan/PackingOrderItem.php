<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackingOrderItem extends Model
{
    	use SoftDeletes;
        protected $table = "penjualan_do_item";
        protected $fillable =[
        	'do_id',
        	'product_id',
        	'so_item_id',
        	'qty',
        	'price',
        	'usd_disc',
        	'percent_disc',
        	'total_disc',
        	'total',
        	'note',
            'packaging',
        	'updated_by',
        	'created_by',
        	'deleted_by'
        ];

        const PACKAGING = [
            1 => '100gr (0.1)',
            2 => '500gr (0.5)',
            3 => 'Jerigen 5kg (5)',
            4 => 'Alumunium 5kg (5)',
            5 => 'Jerigen 25kg (25)',
            6 => 'Drum 25kg (25)',
            7 => 'Free'
        ];

        const PACKAGING_VALUE = [
            1 => 0.1,
            2 => 0.5,
            3 => 5,
            4 => 5,
            5 => 25,
            6 => 25,
            7 => 'Free'
        ];

        public function do(){
        	return $this->BelongsTo('App\Entities\Penjualan\PackingOrder','do_id','id');
        }
        public function product(){
        	return $this->BelongsTo('App\Entities\Master\Product','product_id','id');
        }
        public function so_item(){
        	return $this->BelongsTo('App\Entities\Penjualan\SalesOrderItem','so_item_id','id');
        }

        public function getPriceAttribute($value)
        {
            return floatval($value);
        }
        public function getUsdDiscAttribute($value)
        {
            return floatval($value);
        }
        public function getPercentDiscAttribute($value)
        {
            return floatval($value);
        }
        public function getTotalDiscAttribute($value)
        {
            return floatval($value);
        }
        public function getTotalAttribute($value)
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
