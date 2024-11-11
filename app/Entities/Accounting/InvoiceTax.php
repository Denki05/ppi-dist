<?php

namespace App\Entities\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceTax extends Model
{
    use SoftDeletes;
    
    protected $table = "finance_invoice_mitra";
    protected $fillable = [
    	'code', 
    	'do_id', 
    	'mitra_id', 
    	'type', 
    	'date', 
    	'note', 
    	'ppn_percent', 
    	'ppn_idr', 
    	'sub_total', 
    	'grand_total', 
    	'status', 
    	'created_by', 
    	'updated_by', 
    ];

    const STATUS = [
        'DELETED' => 0,
        'ACTICVE' => 1,
    ];

    const TYPE = [
        'INVOICE UNIFRA JUAL' => 1,
        'INVOICE UNIFRA BELI' => 2,
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

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }

    public function do(){
        return $this->belongsTo('App\Entities\Penjualan\packingOrder', 'do_id', 'id');
    }
}
