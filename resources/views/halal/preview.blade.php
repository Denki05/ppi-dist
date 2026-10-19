<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: "Times New Roman", Times, "DejaVu Serif", serif; color: #111; margin: 40px 44px; font-size: 12px; }
  .c { text-align: center; }
  .garuda { width: 110px; }
  .negara { font-size: 14px; font-weight: bold; margin: 4px 0 0 0; font-family: "Times New Roman", Times, serif; }
  .negara-en { font-size: 12px; color: #1a9e4b; font-style: italic; margin: 0; font-family: "Times New Roman", Times, serif; }
  .ar { font-size: 13px; margin: 0; }
  .judul { font-size: 17px; color: #a88d2e; margin: 6px 0 0 0; letter-spacing: .5px; font-weight: bold; font-family: "Times New Roman", Times, serif; }
  .judul-en { font-size: 12px; color: #1a9e4b; font-style: italic; margin: 0; font-family: "Times New Roman", Times, serif; }
  .ar-big { font-size: 24px; margin: 2px 0 12px 0; }
  table.meta { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
  table.meta td { vertical-align: top; padding: 3px 4px; font-size: 11.5px; font-family: "Times New Roman", Times, serif; }
  table.meta .lbl { width: 160px; }
  table.meta .en { color: #1a9e4b; font-style: italic; font-size: 10.5px; }
  table.meta .arn { text-align: right; font-size: 12px; width: 120px; }
  .daftar-lbl { font-size: 11px; margin: 8px 0 0 0; font-family: "Times New Roman", Times, serif; }
  table.daftar { width: 100%; border-collapse: collapse; }
  table.daftar th, table.daftar td { border: 1px solid #333; padding: 4px 8px; font-size: 11.5px; font-family: "Times New Roman", Times, serif; }
  table.daftar-head { border: 1px solid #333; margin-bottom: 6px; }
  table.daftar-body { border: 1px solid #333; }
  table.daftar td.no { text-align: center; width: 70px; }
  table.daftar td.kosong { color: #fff; }
  .hal { text-align: right; font-size: 10px; margin-top: 2px; }
  .cover-ket { font-size: 10px; }
  .cover-ttd { font-size: 10px; margin-top: 26px; }
  .qr-ttd { width: 92px; height: 92px; margin: 6px 0 4px 0; }
  table.ttd-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
  table.ttd-table td { vertical-align: bottom; padding: 0; }
  table.ttd-table .ttd-left { width: 170px; font-size: 10px; }
  table.ttd-table .ttd-note { font-size: 7.5px; color: #333; text-align: center; padding: 0 8px; }
  table.ttd-table .ttd-bsre { width: 110px; text-align: right; }
  .bsre-logo { width: 96px; }
  .lamp-ttd { font-size: 10px; margin-top: 60px; }
  .lamp-terbit { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
  .lamp-terbit td { padding: 0; vertical-align: top; }
  .halal-sg-head { width: 100%; margin-bottom: 8px; }
  .halal-sg-head td { vertical-align: top; }
  .sg-title { font-size: 15px; font-weight: bold; color: #0f4156; }
  .sg-qr { text-align: right; font-size: 10px; color: #0f4156; }
  .sg-cert { color: #0f4156; font-size: 11px; margin: 14px 0; }
  .sg-prod { font-size: 12px; font-weight: bold; color: #0f4156; margin: 26px 0 8px 0; }
  .sg-scheme { text-align: center; margin-top: 60px; }
  .sg-scheme h3 { color: #0f4156; letter-spacing: 1px; font-size: 15px; margin: 0 0 6px 0; }
  .sg-scheme p { font-size: 9px; color: #0f4156; }
  .sg-cover { text-align: center; color: #0f4156; }
  .sg-cover .sg-serial-top { text-align: right; font-size: 10px; }
  .sg-cover .sg-cert-no { font-size: 12px; font-weight: bold; margin: 2px 0; }
  .sg-cover .sg-cert-title { font-size: 15px; margin: 2px 0 10px 0; }
  .sg-cover p { font-size: 11px; margin: 3px 0; }
  .sg-cover .sg-company { font-size: 12px; font-weight: bold; margin: 8px 0 2px 0; }
  .sg-cover .sg-addr { font-size: 11px; }
  .sg-cover .sg-mufti { margin-top: 18px; font-size: 11px; }
  .sg-cover .sg-expiry { font-size: 12px; font-weight: bold; margin-top: 4px; }
  .sg-cover .sg-issued { font-size: 11px; margin-top: 10px; }
  .sg-cover .sg-paging { font-size: 11px; margin-top: 2px; }
  .sg-cover .sg-muis { font-size: 11px; font-weight: bold; margin-top: 14px; }
  .sg-cover .sg-hub { font-size: 10px; }
  .sg-cover .sg-scheme { margin-top: 26px; }
  .sg-cover .sg-nfd { font-size: 11px; font-weight: bold; margin-top: 16px; }
  .hide-note { font-size: 9px; color: #6b7280; font-style: italic; margin-top: 6px; }
  .foot { margin-top: 12px; font-size: 8px; color: #9ca3af; text-align: center; }
  .page-break { page-break-after: always; }
  .wm-specimen { position: fixed; top: 42%; left: 0; right: 0; text-align: center; font-size: 72px; font-weight: bold; font-family: Helvetica, Arial, sans-serif; color: #f4f4f4; letter-spacing: 8px; }
</style>
</head>
<body>
<div class="wm-specimen">SPECIMEN</div>
@if(($row->sumber ?: 'ID') === 'SG')
  {{-- Replica meniru struktur file contoh (Certificate + Annex A). Nomor seri
       "301260500019600N" = basis contoh + nomor cetak; ganti bila sertifikat baru beda. --}}
  @php
    $sgBase = '301260500019600';
    $sgOf = \App\Services\Halal\HalalSgImporter::SG_JUMLAH_HALAMAN;
    $sgExp = $expired ? strtoupper(date('d M Y', strtotime($expired))) : '30 APR 2027';
  @endphp
  {{-- ================= HAL 1: CERTIFICATE (cetak "1 of 5") ================= --}}
  <div class="sg-cover">
    <div class="sg-serial-top">{{ $sgBase }}1</div>
    <div class="sg-cert-no">{{ $sertifikat }}</div>
    <div class="sg-cert-title">Certificate</div>
    <p>The Majlis Ugama Islam Singapura (MUIS) hereby certifies that<br>the products listed in Annex A for</p>
    <div class="sg-company">{{ strtoupper($perusahaan) }}</div>
    <div class="sg-addr">10 TAMPINES NORTH DRIVE 4 #09-03/01/07, #08-03/01/07 JTC SPACE @ TAMPINES NORTH<br>SINGAPORE 528553<br>(Name and Address of Establishment)</div>
    <p>is in accordance to MUIS Halal Certification Conditions.</p>
    <div class="sg-mufti">Mufti of Singapore</div>
    <div class="sg-expiry">{{ $sgExp }}</div>
    <p>(Date of Expiry)</p>
    <div class="sg-issued">Issued on: {{ $issued ?? '10 Apr 2026' }}</div>
    <div class="sg-paging">1 of {{ $sgOf }}</div>
    <div class="sg-muis">MAJLIS UGAMA ISLAM SINGAPURA (Islamic Religious Council of Singapore)</div>
    <div class="sg-hub">Singapore Islamic Hub &bull; 273 Braddell Road, Singapore 579702</div>
    <div class="sg-scheme">
      <h3>PRODUCT SCHEME</h3>
      <p>The MUIS Halal Certificate is issued under Section 88A of the Administration of Muslim Law Act (Cap. 3).</p>
      <p>The use of the SAC accreditation mark indicates accreditation in respect of those activities covered by the accreditation certificate number PD-2018-18.</p>
    </div>
    <div class="sg-nfd">NOT FOR DISPLAY</div>
  </div>
  <div class="page-break"></div>

  {{-- ================= ANNEX A (halaman produk terpilih) ================= --}}
  <table class="halal-sg-head"><tr>
    <td><div class="sg-title">Annex A</div></td>
    <td class="sg-qr">{{ $sgBase }}{{ $halaman ?: '?' }}<br><strong>{{ $sertifikat }}</strong><br>Issued on: {{ $issued ?? '10 Apr 2026' }}<br>{{ $halaman ? $halaman . ' of ' . $sgOf : '' }}</td>
  </tr></table>
  <div class="sg-cert">The Majlis Ugama Islam Singapura (MUIS) hereby certifies that the following is in accordance to MUIS Halal Certification Conditions.</div>
  @php $tampil = collect($siblings)->where('pilih', true)->values(); if ($tampil->isEmpty()) { $tampil = collect([['nomor' => $row->nomor_urut_list_halal, 'nama' => $row->nama_di_list_halal]]); } @endphp
  @foreach($tampil as $s)
    <div class="sg-prod">{{ $s['nomor'] }}. {{ strtoupper($s['nama']) }}</div>
  @endforeach
  @if(count($siblings) > count($tampil))
    <div class="hide-note">{{ count($siblings) - count($tampil) }} produk lain di halaman ini disembunyikan.</div>
  @endif
  <div class="sg-scheme">
    <h3>PRODUCT SCHEME</h3>
    <p>The MUIS Halal Certificate is issued under Section 88A of the Administration of Muslim Law Act (Cap. 3).</p>
  </div>
  <div class="foot">{{ $perusahaan }} &bull; {{ $sertifikat }} @if($expired) &bull; expired {{ $expired }} @endif &bull; helper #{{ $row->id }}</div>
@else
  {{-- ================= HAL 1: COVER (sama persis isi contoh) ================= --}}
  <div class="c">
    @if(!empty($garuda))<img class="garuda" src="data:image/png;base64,{{ $garuda }}">@endif
    <p class="negara">REPUBLIK INDONESIA</p>
    <p class="negara-en">(REPUBLIC OF INDONESIA)</p>
    <p class="ar">&#1580;&#1605;&#1607;&#1608;&#1585;&#1610;&#1577; &#1573;&#1606;&#1583;&#1608;&#1606;&#1610;&#1587;&#1610;&#1575;</p>
    <p class="judul">SERTIFIKAT HALAL</p>
    <p class="judul-en">(HALAL CERTIFICATE)</p>
    <p class="ar-big">&#1588;&#1607;&#1575;&#1583;&#1577; &#1575;&#1604;&#1581;&#1604;&#1575;&#1604;</p>
  </div>
  <table class="meta">
    <tr>
      <td class="lbl">Nomor Sertifikat<br><span class="en">Certificate Number</span></td>
      <td><strong>{{ $sertifikat }}</strong></td>
      <td class="arn">&#1585;&#1602;&#1605; &#1575;&#1604;&#1588;&#1607;&#1575;&#1583;&#1577;</td>
    </tr>
  </table>
  <div class="c cover-ket">
    Berdasarkan keputusan penetapan halal produk Majelis Ulama Indonesia nomor :<br>
    <span style="color:#1a9e4b; font-style:italic;">Based on the decree to stipulating halal products of the Indonesian Council of Ulama :</span><br>
    MUI-SCI00070006790926 Tanggal 08 Juli 2026
  </div>
  <table class="meta" style="margin-top:10px;">
    <tr><td class="lbl">Jenis Produk<br><span class="en">Type of Product</span></td><td>Bahan kimiawi lainnya</td><td class="arn">&#1606;&#1608;&#1593; &#1575;&#1604;&#1605;&#1606;&#1578;&#1580;</td></tr>
    <tr><td class="lbl">Nama Produk<br><span class="en">Name of Product</span></td><td>-Terlampir / As Attached-</td><td class="arn">&#1575;&#1587;&#1605; &#1575;&#1604;&#1605;&#1606;&#1578;&#1580;</td></tr>
    <tr><td class="lbl">Nama Pelaku Usaha<br><span class="en">Name of Company</span></td><td>PT. Kreasi Mewangikan Indonesia</td><td class="arn">&#1575;&#1587;&#1605; &#1575;&#1604;&#1588;&#1585;&#1603;&#1577;</td></tr>
    <tr><td class="lbl">Alamat Pelaku Usaha<br><span class="en">Company's Address</span></td><td>Kawasan Industri dan Pergudangan SIRIE, Jl. Raya Lingkar Timur KM 5, Blok R6, Kelurahan Rangkahkidul, Kecamatan Sidoarjo, Kabupaten Sidoarjo, Jawa Timur, 61234, Indonesia</td><td class="arn">&#1593;&#1606;&#1608;&#1575;&#1606; &#1575;&#1604;&#1588;&#1585;&#1603;&#1577;</td></tr>
    <tr><td class="lbl">Diterbitkan di Jakarta pada<br><span class="en">Issued in Jakarta on</span></td><td>09 Juli 2026</td><td class="arn">&#1571;&#1589;&#1583;&#1585;&#1578; &#1575;&#1604;&#1588;&#1607;&#1575;&#1583;&#1577; &#1576;&#1580;&#1575;&#1603;&#1585;&#1578;&#1575; &#1601;&#1610;</td></tr>
  </table>
  <div class="c cover-ket" style="margin-top:8px;">
    Telah memenuhi ketentuan perundang-undangan<br>
    <span style="color:#1a9e4b; font-style:italic;">Has complied with the provision of laws and regulations</span>
  </div>
  <div class="cover-ttd">
    KEPALA<br>BADAN PENYELENGGARA JAMINAN PRODUK HALAL<br><span style="color:#1a9e4b; font-style:italic;">HEAD OF HALAL PRODUCT ASSURANCE BODY</span><br>
    <span>&#1585;&#1574;&#1610;&#1587; &#1608;&#1603;&#1575;&#1604;&#1577; &#1590;&#1605;&#1575;&#1606; &#1575;&#1604;&#1605;&#1606;&#1578;&#1580;&#1575;&#1578; &#1575;&#1604;&#1581;&#1604;&#1575;&#1604;</span><br>
    @if(!empty($qrTtd))<img class="qr-ttd" src="data:image/png;base64,{{ $qrTtd }}"><br>@endif
    <table class="ttd-table"><tr>
      <td class="ttd-left"><strong>Ahmad Haikal Hasan</strong></td>
      <td class="ttd-note">Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik, BSSN</td>
      <td class="ttd-bsre">@if(!empty($bsre))<img class="bsre-logo" src="data:image/png;base64,{{ $bsre }}">@endif</td>
    </tr></table>
  </div>
  <div class="page-break"></div>

  {{-- ================= HAL BERIKUT: LAMPIRAN, posisi tabel sama, hide = kosong ================= --}}
  <div class="c">
    @if(!empty($garuda))<img class="garuda" src="data:image/png;base64,{{ $garuda }}">@endif
    <p class="negara">REPUBLIK INDONESIA</p>
    <p class="negara-en">(REPUBLIC OF INDONESIA)</p>
    <p class="ar">&#1580;&#1605;&#1607;&#1608;&#1585;&#1610;&#1577; &#1573;&#1606;&#1583;&#1608;&#1606;&#1610;&#1587;&#1610;&#1575;</p>
    <p class="judul">LAMPIRAN SERTIFIKAT HALAL</p>
    <p class="judul-en">(THE ATTACHMENT OF HALAL CERTIFICATE)</p>
    <p class="ar-big">&#1605;&#1585;&#1601;&#1602;&#1577; &#1604;&#1588;&#1607;&#1575;&#1583;&#1577; &#1575;&#1604;&#1581;&#1604;&#1575;&#1604;</p>
  </div>
  <table class="meta">
    <tr>
      <td class="lbl">Nomor Sertifikat<br><span class="en">Certificate Number</span></td>
      <td><strong>{{ $sertifikat }}</strong></td>
      <td class="arn">&#1585;&#1602;&#1605; &#1575;&#1604;&#1588;&#1607;&#1575;&#1583;&#1577;</td>
    </tr>
    <tr><td class="lbl">Nama Pelaku Usaha<br><span class="en">Name of Company</span></td><td>PT. Kreasi Mewangikan Indonesia</td><td class="arn">&#1575;&#1587;&#1605; &#1575;&#1604;&#1588;&#1585;&#1603;&#1577;</td></tr>
    <tr><td class="lbl">Jenis Produk<br><span class="en">Type Product</span></td><td>Bahan kimiawi lainnya</td><td class="arn">&#1606;&#1608;&#1593; &#1575;&#1604;&#1605;&#1606;&#1578;&#1580;</td></tr>
    <tr><td class="lbl">Alamat Pabrik<br><span class="en">Factory's Address</span></td><td>PT. Kreasi Mewangikan Indonesia<br>Kawasan Industri dan Pergudangan SIRIE, Jl. Raya Lingkar Timur KM 5, Blok R6, Kelurahan Rangkahkidul, Kecamatan Sidoarjo, SIDOARJO, JAWA TIMUR, 61234, Indonesia</td><td class="arn">&#1593;&#1606;&#1608;&#1575;&#1606; &#1575;&#1604;&#1605;&#1589;&#1606;&#1593;</td></tr>
  </table>
  <p class="daftar-lbl">Daftar Produk / Product Name</p>
  {{-- Hanya produk yang dipilih yang tampil dalam kotak; produk lain di halaman yang sama disembunyikan. --}}
  @php
    $tampil = collect($siblings)->where('pilih', true)->values();
    if ($tampil->isEmpty()) {
      $tampil = collect([['nomor' => $row->nomor_urut_pdf ?: $row->nomor_urut_list_halal, 'nama' => $row->nama_di_list_halal, 'pilih' => true]]);
    }
  @endphp
  <table class="daftar daftar-head">
    <thead><tr><th style="width:70px;">No</th><th>Nama Produk/ Product Name</th></tr></thead>
  </table>
  <table class="daftar daftar-body">
    <tbody>
    @foreach($tampil as $s)
      <tr><td class="no">{{ $s['nomor'] }}</td><td>{{ $s['nama'] }}</td></tr>
    @endforeach
    </tbody>
  </table>
  <div class="hal">Hal {{ $halaman ? (($row->sumber ?: 'ID') === 'SG' ? $halaman : max(1, $halaman - 1)) : '-' }} / Total Produk: {{ $totalProduk ?? '1500' }}</div>

  {{-- Footer lampiran sesuai PDF asli: diterbitkan + kepala + QR + e-sign + BSrE. --}}
  <table class="lamp-terbit"><tr>
    <td>Diterbitkan di Jakarta pada<br><span style="color:#1a9e4b; font-style:italic;">Issued in Jakarta on</span></td>
    <td style="width:130px;">09 Juli 2026</td>
    <td style="text-align:right;">&#1571;&#1589;&#1583;&#1585;&#1578; &#1575;&#1604;&#1588;&#1607;&#1575;&#1583;&#1577; &#1576;&#1580;&#1575;&#1603;&#1585;&#1578;&#1575; &#1601;&#1610;</td>
  </tr></table>
  <div class="lamp-ttd">
    KEPALA<br>BADAN PENYELENGGARA JAMINAN PRODUK HALAL<br><span style="color:#1a9e4b; font-style:italic;">HEAD OF HALAL PRODUCT ASSURANCE BODY</span><br>
    <span>&#1585;&#1574;&#1610;&#1587; &#1608;&#1603;&#1575;&#1604;&#1577; &#1590;&#1605;&#1575;&#1606; &#1575;&#1604;&#1605;&#1606;&#1578;&#1580;&#1575;&#1578; &#1575;&#1604;&#1581;&#1604;&#1575;&#1604;</span><br>
    @if(!empty($qrTtd))<img class="qr-ttd" src="data:image/png;base64,{{ $qrTtd }}"><br>@endif
    <table class="ttd-table"><tr>
      <td class="ttd-left"><strong>Ahmad Haikal Hasan</strong></td>
      <td class="ttd-note">Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik, BSSN</td>
      <td class="ttd-bsre">@if(!empty($bsre))<img class="bsre-logo" src="data:image/png;base64,{{ $bsre }}">@endif</td>
    </tr></table>
  </div>
  <div class="foot">Dokumen turunan transaksi — hanya produk terpilih yang ditampilkan &bull; helper #{{ $row->id }} (ID)</div>
@endif
</body>
</html>
