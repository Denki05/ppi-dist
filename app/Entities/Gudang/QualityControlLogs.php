<?php

namespace App\Entities\Gudang;

use App\Entities\Model;
use App\Entities\Account\Superuser;
use Illuminate\Support\Facades\Auth;
use App\Entities\Gudang\ReceivingDetail;


class QualityControlLogs extends Model
{
    protected $fillable = [
        'receiving_details_id', 
        'product_packaging_id', 
        'qty_qc', 
        'status_qc', 
        'is_sellable', 
        'is_approved',
    ];
    protected $table = 'receiving_qc_logs';

    const STATUS_QC = [
        'NOT OK' => 0,
        'OK'  => 1, 
        'HOLD'  => 2, 
    ];

    public function detail()
    {
        return $this->belongsTo(ReceivingDetail::class, 'receiving_details_id');
    }

    public function status_qc()
    {
        return array_search($this->status_qc, self::STATUS_QC);
    }
}
