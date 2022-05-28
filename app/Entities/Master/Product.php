<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $appends = ['image_url', 'image_hd_url'];
    protected $fillable = [
                        'brand_reference_id', 'sub_brand_reference_id', 'category_id', 'type_id',
                        'code', 'name', 'material_code', 'material_name', 'description', 
                        'default_quantity', 'default_unit_id', 'default_warehouse_id',
                        'buying_price', 'selling_price', 'image', 'image_hd', 'status'
                    ];

    protected $table = 'master_products';
    public static $directory_image = 'superuser_assets/media/master/product/';
    
    const NOTE = [
        'BEST_SELLER',
        'NEW',
        'RECOMMENDATION',
        'REGULER',
        'SAMPLE',
    ];

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'INACTIVE' => 2
    ];

    public function brand_reference()
    {
        return $this->BelongsTo('App\Entities\Master\BrandReference');
    }

    public function sub_brand_reference()
    {
        return $this->BelongsTo('App\Entities\Master\SubBrandReference');
    }

    public function category()
    {
        return $this->BelongsTo('App\Entities\Master\ProductCategory');
    }

    public function type()
    {
        return $this->BelongsTo('App\Entities\Master\ProductType');
    }

    public function default_unit()
    {
        return $this->belongsTo('App\Entities\Master\Unit');
    }

    public function default_warehouse()
    {
        return $this->belongsTo('App\Entities\Master\Warehouse');
    }

    public function min_stocks()
    {
        return $this->hasMany('App\Entities\Master\ProductMinStock');
    }

    public function setting_price_log()
    {
        return $this->hasMany('App\Entities\Penjualan\SettingPriceLog');
    }
    public function getImageUrlAttribute()
    {
        if (!$this->image OR !file_exists(Self::$directory_image.$this->image)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image);
    }

    public function getImageHDUrlAttribute()
    {
        if (!$this->image_hd OR !file_exists(Self::$directory_image.$this->image_hd)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image_hd);
    }

    public function getBuyingPriceAttribute($value)
    {
        return floatval($value);
    }

    public function getSellingPriceAttribute($value)
    {
        return floatval($value);
    }

    public function getDefaultQuantityAttribute($value)
    {
        return floatval($value);
    }

    public function do_item(){
        return $this->hasMany('App\Entities\Penjualan\PackingOrderItem','product_id');
    }
    public function so_item(){
        return $this->hasMany('App\Entities\Penjualan\SalesOrderItem','product_id');
    }
}
