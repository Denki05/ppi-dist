<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrderMutation extends Model
{
    	use SoftDeletes;
        protected $table = "penjualan_do_mutation";
        protected $fillable =[
        	'code',
        	'destination_warehouse_id',
        	'origin_warehouse_id',
        	'address',
        	'status',
        	'updated_by',
        	'created_by',
        	'deleted_by'
        ];

        const STATUS = [
        	1 => [
        		'class' => 'secondary',
        		'msg' => 'Draft'
        	],
        	2 => [
        		'class' => 'success',
        		'msg' => 'Sent'
        	]
        ];
        public function do_mutation_item(){
        	return $this->hasMany('App\Entities\Penjualan\DeliveryOrderMutationItem','do_mutation_id');
        }
        public function origin_warehouse(){
        	return $this->BelongsTo('App\Entities\Master\Warehouse','origin_warehouse_id','id');
        }
        public function destination_warehouse(){
        	return $this->BelongsTo('App\Entities\Master\Warehouse','destination_warehouse_id','id');
        }
        public function do_mutation_status(){
        	return (object) self::STATUS[$this->status];
        }
}
