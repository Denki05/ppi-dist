<?php

namespace App\Entities\Penjualan;

use App\Entities\Model;

class SaleReturnDetail extends Model
{
    protected $fillable = [
        'penjualan_retur_id', 'product_id',
        'qty', 'description'];
    protected $table = 'penjualan_retur_detail';

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product');
    }

    public function sale_return()
    {
        return $this->belongsTo('App\Entities\Penjualan\SaleReturn');
    }
}
