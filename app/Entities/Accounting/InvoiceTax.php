<?php

namespace App\Entities\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceTax extends Model
{
    use SoftDeletes;
    protected $table = "finance_invoice_mitra";
    protected $fillable = [
    	'no_invoice_tax',
    	'no_invoice_real',
        'customer_other_address_id',
        'mitra_id',
    	'tot_hit_baru',
        'kurs',
        'invoice_tax_date',
        'type', 
        'note', 
    	'updated_by',
    	'created_by',
    	'deleted_by'
    ];

    const TYPE = [
        'INVOICE TAX JUAL' => 1,
        'INVOICE TAX BELI' => 2,
    ];

    // public function invoice_tax_detail(){
    // 	return $this->hasMany('App\Entities\Finance\InvoiceTaxDetail',' invoice_tax_id');
    // }
    public function invoice_tax_detail(){
    	return $this->hasMany('App\Entities\Accounting\InvoiceTaxDetail','invoice_tax_id');
    }

    public function mitra(){
    	return $this->belongsTo('App\Entities\Master\Mitra', 'mitra_id', 'id');
    }

    public function member(){
    	return $this->belongsTo('App\Entities\Master\CustomerOtherAddress', 'customer_other_address_id', 'id');
    }

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }
}
