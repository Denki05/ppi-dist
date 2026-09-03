<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;
use App\Entities\Account\Superuser;

class DoInternalRevision extends Model
{
    protected $table = "do_internal_revisions";

    protected $fillable = [
        'do_id',
        'so_id',
        'origin_status',
        'requested_by',
        'requested_at',
        'request_reason',
        'revision_detail',
        'items_changed',
        'status',
        'otp_hash',
        'otp_expires_at',
        'otp_attempts',
        'approved_by',
        'approved_at',
        'approval_reason',
    ];

    protected $casts = [
        'revision_detail' => 'array',
        'items_changed' => 'boolean',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    const STATUS = [
        1 => 'Pending',
        2 => 'Approved',
        3 => 'Rejected',
    ];

    public function packingOrder()
    {
        return $this->belongsTo('App\Entities\Penjualan\PackingOrder', 'do_id', 'id');
    }

    public function so()
    {
        return $this->belongsTo('App\Entities\Penjualan\SalesOrder', 'so_id', 'id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(Superuser::class, 'requested_by', 'id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Superuser::class, 'approved_by', 'id');
    }
}