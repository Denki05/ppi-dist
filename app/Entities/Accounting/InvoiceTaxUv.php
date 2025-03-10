<?php

namespace App\Entities\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceTaxUv extends Model
{
    use SoftDeletes;
    
    protected $table = "finance_invoice_mitra_uv";
    protected $fillable = [
    	'code', 
    	'do_uv_id', 
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
        'ACTIVE' => 1,
    ];

    const TYPE = [
        'INVOICE UNIFRA JUAL' => 1,
        'INVOICE UNIFRA BELI' => 2,
    ];

    public function invoice_tax_detail(){
    	return $this->hasMany('App\Entities\Accounting\InvoiceTaxDetailUv','invoice_mitra_id', 'id');
    }

    public function mitra(){
    	return $this->belongsTo('App\Entities\Master\Mitra', 'mitra_id', 'id');
    }

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }

    public function do_uv(){
        return $this->belongsTo('App\Entities\Accounting\PackingOrderUv', 'do_uv_id', 'id');
    }
}
