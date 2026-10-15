<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HalalPdfItem extends Model
{
    protected $table = 'halal_pdf_items';

    protected $fillable = [
        'document_id',
        'nomor_urut',
        'nama_produk',
        'halaman',
    ];
}
