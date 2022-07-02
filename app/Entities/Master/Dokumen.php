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
                            'customer_other_address',
                            'name',
                            'contact',
                            'npwp',
                            'ktp',
                            'image_npwp',
                            'image_ktp'
    ];
    protected $table = 'master_dokumen';
    public static $directory_image = 'superuser_assets/media/master/dokumen/';

    public function member()
    {
        return $this->belongsTo('App\Entities\Master\CustomerOtherAddress');
    }
}
