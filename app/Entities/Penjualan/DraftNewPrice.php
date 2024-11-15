<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;

class DraftNewPrice extends Model
{
    protected $table = "penjualan_setting_price_product";
    public $incrementing = false;

    protected $fillable =[
    	'kode_bahan',
    	'nama_bahan', 
        'kode_produk', 
        'nama_produk', 
        'packaging_id',
        'vendor_id', 
        'brand_name	', 
        'category_id', 
        'brand_reference_id', 
        'sub_brand_reference_id', 
        'harga_lama', 
        'harga_baru', 
        'status'
    ];

    const STATUS = [
        'INACTIVE' => 0,
        'ACTIVE' => 1
    ];
}
