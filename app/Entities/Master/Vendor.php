<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'code', 'name', 'address',
        'email', 'phone', 'owner_name', 'website',
        'description', 'status', 'type'
    ];
    
    protected $table = 'master_vendors';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];
    const TYPE = [
       'NON EKSEPEDISI' => 0,
       'EKSPEDISI' => 1,
       'FACTORY' => 2,
       'MITRA' => 3,
    ];

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }

    public function details()
    {
        return $this->hasMany('App\Entities\Master\VendorDetail', 'vendor_id');
    }

    public function so(){
        return $this->hasMany('App\Entities\Penjualan\SalesOrder','vendor_id');
    }
    public function do(){
        return $this->hasMany('App\Entities\Penjualan\PackingOrder','ekspedisi_id');
    }

    public function product()
    {
        return $this->belongsToMany('App\Entities\Master\Product', 'veendor_products', 'vendor_id', 'product_id')->withPivot('id');
    }
}
