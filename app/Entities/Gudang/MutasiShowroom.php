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
        'so_id',
        'tanggal',
        'status',
        'status_checked',
        'status_barang',
        'image',
        'taken_at',
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

    const INTERNAL_TYPES = [1, 4]; // SHOWROOM, BUNDLING
    const EKSTERNAL_TYPES = [2, 3]; // FREE PRODUCT, KLAIM
    
    const TYPE_SYSTEM_FREE_SO = 5;


    public static function isInternal($type)
    {
        return in_array((int)$type, self::INTERNAL_TYPES);
    }

    public static function isEksternal($type)
    {
        return in_array((int)$type, self::EKSTERNAL_TYPES);
    }
    
    public static function getKodePrefixByType(int $type): string
    {
        if ($type === self::TYPE_SYSTEM_FREE_SO) {
            return 'MS-P';
        }

        if (
            in_array($type, self::INTERNAL_TYPES) ||
            in_array($type, self::EKSTERNAL_TYPES)
        ) {
            return 'MS';
        }

        throw new \InvalidArgumentException('Type mutasi tidak valid');
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
    
    public function so()
    {
        return $this->belongsTo('App\Entities\Penjualan\SalesOrder', 'so_id', 'id');
    }
    
    public function statusChecked()
    {
        return array_search($this->status_checked, self::STATUS_CHECKED);
    }

    public function statusBarang()
    {
        return array_search($this->status_barang, self::STATUS_BARANG);
    }
}