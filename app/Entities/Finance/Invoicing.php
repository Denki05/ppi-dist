<?php

namespace App\Entities\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoicing extends Model
{
    use SoftDeletes;
    
    protected $table = "finance_invoicing";
    protected $fillable = [
    	'code',
    	'do_id',
        'customer_id',
        'customer_other_address_id',
    	'grand_total_idr',
    	'status',
    	'type',
        'image',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];

    const STATUS = [
        'ACTIVE' => 1,
        'DELETED' => 2,
        'REVISI' => 3,
        'PENDING' => 4,
    ];

    const TYPE = [
        'N' => 0, // Normal
        'TT' => 1, // TT
    ];

    public function do(){
    	return $this->BelongsTo('App\Entities\Penjualan\PackingOrder','do_id','id');
    }
    public function payable_detail(){
    	return $this->hasMany('App\Entities\Finance\PayableDetail','invoice_id');
    }
    public function getGrandTotalIdrAttribute($value)
    {
        return floatval($value);
    }

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }
}
