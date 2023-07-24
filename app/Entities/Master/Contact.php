<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $appends = ['img_ktp', 'img_npwp'];
    protected $fillable = ['name', 'phone', 'email', 'position', 'dob', 'npwp', 'ktp', 'image_ktp', 'image_npwp', 'address', 'status', 'is_for'];
    protected $table = 'master_contacts';
    public $incrementing = false;
    public static $directory_image = 'superuser_assets/media/master/contact/';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    const IS_FOR = [
      'MEMBER' => 0,
      'VENDOR' => 1
  ];

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
