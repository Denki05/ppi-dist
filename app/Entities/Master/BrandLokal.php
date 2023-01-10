<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Alfa6661\AutoNumber\AutoNumberTrait;

class BrandLokal extends Model
{
    use SoftDeletes, AutoNumberTrait;

    protected $fillable = ['code' ,'brand_name', 'status'];
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

    public function getAutoNumberOptions()
    {
        return [
            'code' => [
                'format' => function () {
                    return date('Y') . '.BR.?';
                },
                'length' => 3
            ]
        ];
    }
}