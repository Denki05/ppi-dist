<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrandReference extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'description', 'status'];
    protected $table = 'master_brand_references';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function sub_brand_references()
    {
        return $this->hasMany('App\Entities\Master\SubBrandReference', 'brand_reference_id');
    }
    public function products()
    {
        return $this->hasMany('App\Entities\Master\Product')->orderBy('name');
    }
}
