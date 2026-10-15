<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HalalHelper extends Model
{
    protected $table = 'halal_helpers';

    const STATUS_COCOK = 'Cocok';
    const STATUS_TINJAU = 'Perlu ditinjau';
    const STATUS_TIDAK = 'Tidak cocok';

    const PDF_VERIFIED = 'Terverifikasi PDF';
    const PDF_BELUM = 'belum';

    protected $fillable = [
        'master_product_id',
        'kode_produk',
        'merek',
        'sumber',
        'nama_produk',
        'nama_di_list_halal',
        'nomor_urut_list_halal',
        'list_document_id',
        'pdf_document_id',
        'halaman_pdf',
        'nomor_urut_pdf',
        'nomor_sertifikat',
        'nama_perusahaan',
        'status_pencocokan',
        'kunci_yang_dipakai',
        'status_pdf',
        'catatan_tinjauan',
        'tanggal_master',
        'tanggal_list',
        'tanggal_pdf',
        'search_keyword',
    ];

    protected $dates = [
        'tanggal_master',
        'tanggal_list',
        'tanggal_pdf',
    ];

    /**
     * Normalisasi untuk kolom search_keyword (langkah 3 dokumen):
     * lowercase, trim spasi ganda, hapus petik/aksen sederhana.
     */
    public static function normalizeKeyword($merek, $nama, $kode = null)
    {
        $raw = trim($merek . ' ' . $nama . ' ' . ($kode ?: ''));
        $raw = mb_strtolower($raw, 'UTF-8');
        // Hapus aksen umum + semua varian petik (lurus & keriting, cth D'Terry).
        $map = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ñ' => 'n', 'ç' => 'c',
            "'" => '', '"' => '', '`' => '', '‘' => '', '’' => '',
            '“' => '', '”' => '', '‚' => '', '„' => '',
        ];
        $raw = strtr($raw, $map);
        $raw = preg_replace('/\s+/', ' ', $raw);
        return trim($raw);
    }

    /**
     * Landing hanya mengaktifkan tombol lanjut untuk status Cocok.
     */
    public function isButtonActive()
    {
        return $this->status_pencocokan === self::STATUS_COCOK;
    }
}
