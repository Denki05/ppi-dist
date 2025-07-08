<?php

namespace App\Entities\Penjualan;
use Illuminate\Database\Eloquent\Model;

class MigrasiSoList extends Model
{
    protected $table = 'migrasi_so_list';
    protected $guarded = [];
    public $timestamps = false;

    public function migrasi_so_header()
    {
        return $this->belongsTo(MigrasiSoHeader::class, 'so_id', 'id');
    }
}