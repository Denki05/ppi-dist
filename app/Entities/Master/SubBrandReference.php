<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubBrandReference extends Model
{
    use SoftDeletes;

    protected $fillable = ['brand_reference_id', 'code', 'name', 'link', 'description', 'status'];
    protected $table = 'master_sub_brand_references';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function brand_reference()
    {
        return $this->BelongsTo('App\Entities\Master\BrandReference');
    }
}
