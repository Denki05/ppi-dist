<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;

class StockMove extends Model
{
    protected $table = "gudang_move_stock";
    protected $fillable = [
    	'code_transaction',
    	'warehouse_id',
    	'product_id',
    	'stock_in',
    	'stock_out',
    	'stock_balance',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];

    public function warehouse(){
    	return $this->BelongsTo('App\Entities\Master\Warehouse','warehouse_id','id');
    }
    public function product(){
    	return $this->BelongsTo('App\Entities\Master\Product','product_id','id');
    }
    public function getStockInAttribute($value)
    {
        return floatval($value);
    }
    public function getStockOutAttribute($value)
    {
        return floatval($value);
    }
    public function getStockBalanceAttribute($value)
    {
        return floatval($value);
    }
}
