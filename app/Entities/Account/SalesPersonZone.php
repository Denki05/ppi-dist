<?php

namespace App\Entities\Account;

use App\Entities\Model;

class SalesPersonZone extends Model
{
    protected $fillable = ['sales_person_id', 'provinsi', 'kota', 'kecamatan', 'kelurahan', 'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan'];
    protected $table = 'sales_person_zones';
}
