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
    	'grand_total_idr',
        'image',
    	'updated_by',
    	'created_by',
    	'deleted_by'
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
}
