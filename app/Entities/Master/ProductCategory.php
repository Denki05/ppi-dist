<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'description', 'status','image_header_list','image_header_pice'];
    protected $table = 'master_product_categories';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function types()
    {
        // return $this->belongsTo('App\Entities\Master\ProductType');
        return $this->belongsToMany('App\Entities\Master\ProductType', 'master_product_category_types', 'category_id', 'type_id')->withPivot('id');
    }

    public function products()
    {
        return $this->hasMany('App\Entities\Master\Product', 'category_id');
    }
}
