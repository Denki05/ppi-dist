<?php

namespace App\Entities;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoilerplateImage extends Model
{
    use SoftDeletes;

    protected $appends = ['image_url'];
    protected $fillable = ['image'];
    protected $table = 'boilerplate_images';
    public static $directory_image = 'superuser_assets/media/boilerplate/';

    public function getImageUrlAttribute()
    {
        if (!$this->image OR !file_exists(Self::$directory_image.$this->image)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image);
    }
}
