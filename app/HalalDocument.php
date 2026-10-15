<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HalalDocument extends Model
{
    protected $table = 'halal_documents';

    const JENIS_LIST = 'list';
    const JENIS_PDF = 'pdf';

    protected $fillable = [
        'jenis',
        'sumber',
        'lembaga',
        'nama_perusahaan',
        'qr_url',
        'file_path',
        'original_name',
        'tanggal_versi',
        'tanggal_kedaluwarsa',
        'nomor_sertifikat',
        'total_baris',
        'created_by',
    ];

    protected $dates = ['tanggal_versi', 'tanggal_kedaluwarsa'];
}
