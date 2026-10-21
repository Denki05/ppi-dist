<?php

namespace App\Services\Halal;

use App\HalalDocument;
use App\HalalList;

/**
 * Generator halaman "Annex A" MUIS Singapore.
 *
 * PDF resmi MUIS yang diunggah hanya berisi sebagian produk (halaman 2 & 5),
 * sehingga produk lain tidak punya halaman PDF asli. Solusinya: halaman
 * Annex A dibangun ulang dari data QR MUIS memakai template resmi (latar,
 * emblem, QR code) lalu produk yang dipilih saja yang digambar — produk lain
 * tidak digambar sama sekali (bukan ditutup kotak putih, sehingga tidak
 * terlihat garis putih di atas latar gradasi).
 *
 * Geometri diukur dari PDF resmi (origin kiri-bawah, satuan pt):
 * - x baris produk: nomor 67.666, nama 91.423
 * - baseline baris pertama 595.830, jarak baris 18.435, 25 baris per halaman
 * - font produk: Liberation Sans Bold 19 * 0.74937 = 14.238 pt (metrik sama
 *   dengan Helvetica-Bold yang dipakai TCPDF)
 * - warna teks: rgb(9, 61, 84)
 * - nomor halaman cetak "X of 5" + kode dokumen "301260500019600X"
 *
 * Pagination hasil rekonstruksi dicocokkan dengan PDF resmi:
 * halaman 2 berisi produk 1-24, halaman 3 = 25-49, halaman 4 = 50-74,
 * halaman 5 = 75-92 (produk 82 memang berada di halaman "5 of 5" asli).
 */
class HalalSgAnnex
{
    /* Geometri template (pt, origin kiri-bawah) */
    const NUM_X = 67.666;
    const NAME_X = 91.423;
    const FIRST_BASELINE = 595.830;
    const LINE_PITCH = 18.435;
    const LINES_PER_PAGE = 25;
    const PAGES_TOTAL = 5;              // 1 cover + 4 halaman annex
    const PRODUCT_FONT_SIZE = 14.238;   // 19 * 0.74937
    const WRAP_WIDTH = 486.0;           // lebar bungkus baris produk (pt)
    const TEXT_R = 9;
    const TEXT_G = 61;
    const TEXT_B = 84;

    /* Halaman PDF asli (tinggi 841.543 pt) */
    const PAGE_H = 841.543;
    const SCALE = 210.0 / 595.0;        // mm per pt pada template A4

    /* Area produk yang ditutup potongan latar (pt) */
    const PATCH_X = 30.0;
    const PATCH_X2 = 585.0;
    const PATCH_Y = 108.0;
    const PATCH_Y2 = 630.0;

    /* Kop halaman yang berubah tiap halaman (pt) */
    const HEAD_X = 452.163;
    const HEAD_Y = 691.297;
    const HEAD_SIZE = 8.618;            // 11.5 * 0.74937
    const DOCID_X = 503.425;
    const DOCID_Y = 15.365;
    const DOCID_SIZE = 8.243;           // 11 * 0.74937
    const DOCID_PREFIX = '301260500019600';

    /** @var array|null cache daftar produk (per request) */
    protected static $produkCache = null;

    /**
     * Nomor halaman cetak (2..5) untuk sebuah nomor urut produk,
     * mengikuti pagination resmi MUIS.
     * @param int $nomor nomor urut produk pada list QR
     * @return int|null
     */
    public static function halamanCetak($nomor)
    {
        $nomor = (int) $nomor;
        if ($nomor < 1) {
            return null;
        }
        $baris = 0;
        foreach (self::daftarProduk() as $n => $item) {
            if ($n > $nomor) {
                break;
            }
            $baris += self::barisProduk($item['nama'], $n);
        }
        if ($baris < 1) {
            return null;
        }
        $hal = (int) ceil($baris / self::LINES_PER_PAGE);
        return min(self::PAGES_TOTAL, 1 + $hal);
    }

    /**
     * Jumlah baris (1 atau 2) yang dipakai sebuah produk.
     */
    public static function barisProduk($nama, $nomor)
    {
        $text = self::teksBaris($nomor, $nama);
        return self::lebarTeks($text) > self::WRAP_WIDTH ? 2 : 1;
    }

    /**
     * Daftar produk SG (nomor urut => nama) dari dokumen list QR terakhir.
     * @return array
     */
    public static function daftarProduk()
    {
        if (self::$produkCache !== null) {
            return self::$produkCache;
        }
        $doc = self::dokumenList();
        if (!$doc) {
            self::$produkCache = [];
            return self::$produkCache;
        }
        $rows = HalalList::where('document_id', $doc->id)->orderBy('nomor_urut')->get();
        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r->nomor_urut] = ['nama' => trim((string) $r->nama_lengkap)];
        }
        self::$produkCache = $out;
        return self::$produkCache;
    }

    /**
     * Dokumen list (QR) SG terbaru.
     * @return \App\HalalDocument|null
     */
    public static function dokumenList()
    {
        return HalalDocument::where('jenis', HalalDocument::JENIS_LIST)
            ->where('sumber', 'SG')->latest('id')->first();
    }

    /**
     * Dokumen PDF SG terbaru.
     * @return \App\HalalDocument|null
     */
    public static function dokumenPdf()
    {
        return HalalDocument::where('jenis', HalalDocument::JENIS_PDF)
            ->where('sumber', 'SG')->latest('id')->first();
    }

    /**
     * Teks baris produk: "N. NAMA (FRAGRANCE COMPOUND)" huruf besar (MUIS).
     */
    public static function teksBaris($nomor, $nama)
    {
        $nama = strtoupper(trim((string) $nama));
        return $nomor . '. ' . $nama;
    }

    /**
     * Lebar teks (pt) memakai metrik Helvetica-Bold (sama dengan
     * Liberation Sans Bold yang dipakai PDF resmi MUIS).
     * @param string $text
     * @return float
     */
    public static function lebarTeks($text)
    {
        static $w = [
            ' ' => 278, '!' => 333, '"' => 474, '#' => 556, '$' => 556, '%' => 889, '&' => 722,
            "'" => 238, '(' => 333, ')' => 333, '*' => 389, '+' => 584, ',' => 278, '-' => 333,
            '.' => 278, '/' => 278, '0' => 556, '1' => 556, '2' => 556, '3' => 556, '4' => 556,
            '5' => 556, '6' => 556, '7' => 556, '8' => 556, '9' => 556, ':' => 333, ';' => 333,
            '<' => 584, '=' => 584, '>' => 584, '?' => 611, '@' => 975, 'A' => 722, 'B' => 722,
            'C' => 722, 'D' => 722, 'E' => 667, 'F' => 611, 'G' => 778, 'H' => 722, 'I' => 278,
            'J' => 500, 'K' => 667, 'L' => 556, 'M' => 833, 'N' => 722, 'O' => 778, 'P' => 667,
            'Q' => 778, 'R' => 722, 'S' => 667, 'T' => 611, 'U' => 722, 'V' => 667, 'W' => 944,
            'X' => 667, 'Y' => 667, 'Z' => 611, '[' => 333, '\\' => 278, ']' => 333, '^' => 584,
            '_' => 556, '`' => 333, 'a' => 556, 'b' => 611, 'c' => 556, 'd' => 611, 'e' => 556,
            'f' => 333, 'g' => 611, 'h' => 611, 'i' => 278, 'j' => 278, 'k' => 556, 'l' => 278,
            'm' => 889, 'n' => 611, 'o' => 611, 'p' => 611, 'q' => 611, 'r' => 389, 's' => 556,
            't' => 333, 'u' => 611, 'v' => 556, 'w' => 778, 'x' => 556, 'y' => 556, 'z' => 500,
            '{' => 334, '|' => 260, '}' => 334, '~' => 584,
        ];
        $sum = 0;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $ch = $text[$i];
            $sum += isset($w[$ch]) ? $w[$ch] : 556;
        }
        return $sum / 1000 * self::PRODUCT_FONT_SIZE;
    }

    /**
     * Susun layout halaman: nomor urut => ['page', 'y', 'lines', 'text'].
     * Perhitungan satu kali jalan (baris kumulatif per halaman).
     *
     * @param int|null $fokus nomor urut produk yang digambar (null = semua)
     * @return array
     */
    public function layout($fokus = null)
    {
        $produk = self::daftarProduk();
        $hasil = [];
        $baris = 0;
        foreach ($produk as $n => $item) {
            $lines = self::barisProduk($item['nama'], $n);
            $baris += $lines;
            $hal = min(self::PAGES_TOTAL, 1 + (int) ceil($baris / self::LINES_PER_PAGE));
            $offset = $baris - $lines - (self::LINES_PER_PAGE * ($hal - 2));
            $hasil[$n] = [
                'page' => $hal,
                'y' => self::FIRST_BASELINE - self::LINE_PITCH * $offset,
                'lines' => $lines,
                'text' => self::teksBaris($n, $item['nama']),
            ];
        }
        if ($fokus !== null) {
            foreach ($hasil as $n => $row) {
                if ((int) $n !== (int) $fokus) {
                    unset($hasil[$n]);
                }
            }
        }
        return $hasil;
    }

    /**
     * Build PDF SH untuk satu produk: cover asli + 1 halaman annex berisi
     * produk terpilih saja.
     *
     * @param \App\HalalHelper $row baris helper sumber SG
     * @return string bytes PDF
     */
    public function build($row)
    {
        $docPdf = $row->pdf_document_id
            ? HalalDocument::find($row->pdf_document_id)
            : self::dokumenPdf();
        if (!$docPdf) {
            throw new \RuntimeException('Dokumen PDF SG belum ada.');
        }
        $path = $this->resolvePath($docPdf->file_path);
        if (!is_file($path)) {
            throw new \RuntimeException('File PDF SG tidak ditemukan: ' . $docPdf->file_path);
        }
        $nomor = (int) ($row->nomor_urut_pdf ?: $row->nomor_urut_list_halal);
        if ($nomor < 1) {
            throw new \RuntimeException('Nomor produk SG tidak diketahui.');
        }
        $layout = $this->layout($nomor);
        if (!isset($layout[$nomor])) {
            throw new \RuntimeException('Produk tidak ditemukan pada list QR SG.');
        }
        $hal = (int) $layout[$nomor]['page'];

        $pdf = $this->pdfBaru($path);
        $count = $pdf->getNumPages();
        $tplIdx = $this->halamanTemplate($path, $count);

        // Hal 1: cover asli.
        $pdf->AddPage();
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl, 0, 0, 210);
        $this->drawWatermark($pdf);

        // Hal 2: halaman annex produk.
        $pdf->AddPage();
        $tpl = $pdf->importPage($tplIdx);
        $pdf->useTemplate($tpl, 0, 0, 210);
        $this->tutupArea($pdf, $docPdf);
        $this->gambarBaris($pdf, [$layout[$nomor]]);
        $this->gambarKopHalaman($pdf, $hal);
        $this->drawWatermark($pdf);

        return $pdf->Output('', 'S');
    }

    /**
     * Build dokumen sumber lengkap (cover + 4 halaman annex berisi SEMUA
     * produk) - inilah "PDF semua list" sesuai permintaan.
     *
     * @return string bytes PDF
     */
    public function buildSumber()
    {
        $docPdf = self::dokumenPdf();
        if (!$docPdf) {
            throw new \RuntimeException('Dokumen PDF SG belum ada.');
        }
        $path = $this->resolvePath($docPdf->file_path);
        if (!is_file($path)) {
            throw new \RuntimeException('File PDF SG tidak ditemukan: ' . $docPdf->file_path);
        }
        $layout = $this->layout();
        $perHalaman = [];
        foreach ($layout as $n => $row) {
            $perHalaman[$row['page']][] = $row;
        }
        ksort($perHalaman);

        $pdf = $this->pdfBaru($path);
        $count = $pdf->getNumPages();
        $tplIdx = $this->halamanTemplate($path, $count);

        $pdf->AddPage();
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl, 0, 0, 210);
        $this->drawWatermark($pdf);

        foreach ($perHalaman as $hal => $rows) {
            $pdf->AddPage();
            $tpl = $pdf->importPage($tplIdx);
            $pdf->useTemplate($tpl, 0, 0, 210);
            $this->tutupArea($pdf, $docPdf);
            $this->gambarBaris($pdf, $rows);
            $this->gambarKopHalaman($pdf, $hal);
            $this->drawWatermark($pdf);
        }
        return $pdf->Output('', 'S');
    }

    /**
     * PDF baru (TCPDF + FPDI) dengan konfigurasi yang sama untuk SH ID.
     */
    protected function pdfBaru($path)
    {
        $pdf = new \setasign\Fpdi\TcpdfFpdi('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        // Margin/padding sel harus nol supaya koordinat teks sama persis
        // dengan PDF asli (TCPDF menambah margin seldefault ~1mm ke kiri).
        $pdf->setCellMargins(0, 0, 0, 0);
        $pdf->setCellPaddings(0, 0, 0, 0);
        $pdf->setSourceFile($path);
        return $pdf;
    }

    /**
     * Nomor halaman fisik PDF yang dipakai sebagai template annex
     * (halaman dengan 2 gambar = 1 latar + 1 QR).
     */
    protected function halamanTemplate($path, $count)
    {
        $idx = $count;
        try {
            $pdf = new HalalSgPdf($path);
            $pages = $pdf->pageObjects();
            foreach ($pages as $i => $num) {
                $dict = $pdf->objectDict($num);
                if ($dict === '') {
                    continue;
                }
                $places = $pdf->imagePlacements($dict);
                if (count($places) === 2) {
                    $idx = $i + 1;
                    break;
                }
            }
        } catch (\Throwable $e) {
            // fallback: pakai halaman terakhir
        }
        return $idx;
    }

    /**
     * Tutup area produk + kop halaman lama dengan potongan gambar latar
     * resmi, sehingga produk/kop yang tidak dipilih hilang tanpa bekas
     * kotak putih.
     */
    protected function tutupArea($pdf, $docPdf)
    {
        // Aset latar diekstrak sekali dari PDF resmi (kalau belum ada).
        HalalSgAssets::ensure($docPdf);
        $rects = [
            'produk' => [self::PATCH_X, self::PATCH_Y, self::PATCH_X2, self::PATCH_Y2],
            'kop' => [445.0, 684.0, 530.0, 700.0],
            'docid' => [498.0, 8.0, 575.0, 24.0],
        ];
        foreach ($rects as $nama => $r) {
            $patch = $this->buatPatch($docPdf, $nama, $r);
            if (!$patch) {
                continue;
            }
            $pdf->Image(
                $patch,
                $r[0] * self::SCALE,
                (self::PAGE_H - $r[3]) * self::SCALE,
                ($r[2] - $r[0]) * self::SCALE,
                ($r[3] - $r[1]) * self::SCALE,
                'PNG'
            );
        }
    }

    /**
     * Buat potongan PNG dari gambar latar resmi (area tertentu).
     * @return string|null path file patch
     */
    protected function buatPatch($docPdf, $nama, array $rect)
    {
        $dir = storage_path('app/halal/aset/sg');
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $patch = $dir . '/patch-' . $nama . '.png';
        if (is_file($patch)) {
            return $patch;
        }
        $bg = $dir . '/bg.jpg';
        if (!is_file($bg)) {
            $bg = $dir . '/bg.png';
        }
        if (!is_file($bg)) {
            return null;
        }
        $im = strtolower(pathinfo($bg, PATHINFO_EXTENSION)) === 'png'
            ? @imagecreatefrompng($bg)
            : @imagecreatefromjpeg($bg);
        if (!$im) {
            return null;
        }
        $w = imagesx($im);
        $h = imagesy($im);
        // Pemetaan gambar -> PDF: X4 cm [595, 0, 0, 841.543, 0, 0.3773]
        $sx = $w / 595.0;
        $sy = $h / 841.543;
        $x1 = max(0, (int) round($rect[0] * $sx));
        $x2 = min($w, (int) round($rect[2] * $sx));
        $y1 = max(0, (int) round($h - $rect[3] * $sy));
        $y2 = min($h, (int) round($h - $rect[1] * $sy));
        $cw = max(1, $x2 - $x1);
        $ch = max(1, $y2 - $y1);
        $crop = imagecreatetruecolor($cw, $ch);
        imagecopy($crop, $im, 0, 0, $x1, $y1, $cw, $ch);
        imagepng($crop, $patch);
        imagedestroy($crop);
        imagedestroy($im);
        return $patch;
    }

    /**
     * Gambar baris produk (nomor + nama) memakai metrik resmi.
     */
    protected function gambarBaris($pdf, array $rows)
    {
        $pdf->SetFont('helvetica', 'B', self::PRODUCT_FONT_SIZE);
        $pdf->SetTextColor(self::TEXT_R, self::TEXT_G, self::TEXT_B);
        foreach ($rows as $row) {
            $y = $row['y'];
            $x = self::NUM_X;
            foreach ($this->pecahBaris($row['text']) as $line) {
                $this->teks($pdf, $x, $y, $line);
                $y -= self::LINE_PITCH;
                $x = self::NAME_X;
            }
        }
    }

    /**
     * Tulis teks dengan baseline tepat pada (x, y) pt.
     * Catatan: calign 'L' + valign 'T' => titik Y adalah baseline glyph;
     * argumen ke-6 wajib true agar mode render = fill (bukan invisible).
     */
    protected function teks($pdf, $xPt, $yPt, $text)
    {
        $pdf->Text(
            $xPt * self::SCALE,
            (self::PAGE_H - $yPt) * self::SCALE,
            $text,
            0, false, true, 0, 0, '', false, false, 0, false, 'L', 'T'
        );
    }

    /**
     * Pecah baris produk mengikuti lebar bungkus resmi: baris pertama mulai
     * dari kolom nomor, baris lanjutan mulai di kolom nama.
     * @return array<string>
     */
    protected function pecahBaris($text)
    {
        if (self::lebarTeks($text) <= self::WRAP_WIDTH) {
            return [$text];
        }
        $words = explode(' ', $text);
        $lines = [];
        $cur = '';
        foreach ($words as $word) {
            $coba = $cur === '' ? $word : $cur . ' ' . $word;
            if ($cur !== '' && self::lebarTeks($coba) > self::WRAP_WIDTH) {
                $lines[] = $cur;
                $cur = $word;
            } else {
                $cur = $coba;
            }
        }
        if ($cur !== '') {
            $lines[] = $cur;
        }
        return $lines;
    }

    /**
     * Gambar kop halaman yang berubah per halaman: "X of 5" + kode dokumen.
     */
    protected function gambarKopHalaman($pdf, $hal)
    {
        $pdf->SetFont('helvetica', '', self::HEAD_SIZE);
        $pdf->SetTextColor(self::TEXT_R, self::TEXT_G, self::TEXT_B);
        $this->teks($pdf, self::HEAD_X, self::HEAD_Y, $hal . ' of ' . self::PAGES_TOTAL);
        $pdf->SetFont('helvetica', '', self::DOCID_SIZE);
        $this->teks($pdf, self::DOCID_X, self::DOCID_Y, self::DOCID_PREFIX . $hal);
    }

    /**
     * Watermark SPECIMEN diagonal transparan (sama dengan SH ID).
     */
    protected function drawWatermark($pdf)
    {
        $pdf->SetFont('helvetica', 'B', 72);
        $w = $pdf->GetStringWidth('SPECIMEN');
        $x = 92 - $w / 2;
        $pdf->SetAlpha(0.20);
        $pdf->SetTextColor(110, 110, 110);
        $pdf->StartTransform();
        $pdf->Rotate(45, 92, 148.5);
        $pdf->Text($x, 157.5, 'SPECIMEN');
        $pdf->StopTransform();
        $pdf->SetAlpha(1);
        $pdf->SetTextColor(self::TEXT_R, self::TEXT_G, self::TEXT_B);
    }

    protected function resolvePath($filePath)
    {
        $filePath = (string) $filePath;
        $cands = [
            base_path($filePath),
            storage_path('app/' . preg_replace('#^(storage/app/|app/)#', '', $filePath)),
            public_path($filePath),
        ];
        foreach ($cands as $c) {
            if (is_file($c)) {
                return $c;
            }
        }
        return base_path($filePath);
    }
}
