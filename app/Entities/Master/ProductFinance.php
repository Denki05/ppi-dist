<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductFinance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code_product', 
        'name_product', 
        'mitra_id', 
        'product_id', 
        'selling_price_usd_drum', 
        'buying_price_usd_drum', 
        'selling_price_usd_unit', 
        'buying_price_usd_unit',
        'status', 
        'created_by', 
        'updated_by', 
        'created_at',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];

    protected $table = 'master_product_finance';
    public $incrementing = false;

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function master_product()
    {
        return $this->belongsTo('App\Entities\Master\Product', 'product_id');
    }

    public function mitra()
    {
        return $this->belongsTo('App\Entities\Master\Mitra', 'mitra_id', 'id');
    }

    public function invoice_tax_detail()
    {
        return $this->belongsTo('App\Entities\Finance\InvoiceTaxDetail', 'product_tax_id');
    }

    public function log_price()
    {
        return $this->hasMany('App\Entities\Accounting\PriceLogFinance', 'product_finance_id');
    }

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }
}