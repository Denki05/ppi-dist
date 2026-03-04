<?php

namespace App\Entities\Penjualan;

use App\Entities\Model;

class SaleReturnDetail extends Model
{
    protected $fillable = [
        'return_id',
        'product_packaging_id',
        'qty',
        'price',
        'disc_usd',
        'note',
    ];

    protected $table = 'penjualan_retur_detail';

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
    }

    public function sale_return()
    {
        return $this->belongsTo('App\Entities\Penjualan\SaleReturn', 'retur_id', 'id');
    }
}