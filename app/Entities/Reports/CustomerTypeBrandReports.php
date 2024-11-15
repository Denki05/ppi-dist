<?php

namespace App\Entities\Reports;

use Illuminate\Database\Eloquent\Model;

class CustomerTypeBrandReports extends Model
{
    protected $table = "report_customer_type_brand";
    protected $fillable =[
    	'customer_id',
    	'other_address_id',
    	'customer_name',
    	'customer_type',
    	'customer_kota',
    	'customer_provinsi',
    	'customer_zone',
    	'invoice_code',
    	'invoice_date',
        'invoice_brand',
        'invoice_type',
        'invoice_qty',
        'invoice_purchase',
        'invoice_delivery_order_cost',
    ];
    
}