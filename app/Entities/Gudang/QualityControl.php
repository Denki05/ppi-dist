<?php

namespace App\Entities\Gudang;

use App\Entities\Model;
use App\Entities\Account\Superuser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Entities\Gudang\ReceivingDetail;


class QualityControl extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 
        'type',
        'warehouse_id', 
        'pbm_date', 
        'status', 
        'acc_by', 
        'acc_at', 
        'note', 
    ];
    protected $table = 'receiving';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE'  => 1,   // draft admin
        'QC'      => 2,   // proses QC logistik
        'READY'   => 3,   // semua qty QC OK, menunggu ACC
        'ACC'     => 4,   // final
    ];

    const TYPE = [
        'INBOUND' => 0,
        'RETURN'  => 1,
    ];

    public function warehouse()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse');
    }

    public function details()
    {
        return $this->hasMany(QualityControlDetail::class, 'receiving_id');
    }

    public function createdBySuperuser()
    {
        $superuser = Superuser::find($this->created_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
    }

    public function accBySuperuser()
    {
        $superuser = Superuser::find($this->acc_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
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
