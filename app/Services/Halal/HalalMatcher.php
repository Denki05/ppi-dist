<?php

namespace App\Services\Halal;

use App\HalalHelper;
use App\HalalList;
use App\HalalPdfItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Langkah 2-6 dokumen proses pencocokan.
 * Aturan pencocokan (aturan 1-6):
 *  1. Kunci dicoba berurutan: kode, merek+nama, nama saja.
 *     Hasil dari nama saja otomatis Perlu ditinjau.
 *  2. Lebih dari satu kandidat otomatis Perlu ditinjau.
 *  3. Kode sama tetapi nama berbeda otomatis Perlu ditinjau.
 *  4. Nama sama tetapi kode berbeda otomatis Perlu ditinjau.
 *  5. Merek PPI FF / PPI NON FF diperlakukan sama dengan IVA.
 *  6. Pencarian ke situs halal hanya memakai nama produk (di landing/API).
 */
class HalalMatcher
{
    const BRANDMAP = [
        'PPI FF' => 'IVA',
        'PPI NON FF' => 'IVA',
    ];

    const SETTING_PETA_MEREK = 'halal_peta_merek_confirmed';

    public static function mapBrand($brand)
    {
        $brand = trim((string) $brand);
        return isset(self::BRANDMAP[$brand]) ? self::BRANDMAP[$brand] : $brand;
    }

    public static function usesMappedBrand($brand)
    {
        return isset(self::BRANDMAP[trim((string) $brand)]);
    }

    /**
     * True setelah manajer mengonfirmasi di halaman SH Produk
     * bahwa PPI FF / PPI NON FF memang sama dengan IVA (aturan 5 dokumen).
     * Selama belum dikonfirmasi, hasil yang memakai peta merek otomatis
     * Perlu ditinjau agar tidak ada klaim halal yang salah di landing.
     */
    public static function isBrandMapConfirmed()
    {
        if (!Schema::hasTable('halal_settings')) {
            return false;
        }
        $row = DB::table('halal_settings')->where('key', self::SETTING_PETA_MEREK)->first();
        return $row && trim((string) $row->value) === '1';
    }

    public static function confirmBrandMap()
    {
        if (DB::table('halal_settings')->where('key', self::SETTING_PETA_MEREK)->exists()) {
            DB::table('halal_settings')->where('key', self::SETTING_PETA_MEREK)->update(['value' => '1']);
        } else {
            DB::table('halal_settings')->insert(['key' => self::SETTING_PETA_MEREK, 'value' => '1']);
        }
    }

    /**
     * Normalisasi satu string (langkah 3): lowercase, aksen, petik, spasi.
     */
    public static function normalize($s)
    {
        $s = trim((string) $s);
        if ($s === '') {
            return '';
        }
        if (function_exists('mb_strtolower')) {
            $s = mb_strtolower($s, 'UTF-8');
        } else {
            $s = strtolower($s);
        }
        // Hapus diakritik via transliterasi ikonv (tanpa intl).
        $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($t !== false) {
            $s = $t;
        }
        $s = str_replace(["'", '"', '`', '‘', '’', '“', '”', '‚', '„'], '', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }

    /**
     * Pecah "BRAND - NAMA KODE" -> [brand, nama, kode|None].
     * Kode = token angka di ujung (12212/4, 10042/P, 999925002, R.140612.7)
     * atau pasangan prefix+angka (SJA 10868, SMK 14513).
     */
    public static function parseListName($full)
    {
        $full = trim((string) $full);
        if (strpos($full, ' - ') === false) {
            return [null, $full, null];
        }
        list($brand, $rest) = explode(' - ', $full, 2);
        $brand = trim($brand);
        $rest = trim($rest);
        $toks = preg_split('/\s+/', $rest);
        $n = count($toks);
        if ($n >= 2 && preg_match('/^(SJA|SMK|SRL)$/i', $toks[$n - 2]) && ctype_digit($toks[$n - 1])) {
            $kode = strtoupper($toks[$n - 2]) . ' ' . $toks[$n - 1];
            $nama = trim(implode(' ', array_slice($toks, 0, $n - 2)));
            return [$brand, $nama !== '' ? $nama : $rest, $kode];
        }
        $last = $toks[$n - 1];
        if ($last !== '' && preg_match('/^(\d[\d\/]*[A-Za-z]?|R\.\d[\d.]*)$/', $last)) {
            $nama = trim(implode(' ', array_slice($toks, 0, $n - 1)));
            return [$brand, $nama !== '' ? $nama : $rest, strtoupper($last)];
        }
        return [$brand, $rest, null];
    }

    /**
     * Import Excel list halal (3 blok No|Nama) ke halal_lists.
     * Melewati baris judul + tanda halaman nyasar (langkah 2).
     */
    public function importList($path, $documentId, $version)
    {
        $ss = IOFactory::load($path);
        $ws = $ss->getActiveSheet();
        $rows = $ws->toArray(null, true, true, false);

        HalalList::where('document_id', $documentId)->delete();
        $count = 0;
        $junk = [];
        foreach ($rows as $row) {
            $cells = array_values($row);
            $pairs = [
                [isset($cells[0]) ? $cells[0] : null, isset($cells[1]) ? $cells[1] : null],
                [isset($cells[3]) ? $cells[3] : null, isset($cells[4]) ? $cells[4] : null],
                [isset($cells[6]) ? $cells[6] : null, isset($cells[7]) ? $cells[7] : null],
            ];
            foreach ($pairs as $p) {
                $no = trim((string) $p[0]);
                $nama = trim((string) $p[1]);
                if ($nama === '' || $nama === 'Nama Produk Halal' || $nama === '62' || $nama === '63') {
                    if ($nama === '62' || $nama === '63') {
                        $junk[] = $nama;
                    }
                    continue;
                }
                if (!ctype_digit($no)) {
                    $junk[] = $no . '|' . mb_substr($nama, 0, 30);
                    continue;
                }
                list($brand, $nm, $kode) = self::parseListName($nama);
                HalalList::create([
                    'document_id' => $documentId,
                    'sumber' => 'ID',
                    'nomor_urut' => (int) $no,
                    'nama_lengkap' => $nama,
                    'brand_parsed' => $brand,
                    'nama_parsed' => $nm,
                    'kode_parsed' => $kode,
                    'sumber_version' => $version,
                ]);
                $count++;
            }
        }
        return ['total' => $count, 'junk' => $junk];
    }

    /**
     * Import PDF resmi ke halal_pdf_items + ambil nomor sertifikat (langkah 5).
     * 'halaman' yang disimpan = indeks fisik PDF (termasuk cover); tampilan
     * memakai fisik - offset. Offset dideteksi dari footer tercetak halaman
     * konten pertama (normalnya 1: satu cover tak bernomor).
     * Mengembalikan ['total', 'cert', 'pages', 'offset', 'hal_map' => [nomor => halaman]].
     */
    public function importPdf($path, $documentId)
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);
        $pages = $pdf->getPages();

        HalalPdfItem::where('document_id', $documentId)->delete();
        $cert = null;
        $offset = null;
        $items = [];
        $prevEnd = 0;
        foreach ($pages as $idx => $page) {
            $text = $page->getText();
            if (strpos($text, 'Daftar Produk') === false) {
                if ($cert === null && preg_match('/ID(\d{15,25})/', $text, $m)) {
                    $cert = $m[1] . 'ID';
                } elseif ($cert === null && preg_match('/(\d{15,25})ID/', $text, $m)) {
                    $cert = $m[1] . 'ID';
                }
                continue;
            }
            if ($offset === null && preg_match('/Hal\s+(\d+)/u', $text, $hm)) {
                $offset = ($idx + 1) - (int) $hm[1];
            }
            $parts = explode('Nama Produk/ Product Name', $text, 2);
            if (count($parts) < 2) {
                continue;
            }
            $body = explode('Hal ', $parts[1], 2)[0];
            $lines = preg_split('/\r?\n/', $body);
            $curNo = null;
            $buf = [];
            $expected = $prevEnd + 1;
            $flush = function () use (&$items, &$curNo, &$buf, $documentId, $idx) {
                if ($curNo !== null) {
                    $nama = trim(implode(' ', $buf));
                    HalalPdfItem::create([
                        'document_id' => $documentId,
                        'nomor_urut' => $curNo,
                        'nama_produk' => $nama,
                        'halaman' => $idx + 1,
                    ]);
                    $items[$curNo] = $idx + 1;
                }
                $curNo = null;
                $buf = [];
            };
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line === 'No') {
                    continue;
                }
                // Bentuk 1 (smalot): "12 GCF - ATLANTIKA 641710016" sebaris.
                if (preg_match('/^(\d{1,4})\s+(.+)$/', $line, $m)) {
                    $num = (int) $m[1];
                    if (($curNo === null && $num === $expected) || ($curNo !== null && $num === $curNo + 1)) {
                        $flush();
                        $curNo = $num;
                        $buf = [$m[2]];
                        continue;
                    }
                }
                // Bentuk 2 (pypdf): nomor di baris sendiri, nama di baris berikut.
                if (preg_match('/^\d{1,4}$/', $line)) {
                    $num = (int) $line;
                    if ($curNo === null && $num === $expected) {
                        $curNo = $num;
                        continue;
                    } elseif ($curNo !== null && $num === $curNo + 1) {
                        $flush();
                        $curNo = $num;
                        continue;
                    }
                }
                if ($curNo !== null) {
                    $buf[] = $line;
                }
            }
            $flush();
            if (!empty($items)) {
                $prevEnd = max(array_keys($items));
            }
        }
        return ['total' => count($items), 'cert' => $cert, 'pages' => count($pages), 'offset' => $offset, 'hal_map' => $items];
    }

    /**
     * Langkah 4+6: cocokkan master -> list, lalu isi halaman PDF.
     * Satu produk unik (merek+kode) = satu baris helper. Baris master yang
     * ganda (id 11514 dan 11514/1, dst) digabung agar landing tidak dobel.
     * Daftar grup ganda dilaporkan agar bisa dirapikan di master.
     */
    public function match($listDocumentId, $pdfDocumentId, $cert, $tanggalMaster, $tanggalList, $tanggalPdf)
    {
        $lists = HalalList::where('document_id', $listDocumentId)->where('sumber', 'ID')->get();
        $byKode = [];
        $byBn = [];
        $byNama = [];
        foreach ($lists as $e) {
            $bn = self::normalize($e->brand_parsed);
            $nn = self::normalize($e->nama_parsed);
            $byBn[$bn . '|' . $nn][] = $e;
            $byNama[$nn][] = $e;
            if ($e->kode_parsed) {
                $byKode[$bn . '|' . self::normalize($e->kode_parsed)][] = $e;
            }
        }
        $pdfMap = HalalPdfItem::where('document_id', $pdfDocumentId)->pluck('halaman', 'nomor_urut');

        $masters = DB::table('master_products')->select('id', 'brand_name', 'code', 'name')
            ->orderBy('brand_name')->orderBy('code')->get();

        // Bersihkan helper yatim ID saja (jangan hapus baris SG).
        $masterIds = $masters->pluck('id')->all();
        if (!empty($masterIds)) {
            HalalHelper::where('sumber', 'ID')->whereNotIn('master_product_id', $masterIds)->delete();
        }
        // Baris kode-kosong hasil run lama (satu baris dipakai ramai-ramai oleh
        // banyak master sehingga nama + master_product_id tidak konsisten)
        // dibuang; dibuat ulang satu-per-master di loop bawah. Baris yang
        // sudah ditinjau manual (bukan Tidak cocok) dipertahankan.
        HalalHelper::where('sumber', 'ID')
            ->where(function ($w) {
                $w->where('kode_produk', '')->orWhereNull('kode_produk');
            })
            ->where('status_pencocokan', HalalHelper::STATUS_TIDAK)->delete();

        $petaConfirmed = self::isBrandMapConfirmed();

        $stats = ['Cocok' => 0, 'Perlu ditinjau' => 0, 'Tidak cocok' => 0];
        $seenKeys = [];
        foreach ($masters as $m) {
            $kode = trim((string) $m->code);
            $merek = trim((string) $m->brand_name);
            $nama = trim((string) $m->name);
            $row = [
                'kode_produk' => $kode,
                'merek' => $merek,
                'sumber' => 'ID',
                'nama_produk' => $nama,
                'tanggal_master' => $tanggalMaster,
                'tanggal_list' => $tanggalList,
                'tanggal_pdf' => $tanggalPdf,
                'list_document_id' => $listDocumentId,
                'pdf_document_id' => $pdfDocumentId,
                'search_keyword' => HalalHelper::normalizeKeyword($merek, $nama, $kode),
            ];
            if ($kode === '' || $nama === '') {
                $row += $this->noMatch('kode/nama kosong di master — lengkapi dulu di master produk');
                $this->saveHelper($m->id, $row, $stats);
                continue;
            }
            $mb = self::normalize(self::mapBrand($merek));
            $nn = self::normalize($nama);
            $kn = self::normalize($kode);
            $found = null;

            $ck = isset($byKode[$mb . '|' . $kn]) ? $byKode[$mb . '|' . $kn] : [];
            if ($ck) {
                $e = $ck[0];
                if (count($ck) === 1 && self::normalize($e->nama_parsed) === $nn) {
                    $found = ['Cocok', 'Kode', $e, ''];
                } else {
                    $found = ['Perlu ditinjau', 'Kode', $e,
                        count($ck) === 1 ? 'kode sama tetapi nama beda' : 'kode punya ' . count($ck) . ' kandidat'];
                }
            } else {
                $cb = isset($byBn[$mb . '|' . $nn]) ? $byBn[$mb . '|' . $nn] : [];
                if ($cb) {
                    $e = $cb[0];
                    if (count($cb) === 1 && !$e->kode_parsed) {
                        $found = ['Cocok', 'merek+nama', $e, ''];
                    } else {
                        $found = ['Perlu ditinjau', 'merek+nama', $e,
                            count($cb) === 1 ? 'nama sama tetapi kode berbeda' : 'merek+nama punya ' . count($cb) . ' kandidat'];
                    }
                } else {
                    $cn = isset($byNama[$nn]) ? $byNama[$nn] : [];
                    if ($cn) {
                        $found = ['Perlu ditinjau', 'nama saja', $cn[0],
                            'hanya cocok lewat nama (' . count($cn) . ' kandidat)'];
                    }
                }
            }

            if ($found) {
                list($status, $kunci, $e, $catatan) = $found;
                // Gerbang aturan 5: tanpa konfirmasi manajer, hasil yang memakai
                // peta merek (PPI FF/NON FF = IVA) tidak boleh langsung Cocok.
                if ($status === HalalHelper::STATUS_COCOK && !$petaConfirmed && self::usesMappedBrand($merek)) {
                    $status = HalalHelper::STATUS_TINJAU;
                    $catatan = trim($catatan . '; peta merek ' . $merek . ' = IVA belum dikonfirmasi', '; ');
                }
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
                    $row['halaman_pdf'] = null;
                    $row['nomor_urut_pdf'] = null;
                    $row['status_pdf'] = HalalHelper::PDF_BELUM;
                }
                $row['nomor_sertifikat'] = $cert;
            } else {
                $row += $this->noMatch('tidak ditemukan di list halal');
            }
            $key = $merek . '|' . $kode;
            if (isset($seenKeys[$key])) {
                $stats['digabung'] = isset($stats['digabung']) ? $stats['digabung'] + 1 : 1;
            }
            $seenKeys[$key] = true;
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
        // Kunci keunikan = (kode, merek, sumber): 1 produk bisa punya baris ID + SG.
        // master_product_id diisi dari baris pertama saja (tidak ditimpa).
        // Pengecualian: kode kosong (banyak master tanpa kode) dikunci per-master,
        // kalau tidak N master menimpa 1 baris yang sama (nama campur aduk).
        $kunci = [
            'kode_produk' => $row['kode_produk'],
            'merek' => $row['merek'],
            'sumber' => isset($row['sumber']) ? $row['sumber'] : 'ID',
        ];
        if (trim((string) $row['kode_produk']) === '') {
            $kunci['master_product_id'] = $masterId;
        }
        $helper = HalalHelper::firstOrNew($kunci);
        $isNew = !$helper->exists;
        if ($isNew) {
            $helper->master_product_id = $masterId;
        }
        $data = $row;
        unset($data['kode_produk'], $data['merek'], $data['sumber']);
        $helper->fill($data);
        $helper->save();
        $stats[$row['status_pencocokan']]++;
    }
}
