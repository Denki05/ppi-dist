<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;

class SalesOrderProformaItem extends Model
{
    protected $table = "penjualan_so_proforma_item";
    
    protected $fillable = [
        'so_proforma_id',
        'product_packaging_id', 
        'price',
        'qty',
        'disc_usd',
        'packaging_id', 
        'total_item',
        'free_product'
    ];

    public function soProforma(){
        return $this->belongsTo('App\Entities\Penjualan\SalesOrderProforma', 'so_proforma_id', 'id');
    }

    public function productPack(){
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
    }

    public function packaging(){
        return $this->belongsTo('App\Entities\Master\Packaging', 'packaging_id', 'id');
    }
}