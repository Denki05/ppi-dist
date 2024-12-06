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
        'status',
    ];

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
    ];

    public function simulation_detail(){
    	return $this->hasMany('App\Entities\Accounting\FinanceSimulationPriceDetail', 'finance_simulation_id');
    }
}
