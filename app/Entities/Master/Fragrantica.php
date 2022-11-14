<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fragrantica extends Model
{
    use SoftDeletes;

    // protected $appends = ['image_url', 'image_hd_url'];s
    protected $fillable = [
                        'product_id', 'brand_references_id', 
                        'parfume_scent', 'scent_range'
                    ];

    protected $table = 'master_product_fragrantica';
    // public static $directory_image = 'superuser_assets/media/master/product/';
    
    // const NOTE = [
    //     'BEST SELLER',
    //     'NEW',
    //     'RECOMMENDATION',
    //     'REGULER',
    //     'SAMPLE',
    // ];

    // const STATUS = [
    //     'DELETED' => 0,
    //     'ACTIVE' => 1,
    //     'INACTIVE' => 2
    // ];

    public function product()
    {
        return $this->hasOne('App\Entities\Master\Product');
    }

    public function brand_reference()
    {
        return $this->BelongsTo('App\Entities\Master\BrandReference');
    }
}
