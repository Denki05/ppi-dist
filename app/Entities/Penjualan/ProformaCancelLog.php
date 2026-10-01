<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;

class ProformaCancelLog extends Model
{
    protected $table = 'proforma_cancel_log';

    protected $fillable = [
        'pengajuan_proforma_id',
        'estimate_number',
        'customer_name',
        'phone',
        'address',
        'city',
        'provinsi',
        'kecamatan',
        'kelurahan',
        'ktp',
        'npwp',
        'ao_pic',
        'customer_id',
        'member_id',
        'grand_total',
        'proforma_code',
        'cancelled_by',
        'cancelled_at',
        'alasan',
        'customer_rolled_back',
    ];

    protected $casts = [
        'cancelled_at'         => 'datetime',
        'customer_rolled_back' => 'boolean',
        'grand_total'          => 'decimal:2',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanProforma::class, 'pengajuan_proforma_id');
    }
}
