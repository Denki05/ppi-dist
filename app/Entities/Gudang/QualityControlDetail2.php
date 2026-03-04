<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class QualityControlDetail2 extends Model
{
    protected $table = 'receiving_komplain_detail';
    protected $fillable = [
        'receving_komplain_id', 
        'product_packaging_id',
        'qty',
    ];

    public function quality_control()
    {
        return $this->belongsTo('App\Entities\Gudang\QualityControl2', 'receving_komplain_id', 'id');
    }

    public function product_pack()
    {
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
    }
}