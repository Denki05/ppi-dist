<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'code', 'name', 'address',
        'provinsi', 'kota', 'kecamatan', 'kelurahan',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan',
        'zipcode', 'email', 'phone', 'owner_name', 'website',
        'description', 'status'
    ];
    
    protected $table = 'master_vendors';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function contacts()
    {
        return $this->belongsToMany('App\Entities\Master\Contact', 'master_vendor_contacts', 'vendor_id', 'contact_id')->withPivot('id');
    }
}
