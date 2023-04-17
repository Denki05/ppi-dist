<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Alfa6661\AutoNumber\AutoNumberTrait;

class ProductCategory extends Model
{
    use SoftDeletes, AutoNumberTrait;

    protected $fillable = ['brand_lokal_id', 'packaging_id', 'brand_name', 'code', 'name', 'type','status'];
    protected $table = 'master_product_categories';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    

    public function products()
    {
        return $this->hasMany('App\Entities\Master\Product', 'category_id');
    }

    public function brand_lokal()
    {
        return $this->belongsTo('App\Entities\Master\BrandLokal', 'brand_lokal_id');
    }

    public function packaging()
    {
        return $this->belongsTo('App\Entities\Master\Packaging', 'packaging_id');
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
