<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductType extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'description', 'status'];
    protected $table = 'master_product_types';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function products()
    {
        return $this->hasMany('App\Entities\Master\Product', 'type_id');
    }
}
