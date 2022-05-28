<?php

namespace App\Entities\Master;

use App\Entities\Model;

class CustomerCategoryType extends Model
{
    protected $fillable = ['category_id', 'type_id'];
    protected $table = 'master_customer_category_types';
}
