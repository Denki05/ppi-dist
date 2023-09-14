<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receiving extends Model
{
	use SoftDeletes;
    protected $table = "receiving";
    protected $fillable = [
    	'code', 
        'warehouse_id', 
        'pbm_date', 
        'status', 
        'acc_by', 
        'acc_at', 
        'note',
    ];

    public function warehouse(){
    	return $this->BelongsTo('App\Entities\Master\Warehouse','warehouse_id','id');
    }
}
