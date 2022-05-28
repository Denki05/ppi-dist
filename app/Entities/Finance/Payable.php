<?php

namespace App\Entities\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payable extends Model
{
    use SoftDeletes;
    protected $table = "finance_payable";
    protected $fillable = [
    	'code',
    	'customer_id',
    	'total',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];

    public function customer(){
    	return $this->BelongsTo('App\Entities\Master\Customer','customer_id','id');
    }

    public function payable_detail(){
    	return $this->hasMany('App\Entities\Finance\PayableDetail','payable_id');
    }

    public function getTotalAttribute($value)
    {
        return floatval($value);
    }

}
