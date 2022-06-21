<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerOtherAddress extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'contact_person', 'npwp', 'phone', 'address',
        'gps_latitude', 'gps_longitude',
        'provinsi', 'kota', 'kecamatan', 'kelurahan',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan',
        'zipcode', 'status'
    ];
    protected $table = 'master_stores';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];
    
    public function customer()
    {
        return $this->belongsToMany('App\Entities\Master\Customer', 'store_id');
    }
}
