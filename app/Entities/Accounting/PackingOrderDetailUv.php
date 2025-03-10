<?php

namespace App\Entities\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackingOrderDetailUv extends Model
{
    protected $table = "penjualan_do_detail_uv";
    protected $fillable = [
    	'do_uv_id',
    	'disc_1',
    	'disc_2',
    	'disc_1_idr',
    	'disc_2_idr',
    	'disc_idr',
    	'voucher_idr',
    	'ppn_percent',
    	'ppn_idr',
    	'delivery_cost_idr',
    	'grand_total_idr',
    ];

    public function simulation_price(){
    	return $this->belongsTo('App\Entities\Accounting\FinanceSimulationPrice', 'do_uv_id', 'id');
    }
}
