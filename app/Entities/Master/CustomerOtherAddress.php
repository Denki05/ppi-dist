<?php

namespace App\Entities\Master;

use App\Entities\Model;

class CustomerOtherAddress extends Model
{
   
    protected $appends = ['img_ktp', 'img_npwp'];
    protected $fillable = [
        'customer_id', 'name', 'contact_person', 'npwp', 'ktp', 'phone', 'address',
        'gps_latitude', 'gps_longitude',
        'provinsi', 'kota', 'kecamatan', 'kelurahan',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan',
        'zipcode', 'image_npwp', 'image_ktp', 'status'
    ];
    protected $table = 'master_customer_other_addresses';
    public static $directory_image = 'superuser_assets/media/master/member/';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];
    
    public function customer()
    {
        return $this->BelongsTo('App\Entities\Master\Customer', 'customer_id');
    }

    public function dokumen(){
        return $this->hasMany('App\Entities\Master\Dokumen','customer_other_address_id');
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
}
