<?php

namespace App\Services\Halal;

use App\HalalDocument;
use App\HalalPdfItem;

/**
 * Rombakan ID pixel-identik: import halaman asli PDF (cover + halaman produk)
 * via FPDI, lalu sembunyikan produk selain yang dipilih.
 * Font, Arab, Garuda, kop 100% asli karena halamannya memang asli.
 *
 * Geometri tabel lampiran (diukur dari vector PDF, origin kiri-atas, pt):
 * - bingkai luar x 28.3-566.4, garis bagi kolom x ~86.3
 * - header y 399 h 20.5; 8 baris body y = 419.5 + i*20.35, h 20.35
 * Baris tersembunyi diputihkan PENUH (termasuk garis gridnya), lalu
 * header + baris terpilih digambar border sendiri via Rect.
 * Footer tanda tangan (Diterbitkan/KEPALA/QR/BSrE) hanya ada di halaman
 * lampiran terakhir PDF asli; untuk halaman tengah, footer di-stamp
 * dari crop PDF asli agar setiap preview punya footer.
 */
class HalalIdRombakan
{
    const TBL_X = 28.3;
    const TBL_R = 566.4;
    const HEAD_Y = 399.0;
    const HEAD_H = 20.5;
    const BODY_Y0 = 419.5;
    const ROW_H = 20.35;

    /**
     * @param \App\HalalHelper $row  baris helper sumber ID (status Cocok/Tinjau)
     * @return string  bytes PDF (cover + 1 halaman lampiran terombak)
     */
    public function build($row)
    {
        $docPdf = $row->pdf_document_id
            ? HalalDocument::find($row->pdf_document_id)
            : HalalDocument::where('jenis', 'pdf')->where('sumber', 'ID')->latest('id')->first();
        if (!$docPdf) {
            throw new \RuntimeException('Dokumen PDF ID belum ada.');
        }
        $path = $this->resolvePath($docPdf->file_path);
        if (!is_file($path)) {
            throw new \RuntimeException('File PDF ID tidak ditemukan: ' . $docPdf->file_path);
        }
        $halaman = (int) $row->halaman_pdf;
        if ($halaman < 2) {
            throw new \RuntimeException('Halaman PDF belum terpetakan untuk produk ini.');
        }
        $dipilih = (int) ($row->nomor_urut_pdf ?: $row->nomor_urut_list_halal);
        $items = HalalPdfItem::where('document_id', $docPdf->id)
            ->where('halaman', $halaman)->orderBy('nomor_urut')->get();
        if ($items->isEmpty()) {
            throw new \RuntimeException('Isi halaman ' . $halaman . ' tidak ditemukan.');
        }

        $pdf = new \setasign\Fpdi\TcpdfFpdi('P', 'mm', 'A4');
        // Font footer = font PDF asli (ikut repo agar tidak tergantung vendor).
        $fontDir = __DIR__ . '/fonts';
        foreach (['bookos', 'bookosi', 'timesar'] as $fam) {
            $fp = $fontDir . '/' . $fam . '.php';
            if (is_file($fp)) {
                $pdf->AddFont($fam, '', $fp);
            }
        }
        $pdf->SetAutoPageBreak(false);
        // Matikan header/footer bawaan TCPDF (sumber garis halus di atas).
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $count = $pdf->setSourceFile($path);

        // Hal 1: cover asli + tempel ulang QR TTD elektronik.
        // QR cover adalah anotasi/stempel sehingga tidak ikut ter-import
        // via FPDI (halaman import kehilangan QR); tempel ulang dari
        // crop PDF asli, di atas "Ahmad Haikal Hasan", di bawah Arab.
        $pdf->AddPage();
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl, 0, 0, 210);
        $this->drawWatermark($pdf);
        $qrPath = public_path('images/halal-qr-ttd.png');
        if (is_file($qrPath)) {
            // x=16mm, y=244mm, 26x26mm — celah antara Arab dan nama.
            $pdf->Image($qrPath, 16, 244, 26, 26, 'PNG');
        }

        // Hal produk: import asli + tutup baris yang tidak dipilih.
        if ($halaman > $count) {
            throw new \RuntimeException('Halaman ' . $halaman . ' di luar PDF (' . $count . ' hal).');
        }
        $pdf->AddPage();
        $tpl = $pdf->importPage($halaman);
        $pdf->useTemplate($tpl, 0, 0, 210);

        $k = 25.4 / 72; // pt -> mm (origin kiri-atas di TCPDF, sama dgn PDF)
        $tblX = self::TBL_X * $k;
        $tblW = (self::TBL_R - self::TBL_X) * $k;
        $selIdx = null;
        $i = 0;
        foreach ($items as $it) {
            if ((int) $it->nomor_urut === $dipilih && $selIdx === null) {
                $selIdx = $i;
            }
            $i++;
        }
        if ($selIdx === null) {
            throw new \RuntimeException('Nomor produk tidak ada di halaman ' . $halaman . '.');
        }

        // 1. Putihkan PENUH setiap baris tersembunyi (teks + garis gridnya),
        // overlap 0.7pt ke atas-bawah agar garis horizontal bersama ikut hilang.
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(255, 255, 255);
        $i = 0;
        foreach ($items as $it) {
            if ($i !== $selIdx) {
                $ry = (self::BODY_Y0 + $i * self::ROW_H) * $k;
                $pdf->Rect($tblX - 0.3, $ry - 0.25, $tblW + 0.6, (self::ROW_H + 0.5) * $k, 'F');
            }
            $i++;
        }

        // 2. Hapus sisa garis bingkai bawah tabel (lolos dari kotak baris
        // bila baris terakhir tersembunyi) — digambar ulang di langkah 3
        // bila baris terpilih memang baris terakhir.
        $pdf->Rect($tblX - 0.3, 205.3, $tblW + 0.6, 1.2, 'F');

        // 3. Border sendiri: header + baris terpilih digambar ulang penuh.
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.15);
        $pdf->Rect($tblX, self::HEAD_Y * $k, $tblW, self::HEAD_H * $k, 'D');
        $selY = (self::BODY_Y0 + $selIdx * self::ROW_H) * $k;
        $pdf->Rect($tblX, $selY, $tblW, self::ROW_H * $k, 'D');

        // 4. Watermark SPECIMEN diagonal transparan di tengah halaman
        // (alpha rendah agar tidak menutupi tulisan; footer digambar
        // sesudahnya agar teks & QR tetap tajam).
        $this->drawWatermark($pdf);

        // 5. Footer tanda tangan teks-native di halaman tengah (halaman
        // terakhir PDF asli sudah punya footer; jangan digambar ganda).
        // Digambar sebagai teks agar menyatu dengan halaman (bukan tempelan
        // gambar). Hanya QR & logo BSrE yang berupa gambar.
        if ($halaman < $count) {
            $this->drawFooterLampiran($pdf);
        }

        return $pdf->Output('', 'S');
    }

    /**
     * Watermark SPECIMEN diagonal di tengah halaman, transparan agar
     * tulisan di bawahnya tetap terbaca. Dipanggil sekali per halaman
     * (setelah template + rombakan, sebelum footer/QR).
     */
    protected function drawWatermark($pdf)
    {
        // Tengah A4 sedikit ke kiri (koreksi optik: teks diagonal terbaca
        // berat ke kanan): rotasi terhadap (92,148.5), posisi teks dihitung
        // dari lebar aktual.
        $pdf->SetFont('helvetica', 'B', 72);
        $w = $pdf->GetStringWidth('SPECIMEN');
        $x = 92 - $w / 2;
        $y = 148.5 + 9; // baseline ~setengah tinggi huruf di bawah tengah
        $pdf->SetAlpha(0.20);
        $pdf->SetTextColor(110, 110, 110);
        $pdf->StartTransform();
        $pdf->Rotate(45, 92, 148.5);
        $pdf->Text($x, $y, 'SPECIMEN');
        $pdf->StopTransform();
        $pdf->SetAlpha(1);
        $pdf->SetTextColor(0, 0, 0);
    }

    /**
     * Footer lampiran (Diterbitkan/KEPALA/QR/nama/e-sign/BSrE) digambar
     * sebagai teks native TCPDF agar menyatu dengan halaman asli.
     * Font = Bookman Old Style + Times New Roman (sama dgn PDF asli,
     * dikonversi ke TCPDF), posisi = span PDF asli (pt->mm).
     */
    protected function drawFooterLampiran($pdf)
    {
        $green = [0, 147, 73];
        $gray = [90, 90, 90];

        // Baris diterbitkan + tanggal + Arab kanan (y 220.4/220.5).
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('bookos', '', 9.6);
        $pdf->Text(10.4, 219.4, 'Diterbitkan di Jakarta pada');
        $pdf->Text(67.4, 219.4, '09 Juli 2026');
        $pdf->SetFont('timesar', '', 15.6);
        $pdf->setRTL(true);
        $pdf->SetXY(10, 219.5);
        $pdf->Cell(190, 5, 'أصدرت الشهادة بجاكرتا في', 0, 1, 'R');
        $pdf->setRTL(false);

        $pdf->SetTextColor($green[0], $green[1], $green[2]);
        $pdf->SetFont('bookosi', '', 9.6);
        $pdf->Text(10.2, 223.5, 'Issued in Jakarta on');

        // Blok KEPALA (y 230.9 / 234.9 / 239.0 / Arab 242.5).
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('bookos', '', 9.6);
        $pdf->Text(10.4, 229.9, 'KEPALA');
        $pdf->Text(10.4, 233.9, 'BADAN PENYELENGGARA JAMINAN PRODUK HALAL');
        $pdf->SetTextColor($green[0], $green[1], $green[2]);
        $pdf->SetFont('bookosi', '', 9.6);
        $pdf->Text(10.2, 238, 'HEAD OF HALAL PRODUCT ASSURANCE BODY');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('timesar', '', 15.6);
        $pdf->SetXY(10.4, 241.5);
        $pdf->Cell(120, 5, 'رئيس وكالة ضمان المنتجات الحلال', 0, 1, 'L');

        // QR TTD elektronik + nama penandatangan (nama y 273.9).
        $qrPath = public_path('images/halal-qr-ttd.png');
        if (is_file($qrPath)) {
            $pdf->Image($qrPath, 19, 249.5, 26, 26, 'PNG');
        }
        $pdf->SetFont('bookos', '', 9.6);
        $pdf->Text(10.4, 272.9, 'Ahmad Haikal Hasan');

        // Catatan e-sign 7.1pt, tepi kanan 174 (rata kanan spt asli).
        $pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
        $pdf->SetFont('bookos', '', 7.1);
        $pdf->SetXY(24, 273.2);
        $pdf->Cell(150, 3.5, 'Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik', 0, 1, 'R');
        $pdf->SetX(24);
        $pdf->Cell(150, 3.5, 'yang diterbitkan oleh Balai Sertifikasi Elektronik, BSSN', 0, 1, 'R');
        $bsrePath = public_path('images/halal-bsre.png');
        if (is_file($bsrePath)) {
            $pdf->Image($bsrePath, 176, 273.5, 26, 16.7, 'PNG');
        }

        // Kembalikan default.
        $pdf->SetTextColor(0, 0, 0);
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
