<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoProforma extends Model
{
	use SoftDeletes;
    protected $table = "so_proforma";
    protected $fillable =[
    	'code',
    	'so_id',
		'grand_total_idr',
    	'status',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];
    const STATUS = [
    	1 => 'Active',
    	2 => 'Deleted',
    	3 => 'Inactive'
    ];

    
    public function so(){
    	return $this->BelongsTo('App\Entities\Penjualan\Salesorder','so_id','id');
    }
    
}
