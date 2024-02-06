<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Alfa6661\AutoNumber\AutoNumberTrait;

class ProductPack extends Model
{
    use SoftDeletes;

    protected $fillable = [
                        'id', 
                        'product_id', 
                        'warehouse_id', 
                        'packaging_id', 
                        'material_code', 
                        'material_name', 
                        'code', 
                        'name',
                        'price', 
                        'gender', 
                        'note', 
                        'status',
                        'condition',
                        'updated_by',
                        'deleted_by',
                    ];

    protected $table = 'master_products_packaging';
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

    const CONDITION = [
        'ENABLE' => 0, 
        'DISABLE' => 1, 
    ];

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product', 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Entities\Master\Warehouse', 'warehouse_id');
    }

    public function packaging()
    {
        return $this->belongsTo('App\Entities\Master\Packaging', 'packaging_id');
    }

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }

    public function condition()
    {
        return array_search($this->condition, self::CONDITION);
    }

    public function kemasan()
    {
        $id_product = $this->id;
        
        $pecah = explode("-", $id_product);

        $packaging = Packaging::find($pecah[1]);

        return $packaging;
    }
}
