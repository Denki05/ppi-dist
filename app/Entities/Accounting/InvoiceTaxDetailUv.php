<?php

namespace App\Entities\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceTaxDetailUv extends Model
{
    use SoftDeletes;
    protected $table = "finance_invoice_mitra_detail_uv";
    protected $fillable = [
    	'invoice_mitra_id',
    	'product_finance_id',
        'price',
        'qty',
        'sub_total',
    ];

    public function invoice_tax(){
    	return $this->belongsTo('App\Entities\Accounting\InvoiceTax', 'invoice_tax_id', 'id');
    }

    public function product_tax(){
    	return $this->belongsTo('App\Entities\Master\ProductFinance', 'product_finance_id', 'id');
    }
}
