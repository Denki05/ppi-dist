<?php

namespace App\Entities\Gudang;

use App\Entities\Model;
use App\Entities\Account\Superuser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Entities\Gudang\ReceivingDetail;


class ReceivingQcLogs extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'receiving_details_id', 
        'product_packing_id', 
        'qty_qc', 
        'status_qc', 
        'is_sellable', 
    ];
    protected $table = 'receiving_qc_logs';

    const STATUS_QC = [
        'NOT OK' => 0,
        'OK'  => 1, 
        'HOLD'  => 2, 
    ];

    public function details()
    {
        return $this->hasMany(ReceivingDetail::class);
    }

    public function status_qc()
    {
        return array_search($this->status_qc, self::STATUS_QC);
    }
}
