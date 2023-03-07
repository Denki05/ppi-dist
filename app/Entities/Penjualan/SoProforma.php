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
		'type_transaction',
    	'so_id',
		'grand_total_idr',
    	'status',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];
    const STATUS = [
    	1 => 'ACTIVE',
    	2 => 'PAID OFF',
    	3 => 'DELETED',
    ];

	const TYPE_TRANSACTION = [
    	1 => 'CASH',
    	2 => 'TEMPO',
    	3 => 'MARKETPLACE'
    ];

    
    public function so(){
    	return $this->BelongsTo('App\Entities\Penjualan\Salesorder','so_id','id');
    }

	public function so_type_transaction()
    {
        if (isset($this->type_transaction)) {
            return (object) self::TYPE_TRANSACTION[$this->type_transaction];
        } else {
            return null;
        }
    }

	public function status()
    {
        return (object) self::STATUS[$this->status];
    }
    
}
