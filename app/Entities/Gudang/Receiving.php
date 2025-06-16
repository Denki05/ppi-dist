<?php

namespace App\Entities\Gudang;

use App\Entities\Model;
use App\Entities\Account\Superuser;
use App\Entities\Finance\CBPaymentInvoiceDetail;
use App\Entities\Master\SupplierCoa;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;


class Receiving extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 
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

    public function warehouse()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse');
    }

    public function details()
    {
        return $this->hasMany('App\Entities\Gudang\ReceivingDetail', 'receiving_id');
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

}
