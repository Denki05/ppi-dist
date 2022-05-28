<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
	use SoftDeletes;
    protected $table = "penjualan_so";
    protected $fillable =[
    	'code',
    	'sales_id',
    	'sales_senior_id',
    	'origin_warehouse_id',
    	'destination_warehouse_id',
    	'customer_id',
        'ekspedisi_id',
    	'type_transaction',
    	'status',
        'keterangan_tidak_lanjut',
    	'so_for',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];
    const STATUS = [
    	1 => 'CASH',
    	2 => 'TEMPO',
    	3 => 'MARKETING'
    ];
    const STEP = [
    	1 => 'AWAL',
    	2 => 'LANJUTAN',
        3 => 'AWAL PERLU REVISI',
        4 => 'TUTUP',
    	9 => 'MUTASI',
    ];
    public function customer(){
    	return $this->BelongsTo('App\Entities\Master\Customer','customer_id','id');
    }
    public function customer_gudang(){
    	return $this->BelongsTo('App\Entities\Master\Warehouse','destination_warehouse_id','id');
    }
    public function sales_senior(){
    	return $this->BelongsTo('App\Entities\Master\Sales','sales_senior_id','id');
    }
    public function sales(){
    	return $this->BelongsTo('App\Entities\Master\Sales','sales_id','id');
    }
    public function origin_warehouse(){
        return $this->BelongsTo('App\Entities\Master\Warehouse','origin_warehouse_id','id');
    }
    public function ekspedisi(){
        return $this->BelongsTo('App\Entities\Master\Ekspedisi','ekspedisi_id','id');
    }
    public function so_detail(){
    	return $this->hasMany('App\Entities\Penjualan\SalesOrderItem','so_id');
    }
    public function so_type_transaction()
    {
        if (isset($this->type_transaction)) {
            return (object) self::STATUS[$this->type_transaction];
        } else {
            return null;
        }
    }
    public function so_status()
    {
        return (object) self::STEP[$this->status];
    }
    public function user_update(){
        return $this->BelongsTo('App\Entities\Account\Superuser','updated_by','id');
    }
}
