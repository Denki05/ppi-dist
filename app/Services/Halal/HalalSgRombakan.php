<?php

namespace App\Services\Halal;

use App\HalalDocument;
use App\HalalHelper;
use App\HalalPdfItem;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Rombakan SG pixel-identik (setara rombakan ID): import halaman asli PDF
 * (cover cetak "1 of N" + halaman annex produk) via FPDI, lalu sembunyikan
 * produk selain yang dipilih.
 *
 * Beda dengan ID: posisi baris produk TIDAK diukur manual, melainkan dibaca
 * dinamis dari matriks Tm tiap potongan teks (Smalot getDataTm), sehingga
 * tahan terhadap susunan annex yang tak merata. Nomor halaman yang dipakai
 * adalah NOMOR CETAK ("N of M"); file upload boleh tidak lengkap.
 */
class HalalSgRombakan extends HalalIdRombakan
{
    /**
     * @param \App\HalalHelper $row  baris helper sumber SG terverifikasi PDF
     * @return string  bytes PDF (cover asli + 1 halaman annex terombak)
     */
    public function build($row)
    {
        $docPdf = $row->pdf_document_id
            ? HalalDocument::find($row->pdf_document_id)
            : HalalDocument::where('jenis', 'pdf')->where('sumber', 'SG')->latest('id')->first();
        if (!$docPdf) {
            throw new \RuntimeException('Dokumen PDF SG belum ada.');
        }
        $path = $this->resolvePath($docPdf->file_path);
        if (!is_file($path)) {
            throw new \RuntimeException('File PDF SG tidak ditemukan: ' . $docPdf->file_path);
        }
        $cetak = (int) $row->halaman_pdf;
        if ($cetak < 1) {
            throw new \RuntimeException('Halaman PDF belum terpetakan untuk produk ini.');
        }
        $dipilih = (int) ($row->nomor_urut_pdf ?: $row->nomor_urut_list_halal);
        if ($dipilih < 1) {
            throw new \RuntimeException('Nomor produk SG tidak diketahui.');
        }
        $items = HalalPdfItem::where('document_id', $docPdf->id)
            ->where('halaman', $cetak)->orderBy('nomor_urut')->get();
        if ($items->isEmpty()) {
            throw new \RuntimeException('Isi halaman cetak ' . $cetak . ' tidak ditemukan.');
        }
        // Sembuh-sendiri bila halaman tercatat stale (estimasi lama): cari
        // halaman sebenarnya dari nomor produk, pakai itu + betulkan helper.
        $ada = false;
        foreach ($items as $it) {
            if ((int) $it->nomor_urut === $dipilih) {
                $ada = true;
                break;
            }
        }
        if (!$ada) {
            $fix = HalalPdfItem::where('document_id', $docPdf->id)
                ->where('nomor_urut', $dipilih)->first();
            if (!$fix) {
                throw new \RuntimeException('Nomor produk tidak ada di PDF SG ini.');
            }
            \Log::warning("SH rombakan SG: helper {$row->id} hal {$cetak} stale, pakai hal {$fix->halaman}.");
            $cetak = (int) $fix->halaman;
            $row->halaman_pdf = $cetak;
            $row->save();
            $items = HalalPdfItem::where('document_id', $docPdf->id)
                ->where('halaman', $cetak)->orderBy('nomor_urut')->get();
        }

        // Petakan nomor cetak -> halaman fisik dari footer "N of M".
        $map = $this->printedMap($path);
        if (!isset($map[$cetak])) {
            throw new \RuntimeException('Halaman cetak ' . $cetak . ' tidak ada di file PDF.');
        }
        $fisikAnnex = $map[$cetak];
        $fisikCover = isset($map[1]) ? $map[1] : 1;

        $pdf = new \setasign\Fpdi\TcpdfFpdi('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $count = $pdf->setSourceFile($path);

        // Hal 1: cover asli apa adanya (tak ada daftar produk di sini).
        $pdf->AddPage();
        $tpl = $pdf->importPage($fisikCover);
        $pdf->useTemplate($tpl, 0, 0, 210);
        $this->drawWatermark($pdf);

        // Hal produk: import asli + tutup baris yang tidak dipilih.
        if ($fisikAnnex > $count) {
            throw new \RuntimeException('Halaman fisik ' . $fisikAnnex . ' di luar PDF.');
        }
        $pdf->AddPage();
        $tpl = $pdf->importPage($fisikAnnex);
        $size = $pdf->getTemplateSize($tpl);
        $pdf->useTemplate($tpl, 0, 0, 210);

        $garisY = $this->productLineY($path, $fisikAnnex);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(255, 255, 255);
        $k = 25.4 / 72; // pt -> mm
        $pageHmm = $size['height'];
        foreach ($items as $it) {
            if ((int) $it->nomor_urut === $dipilih) {
                continue;
            }
            $no = (int) $it->nomor_urut;
            if (!isset($garisY[$no])) {
                continue;
            }
            // Kotak putih menutupi baseline +- (descender 4pt, ascender 11pt).
            $yPt = $garisY[$no];
            $topMm = $pageHmm - ($yPt + 11) * $k;
            $pdf->Rect(8, $topMm, 194, 15 * $k, 'F');
        }
        $this->drawWatermark($pdf);

        return $pdf->Output('', 'S');
    }

    /**
     * Peta nomor cetak ("N of M" di footer) -> nomor fisik (1-based).
     */
    protected function printedMap($path)
    {
        $parser = new PdfParser();
        $pages = $parser->parseFile($path)->getPages();
        $map = [];
        foreach ($pages as $idx => $page) {
            $text = $page->getText();
            if (preg_match('/(\d+)\s+of\s+(\d+)/i', $text, $m)) {
                $n = (int) $m[1];
                if (!isset($map[$n])) {
                    $map[$n] = $idx + 1;
                }
            }
        }
        return $map;
    }

    /**
     * Y baseline (pt, origin kiri-bawah PDF) tiap baris produk "N. ...".
     * Baris direkonstruksi dari potongan Tj per bucket Y (toleransi 1pt).
     *
     * @return array [nomor => yPt]
     */
    protected function productLineY($path, $fisik)
    {
        $parser = new PdfParser();
        $pages = $parser->parseFile($path)->getPages();
        if (!isset($pages[$fisik - 1])) {
            return [];
        }
        $data = $pages[$fisik - 1]->getDataTm();
        $buckets = [];
        foreach ($data as $entry) {
            if (!is_array($entry) || count($entry) < 2) {
                continue;
            }
            $tm = $entry[0];
            $txt = trim((string) $entry[1]);
            if ($txt === '' || !isset($tm[4], $tm[5])) {
                continue;
            }
            $b = (string) round((float) $tm[5]);
            $buckets[$b][] = [(float) $tm[4], $txt];
        }
        $hasil = [];
        foreach ($buckets as $yKunci => $lines) {
            usort($lines, function ($a, $b) {
                return $a[0] < $b[0] ? -1 : 1;
            });
            $gabung = trim(implode(' ', array_column($lines, 1)));
            if (preg_match('/^(\d{1,4})\.\s+/', $gabung, $m)) {
                $hasil[(int) $m[1]] = (float) $yKunci;
            }
        }
        return $hasil;
    }
}
