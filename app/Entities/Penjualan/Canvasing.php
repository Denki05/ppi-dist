<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Canvasing extends Model
{
    	use SoftDeletes;
        protected $table = "penjualan_canvasing";
        protected $fillable =[
        	'code',
        	'sales_id',
        	'warehouse_id',
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
        public function canvasing_item(){
        	return $this->hasMany('App\Entities\Penjualan\CanvasingItem','canvasing_id');
        }
        public function sales(){
        	return $this->BelongsTo('App\Entities\Master\Sales','sales_id','id');
        }
        public function warehouse(){
        	return $this->BelongsTo('App\Entities\Master\Warehouse','warehouse_id','id');
        }

        public function canvasing_status(){
        	return (object) self::STATUS[$this->status];
        }
}
