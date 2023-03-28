<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackingOrderDetail extends Model
{
    use SoftDeletes;
    protected $table = "penjualan_do_details";
    protected $fillable =[
    	'do_id',
    	'discount_1',
        'discount_1_idr',
    	'discount_2',
        'discount_2_idr',
    	'discount_idr',
    	'total_discount_idr',
    	'ppn',
    	'voucher_idr',
    	'cashback_idr',
    	'purchase_total_idr',
    	'delivery_cost_idr',
        'delivery_cost_note',
    	'other_cost_idr',
        'other_cost_note',
    	'grand_total_idr',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];

    public function do(){
    	return $this->BelongsTo('App\Entities\Penjualan\PackingOrder','do_id','id');
    }

    public function getDiscount1Attribute($value)
    {
        return floatval($value);
    }
    public function getDiscount2Attribute($value)
    {
        return floatval($value);
    }
    public function getDiscountIdrAttribute($value)
    {
        return floatval($value);
    }
    public function getTotalDiscountIdrAttribute($value)
    {
        return floatval($value);
    }
    public function getPpnAttribute($value)
    {
        return floatval($value);
    }
    public function getVoucherIdrAttribute($value)
    {
        return floatval($value);
    }
    public function getCashbackIdrAttribute($value)
    {
        return floatval($value);
    }
    public function getPurchaseTotalIdrAttribute($value)
    {
        return floatval($value);
    }
    public function getDeliveryCostIdrAttribute($value)
    {
        return floatval($value);
    }
    public function getOtherCostIdrAttribute($value)
    {
        return floatval($value);
    }
    public function getGrandTotalIdrAttribute($value)
    {
        return floatval($value);
    }
}
