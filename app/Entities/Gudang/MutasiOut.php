<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use App\Entities\Account\Superuser;

class MutasiOut extends Model
{
    protected $table = 'mutasi_out';

    protected $fillable = [
        'code',
        'warehouse_from',
        'warehouse_to',
        'note',
        'status',
        'created_by',
        'acc_by',
        'acc_date'
    ];

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2
    ];

    public function mutasiOutDetails()
    {
        return $this->hasMany('App\Entities\Gudang\MutasiOutDetail', 'mutasi_out_id', 'id');
    }
}