<?php

namespace App\Entities\Enrichment;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReferenceMap extends Model
{
    use SoftDeletes;

    protected $table = 'product_reference_map';

    protected $fillable = ['product_id', 'fragrance_reference_id'];

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product');
    }

    public function reference()
    {
        return $this->belongsTo('App\Entities\Enrichment\FragranceReference');
    }
}
