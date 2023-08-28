<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Alfa6661\AutoNumber\AutoNumberTrait;

class ProductChild extends Model
{
    use SoftDeletes;

    protected $fillable = [
                        'product_id', 
                        'material_code', 
                        'material_name', 
                        // 'brand_reference_id', 
                        'warehouse_id', 
                        'code', 
                        'name',
                        'price', 
                        'stock', 
                        'gender', 
                        'note', 
                        'status',
                    ];

    protected $table = 'master_products_child';
    public $incrementing = false;
    
    const NOTE = [
        'BEST SELLER',
        'NEW',
        'RECOMMENDATION',
        'REGULER',
        'SAMPLE',
    ];

    const GENDER = [
        'MALE',
        'FEMALE',
        'UNISEX',
    ];

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function product()
    {
        return $this->BelongsTo('App\Entities\Master\Product', 'product_id');
    }

}
