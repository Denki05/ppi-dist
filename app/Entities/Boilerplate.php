<?php

namespace App\Entities;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boilerplate extends Model
{
    use SoftDeletes;

    protected $appends = ['image_url', 'images_url'];
    protected $fillable = ['text', 'textarea', 'select', 'select_multiple', 'image'];
    protected $table = 'boilerplates';
    public static $directory_image = 'superuser_assets/media/boilerplate/';

    public static function boot()
    {
        parent::boot();

        static::deleting(function($boilerplate) {
             $boilerplate->images()->delete();
        });
    }

    public function images()
    {
        return $this->hasMany('App\Entities\BoilerplateImage');
    }

    public function setSelectMultipleAttribute($value)
    {
        $this->attributes['select_multiple'] = implode(',', $value);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image OR !file_exists(Self::$directory_image.$this->image)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image);
    }

    public function getImagesUrlAttribute()
    {
        if ($this->images()->exists()) {
            return $this->images->pluck('image_url');
        }

        return img_holder();
    }

}
