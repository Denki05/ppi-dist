<?php

namespace App\Entities\Gudang;

use App\Entities\Model;
use App\Entities\Account\Superuser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class QualityControl2 extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 
        'warehouse_id', 
        'customer_id', 
        'retur_id', 
        'tanggal', 
        'note', 
        'status', 
        'created_by',
    ];
    protected $table = 'receiving_komplain';

    const STATUS = [
        'DELETED'   => 0,
        'ACTIVE'    => 1,   // draft
        'ACC'       => 2,   // approved
    ];

    public function details()
    {
        return $this->hasMany('App\Entities\Gudang\QualityControlDetail2', 'receving_komplain_id', 'id');
    }

    public function warehouse()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse');
    }

    public function status()
    {
        return array_search($this->status, self::STATUS);
    }

    public function customer()
    {
        return $this->BelongsTo('App\Entities\Master\CustomerOtherAddress');
    }

    public function retur()
    {
        return $this->BelongsTo('App\Entities\Penjualan\SaleReturn', 'retur_id', 'id');
    }
}