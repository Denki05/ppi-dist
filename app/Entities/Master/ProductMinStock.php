<?php

namespace App\Entities\Master;

use App\Entities\Model;

class ProductMinStock extends Model
{
    protected $fillable = ['product_packaging_id', 'warehouse_id', 'unit_id', 'quantity', 'selling_price'];
    protected $table = 'master_product_min_stocks';

    public function product_pack()
    {
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Entities\Master\Warehouse');
    }

    public function unit()
    {
        return $this->belongsTo('App\Entities\Master\Unit');
    }

    public function getQuantityAttribute($value)
    {
        return floatval($value);
    }

}
