<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Alfa6661\AutoNumber\AutoNumberTrait;

class Product extends Model
{
    use SoftDeletes;

    protected $appends = ['image_url', 'image_hd_url'];
    protected $fillable = [
                        'vendor_id', 'packaging_id', 'category_id', 'type_id', 'brand_reference_id', 'sub_brand_reference_id', 'brand_name',
                        'code', 'name', 'material_code', 'material_name', 'alias', 'description', 
                        'default_quantity', 'default_unit_id', 'ratio', 'default_warehouse_id',
                        'buying_price', 'selling_price', 'image', 'image_hd', 'status', 'gender'
                    ];

    protected $table = 'master_products';
    public $incrementing = false;
    public static $directory_image = 'superuser_assets/media/master/product/';

    // protected $casts = [
    //     'vendor_id' => 'array',
    // ];
    
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
        'ACTIVE' => 1,
        'INACTIVE' => 2
    ];

    public function sub_brand_reference()
    {
        return $this->BelongsTo('App\Entities\Master\SubBrandReference', 'sub_brand_reference_id');
    }

    public function brand_ppi()
    {
        return $this->BelongsTo('App\Entities\Master\BrandLokal', 'brand_lokal_id');
    }

    public function packaging()
    {
        return $this->BelongsTo('App\Entities\Master\Packaging', 'packaging_id');
    }

    public function category()
    {
        return $this->BelongsTo('App\Entities\Master\ProductCategory', 'category_id');
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

    public function detail_proforma()
    {
        return $this->hasMany('App\Entities\Penjualan\SoProformaDetail');
    }

    public function product_child()
    {
        return $this->hasMany('App\Entities\Master\ProductPack', 'product_id');
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

    public function frag()
    {
        return $this->hasMany('App\Entities\Master\Fragrantica');
    }

    public function factory(){
        return $this->belongsTo('App\Entities\Master\Vendor', 'vendor_id', 'id');
    }

    public function updatedBySuperuser()
    {
        $superuser = Superuser::find($this->updated_by);

        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
    }

    // public function setVendorAttribute($value)
    // {
    //     $this->attributes['vendor_id'] = json_encode($value);
    // }

    // public function getVendorAttribute($value)
    // {
    //     return $this->attributes['vendor_id'] = json_decode($value);
    // }
}
