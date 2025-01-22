<?php
  $idr_total = 0; 
  $code = $result->code;
?>
<style type="text/css">
  body {
    color: #333;
    font-family: Arial, sans-serif;
    font-size: 12px;
  }
  table.borderless {
    border-collapse: collapse;
    border-spacing: 0;
  }
  .borderless td, .borderless th {
    border: none;
  }
  .info td, .info th {
    padding: 2px;
    margin: 2px;
    box-sizing: border-box;
  }
  .column-float {
    float: left;
    width: 50%;
  }
  .row-float {
    position: relative;
  }
  .row-float:after {
    content: "";
    display: block;
    clear: both;
  }
  table.table-data {
    width: 100%;
    border-collapse: collapse;
    color: #333;
  }
  table.table-data th {
    font-size: 12px;
    background-color: #d3d3d3;
  }
  table.table-data td {
    border: none;
  }
  table.table-data tbody {
    text-align: center;
    font-size: 12px;
  }
  @page {
    margin-top: 0px;
  }
  .text-right {
    text-align: right;
  }
  .text-left {
    text-align: left;
  }
  .header {
    width: 100%;
    position: fixed;
    z-index: 99999;
    letter-spacing: 10px;
    font-size: 150px;
    font-weight: 800;
    opacity: 0.3;
    color: #404040;
    text-transform: uppercase;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-20deg);
    text-align: center;
  }
  .page-break {
    page-break-after: always;
  }

  .clearfix::after {
    content: "";
    display: table;
    clear: both;
  }
</style>

@php
  $limit = 12; // Limit items per page
  $doDetails = $result->do->do_detail;
  $doDetails = $doDetails->sortBy(function($row) {
      return $row->product_pack->name ?? '';
  });
  $totalItems = $doDetails->count();
  $totalPages = ceil($totalItems / $limit);
@endphp

@php
  $offset = 0; // Untuk menyimpan indeks awal di setiap halaman
@endphp

@for ($page = 0; $page < $totalPages; $page++)
<div>
  <div class="header {{ $watermark == 'Paid' ? 'copy' : '' }}">
    {{$watermark}}
  </div>
  <h2 style="text-align: center; margin: 0; padding: 0; margin-bottom: 5px;"><u>PROFORMA INVOICE</u></h2>
  
  <div style="margin-bottom: 15px; font-size: 11px;">
    <div class="row-float">
      <div class="column-float" style="width: 40%; margin-top: 4px;">
        <table class="table borderless info" style="width: 100%;">
          <tbody>
            <tr>
              <td style="width: 35%;">Code</td>
              <td style="width: 2%;">:</td>
                <td style="width: 63%;"><b>{{ $code }}</b> / Page {{ $page + 1 }} of {{ $totalPages }}</td>
            </tr>
            <tr>
              <td>Tanggal</td>
              <td>:</td>
              <td>{{ date('d-m-Y', strtotime($result->do->created_at)) }}</td>
            </tr>
            <tr>
              <td>Jatuh Tempo</td>
              <td>:</td>
              <td>-</td>
            </tr>
            <tr>
              <td>Sales</td>
              <td>:</td>
              <td>{{ $result->do->so->sales() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="column-float" style="width: 60%;">
          <table class="table borderless info" style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 35% !important;"><b>Account</b></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">
                    <b>{{ $result->do->member->name }} {{ $result->do->member->text_kota }}</b>
                </td>
              </tr>
              <tr>
                <td style="width: 35% !important;">Toko</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">
                    {{ $result->do->member->name }} {{ $result->do->member->text_kota }}
                </td>
              </tr>
              <tr>
                <td style="width: 35% !important;">Alamat</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->do->customer->address}}, {{ $result->do->customer->text_kelurahan }}, {{ $result->do->customer->text_kecamatan }}, {{ $result->do->customer->text_kota }}, {{ $result->do->customer->text_provinsi }}</td>
              </tr>
              <tr>
                <td style="width: 35% !important;">Telepon</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">
                    {{$result->do->member->phone ?? ''}}
                </td>
              </tr>
            </tbody>
          </table>
      </div>
    </div>
  </div>
  
  <table class="table-data">
    <thead>
      <tr>
        <th>No</th>
        <th>Kode</th>
        <th>Variant</th>
        <th>Acuan</th>
        <th>Qty</th>
        <th>Kemasan</th>
        <th class="text-right">Harga</th>
        <th class="text-right">Disc</th>
        <th class="text-right">Netto</th>
        <th class="text-right">Jumlah</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($doDetails->slice($page * $limit, $limit)->values() as $index => $row)
      @php
        $harga = ceil($result->do->idr_rate * $row->price);
        $disc_cash = ceil($result->do->idr_rate * ($row->total_disc / $row->qty));
        $neto = $harga - $disc_cash;
        $sub_total = ceil($neto * $row->qty);
        $idr_total += $sub_total;

        // Nomor urut dihitung berdasarkan offset + index
        $nomor_urut = $offset + $index + 1;
      @endphp
      <tr>
        <td>{{ $nomor_urut }}</td>
        <td>{{ $row->product_pack->code ?? '' }}</td>
        <td>{{ $row->product_pack->name ?? '' }}</td>
        <td>{{ number_format($row->price, 2, ',', '.') }}</td>
        <td>{{ $row->qty }}</td>
        <td>{{ $row->packaging->pack_name ?? '' }}</td>
        <td class="text-right">{{ number_format($harga, 0, ',', '.') }}</td>
        <td class="text-right">{{ number_format($disc_cash, 0, ',', '.') }}</td>
        <td class="text-right">{{ number_format($neto, 0, ',', '.') }}</td>
        <td class="text-right">{{ number_format($sub_total, 0, ',', '.') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

@php
  // Tambahkan jumlah item yang dirender di halaman ini ke offset
  $offset += $doDetails->slice($page * $limit, $limit)->count();
@endphp

@if ($page < $totalPages - 1)
<div class="page-break"></div>
@endif
@endfor