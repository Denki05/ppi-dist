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
        'code', 'warehouse_id', 'pbm_date', 'status', 'acc_by', 'acc_at', 'description', 'no_batch'
    ];
    protected $table = 'receiving';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2
    ];
    
    const TRANSACTION_TYPE = [
        'Tunai' => 1,   
        'Non Tunai' => 0,
    ];

    public function transaction_type()
    {
        return array_search($this->transaction_type, self::TRANSACTION_TYPE);
    }

    public function warehouse()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse');
    }

    public function details()
    {
        return $this->hasMany('App\Entities\Gudang\ReceivingDetail')->orderBy('created_at', 'DESC');
    }
    
    public function price_format($value)
    {
        return number_format($value, 2, ".", ",");
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
