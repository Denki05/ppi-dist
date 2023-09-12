<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Alfa6661\AutoNumber\AutoNumberTrait;

class ProductPack extends Model
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
                        // 'stock', 
                        'gender', 
                        'note', 
                        'status',
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

    public function product()
    {
        return $this->BelongsTo('App\Entities\Master\Product', 'product_id');
    }

    public function purchase_order_detail()
    {
        return $this->belongsTo(PurchaseOrderDetail::class, 'product_packaging_id', 'id');
    }

    public function warehouse()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse', 'warehouse_id');
    }

    public function kemasan()
    {
        $id_product = $this->id;
        
        $pecah = explode("-", $id_product);

        $packaging = Packaging::find($pecah[1]);

        // if($packaging){
        //     return $packaging->pack_name;
        // }
        return $packaging;
    }
}
