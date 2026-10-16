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
    // Annex A: hal 1 = cover, hal 2-5 = 92 produk (~23/hal). Contoh rombakan:
    // no 12/14/15/21 di hal 2, no 82 di hal 5 -> konsisten 23/hal.
    const SG_JUMLAH_HALAMAN = 5;
    const SG_PER_HALAMAN = 23;

    public static function estimasiHalaman($nomor)
    {
        $nomor = (int) $nomor;
        if ($nomor < 1) {
            return null;
        }
        $hal = (int) ceil($nomor / self::SG_PER_HALAMAN) + 1;
        return min($hal, self::SG_JUMLAH_HALAMAN);
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
     * Import PDF MUIS Annex A (5 halaman) -> halal_pdf_items.
     * Format baris: "12. CANDY COFFEE 24629 (FRAGRANCE COMPOUND)".
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
                        'halaman' => $idx + 1,
                    ]);
                    $items[$num] = $idx + 1;
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
        $pdfMap = $pdfDocumentId
            ? HalalPdfItem::where('document_id', $pdfDocumentId)->pluck('halaman', 'nomor_urut')
            : collect();

        $masters = DB::table('master_products')->select('id', 'brand_name', 'code', 'name')
            ->orderBy('brand_name')->orderBy('code')->get();

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
                $row += $this->noMatch('baris sampah: kode/nama kosong');
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
                if (isset($pdfMap[$e->nomor_urut])) {
                    $row['halaman_pdf'] = $pdfMap[$e->nomor_urut];
                    $row['nomor_urut_pdf'] = $e->nomor_urut;
                    $row['status_pdf'] = HalalHelper::PDF_VERIFIED;
                } else {
                    // Tanpa PDF asli: halaman diestimasi dari urutan QR (23/hal, 5 hal).
                    // Cukup untuk rombakan "X of 5"; status tetap belum terverifikasi PDF.
                    $row['halaman_pdf'] = self::estimasiHalaman($e->nomor_urut);
                    $row['nomor_urut_pdf'] = $e->nomor_urut;
                    $row['status_pdf'] = HalalHelper::PDF_BELUM;
                }
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
        $helper = HalalHelper::firstOrNew([
            'kode_produk' => $row['kode_produk'],
            'merek' => $row['merek'],
            'sumber' => self::SUMBER,
        ]);
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
