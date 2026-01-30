<?php

namespace App\Entities\Gudang;

use Illuminate\Database\Eloquent\Model;
use App\Entities\Account\Superuser;

class MutasiShowroom extends Model
{
    protected $table = 'penjualan_showroom';

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    protected $fillable = [
        'kode',
        'brand_name',
        'type',
        'kurs',
        'warehouse_from_id',
        'warehouse_to_id',
        'customer_other_address_id',
        'tanggal',
        'status',
        'print_count',
        'printed_at',
        'created_by',
        'updated_by',
    ];

    const STATUS = [
        'DELETED'   => 0,
        'ACTIVE'    => 1, // Setelah admin input
        'PUBLISH'   => 2, // Setelah staff finance ubah status dan cetak dokumen
        'SETTLE'      => 3,
    ];

    const TYPE = [
        'SHOWROOM'      => 1,
        'FREE PRODUCT'  => 2,
        'KLAIM'         => 3,
        'BUNDLING'      => 4,
    ];

    const INTERNAL_TYPES = [1, 4]; // SHOWROOM, BUNDLING
    const EKSTERNAL_TYPES = [2, 3]; // FREE PRODUCT, KLAIM

    public static function isInternal($type)
    {
        return in_array((int)$type, self::INTERNAL_TYPES);
    }

    public static function isEksternal($type)
    {
        return in_array((int)$type, self::EKSTERNAL_TYPES);
    }

    public function details()
    {
        return $this->hasMany('App\Entities\Gudang\MutasiShowroomDetail', 'penjualan_showroom_id', 'id');
    }

    public function warehouse_from()
    {
        return $this->belongsTo('App\Entities\Master\Warehouse', 'warehouse_from_id', 'id');
    }

    public function customer_other_address()
    {
        return $this->belongsTo('App\Entities\Master\CustomerOtherAddress', 'customer_other_address_id', 'id');
    }

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }
}
