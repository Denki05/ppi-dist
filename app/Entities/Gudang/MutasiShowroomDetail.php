<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use App\Entities\Account\Superuser;

class MutasiShowroomDetail extends Model
{
    protected $table = 'penjualan_showroom_detail';

    protected $fillable = [
        'penjualan_showroom_id',
        'product_packaging_id',
        'qty',
        'is_checked',
        'price_usd',
        'price_idr',
        'total_price',
        'note',
    ];

    public function product_packaging()
    {
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
    }

    public function mutasi_showroom()
    {
        return $this->belongsTo('App\Entities\Gudang\MutasiShowroom', 'penjualan_showroom_id', 'id');
    }
}
