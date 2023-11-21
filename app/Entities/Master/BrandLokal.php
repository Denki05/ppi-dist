<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrandLokal extends Model
{
    use SoftDeletes;

    protected $fillable = ['brand_name', 'status'];
    protected $table = 'master_brand_lokal';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function category()
    {
        return $this->hasMany('App\Entities\Master\ProductCategory', 'brand_lokal_id');
    }

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }
}