<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ekspedisi extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'address',
        'provinsi', 'kota', 'kecamatan', 'kelurahan',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan',
        'zipcode', 'email', 'phone', 'fax', 'owner_name', 'website',
        'description', 'status'
    ];
    protected $table = 'master_ekspedisi';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function so(){
        return $this->hasMany('App\Entities\Penjualan\SalesOrder','ekspedisi_id');
    }
    public function do(){
        return $this->hasMany('App\Entities\Penjualan\PackingOrder','ekspedisi_id');
    }

}
