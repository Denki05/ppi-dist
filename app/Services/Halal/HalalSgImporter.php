<?php

namespace App\Services\Halal;

use App\HalalDocument;
use App\HalalHelper;
use App\HalalList;
use App\HalalPdfItem;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Importer khusus Singapura (MUIS).
 * Sumber = link QR https://halal.muis.gov.sg/qr?n=... (92 produk SENSES)
 * + PDF Annex A 5 halaman (jika ada file-nya).
 *
 * Format QR: "12. CANDY COFFEE 24629 (FRAGRANCE COMPOUND)"
 * Format PDF: "12. CANDY COFFEE 24629 (FRAGRANCE COMPOUND)" per baris Annex A.
 */
class HalalSgImporter
{
    const SUMBER = 'SG';
    const LEMBAGA = 'MUIS';
    // Annex A: 5 halaman total; cover = cetak "1 of 5" (cover DIHITUNG di SG,
    // beda dengan ID). Konvensi SG = nomor CETAK tampil apa adanya ("X of 5").
    // Terverifikasi dari file contoh: no 12/14/15/21 di cetak "2 of 5",
    // no 82 di cetak "5 of 5" (file contoh hanya berisi hal cetak 1, 2, 5).
    // Halaman annex dibangun ulang dari list QR (lihat HalalSgAnnex) dengan
    // pagination resmi: 25 baris/halaman -> hal 2 = no 1-24, hal 3 = 25-49,
    // hal 4 = 50-74, hal 5 = 75-92.
    const SG_JUMLAH_HALAMAN = 5;
    const SG_PER_HALAMAN = 24;

    /**
     * Nomor halaman cetak resmi ("2 of 5" .. "5 of 5") untuk sebuah nomor
     * urut produk. Pagination MUIS = 25 baris per halaman, halaman 2 berisi
     * produk 1-24, halaman 3 = 25-49, halaman 4 = 50-74, halaman 5 = 75-92
     * (dicalibrasi dari PDF resmi, lihat HalalSgAnnex).
     */
    public static function estimasiHalaman($nomor)
    {
        return \App\Services\Halal\HalalSgAnnex::halamanCetak($nomor);
    }

    /**
     * Pecah "CANDY COFFEE 24629" -> [nama, kode].
     * Kode = token angka di ujung (24629, 10775) atau prefix+angka (SJA 10760).
     */
    public static function parseSgName($raw)
    {
        $raw = trim((string) $raw);
        // Buang suffix "(FRAGRANCE COMPOUND)" dan trailing " -".
        $raw = preg_replace('/\s*\(FRAGRANCE COMPOUND\)\s*$/i', '', $raw);
        $raw = preg_replace('/\s+-\s*$/', '', $raw);
        $raw = trim($raw);
        if ($raw === '') {
            return [null, null];
        }
        $toks = preg_split('/\s+/', $raw);
        $n = count($toks);
        if ($n >= 2 && preg_match('/^(SJA|SMK|SPL|SJC|SRL|SZT|SJW)$/i', $toks[$n - 2]) && preg_match('/^\d+[A-Z]?$/i', $toks[$n - 1])) {
            $kode = strtoupper($toks[$n - 2]) . ' ' . strtoupper($toks[$n - 1]);
            $nama = trim(implode(' ', array_slice($toks, 0, $n - 2)));
            return [$nama !== '' ? $nama : $kode, $kode];
        }
        $last = $toks[$n - 1];
        if (preg_match('/^(\d[\d\/]*[A-Za-z]?|R\.\d[\d.]*)$/', $last)) {
            $nama = trim(implode(' ', array_slice($toks, 0, $n - 1)));
            // Kasus "SJA 16042" (nama kosong) -> nama = kode agar tetap bisa dicocokkan.
            if ($nama === '') {
                return [strtoupper($last) === $raw ? $raw : $raw, strtoupper($raw)];
            }
            return [$nama, strtoupper($last)];
        }
        // Tidak ada kode jelas (mis. "SJA 16074 -" sudah dibersihkan) -> anggap semua nama.
        return [$raw, null];
    }

    /**
     * Ambil HTML QR MUIS -> array header + items.
     */
    public function fetchQr($url)
    {
        $client = new \GuzzleHttp\Client(['timeout' => 25, 'verify' => false]);
        $res = $client->get($url);
        $html = (string) $res->getBody();

        $company = null;
        $cert = null;
        $expired = null;
        if (preg_match('/<span class="name[^>]*>(.*?)<\/span>/s', $html, $m)) {
            $company = trim(html_entity_decode(strip_tags($m[1])));
        }
        if (preg_match_all('/<span class="name[^>]*>(.*?)<\/span>/s', $html, $mm)) {
            // span name ke-2 = nomor sertifikat (FMPR...).
            if (isset($mm[1][1])) {
                $cert = trim(html_entity_decode(strip_tags($mm[1][1])));
            }
        }
        if (preg_match('/<span class="date-of-expiry[^>]*>(.*?)<\/span>/s', $html, $m)) {
            $expired = trim(html_entity_decode(strip_tags($m[1])));
        }

        $items = [];
        if (preg_match_all('/<table class="product-list.*?<\/table>/s', $html, $tm)) {
            foreach ($tm[0] as $table) {
                preg_match_all('/<tr>.*?<div>(.*?)<\/div>.*?<div>(.*?)<\/div>.*?<\/tr>/s', $table, $rows, PREG_SET_ORDER);
                foreach ($rows as $r) {
                    $no = (int) trim(str_replace(['.', '&nbsp;', "\xc2\xa0"], '', html_entity_decode(strip_tags($r[1]))));
                    $nama = trim(html_entity_decode(strip_tags($r[2])));
                    $nama = preg_replace('/\s+/', ' ', $nama);
                    if ($no > 0 && $nama !== '') {
                        $items[] = ['nomor' => $no, 'nama' => $nama];
                    }
                }
            }
        }

        return [
            'company' => $company,
            'cert' => $cert,
            'expired' => $expired,
            'items' => $items,
        ];
    }

    /**
     * Import hasil QR ke halal_documents + halal_lists (sumber SG).
     */
    public function importQr($url, $tanggalVersi, $createdBy = null)
    {
        $data = $this->fetchQr($url);
        if (empty($data['items'])) {
            throw new \RuntimeException('QR MUIS tidak mengembalikan daftar produk (cek link).');
        }
        $expired = null;
        if ($data['expired']) {
            try {
                $expired = date('Y-m-d', strtotime($data['expired']));
            } catch (\Exception $e) {
                $expired = null;
            }
        }
        $doc = HalalDocument::create([
            'jenis' => HalalDocument::JENIS_LIST,
            'sumber' => self::SUMBER,
            'lembaga' => self::LEMBAGA,
            'nama_perusahaan' => $data['company'],
            'qr_url' => $url,
            'file_path' => 'qr:' . $url,
            'original_name' => 'QR MUIS ' . ($data['cert'] ?: date('Y-m-d')),
            'tanggal_versi' => $tanggalVersi,
            'tanggal_kedaluwarsa' => $expired,
            'nomor_sertifikat' => $data['cert'],
            'total_baris' => count($data['items']),
            'created_by' => $createdBy,
        ]);

        HalalList::where('document_id', $doc->id)->delete();
        foreach ($data['items'] as $it) {
            list($nama, $kode) = self::parseSgName($it['nama']);
            HalalList::create([
                'document_id' => $doc->id,
                'sumber' => self::SUMBER,
                'nomor_urut' => $it['nomor'],
                'nama_lengkap' => $it['nama'],
                'brand_parsed' => 'SENSES',
                'nama_parsed' => $nama,
                'kode_parsed' => $kode,
                'sumber_version' => $tanggalVersi,
            ]);
        }

        return ['doc' => $doc, 'total' => count($data['items']), 'cert' => $data['cert'], 'expired' => $expired];
    }

    /**
     * Cari binary Ghostscript (untuk normalisasi PDF agar dibaca FPDI).
     * @return string|null  nama binary atau null bila exec/Ghostscript tak ada.
     */
    public static function gsBinary()
    {
        if (!function_exists('exec')) {
            return null;
        }
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        if (in_array('exec', $disabled, true)) {
            return null;
        }
        $cands = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? ['gswin64c', 'gswin32c'] : ['gs'];
        foreach ($cands as $b) {
            $out = [];
            $ret = 1;
            @exec($b . ' --version 2>&1', $out, $ret);
            if ($ret === 0) {
                return $b;
            }
        }
        return null;
    }

    /**
     * Normalisasi PDF SG (object-stream/xref modern) menjadi PDF 1.4 biasa
     * agar bisa di-import FPDI untuk rombakan pixel-identik. Isi visual sama.
     * @return bool  true bila file dinormalisasi (in-place).
     */
    public static function normalisasiUntukFpdi($path)
    {
        $gs = self::gsBinary();
        if ($gs === null || !is_file($path)) {
            return false;
        }
        $tmp = $path . '.norm.pdf';
        $cmd = $gs . ' -sDEVICE=pdfwrite -dCompatibilityLevel=1.4'
            . ' -dPDFSETTINGS=/prepress -dNOPAUSE -dQUIET -dBATCH'
            . ' -sOutputFile=' . escapeshellarg($tmp) . ' ' . escapeshellarg($path) . ' 2>&1';
        $out = [];
        $ret = 1;
        @exec($cmd, $out, $ret);
        if ($ret !== 0 || !is_file($tmp) || filesize($tmp) < 1024) {
            @unlink($tmp);
            return false;
        }
        // Verifikasi cepat: Smalot tetap membaca jumlah halaman yang sama.
        try {
            $sebelum = count((new PdfParser())->parseFile($path)->getPages());
            $sesudah = count((new PdfParser())->parseFile($tmp)->getPages());
            if ($sebelum !== $sesudah) {
                @unlink($tmp);
                return false;
            }
        } catch (\Throwable $e) {
            @unlink($tmp);
            return false;
        }
        @unlink($path . '.orig.pdf');
        @rename($path, $path . '.orig.pdf');
        @rename($tmp, $path);
        return is_file($path);
    }

    /**
     * Import PDF MUIS Annex A -> halal_pdf_items.
     * Format baris: "12. CANDY COFFEE 24629 (FRAGRANCE COMPOUND)".
     * 'halaman' yang disimpan = NOMOR CETAK ("N of 5"), bukan indeks fisik,
     * karena file upload bisa tidak lengkap (contoh: fisik p3 = cetak "5 of 5").
     * Fallback ke indeks fisik bila footer tak terbaca.
     */
    public function importPdf($path, $documentId)
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);
        $pages = $pdf->getPages();

        HalalPdfItem::where('document_id', $documentId)->delete();
        $cert = null;
        $items = [];
        foreach ($pages as $idx => $page) {
            $text = $page->getText();
            if ($cert === null && preg_match('/FMPR\d+/i', $text, $m)) {
                $cert = strtoupper($m[0]);
            }
            $halaman = $idx + 1;
            if (preg_match('/(\d+)\s+of\s+(\d+)/i', $text, $hm)) {
                $halaman = (int) $hm[1];
            }
            foreach (preg_split('/\r?\n/', $text) as $line) {
                $line = trim(preg_replace('/\s+/', ' ', $line));
                if (preg_match('/^(\d{1,4})\.\s+(.+)$/', $line, $m)) {
                    $num = (int) $m[1];
                    $nama = trim($m[2]);
                    if ($nama === '' || stripos($nama, 'MUIS hereby certifies') !== false) {
                        continue;
                    }
                    HalalPdfItem::create([
                        'document_id' => $documentId,
                        'nomor_urut' => $num,
                        'nama_produk' => $nama,
                        'halaman' => $halaman,
                    ]);
                    $items[$num] = $halaman;
                }
            }
        }

        return ['total' => count($items), 'cert' => $cert, 'pages' => count($pages), 'hal_map' => $items];
    }

    /**
     * Pencocokan master -> list SG. Grain helper (merek, kode, sumber=SG).
     */
    public function match($listDocumentId, $pdfDocumentId, $cert, $tanggalMaster, $tanggalList, $tanggalPdf, $expired = null, $company = null)
    {
        $lists = HalalList::where('document_id', $listDocumentId)->where('sumber', self::SUMBER)->get();
        $byKode = [];
        $byNama = [];
        foreach ($lists as $e) {
            $nn = HalalMatcher::normalize($e->nama_parsed);
            $byNama[$nn][] = $e;
            if ($e->kode_parsed) {
                $byKode[HalalMatcher::normalize($e->kode_parsed)][] = $e;
            }
        }
        $masters = DB::table('master_products')->select('id', 'brand_name', 'code', 'name')
            ->orderBy('brand_name')->orderBy('code')->get();

        // Sama seperti ID: buang baris kode-kosong lama yang dipakai ramai-ramai
        // (nama + master_product_id tidak konsisten); dibuat ulang per-master.
        // Baris tinjauan manual dipertahankan.
        HalalHelper::where('sumber', self::SUMBER)
            ->where(function ($w) {
                $w->where('kode_produk', '')->orWhereNull('kode_produk');
            })
            ->where('status_pencocokan', HalalHelper::STATUS_TIDAK)->delete();

        $stats = ['Cocok' => 0, 'Perlu ditinjau' => 0, 'Tidak cocok' => 0];
        foreach ($masters as $m) {
            $kode = trim((string) $m->code);
            $merek = trim((string) $m->brand_name);
            $nama = trim((string) $m->name);
            $row = [
                'kode_produk' => $kode,
                'merek' => $merek,
                'sumber' => self::SUMBER,
                'nama_produk' => $nama,
                'tanggal_master' => $tanggalMaster,
                'tanggal_list' => $tanggalList,
                'tanggal_pdf' => $tanggalPdf,
                'tanggal_kedaluwarsa' => $expired,
                'nama_perusahaan' => $company,
                'list_document_id' => $listDocumentId,
                'pdf_document_id' => $pdfDocumentId,
                'search_keyword' => HalalHelper::normalizeKeyword($merek, $nama, $kode),
            ];
            if ($kode === '' || $nama === '') {
                $row += $this->noMatch('kode/nama kosong di master — lengkapi dulu di master produk');
                $this->saveHelper($m->id, $row, $stats);
                continue;
            }
            $nn = HalalMatcher::normalize($nama);
            $kn = HalalMatcher::normalize($kode);
            $found = null;
            if (isset($byKode[$kn]) && $byKode[$kn]) {
                $cands = $byKode[$kn];
                $e = $cands[0];
                if (count($cands) === 1 && HalalMatcher::normalize($e->nama_parsed) === $nn) {
                    $found = ['Cocok', 'Kode', $e, ''];
                } else {
                    $found = ['Perlu ditinjau', 'Kode', $e,
                        count($cands) === 1 ? 'kode sama tetapi nama beda' : 'kode punya ' . count($cands) . ' kandidat'];
                }
            } elseif (isset($byNama[$nn]) && $byNama[$nn]) {
                // Aturan 4 (konsisten ID): nama sama tetapi kode berbeda = Perlu ditinjau.
                // List SG selalu berkode, jadi cocok-nama saja tidak boleh langsung Cocok.
                $cands = $byNama[$nn];
                $found = ['Perlu ditinjau', 'nama', $cands[0],
                    'hanya cocok lewat nama (' . count($cands) . ' kandidat), kode berbeda'];
            }
            if ($found) {
                list($status, $kunci, $e, $catatan) = $found;
                $row['status_pencocokan'] = $status;
                $row['kunci_yang_dipakai'] = $kunci;
                $row['nama_di_list_halal'] = $e->nama_lengkap;
                $row['nomor_urut_list_halal'] = $e->nomor_urut;
                $row['catatan_tinjauan'] = $catatan ?: null;
                // Nomor halaman mengikuti pagination resmi MUIS (bukan
                // estimasi): halaman "X of 5" tempat produk ini tercantum.
                $hal = \App\Services\Halal\HalalSgAnnex::halamanCetak($e->nomor_urut);
                $row['halaman_pdf'] = $hal;
                $row['nomor_urut_pdf'] = $e->nomor_urut;
                $row['status_pdf'] = $hal ? HalalHelper::PDF_VERIFIED : HalalHelper::PDF_BELUM;
                $row['nomor_sertifikat'] = $cert;
            } else {
                $row += $this->noMatch('tidak ditemukan di list halal SG');
            }
            $this->saveHelper($m->id, $row, $stats);
        }

        return $stats;
    }

    protected function noMatch($catatan)
    {
        return [
            'status_pencocokan' => HalalHelper::STATUS_TIDAK,
            'kunci_yang_dipakai' => null,
            'nama_di_list_halal' => null,
            'nomor_urut_list_halal' => null,
            'halaman_pdf' => null,
            'nomor_urut_pdf' => null,
            'nomor_sertifikat' => null,
            'status_pdf' => HalalHelper::PDF_BELUM,
            'catatan_tinjauan' => $catatan,
        ];
    }

    protected function saveHelper($masterId, array $row, array &$stats)
    {
        // Kode kosong dikunci per-master (lihat HalalMatcher::saveHelper).
        $kunci = [
            'kode_produk' => $row['kode_produk'],
            'merek' => $row['merek'],
            'sumber' => self::SUMBER,
        ];
        if (trim((string) $row['kode_produk']) === '') {
            $kunci['master_product_id'] = $masterId;
        }
        $helper = HalalHelper::firstOrNew($kunci);
        if (!$helper->exists) {
            $helper->master_product_id = $masterId;
        }
        $data = $row;
        unset($data['kode_produk'], $data['merek'], $data['sumber']);
        $helper->fill($data);
        $helper->save();
        $stats[$row['status_pencocokan']]++;
    }
}
