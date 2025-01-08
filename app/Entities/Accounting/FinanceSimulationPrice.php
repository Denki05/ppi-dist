<?php

namespace App\Entities\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceSimulationPrice extends Model
{
    use SoftDeletes;

    protected $table = "finance_simulation_price";
    protected $fillable = [
    	'code',
    	'do_id',
        'status',
    ];

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
    ];

    public function simulation_detail(){
    	return $this->hasMany('App\Entities\Accounting\FinanceSimulationPriceDetail', 'finance_simulation_id');
    }

    public function do(){
    	return $this->belongsTo('App\Entities\Penjualan\PackingOrder', 'do_id');
    }
}
