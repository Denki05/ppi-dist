<?php

namespace App\Entities\Master;

use App\Entities\Model;

class ProductCategoryType extends Model
{
    protected $fillable = ['category_id', 'type_id'];
    protected $table = 'master_product_category_types';
}
