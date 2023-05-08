<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubBrandReference extends Model
{
    use SoftDeletes;

    protected $appends = ['image_botol_url', 'image_table_botol_url'];
    protected $fillable = [
                            'brand_reference_id', 'code', 'name',
                            'link', 'description', 'image_botol', 
                            'image_table_botol', 'status'
                    ];
    
    protected $table = 'master_sub_brand_references';
    public static $directory_image = 'superuser_assets/media/master/searah/';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function brand_reference()
    {
        return $this->BelongsTo('App\Entities\Master\BrandReference', 'brand_reference_id');
    }

    public function getImageBotolUrlAttribute()
    {
        if (!$this->image_botol OR !file_exists(Self::$directory_image.$this->image_botol)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image_botol);
    }

    public function getImageTableBotolUrlAttribute()
    {
        if (!$this->image_table_botol OR !file_exists(Self::$directory_image.$this->image_table_botol)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image_table_botol);
    }
}
