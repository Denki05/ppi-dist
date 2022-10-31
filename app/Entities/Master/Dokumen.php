<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dokumen extends Model
{
    use SoftDeletes;

    protected $appends = ['img_ktp', 'img_npwp'];
    protected $fillable = [
                            'id',
                            'customer_id',
                            'customer_other_address_id', 
                            'document_type',
                            'document_number',
                            'name_person', 
                            'address', 
                            'for_member', 
                            'image_npwp',
                            'image_ktp'
    ];
    protected $table = 'master_dokumen';
    public static $directory_image = 'superuser_assets/media/master/dokumen/';

    public function other_address()
    {
        return $this->belongsTo('App\Entities\Master\CustomerOtherAddress', 'customer_other_address_id');
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
