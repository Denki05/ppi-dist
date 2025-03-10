<?php

namespace App\Entities\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashbackUv extends Model
{
    protected $table = "finance_cashback_uv";
    protected $fillable = [
        'code', 
        'customer_other_address_id', 
        'do_uv_id', 
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

    public function detail()
    {
        return $this->hasMany('App\Entities\Finance\CashbackItemUv', 'cashback_uv_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Entities\Master\CustomerOtherAddress', 'customer_other_address_id', 'id');
    }

    public function packing_order_uv()
    {
        return $this->belongsTo('App\Entities\Accounting\PackingOrderUv', 'do_uv_id');
    }
}
