<?php

namespace App\Entities\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cashback extends Model
{
    use SoftDeletes;

    protected $table = "finance_cashback";
    protected $fillable = [
        'code', 
        'customer_other_address_id', 
        'do_id', 
        'idr_rate', 
        'note', 
        'status', 
        'created_by', 
        'updated_by', 
        'deleted_by'
    ];

    const STATUS = [
        'ACTIVE' => 1,
        'DELETED' => 0,
    ];

    public function cashback_detail(){
    	return $this->hasMany('App\Entities\Finance\CashbackItem', 'cashback_id');
    }

    public function do(){
    	return $this->hasMany('App\Entities\Penjualan\PackingOrder', 'do_id');
    }

    public function store(){
    	return $this->hasMany('App\Entities\Master\CustomerOtherAddress', 'customer_other_address_id');
    }

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }
}
