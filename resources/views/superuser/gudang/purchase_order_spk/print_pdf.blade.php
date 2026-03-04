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
  $doDetails = $result->purchase_order_detail;
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
  <h2 style="text-align: center; margin: 0; padding: 0; margin-bottom: 5px;"><u>SURAT PERINTAH KERJA</u></h2>
  
  <div style="margin-bottom: 15px; font-size: 11px;">
    <div class="row-float">
      <div class="column-float" style="width: 50%; margin-top: 4px;">
        <table class="table borderless info" style="width: 100%;">
          <tbody>
            <tr>
                <td style="width: 35%;">NO - PO</td>
                <td style="width: 2%;">:</td>
                <td style="width: 63%;">{{ $result->code }}</td>
            </tr>
            <tr>
                <td style="width: 35%;">Tanggal</td>
                <td style="width: 2%;">:</td>
                <td style="width: 63%;">{{ \Carbon\Carbon::parse($result->created_at)->format('d-m-Y') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="column-float" style="width: 50%;">
          <table class="table borderless info" style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 35%;"><b>ETD</b></td>
                <td style="width: 2%;">:</td>
                <td style="width: 63%;"><b>{{ \Carbon\Carbon::parse($result->etd)->format('d-m-Y') }}</b></td>
              </tr>
              <tr>
                <td style="width: 35%;">KIRIM GUDANG</td>
                <td style="width: 2%;">:</td>
                <td style="width: 63%;">{{ $result->warehouse->name }}</td>
              </tr>
              <tr>
                <td style="width: 35%;">Brand</td>
                <td style="width: 2%;">:</td>
                <td style="width: 63%;">{{ $result->purchase_order_detail->first()->product_pack->product->brand_name }}</td>
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
            <th style="border: 1px solid black;">Kode</th>
            <th style="border: 1px solid black;">Nama (KG)</th>
            <th style="border: 1px solid black;">Qty (KG)</th>
            <th class="text-center" style="border: 1px solid black;">Kemasan</th>
            <th class="text-center" style="border: 1px solid black;">Notes</th>
            <th class="text-center" style="border: 1px solid black;">Customer</th>
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
            <td style="border: 1px solid black;">{{ $row->product_pack->code }}</td>
            <td style="border: 1px solid black;">{{ $row->product_pack->name }}</td>
            <td style="border: 1px solid black;">{{ $row->quantity }}</td>
            <td style="border: 1px solid black;">{{ $row->product_pack->packaging->pack_name }}</td>
            <td style="border: 1px solid black;">{{ $row->note_produksi }}</td>
            <td style="border: 1px solid black;">{{ $row->note_repack }}</td>
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

<div>
  <div style="font-size: 12px; position: absolute; bottom: 10px; width: 100%; margin-top: 30px;">
    <div class="row-float clearfix" style="display: flex; justify-content: space-between;">

      <div class="row-float" style="display: flex; justify-content: space-between; align-items: flex-start;">
        
        <!-- Bank Logo Column -->
        <div class="column-float" style="width: 20%; text-align: center;">
            Mengajukan
            <br><br><br><br>
            .......................
        </div>
        
        <!-- Signature Column -->
        <div class="column-float" style="width: 20%; text-align: center; margin-left: 60%;">
            Menyetujui
            <br><br><br><br>
            .......................
        </div>

      </div>
    </div>
    

    <div id="footer">
      <div class="page-number"></div>
    </div>
</div>