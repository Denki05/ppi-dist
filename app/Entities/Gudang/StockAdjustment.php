<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model
{
	use SoftDeletes;
    protected $table = "gudang_stock_adjustment";
    protected $fillable = [
    	'code',
    	'warehouse_id',
    	'product_packaging_id',
    	'prev',
    	'min',
    	'plus',
    	'update',
    	'note',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];

    public function warehouse(){
    	return $this->BelongsTo('App\Entities\Master\Warehouse','warehouse_id','id');
    }
    public function product_pack(){
    	return $this->BelongsTo('App\Entities\Master\ProductPack','product_packaging_id','id');
    }
    public function getPrevAttribute($value)
    {
        return floatval($value);
    }
    public function getMinAttribute($value)
    {
        return floatval($value);
    }
    public function getPlusAttribute($value)
    {
        return floatval($value);
    }
    public function getUpdateAttribute($value)
    {
        return floatval($value);
    }
}
