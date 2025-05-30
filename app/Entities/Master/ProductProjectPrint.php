<?php

namespace App\Entities\Master;

use Illuminate\Database\Eloquent\Model;

class ProductProjectPrint extends Model
{
    protected $table = 'master_products_project_print';
    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'tipe',
        'kategori',
        'brand',
        'searah',
        'sex',
        'harga',
        'created_by',
    ];
}
