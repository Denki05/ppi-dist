<?php

namespace App\Entities\Master;

use Illuminate\Database\Eloquent\Model;

class Packaging extends Model
{
    protected $fillable = ['unit_id', 'pack_no', 'pack_name', 'pack_value', 'description', 'status'];
    protected $table = 'master_packaging';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function product()
    {
        return $this->hasMany('App\Entities\Master\Product', 'packaging_id');
    }

    public function purchase_order_detail()
    {
        return $this->hasMany('App\Entities\Gudang\PurchaseOrderDetail', 'packaging_id');
    }

    public function sales_order_item()
    {
        return $this->hasMany('App\Entities\Penjualan\SalesOrderItem', 'packaging_id');
    }

    public function do_item()
    {
        return $this->hasMany('App\Entities\Penjualan\PackingOrderItem', 'packaging_id');
    }

    public function unit()
    {
        return $this->belongsTo('App\Entities\Master\Unit', 'unit_id');
    }
}
