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
  $doDetails = $result->so_detail;
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
  <h2 style="text-align: center; margin: 0; padding: 0; margin-bottom: 5px;"><u>SALES ORDER</u></h2>
  
  <div style="margin-bottom: 15px; font-size: 11px;">
    <div class="row-float">
      <div class="column-float" style="width: 40%; margin-top: 4px;">
        <table class="table borderless info" style="width: 100%;">
          <tbody>
            <tr>
                <td style="width: 35%;">Sales</td>
                <td style="width: 2%;">:</td>
                <td style="width: 63%;">{{ $result->sales() }}</td>
            </tr>
            <tr>
                <td style="width: 35%;">Customer</td>
                <td style="width: 2%;">:</td>
                <td style="width: 63%;">{{ $result->member->name }}  {{ $result->member->text_kota }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="column-float" style="width: 60%;">
          <table class="table borderless info" style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 35%;"><b>No. Nota</b></td>
                <td style="width: 2%;">:</td>
                <td style="width: 63%;"><b>{{ $result->so_code }}</b></td>
              </tr>
              <tr>
                <td style="width: 35%;">Tanggal</td>
                <td style="width: 2%;">:</td>
                <td style="width: 63%;">{{ \Carbon\Carbon::parse($result->so_date)->format('d-m-Y') }}</td>
              </tr>
              <tr>
                <td style="width: 35%;">Pembayaran</td>
                <td style="width: 2%;">:</td>
                <td style="width: 63%;">{{ $result->type_transaction }}</td>
              </tr>
              <tr>
                <td style="width: 35%;">Disc (%)</td>
                <td style="width: 2%;">:</td>
                <td style="width: 63%;">{{ $result->catatan }}</td>
              </tr>
            </tbody>
          </table>
      </div>
    </div>
  </div>
  
    <table class="table-data" style="border: 1px solid black;">
        <thead>
        <tr>
            <th style="border: 1px solid black;">No</th>
            <th style="border: 1px solid black;">Product</th>
            <th style="border: 1px solid black;">Qty (KG)</th>
            <th style="border: 1px solid black;">Kemasan</th>
            <th class="text-right" style="border: 1px solid black;">Harga ($)</th>
            <th class="text-right" style="border: 1px solid black;">Disc</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($doDetails->slice($page * $limit, $limit)->values() as $index => $row)
        @php
            // Nomor urut dihitung berdasarkan offset + index
            $nomor_urut = $offset + $index + 1;
        @endphp
        <tr>
            <td style="border: 1px solid black;">{{ $nomor_urut }}</td>
            <td style="border: 1px solid black;">{{ $row->product_pack->code }} - {{ $row->product_pack->name }}</td>
            <td style="border: 1px solid black;">{{ $row->qty }}</td>
            <td style="border: 1px solid black;">{{ $row->product_pack->packaging->pack_name }}</td>
            <td class="text-right" style="border: 1px solid black;">{{ number_format($row->price, 0, ',', '.') }}</td>
            <td class="text-right" style="border: 1px solid black;">{{ number_format($row->disc_usd, 0, ',', '.') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="column-float" style="width: 60%;">
        <b>Note: </b> <br>
        {{ $result->note }}
    </div>
</div>

@php
  // Tambahkan jumlah item yang dirender di halaman ini ke offset
  $offset += $doDetails->slice($page * $limit, $limit)->count();
@endphp

@if ($page < $totalPages - 1)
<div class="page-break"></div>
@endif
@endfor

<div>
  <div style="font-size: 12px; position: absolute; bottom: 10px; width: 100%; margin-top: 30px;">
    <div class="row-float clearfix" style="display: flex; justify-content: space-between;">

      <div class="row-float" style="display: flex; justify-content: space-between; align-items: flex-start;">
        
        <!-- Payment Notes Column -->
        <div class="column-float" style="width: 60%; font-size: 9px; font-weight: bold;">
            - Barang yang sudah dibeli tidak dapat ditukarkan / dikembalikan <br>
            - Pembayaran dengan cheque / wesel / BG diangap sah apabila telah diuangkan <br>
            - Barang telah diperiksa dan diterima dengan baik <br>
        </div>
        
        <!-- Bank Logo Column -->
        <div class="column-float" style="width: 20%; text-align: center;">
            Marketing
            <br><br><br><br>
            .......................
        </div>
        
        <!-- Signature Column -->
        <div class="column-float" style="width: 20%; text-align: center;">
            Menyetujui (ACC)
            <br><br><br><br>
            .......................
        </div>

      </div>
    </div>
    

    <div id="footer">
      <div class="page-number"></div>
    </div>
</div>