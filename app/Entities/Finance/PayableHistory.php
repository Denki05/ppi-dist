<?php

namespace App\Entities\Finance;

use Illuminate\Database\Eloquent\Model;

class PayableHistory extends Model
{
    protected $table = "payable_history";
    protected $fillable = [
    	'payable_id',
    	'do_id',
        'invoice_id', 
    	'invoice_code',
        'payable_code',
        'customer_other_address_id',
        'acc_by', 
    	'created_by',
    ];
}