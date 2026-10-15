<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HalalList extends Model
{
    protected $table = 'halal_lists';

    protected $fillable = [
        'document_id',
        'sumber',
        'nomor_urut',
        'nama_lengkap',
        'brand_parsed',
        'nama_parsed',
        'kode_parsed',
        'sumber_version',
    ];
}
