<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatalogDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'catalog_id',
        'product_id',
        'created_by',
    ];
    protected $table = 'master_catalog_detail';

    public function catalog()
    {
        return $this->BelongsTo('App\Entities\Master\Catalog', 'catalog_id');
    }
}
