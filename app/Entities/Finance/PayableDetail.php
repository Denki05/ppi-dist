<?php

namespace App\Entities\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayableDetail extends Model
{
    use SoftDeletes;
    protected $table = "finance_payable_detail";
    protected $fillable = [
    	'payable_id',
    	'invoice_id',
    	'prev_account_receivable',
    	'total',
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];

    public function payable(){
    	return $this->BelongsTo('App\Entities\Finance\Payable','payable_id','id');
    }
    public function invoice(){
    	return $this->BelongsTo('App\Entities\Finance\Invoicing','invoice_id','id');
    }
    public function getTotalAttribute($value)
    {
        return floatval($value);
    }
}
