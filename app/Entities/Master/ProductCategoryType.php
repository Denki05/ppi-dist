<?php

namespace App\Entities\Master;

use App\Entities\Model;

class ProductCategoryType extends Model
{
    protected $fillable = ['product_packaging_id', 'category_id', 'type_id', 'fee'];
    protected $table = 'master_product_category_types';

    public function product_pack()
    {
        return $this->belongsTo('App\Entities\Master\ProductPack', 'product_packaging_id', 'id');
    }
}
