<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Alfa6661\AutoNumber\AutoNumberTrait;

class ProductCategory extends Model
{
    use SoftDeletes, AutoNumberTrait;

    protected $fillable = ['brand_lokal_id', 'code', 'name', 'type', 'status','packaging','status'];
    protected $table = 'master_product_category';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    // public function types()
    // {
    //     // return $this->belongsTo('App\Entities\Master\ProductType');
    //     return $this->belongsToMany('App\Entities\Master\ProductType', 'master_product_category_types', 'category_id', 'type_id')->withPivot('id');
    // }

    public function products()
    {
        return $this->hasMany('App\Entities\Master\Product', 'category_id');
    }

    public function getAutoNumberOptions()
    {
        return [
            'code' => [
                'format' => function () {
                    return date('Y') . '.PCT.?';
                },
                'length' => 3
            ]
        ];
    }
}
