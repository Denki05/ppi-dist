<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;

class PackingOrderCost extends Model
{
	protected $table = "penjualan_do_other_cost";
    protected $fillable =[
    	'do_id',
    	'cost_idr',
    	'note',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];

    public function getCostIdrAttribute($value)
    {
        return floatval($value);
    }
}
