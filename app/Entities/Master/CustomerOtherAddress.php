<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerOtherAddress extends Model
{
    use SoftDeletes;

    protected $appends = ['img_npwp'];
    protected $fillable = [
        'name', 'contact_person', 'phone', 'npwp', 'address',
        'gps_latitude', 'gps_longitude',
        'provinsi', 'kota', 'kecamatan', 'kelurahan',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan',
        'zipcode', 'image_npwp', 'status'
    ];
    protected $table = 'master_stores';
    public static $directory_image = 'superuser_assets/media/master/store/';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];
    
    public function member()
    {
        return $this->hasMany('App\Entities\Master\Customer');
    }

    public function getImgNpwpAttribute()
    {
        if (!$this->image_npwp OR !file_exists(Self::$directory_image.$this->image_npwp)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image_npwp);
    }
}
