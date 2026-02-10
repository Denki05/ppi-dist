<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use App\Entities\Account\Superuser;

class MutasiOut extends Model
{
    protected $table = 'gudang_mutasi_out';

    protected $fillable = [
        'code',
        'date',
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
        'ACTIVE'  => 1,
        'PUBLISH' => 2,
        'ACC'     => 3
    ];

    public function mutasiOutDetails()
    {
        return $this->hasMany('App\Entities\Gudang\MutasiOutDetail', 'mutasi_out_id', 'id');
    }

    public function warehouse_from_attribute()
    {
        return $this->belongsTo('App\Entities\Master\Warehouse', 'warehouse_from', 'id');
    }

    public function warehouse_to_attribute()
    {
        return $this->belongsTo('App\Entities\Master\Warehouse', 'warehouse_to', 'id');
    }

    public function statusLabel()
    {
        switch ($this->status) {
            case self::STATUS['DELETED']:
                return '<span class="badge bg-warning">Draft</span>';
            case self::STATUS['ACTIVE']:
                return '<span class="badge bg-info">Aktif</span>';
            case self::STATUS['PUBLISH']:
                return '<span class="badge bg-primary">Publish</span>';
            case self::STATUS['ACC']:
                return '<span class="badge bg-success">ACC</span>';
            default:
                return '<span class="badge bg-secondary">Unknown</span>';
        }
    }
}