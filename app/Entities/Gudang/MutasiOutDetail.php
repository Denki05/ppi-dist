<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use App\Entities\Account\Superuser;

class MutasiOutDetail extends Model
{
    protected $table = "gudang_mutasi_out_detail";
    protected $fillable = [
        'mutasi_out_id',
        'product_packaging_id',
        'quantity',
        'note'
    ];

    public function mutasiOut()
    {
        return $this->belongsTo('App\Entities\Gudang\MutasiOut', 'mutasi_out_id');
    }

    public function productPackaging()
    {
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id');
    }
}