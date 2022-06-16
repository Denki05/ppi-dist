<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerOtherAddress extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'contact_person', 'phone', 'npwp', 'address',
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
        return $this->BelongsTo('App\Entities\Master\Customer');
    }
}
