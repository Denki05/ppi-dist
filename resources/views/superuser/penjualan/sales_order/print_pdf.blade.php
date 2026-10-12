<?php
  $doDetails = $result->so_detail;
  $doDetails = $doDetails->sortBy(function($row) {
      return $row->product_pack->name ?? '';
  });
  $totalItems = $doDetails->count();
  $limit = 6; // Limit items per page (kertas A5 landscape)
  $totalPages = max(1, ceil($totalItems / $limit));
  $tanggal = !empty($result->so_date)
      ? \Carbon\Carbon::parse($result->so_date)->format('d/m/Y')
      : ($result->created_at ? \Carbon\Carbon::parse($result->created_at)->format('d/m/Y') : '-');
?>
<style type="text/css">
  body {
    color: #000;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
  }
  @page {
    margin: 30px 30px 30px 30px;
  }
  .page-no {
    text-align: right;
    font-size: 12px;
    font-weight: bold;
    margin: 0 0 4px 0;
  }
  .nota-title {
    text-align: center;
    font-size: 22px;
    font-weight: bold;
    text-decoration: underline;
    margin: 0 0 12px 0;
    padding: 0;
  }
  .info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
  }
  .info-table td {
    padding: 2px 4px;
    vertical-align: top;
    font-size: 13px;
  }
  .info-label {
    font-weight: bold;
    white-space: nowrap;
  }
  table.table-data {
    width: 100%;
    border-collapse: collapse;
    border: 2px solid #000;
  }
  table.table-data th {
    font-size: 13px;
    font-weight: bold;
    border: 1px solid #000;
    padding: 5px 6px;
    text-align: center;
  }
  table.table-data td {
    border: 1px solid #000;
    padding: 5px 6px;
    font-size: 12px;
  }
  .text-center { text-align: center; }
  .text-right { text-align: right; }
  .noted-box {
    border: 2px solid #000;
    width: 80%;
    height: 100px;
    padding: 6px 8px;
    margin-top: 2px;
    font-size: 12px;
    overflow: visible;
  }
  .noted-box b { font-size: 13px; }
  .syarat {
    font-size: 11px;
    margin-top: 10px;
  }
  .sign-row { width: 100%; }
  .sign-row td {
    vertical-align: top;
    font-size: 12px;
  }
  .sign-col {
    text-align: center;
    font-weight: bold;
    font-size: 13px;
  }
  .sign-line { margin-top: 55px; }
  .page-break { page-break-after: always; }
  /* Psikologi anti-lupa: print dari Draft harus terlihat belum valid saat di-share ke grup */
  .draft-watermark {
    position: fixed; top: 38%; left: 5%; right: 5%; text-align: center;
    font-size: 72px; font-weight: bold; color: #dc2626; opacity: 0.13;
    transform: rotate(-24deg); letter-spacing: 2px; pointer-events: none; z-index: 9999;
  }
  .draft-banner {
    border: 2px dashed #dc2626; color: #b91c1c; background: #fef2f2;
    font-size: 13px; font-weight: bold; text-align: center;
    padding: 7px 10px; margin: 0 0 10px 0;
  }
  .draft-footer {
    margin-top: 8px; font-size: 11px; color: #b91c1c; text-align: center; font-weight: bold;
  }
</style>
@php $isDraft = in_array((int)($result->status ?? 0), [1, 3]); @endphp
@if($isDraft)
<div class="draft-watermark">DRAFT<br><span style="font-size:34px;">BELUM DILANJUTKAN</span></div>
@endif

@php $offset = 0; @endphp
@for ($page = 0; $page < $totalPages; $page++)
<div>
  <div class="page-no">Page {{ $page + 1 }} of {{ $totalPages }}</div>
  @if($page == 0 && $isDraft)
  <div class="draft-banner">DRAFT — BELUM DILANJUTKAN KE ADMIN, BELUM VALID UNTUK DIPROSES</div>
  @endif
  <h2 class="nota-title">SALES ORDER{{ $isDraft ? ' (DRAFT)' : '' }}</h2>

  <table class="info-table">
    <tr>
      <td style="width: 13%;"><span class="info-label">Sales</span></td>
      <td style="width: 2%;">:</td>
      <td style="width: 35%;">{{ $result->so_sales() }}</td>
      <td style="width: 15%;"><span class="info-label">No. Nota</span></td>
      <td style="width: 2%;">:</td>
      <td style="width: 33%;"><b>{{ $result->so_code }}</b></td>
    </tr>
    <tr>
      <td><span class="info-label">Customer</span></td>
      <td>:</td>
      <td>{{ $result->member->name ?? '-' }} {{ $result->member->text_kota ?? '' }}</td>
      <td><span class="info-label">Tanggal</span></td>
      <td>:</td>
      <td>{{ $tanggal }}</td>
    </tr>
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td><span class="info-label">Pembayaran</span></td>
      <td>:</td>
      <td>{{ $result->type_transaction }}</td>
    </tr>
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td><span class="info-label">Disc (%)</span></td>
      <td>:</td>
      <td>{{ $result->catatan ?? 0 }}</td>
    </tr>
  </table>

  <table class="table-data">
    <thead>
      <tr>
        <th style="width: 6%;">No.</th>
        <th style="width: 44%;">Product</th>
        <th style="width: 8%;">Kg</th>
        <th style="width: 16%;">Packing</th>
        <th style="width: 14%;">Harga ($)</th>
        <th style="width: 12%;">Disc</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($doDetails->slice($page * $limit, $limit)->values() as $index => $row)
      <tr>
        <td class="text-center">{{ $offset + $index + 1 }}</td>
        <td class="text-center">{{ $row->product_pack->code ?? '' }} - {{ $row->product_pack->name ?? '' }}</td>
        <td class="text-center">{{ number_format((float) $row->qty, 2, '.', '') }}</td>
        <td class="text-center">{{ $row->product_pack->packaging->pack_name ?? '-' }}</td>
        <td class="text-right">{{ number_format((float) $row->price, 2, '.', '') }}</td>
        <td class="text-right">{{ number_format((float) $row->disc_usd, 2, '.', '') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  @if ($page == $totalPages - 1)
  <div class="noted-box">
    <b>NOTED :</b><br>
    {{ $result->note ?? '-' }}
  </div>

  <table class="sign-row" style="margin-top: 10px;">
    <tr>
      <td style="width: 60%;" class="syarat">
        <b>Syarat Pembayaran :</b><br>
        - Barang yang sudah dibeli tidak dapat ditukarkan/dikembalikan<br>
        - Pembayaran dengan cheque/wesel/BG dianggap sah apabila telah diuangkan<br>
        - Barang telah diperiksa dan diterima dengan baik
      </td>
      <td style="width: 20%;" class="sign-col">
        Marketing
        <div class="sign-line">............................</div>
      </td>
      <td style="width: 20%;" class="sign-col">
        Menyetujui(ACC)
        <div class="sign-line">............................</div>
      </td>
    </tr>
  </table>
  @if($isDraft)
  <div class="draft-footer">Dokumen ini dicetak dari status DRAFT — wajib klik LANJUTKAN di program agar diproses admin. Jangan jadikan acuan final.</div>
  @endif
  @endif
</div>

@php $offset += $doDetails->slice($page * $limit, $limit)->count(); @endphp
@if ($page < $totalPages - 1)
<div class="page-break"></div>
@endif
@endfor
