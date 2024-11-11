<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderKontrakItem extends Model
{
    use SoftDeletes;

    protected $table = "penjualan_so_kontrak_item";
    protected $fillable = [
        'so_kontrak_id', 
        'product_packaging_id', 
        'packaging_id', 
        'price', 
        'qty', 
        'qty_sent', 
        'disc_usd'
    ];

    public function so_kontrak(){
        return $this->belongsTo('App\Entities\Penjualan\SalesOrderKontrak', 'so_kontrak_id', 'id');
    }

    public function product_pack(){
        return $this->BelongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
    }

    public function packaging(){
        return $this->BelongsTo('App\Entities\Master\Packaging', 'packaging_id', 'id');
    }
}