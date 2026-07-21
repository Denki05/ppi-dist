<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $delivery_order->do_code }}</title>
    @include('superuser.asset.css-pdf')
    <style>
      @page {
        size: A5 portrait;
        margin: 8mm 7mm;
      }
      body {
        font-size: 13px;
        font-family: Arial, sans-serif;
        color: #000;
        position: relative; /* Penting untuk mengunci TTD di bawah */
        height: 96%; /* Memastikan tinggi body hampir penuh 1 halaman */
      }
      table { width: 100%; }
      .text-center { text-align: center; }
      .text-left { text-align: left; }
      .text-right { text-align: right; }
      .text-bold { font-weight: bold; }

      /* ============ HEADER ============ */
      .title {
        font-size: 19px;
        font-weight: bold;
        letter-spacing: 2px;
        border-bottom: 2px solid #000;
        padding-bottom: 5px;
        margin-bottom: 6px;
      }

      table.info-table {
        margin-bottom: 4px;
        width: 100%;
      }
      table.info-table td {
        font-size: 13px;
        padding: 1.5px 0;
        vertical-align: top;
        line-height: 1.3;
      }
      .info-label { width: 12%; font-weight: bold; }
      .info-colon { width: 2%; }
      .info-value { width: 36%; }
      
      .info-value.reff {
        font-weight: bold;
        font-size: 15px;
        letter-spacing: .5px;
      }

      hr.divider {
        border: none;
        border-top: 1.5px solid #000;
        margin: 6px 0 8px 0;
      }

      /* ============ TABEL ITEM ============ */
      table.item-table {
        border-collapse: collapse;
        margin-top: 4px;
        width: 100%;
      }
      table.item-table th {
        border: 1.5px solid #000;
        padding: 5px;
        font-size: 12.5px;
        text-align: center;
        background-color: #000;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: .3px;
      }
      table.item-table td {
        border: 1px solid #000;
        padding: 6px 6px; 
        font-size: 14px;
        vertical-align: middle;
      }
      
      /* Tinggi baris kosong dirapikan kembali agar terlihat penuh tapi aman */
      table.item-table tr.empty-row td {
        height: 24px; 
      }
      
      .col-no { width: 6%; text-align: center; font-size: 14px; font-weight: bold; }
      .col-qty { width: 13%; text-align: center; font-weight: bold; font-size: 16px; }
      .col-unit { width: 10%; text-align: center; }
      .col-kemasan { width: 15%; text-align: center; }
      .col-jumlah { width: 12%; text-align: center; font-weight: bold; font-size: 15px; }
      .col-check { width: 12%; height: 26px; }

      .item-sku {
        font-size: 11px;
        color: #444;
      }
      .item-name {
        font-size: 15px;
        font-weight: bold;
        line-height: 1.25;
      }

      tr.total-row td {
        border-top: 2px solid #000;
        background-color: #f2f2f2;
        font-size: 15px;
        padding: 6px;
      }

      /* Note posisinya normal (nempel di bawah tabel persis) */
      .note {
        font-size: 11px;
        font-style: italic;
        margin-top: 6px;
      }

      /* ============ FOOTER (TTD SAJA YG DI BAWAH) ============ */
      .footer-wrapper {
        position: absolute;
        bottom: 0;
        width: 100%;
      }

      .sign-table {
        width: 100%;
      }
      .sign-table td {
        text-align: center;
        font-size: 13px;
        vertical-align: bottom;
      }
      .sign-line {
        display: block;
        margin-bottom: 4px;
      }

      .print-date {
        font-size: 10px;
        text-align: right;
        margin-top: 15px;
        font-style: italic;
        color: #333;
      }
    </style>
  </head>
  <body>
    <table class="header-row">
      <tr>
        <td class="text-center title">SPK - PACKING PLAN</td>
      </tr>
    </table>

    <table class="info-table">
      <tr>
        <td class="info-label">Code</td>
        <td class="info-colon">:</td>
        <td class="info-value">{{ $delivery_order->code }}</td>
        
        <td class="info-label">Customer</td>
        <td class="info-colon">:</td>
        <td class="info-value">{{ $customer_name }}</td>
      </tr>
      <tr>
        <td class="info-label">Reff</td>
        <td class="info-colon">:</td>
        <td class="info-value reff">{{ $delivery_order->do_code }}</td>
        
        <td class="info-label">Alamat</td>
        <td class="info-colon">:</td>
        <td class="info-value">{{ $customer_address }}</td>
      </tr>
    </table>

    <hr class="divider">

    <table class="item-table">
      <tr>
        <th class="col-no">No</th>
        <th>Product</th>
        <th class="col-qty">Qty</th>
        <th class="col-unit">Unit</th>
        <th class="col-kemasan">Kemasan</th>
        <th class="col-jumlah">Jumlah</th>
        <th class="col-check">Check</th>
      </tr>
      @php
        $totalQty = 0;
        $totalJumlah = 0;
        $maxRows = 10; // Menentukan total 12 baris list
        $rowCount = count($items);
      @endphp
      
      {{-- Baris Data Asli --}}
      @foreach ($items as $index => $item)
        @php
          $totalQty += $item['qty'];
          $totalJumlah += $item['jumlah'];
        @endphp
        <tr>
          <td class="col-no">{{ $index + 1 }}</td>
          <td>
            <span class="item-sku">{{ $item['sku'] }}</span><br>
            <span class="item-name">{{ $item['name'] }}</span>
          </td>
          <td class="col-qty">{{ number_format($item['qty'], 2) }}</td>
          <td class="col-unit">{{ $item['unit'] }}</td>
          <td class="col-kemasan">{{ $item['kemasan'] }}</td>
          <td class="col-jumlah">{{ number_format($item['jumlah'], 0) }}</td>
          <td class="col-check"></td>
        </tr>
      @endforeach

      {{-- Baris Tambahan / Kosong supaya genap 12 baris --}}
      @for ($i = $rowCount; $i < $maxRows; $i++)
        <tr class="empty-row">
          <td class="col-no">{{ $i + 1 }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
      @endfor

      {{-- Baris Total --}}
      <tr class="total-row text-bold">
        <td colspan="2" class="text-center">TOTAL</td>
        <td class="col-qty">{{ number_format($totalQty, 2) }}</td>
        <td></td>
        <td></td>
        <td class="col-jumlah">{{ number_format($totalJumlah, 0) }}</td>
        <td></td>
      </tr>
    </table>

    <div class="note">* Cek kembali barang sebelum dipacking &amp; dikirim</div>

    <div class="footer-wrapper">
      <table class="sign-table">
        <tr>
          <td width="50%">
            <br><br><br>
            <span class="sign-line">(.....................................)</span>
            Gudang
          </td>
          <td width="50%">
            <br><br><br>
            <span class="sign-line">(.....................................)</span>
            Checker
          </td>
        </tr>
      </table>
      
      <div class="print-date">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
      </div>
    </div>
  </body>
</html>