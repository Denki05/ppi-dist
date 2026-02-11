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
        'print_count',
        'printed_at',
        'note',
        'status',
        'status_checked',
        'status_barang',
        'created_by',
        'updated_by',
        'acc_by',
        'acc_date'
    ];

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE'  => 1,
        'PUBLISH' => 2,
        'ACC'     => 3
    ];

    const STATUS_CHECKED = [
        'PENDING' => 0,
        'CHECKED'   => 1,
        'CANCELED'   => 2,
    ];

    const STATUS_BARANG = [
        'PENDING' => 0,
        'BELUM_DIAMBIL'   => 1,
        'DIAMBIL'   => 2,
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

    public function statusChecked()
    {
        return array_search($this->status_checked, self::STATUS_CHECKED);
    }

    public function statusBarang()
    {
        return array_search($this->status_barang, self::STATUS_BARANG);
    }

    public function getDetailsAttribute()
    {
        return $this->mutasiOutDetails;
    }
}