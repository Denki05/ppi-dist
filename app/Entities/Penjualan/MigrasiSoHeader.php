<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;

class MigrasiSoHeader extends Model
{
    protected $table = 'migrasi_so_header';
    protected $guarded = [];
    public $timestamps = false;

    public function migrasi_so_list()
    {
        return $this->hasMany(MigrasiSoList::class, 'so_id', 'id');
    }
}
