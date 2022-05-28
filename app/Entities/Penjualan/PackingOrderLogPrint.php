<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;

class PackingOrderLogPrint extends Model
{
    protected $table = "penjualan_do_log_print";
    protected $fillable = [
    	'do_id',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];
    public function do(){
    	return $this->belongsTo('App\Entities\Penjualan\PackingOrder','do_id','id');
    }
}
