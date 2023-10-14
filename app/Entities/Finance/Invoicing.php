<?php

namespace App\Entities\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoicing extends Model
{
    use SoftDeletes;
    protected $table = "finance_invoicing";
    protected $fillable = [
    	'code',
    	'do_id',
    	'customer_id',
        'customer_other_address_id',
    	'grand_total_idr',
        'image',
    	'updated_by',
    	'created_by',
    	'deleted_by', 
    ];

    public function do(){
    	return $this->BelongsTo('App\Entities\Penjualan\PackingOrder','do_id','id');
    }
    public function payable_detail(){
    	return $this->hasMany('App\Entities\Finance\PayableDetail','invoice_id');
    }
    public function getGrandTotalIdrAttribute($value)
    {
        return floatval($value);
    }
    public function sale_return()
    {
        return $this->hasOne('App\Entities\Penjualan\SaleReturn', 'invoice_id');
    }

    public function store(){
    	return $this->BelongsTo('App\Entities\Master\Customer','customer_id','id');
    }
}
