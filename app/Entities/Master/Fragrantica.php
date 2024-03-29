<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fragrantica extends Model
{
    use SoftDeletes;

    // protected $appends = ['image_url', 'image_hd_url'];s
    protected $fillable = [
                        'product_id', 'brand_reference_id', 
                        'parfume_scent', 'scent_range', 'color_scent'
                    ];

    protected $table = 'master_product_fragrantica';

    public function product()
    {
        return $this->BelongsTo('App\Entities\Master\Product', 'product_id');
    }

    public function brand_reference()
    {
        return $this->BelongsTo('App\Entities\Master\BrandReference', 'brand_reference_id');
    }
}
