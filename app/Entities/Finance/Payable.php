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
    	'customer_other_address_id',
        'pay_date', 
    	'total',
        'status',
        'note',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'APPROVE' => 2,
    ];

    const TYPE = [
        'LUNAS PER NOTA' => 1,
        'LUNAS BEBERAPA NOTA' => 2,
        'CICILAN' => 3,
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

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }

}
