<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receiving extends Model
{
	use SoftDeletes;
    protected $table = "receiving";
    protected $fillable = [
    	'warehouse_id',
        'code', 
        'receiving_number',
        'date', 
        'batch', 
        'number_po', 
        'description', 
        'status', 
    	
    ];

    public function warehouse(){
    	return $this->BelongsTo('App\Entities\Master\Warehouse','warehouse_id','id');
    }
}
