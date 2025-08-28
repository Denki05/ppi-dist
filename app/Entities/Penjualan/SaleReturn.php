<?php

namespace App\Entities\Penjualan;

use App\Entities\Model;
use App\Entities\Account\Superuser as AccountSuperuser;

class SaleReturn extends Model
{
    protected $fillable = [
        'code',
        'payment_status',
        'idr_rate',
        'do_id',
        'return_date',
        'warehouse_id',
        'customer_other_address_id',
        'fat_status',
        'status',
        'type',
        'created_by',
        'updated_by',
    ];
    protected $table = 'penjualan_retur';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2,
    ];

    const TYPE = [
        'RETUR' => 1,
        'TUKAR BARANG' => 2,
    ];

    const PAYMENT_STATUS = [
        'BELUM LUNAS' => 0,
        'LUNAS' => 1,
    ];

    const FAT_STATUS = [
        'NONE' => 0,
        'KASIR' => 1,
        'SPV' => 2,
        'DONE' => 3,
    ];

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }

    public function payment_status()
    {
        return array_search($this->payment_status, self::PAYMENT_STATUS);
    }

    public function fat_status()
    {
        return array_search($this->fat_status, self::FAT_STATUS);
    }

    public function invoice()
    {
        return $this->belongsTo('App\Entities\Penjualan\PackingOrder', 'do_id');
    }

    public function sale_return_details()
    {
        return $this->hasMany('App\Entities\Penjualan\SaleReturnDetail', 'retur_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Entities\Master\Warehouse','warehouse_id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Entities\Master\CustomerOtherAddress', 'customer_other_address_id');
    }

    public function createdBySuperuser()
    {
        $superuser = AccountSuperuser::find($this->created_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
    }

    public function cost()
    {
        return $this->hasOne('App\Entities\Penjualan\SaleReturnCost', 'retur_id', 'id');
    }
}