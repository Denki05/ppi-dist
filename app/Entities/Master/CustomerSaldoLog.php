<?php

namespace App\Entities\Master;

use App\Entities\Model;

class CustomerSaldoLog extends Model
{

    protected $fillable = [
        'customer_id', 'saldo_log', 'note'
    ];
    protected $table = 'master_customer_saldo_log';

    const NOTE = [
        'SALDO AWAL' => 0,
        'SALDO UPDATE' => 1,
        'SALDO PENGURANGAN SO' => 2
    ];

    public function customer()
    {
        return $this->belongsTo('App\Entities\Master\Customer');
    }

}
