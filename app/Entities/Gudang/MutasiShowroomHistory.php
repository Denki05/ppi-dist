<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use App\Entities\Account\Superuser;

class MutasiShowroomHistory extends Model
{
    protected $table = 'penjualan_showroom_history';

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    protected $fillable = [
        'kode_mutasi',
        'total_mutasi',
        'status',
        'tanggal',
        'printed_at',
        'printed_by',
    ];
}
