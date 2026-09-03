<table>
    <tr>
        <td colspan="6"><strong>QA CHECKLIST - FLOW SALES ORDER</strong></td>
    </tr>
    <tr>
        <td colspan="6">Test sebelum upload ke trial program dan di coba user</td>
    </tr>
    <tr>
        <td><strong>Centang</strong></td>
        <td><strong>No</strong></td>
        <td><strong>Check Item</strong></td>
        <td><strong>Catatan / Hasil</strong></td>
        <td><strong>Status</strong></td>
        <td><strong>Keterangan</strong></td>
    </tr>
    <tr>
        <td colspan="6"><strong>PHASE 1: SALES ORDER AWAL (Cash &amp; Tempo)</strong></td>
    </tr>
    <tr>
        <td colspan="6"><strong>CASH</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>1.1</td>
        <td>Buka modal Add SO - Pilih Customer</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>1.2</td>
        <td>Pilih Type: CASH - checkbox Estimate otomatis centang</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>1.3</td>
        <td>Input Kurs, Disc %, Disc Kemasan, Disc IDR</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>1.4</td>
        <td>Pilih Brand, Kemasan, Indent - Klik Add</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>1.5</td>
        <td>Redirect ke halaman Create SO - Item muncul</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>TEMPO</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>1.6</td>
        <td>Buka modal Add SO - Pilih Customer - Type: TEMPO</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>1.7</td>
        <td>Checkbox Estimate tidak otomatis centang (sesuai UX Cash vs Tempo)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>1.8</td>
        <td>Input data lengkap - Klik Add - Redirect ke Create SO</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>PHASE 2: ESTIMATE / PDF ESTIMATE</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>2.1</td>
        <td>Klik Estimate PDF - PDF terbuka 1 halaman</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>2.2</td>
        <td>PDF: Header SALES ESTIMATE + Customer + Tanggal SO + AO/Sales tampil</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>2.3</td>
        <td>PDF: Note merah (estimasi harga, belum termasuk ongkir, stock &amp; kurs)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>2.4</td>
        <td>PDF: Tabel produk (No, Produk, Qty, Kemasan, Harga, Disc, Netto, Jumlah)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>2.5</td>
        <td>PDF: Terbilang, Kurs USD, Sub total, Disc %, Grand Total</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>2.6</td>
        <td>PDF: Tanda tangan Hormat kami di posisi kanan bawah</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>PHASE 3: LANJUTKAN SO - SO LANJUTAN</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>3.1</td>
        <td>Klik Lanjutkan di SO Awal - Konfirmasi SweetAlert muncul</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>3.2</td>
        <td>SO berpindah ke tab SO LANJUTAN dengan benar</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>3.3</td>
        <td>SO muncul di tabel SO Lanjutan dengan status benar</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>PHASE 4: TUTUP SO - LIST QUEUE</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>4.1</td>
        <td>Klik Tutup SO - SweetAlert konfirmasi</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>4.2</td>
        <td>SO berpindah ke tab LIST QUEUE (Packing Order)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>4.3</td>
        <td>Stok di-reserve (reserved_quantity bertambah)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>4.4</td>
        <td>Kurs valid (idr_rate lebih dari 1) - Invoice terbuat</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>PHASE 5: TEST REVISI SO</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>5.1</td>
        <td>Klik Revisi di List Queue - Form revisi terbuka</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>5.2</td>
        <td>Ubah qty/harga/disc - Simpan revisi</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>5.3</td>
        <td>Revisi tercatat di history (count_rev bertambah)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>PHASE 6: DO - PICKER</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>6.1</td>
        <td>DO terbuat dari SO - Status Packing Order = Ready</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>6.2</td>
        <td>Packing Order items sesuai dengan SO items</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>PHASE 7: DO - CHECKER (Status 3 ke 4)</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>7.1</td>
        <td>Buka DO di halaman Checker - Item terlist dengan benar</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>7.2</td>
        <td>Checklist semua item (checkbox harus centang semua)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>7.3</td>
        <td>Jika belum centang semua - tombol Save tidak bisa diklik</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>7.4</td>
        <td>Klik Save - Status DO berubah ke Siap Kirim (Status 4)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>PHASE 8: TEST REVISI ULANG</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>8.1</td>
        <td>Setelah DO Status 4, cek apakah bisa revisi lagi</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>8.2</td>
        <td>Jika sudah pernah di-revisi - tombol Ajukan Revisi disabled</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>PHASE 9: DO - DELIVERING (Status 4 ke 5)</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>9.1</td>
        <td>Cetak Surat Jalan (SJ) - PDF SJ terbuka</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>9.2</td>
        <td>Klik Delivering - Konfirmasi - Status berubah ke 5</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>9.3</td>
        <td>Stok fisik terpotong (quantity berkurang di ProductMinStock)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>9.4</td>
        <td>Kurs guard: Jika kurs kurang dari sama dengan 1 - DIBLOKIR</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>PHASE 10: UPDATE RESI (Status 5 ke 6)</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>10.1</td>
        <td>Upload foto bukti kirim (1-2 foto)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>10.2</td>
        <td>Input Ongkir + Resi (Ekspedisi + Nominal)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>10.3</td>
        <td>Klik Selesaikan - Status DO berubah ke Delivered (6)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>10.4</td>
        <td>Stock Move tercatat di kartu stok (StockMove)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>10.5</td>
        <td>Invoice terupdate (grand_total_idr sesuai)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>PHASE 11: REVISI INTERNAL DO</strong></td>
    </tr>
    <tr>
        <td colspan="6"><strong>PENGAJUAN</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>11.1</td>
        <td>Klik Ajukan Revisi di DO Status 5/6 - Form terbuka</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>11.2</td>
        <td>Ubah qty/harga/disc - Kirim pengajuan (status = Pending)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>11.3</td>
        <td>DO terkunci (internal_revision_status = 1) - tidak bisa diajukan ulang</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>APPROVAL</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>11.4</td>
        <td>Buka halaman Revisi Internal (tab SO Lanjutan) - Data muncul</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>11.5</td>
        <td>Klik Lihat Detail - Modal terbuka dengan data before/after</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>11.6</td>
        <td>Modal close button berfungsi dengan benar</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>OTP &amp; APPROVE</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>11.7</td>
        <td>Klik Kirim Kode OTP - SweetAlert konfirmasi</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>11.8</td>
        <td>OTP muncul di modal (kotak kuning) + countdown 5 menit</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>11.9</td>
        <td>Input OTP manual + Alasan - Klik Approve Sekarang</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>11.10</td>
        <td>SweetAlert konfirmasi approve - Proses - Success - Reload</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>HASIL APPROVE</strong></td>
    </tr>
    <tr>
        <td></td>
        <td>11.11</td>
        <td>DO items terupdate sesuai revisi</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>11.12</td>
        <td>PackingOrderDetail terupdate (disc, ongkir, grand total)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>11.13</td>
        <td>Invoice terupdate (grand_total_idr sesuai)</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>11.14</td>
        <td>Stok terkoreksi sesuai delta qty</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>11.15</td>
        <td>internal_revision_count bertambah - DO tidak bisa diajukan lagi</td>
        <td></td>
        <td>Pass / Fail / Skip</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="6"><strong>SUMMARY</strong></td>
    </tr>
    <tr>
        <td colspan="2"><strong>Total Item Test</strong></td>
        <td colspan="4">56</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Pass</strong></td>
        <td colspan="4"></td>
    </tr>
    <tr>
        <td colspan="2"><strong>Fail</strong></td>
        <td colspan="4"></td>
    </tr>
    <tr>
        <td colspan="2"><strong>Skip</strong></td>
        <td colspan="4"></td>
    </tr>
    <tr>
        <td colspan="2"><strong>Nama Tester</strong></td>
        <td colspan="4"></td>
    </tr>
    <tr>
        <td colspan="2"><strong>Tanggal Test</strong></td>
        <td colspan="4">{{ date('d/m/Y') }}</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Catatan / Bug Found</strong></td>
        <td colspan="4"></td>
    </tr>
</table>
