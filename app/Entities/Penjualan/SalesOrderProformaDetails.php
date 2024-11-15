<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;

class SalesOrderProformaDetails extends Model
{
    protected $table = "penjualan_so_proforma_detail";
    protected $fillable = [
        'so_proforma_id',
        'discount_1_percent',
        'discount_1', 
        'discount_2_percent',
        'discount_2',
        'discount_idr',
        'voucher_idr',
        'purchase_total_idr',
        'delivery_cost_idr',
        'grand_total_idr',
    ];

    public function so_proforma(){
        return $this->belongsTo('App\Entities\Penjualan\SalesOrderproforma', 'so_proforma_id', 'id');
    }
}
