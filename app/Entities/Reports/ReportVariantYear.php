<?php

namespace App\Entities\Reports;

use Illuminate\Database\Eloquent\Model;

class ReportVariantYear extends Model
{
    protected $table = 'report_variant_year';

    protected $fillable = [
        'brand_name',
        'material_code',
        'material_name',
        'product_code',
        'product_name',
        'packaging',
        'tahun',
        'qty',
    ];

    public function getQtyAttribute($value)
    {
        return floatval($value);
    }
}