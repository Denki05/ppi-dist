<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrandLokal extends Model
{
    use SoftDeletes;

    protected $fillable = ['brand_name', 'category', 'type', 'packaging', 'status'];
    protected $table = 'master_brand_lokal';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    // public function sub_brand_references()
    // {
    //     return $this->hasMany('App\Entities\Master\SubBrandReference')->orderBy('name');
    // }
    // public function products()
    // {
    //     return $this->hasMany('App\Entities\Master\Product')->orderBy('name');
    // }
}
