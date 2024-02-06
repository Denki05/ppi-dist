<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{

        protected $table = "penjualan_so_item";
        protected $fillable =[
        	'so_id',
        	'product_packaging_id',
        	'qty',
            'disc_usd',
        	'qty_worked',
            'packaging_id',
            'free_product',
            'item_indent', 
        	'updated_by',
        	'created_by',
        	'deleted_by', 
            'status'
        ];

        const STATUS = [
            'DELETED' => 0,
            'ACTIVE' => 1,
        ];

        public function so(){
        	return $this->BelongsTo('App\Entities\Penjualan\SalesOrder','so_id','id');
        }
        public function product_pack(){
        	return $this->BelongsTo('App\Entities\Master\ProductPack','product_packaging_id','id');
        }

        public function getQtyAttribute($value)
        {
            return floatval($value);
        }

        public function doItem(){
            return $this->hasMany('App\Entities\Penjualan\PackingOrderItem', 'so_item_id');
        }

        public function packaging(){
            return $this->BelongsTo('App\Entities\Master\Packaging', 'packaging_id', 'id');
        }

        public function so_item_status()
    {
        return array_search($this->status, self::STATUS);
    }
}
