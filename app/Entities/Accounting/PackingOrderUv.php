<?php

namespace App\Entities\Accounting;

use Illuminate\Database\Eloquent\Model;

class PackingOrderUv extends Model
{
    protected $table = "penjualan_do_uv";
    protected $fillable = [
    	'id_uv',
    	'customer_other_address_id ',
        'do_id',
        'code',
        'idr_rate',
        'transaksi',
        'proses_by',
        'deleted_by',
        'proses_at',
        'payment_status',
        'payment_date',
        'payment_proses',
        'count_kpi',
        'invoice_date',
        'status',
    ];

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
    ];

    public function simulation_detail()
    {
    	return $this->hasMany('App\Entities\Accounting\PackingOrderDetailUv', 'do_uv_id');
    }

    public function simulation_item()
    {
    	return $this->hasMany('App\Entities\Accounting\PackingOrderItemUv', 'do_uv_id', 'id');
    }

    public function customer_other_address()
    {
    	return $this->belongsTo('App\Entities\Master\CustomerOtherAddress', 'customer_other_address_id');
    }

    public function cashbackUv()
    {
        return $this->hasOne('App\Entities\Finance\CashbackUv', 'do_uv_id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Entities\Master\CustomerOtherAddress', 'customer_other_address_id', 'id');
    }

    public function do()
    {
        return $this->belongsTo('App\Entities\Penjualan\PackingOrder', 'do_id', 'id');
    }
}
