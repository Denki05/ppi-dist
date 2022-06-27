<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerOtherAddress extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id', 'name', 'contact_person', 'phone', 'address',
        'gps_latitude', 'gps_longitude',
        'provinsi', 'kota', 'kecamatan', 'kelurahan',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan',
        'zipcode', 'status'
    ];
    protected $table = 'master_customer_other_addresses';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];
    
    public function customer()
    {
        return $this->belongsTo('App\Entities\Master\Customer');
    }

    public function po(){
        return $this->hasMany('App\Entities\Penjualan\PackingOrder','customer_other_address_id');
    }
}
