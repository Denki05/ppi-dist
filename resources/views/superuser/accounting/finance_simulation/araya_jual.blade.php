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
  .page-break {
    page-break-after: always;
  }
  .clearfix::after {
    content: "";
    display: table;
    clear: both;
  }
  .signature-container {
    position: absolute;
    right: 0;
    bottom: 0;
    text-align: center;
    width: 20%;
  }
</style>

@php
  $limit = 12; // Limit items per page
  $doDetails = $result->simulation_item;
  $doDetails = $doDetails->sortBy(function($row) {
      return $row->product->name ?? '';
  });
  $totalItems = $doDetails->count();
  $totalPages = ceil($totalItems / $limit);
@endphp

@php
  $offset = 0; // Untuk menyimpan indeks awal di setiap halaman
@endphp

@for ($page = 0; $page < $totalPages; $page++)
<div>
  <h2 style="text-align: center; margin: 0; padding: 15; margin-bottom: 5px;"><u>INVOICE JUAL UV ARAYA</u></h2>
  
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
              <td>{{ date('d-m-Y', strtotime($result->created_at)) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="column-float" style="width: 60%;">
          <table class="table borderless info" style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 35% !important;">Toko</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">
                    {{ $result->customer->name }} {{ $result->customer->text_kota }}
                </td>
              </tr>
              <tr>
                <td style="width: 35% !important;">Alamat</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->customer->address}}, {{ $result->customer->text_kelurahan }}, {{ $result->customer->text_kecamatan }}, {{ $result->customer->text_kota }}, {{ $result->customer->text_provinsi }}</td>
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
        <th>Product</th>
        <th>Qty</th>
        <th class="text-right">Price</th>
        <th class="text-right">Sub Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($doDetails->slice($page * $limit, $limit)->values() as $index => $row)
      @php
        // Nomor urut dihitung berdasarkan offset + index
        $nomor_urut = $offset + $index + 1;
      @endphp
      <tr>
        <td>{{ $nomor_urut }}</td>
        <td>{{ $row->product->code }} - {{ $row->product->name }}</td>
        <td>{{ number_format($row->qty, 0, ',', '.') }}</td>
        <td class="text-right">{{ number_format($row->price_jual, 2, ',', '.') }}</td>
        <td class="text-right">{{ number_format($row->total, 2, ',', '.') }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-right" style="font-size: 14px; border-bottom: 1px solid black;"></td>
        </tr>
        <tr>
            <td colspan="4" class="text-right" style="font-size: 14px;"><strong>Total :</strong></td>
            <td class="text-right" style="font-size: 14px; "><strong>{{ number_format($doDetails->slice($page * $limit, $limit)->sum('total'), 2, ',', '.') }}</strong></td>
        </tr>
    </tfoot>
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

<div style="font-size : 14px;">
  <div style="position: relative; height: 100px; margin-top: 30px;">
    <div class="signature-container">
      <br><br><br><br>
      .......................
      <br>
      Mengetahui,
    </div>
  </div>
</div>