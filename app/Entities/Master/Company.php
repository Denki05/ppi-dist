<?php

namespace App\Entities\Master;

use App\Entities\Model;

class Company extends Model
{
    protected $appends = ['logo_url'];
    protected $table = 'master_company';
    public static $directory_image = 'superuser_assets/media/master/company/';

    public function getLogoUrlAttribute()
    {
        if (!$this->logo OR !file_exists(Self::$directory_image.$this->logo)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->logo);
    }
}
