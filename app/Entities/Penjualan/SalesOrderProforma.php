<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Entities\Account\Superuser as AccountSuperuser;

class SalesOrderProforma extends Model
{
    use SoftDeletes;

    protected $table = "penjualan_so_proforma";
    protected $fillable = [
        'so_id',
        'code',
        'customer_name', 
        'customer_address',
        'customer_region',
        'customer_city',
        'customer_phone',
        'customer_owner',
        'so_date',
        'so_brand_name',
        'so_type_transaction',
        'so_idr_rate',
        'note',
        'so_lanjutan',
        'status',
        'transfer_verified',
        'customer_other_address_id', 
        'warehouse_id',
        'rekening_id', 
        'vendor_id',
        'sales_senior_id',
        'sales_id',
        'exsisting_customer',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    const STATUS = [
        0 => 'DELETED',
        1 => 'ACTIVE',
        2 => 'ACC',
        3 => 'REVISI',
        4 => 'LANJUTAN',
    ];

    const LANJUTAN = [
        0 => 'NO',
        1 => 'YES'
    ];

    const SO_TYPE_TRANSACTION = [
    	1 => 'CASH',
        2 => 'TEMPO',
        3 => 'MARKETPLACE',
        4 => 'COD',
    ];

    public function getSoLanjutanAttribute()
    {
        return self::LANJUTAN[$this->attributes['so_lanjutan']];
    }

    public function getSoTypeTransactionAttribute()
    {
        $value = $this->attributes['so_type_transaction'] ?? null;

        return self::SO_TYPE_TRANSACTION[$value] ?? null;
    }

    public function items()
    {
        return $this->hasMany('App\Entities\Penjualan\SalesOrderProformaItem', 'so_proforma_id');
    }

    public function details_cost()
    {
        return $this->hasOne(
            \App\Entities\Penjualan\SalesOrderProformaDetails::class,
            'so_proforma_id',
            'id'
        );
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Entities\Master\Warehouse', 'warehouse_id', 'id');
    }

    public function member()
    {
        return $this->belongsTo('App\Entities\Master\CustomerOtherAddress', 'customer_other_address_id', 'id');
    }

    public function getStatusAttribute($value)
    {
        return self::STATUS[$value];
    }

    public function createdBySuperuser()
    {
        $superuser = AccountSuperuser::find($this->created_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
        return null; // Return null if no superuser is found
    }

    public function salesOrder()
    {
        return $this->belongsTo('App\Entities\Penjualan\SalesOrder', 'so_id', 'id');
    }

    public function getStatusTypeAttribute()
    {
        return self::SO_TYPE_TRANSACTION[$this->attributes['so_type_transaction']] ?? null;
    }
}
