<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    
    protected $appends = ['img_store', 'img_ktp', 'img_npwp'];
    protected $fillable = [
        'category_id', /* 'type_id', */ 'store_id', 'code', 'name',
        'email', 'phone', 'ktp', 'npwp', 'address',
        'owner_name', 'plafon_piutang', 'gps_latitude', 'gps_longitude',
        'provinsi', 'kota', 'kecamatan', 'kelurahan',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan',
        'zipcode', 'image_store', 'image_ktp', 'image_npwp', 'notification_email', 'status'
    ];
    protected $table = 'master_members';
    public static $directory_image = 'superuser_assets/media/master/customer/';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function category()
    {
        return $this->BelongsTo('App\Entities\Master\CustomerCategory');
    }

    public function types()
    {
        // return $this->BelongsTo('App\Entities\Master\CustomerType');
        return $this->belongsToMany('App\Entities\Master\CustomerType', 'master_customer_type_pivot', 'customer_id', 'type_id')->withPivot('id');
    }

    public function store()
    {
        return $this->BelongsTo('App\Entities\Master\CustomerOtherAddress');
    }

    public function getImgStoreAttribute()
    {
        if (!$this->image_store OR !file_exists(Self::$directory_image.$this->image_store)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image_store);
    }

    public function getImgKtpAttribute()
    {
        if (!$this->image_ktp OR !file_exists(Self::$directory_image.$this->image_ktp)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image_ktp);
    }

    public function getImgNpwpAttribute()
    {
        if (!$this->image_npwp OR !file_exists(Self::$directory_image.$this->image_npwp)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image_npwp);
    }

    public function do(){
        return $this->hasMany('App\Entities\Penjualan\PackingOrder','customer_id');
    }

    function contact(){
		return $this->hasMany('App\Entities\Master\Contact','customer_id');
	}
}
