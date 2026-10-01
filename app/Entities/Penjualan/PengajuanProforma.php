<?php

namespace App\Entities\Penjualan;

use Illuminate\Database\Eloquent\Model;

class PengajuanProforma extends Model
{
    protected $table = 'pengajuan_proforma';

    protected $fillable = [
        'estimate_number',
        'ao_pic',
        'prospect_name',
        'perusahaan',
        'owner',
        'phone',
        'address',
        'city',
        'provinsi',
        'kecamatan',
        'kelurahan',
        'ktp',
        'npwp',
        'termin',
        'foto_ktp_ada',
        'foto_npwp_ada',
        'grand_total',
        'items_count',
        'items_json',
        'submitted_at',
        'status',
        'catatan',
        'verified_by',
        'verified_at',
        'prospect_id',
        'customer_id_hasil',
        'member_id_hasil',
        'parent_dibuat_baru',
        'member_dibuat_baru',
        'notif_ao_status',
        'ktp_photo_path',
        'npwp_photo_path',
        'bukti_chat_path',
        'bukti_chat_at',
    ];

    protected $casts = [
        'foto_ktp_ada'         => 'boolean',
        'foto_npwp_ada'        => 'boolean',
        'parent_dibuat_baru'   => 'boolean',
        'member_dibuat_baru'   => 'boolean',
        'grand_total'          => 'decimal:2',
        'submitted_at'         => 'datetime',
        'verified_at'          => 'datetime',
        'bukti_chat_at'        => 'datetime',
    ];

    const STATUS_MENUNGGU   = 'menunggu';
    const STATUS_DISETUJUI  = 'disetujui';
    const STATUS_DITOLAK    = 'ditolak';
    const STATUS_DIBATALKAN = 'dibatalkan';
}
