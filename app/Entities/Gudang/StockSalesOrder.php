<?php

namespace App\Entities\Gudang;

use App\Entities\Model;

class StockSalesOrder extends Model
{
    protected $fillable = ['product_id', 'warehouse_id', 'quantity'];
    protected $table = 'stock_sales_order';

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Entities\Master\Warehouse');
    }

    public function getQuantityAttribute($value)
    {
        return floatval($value);
    }

}
