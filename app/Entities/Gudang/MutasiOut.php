<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use App\Entities\Account\Superuser;

class MutasiOut extends Model
{
    protected $table = 'gudang_mutasi_out';

    protected $fillable = [
        'code',
        'warehouse_from',
        'warehouse_to',
        'type',
        'spk_id',
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

    const TYPE = [
        'REGULAR' => 0,
        'INDUSTRI' => 1
    ];

    public function mutasiOutDetails()
    {
        return $this->hasMany('App\Entities\Gudang\MutasiOutDetail', 'mutasi_out_id', 'id');
    }

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }

    public function warehouse_from_attribute()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse', 'warehouse_from', 'id');
    }

    public function warehouse_to_attribute()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse', 'warehouse_to', 'id');
    }

    public function spk()
    {
        return $this->BelongsTo('App\Entities\Gudang\PurchaseOrder', 'spk_id', 'id');
    }
}