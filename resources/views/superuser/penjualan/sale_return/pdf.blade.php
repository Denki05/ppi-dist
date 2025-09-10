@php
  $idr_total = 0;
  $code = $result->code;
  $kurs = $result->idr_rate;
@endphp
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
  $doDetails = $result->sale_return_details;
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
  <h2 style="text-align: center; margin: 0; padding: 0; margin-bottom: 5px;"><u>NOTA KREDIT</u></h2>
  
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
              <td>{{ date('d-m-Y', strtotime($result->retur_date)) }}</td>
            </tr>
            <tr>
              <td>Reff</td>
              <td>:</td>
              <td>{{ $result->invoice->do_code }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="column-float" style="width: 60%;">
          <table class="table borderless info" style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 35% !important;"><b>Customer</b></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">
                    {{ $result->customer->name }} {{ $result->customer->text_kota }}
                </td>
              </tr>
              <tr>
                <td style="width: 35% !important;">Alamat</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">
                  {{ $result->customer->address }}, {{ $result->customer->text_kelurahan }}, {{ $result->customer->text_kecamatan }}, {{ $result->customer->text_kota }}, {{ $result->customer->text_provinsi }}
                </td>
              </tr>
              <tr>
                <td style="width: 35% !important;">Telepon</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">
                   {{ $result->customer->phone ?? '-' }}
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
        <th class="text-right">Jumlah</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($doDetails->slice($page * $limit, $limit)->values() as $index => $row)
      @php
        $harga = $row->price * $kurs;
        $disc_usd = $row->disc_usd * $kurs;
        $jumlah = ($row->price - $row->disc_usd) * $row->qty * $kurs;
        $idr_total += $jumlah;

        // Nomor urut dihitung berdasarkan offset + index
        $nomor_urut = $offset + $index + 1;
      @endphp
      <tr>
        <td>{{ $nomor_urut }}</td>
        <td>{{ $row->product->code }}</td>
        <td>{{ $row->product->name ?? '' }}</td>
        <td>{{ number_format($row->price, 2) }}</td>
        <td>{{ $row->qty }}</td>
        <td>{{ $row->product->packaging->pack_name ?? '' }}</td>
        <td class="text-right">{{ number_format($harga, 0, ',', '.') }}</td>
        <td class="text-right">{{ number_format($disc_usd, 0, ',', '.') }}</td>
        <td class="text-right">{{ number_format($jumlah, 0, ',', '.') }}</td>
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
  <div style="font-size: 12px; position: absolute; bottom: -50px; width: 100%; margin-top: 30px;">
    <div class="row-float clearfix" style="display: flex; justify-content: space-between;">
        @php
          use App\Helper\CustomHelper;

          $disc_1 = $result->cost->discount_1 ?? 0;
          $disc_2 = $result->cost->discount_2 ?? 0;
          $disc_idr = $result->cost->discount_idr ?? 0;
          $grand_total = $result->cost->purchase_total_idr ?? ($idr_total - $disc_1 - $disc_2 - $disc_idr);

          $terbilang = ucwords(CustomHelper::terbilang($grand_total)) . ' Rupiah';
        @endphp
        <!-- Left Column -->
        <div class="column-float" style="width: 70%">
          Terbilang: {{ $terbilang }}<br><br>
          <b>*Kurs USD: {{ number_format($kurs, 0, ',', '.') }}</b>
        </div>
        
        <!-- Right Column (Table) -->
        <div class="column-float" style="width: 30%;">
          <table style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 60%; text-align: right;">Sub Total</td>
                <td style="width: 40%; text-align: left;">: {{ number_format($idr_total, 0, ',', '.') }}</td>
              </tr>
              <tr>
                <td style="width: 60%; text-align: right;">Disc % ({{ $result->invoice->do_detail_cost->discount_1 }})</td>
                <td style="width: 40%; text-align: left;">: {{ number_format($disc_1, 0, ',', '.') }}</td>
              </tr>
              <tr>
                <td style="width: 60%; text-align: right;">Disc Kemasan ({{ $result->invoice->do_detail_cost->discount_2 }})</td>
                <td style="width: 40%; text-align: left; border-bottom: 1px solid black;">: {{ number_format($disc_2, 0, ',', '.') }}</td>
              </tr>
              <tr>
                <td style="width: 60%; text-align: right;">Disc IDR</td>
                <td style="width: 40%; text-align: left;">: {{ number_format($disc_idr, 0, ',', '.') }}</td>
              </tr>
              <tr>
                <td style="width: 60%; text-align: right;"><strong>Grand Total</strong></td>
                <td style="width: 40%; text-align: left;"><strong>: {{ number_format($grand_total, 0, ',', '.') }}</strong></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="row-float" style="display: flex; justify-content: space-between; align-items: flex-start;">
        
        <!-- Payment Notes Column -->
        <div class="column-float" style="width: 60%; font-size: 9px; font-weight: bold;">
          <!-- - Pembayaran Cheque/Wesel/BG dianggap sah bila telah diuangkan <br>
          - Pembayaran TUNAI wajib disertai Tanda Terima Tunai resmi dari kantor <br>
          - Pembayaran diluar ketentuan diatas tidak diakui <br>
          - Barang yang sudah dibeli tidak dapat ditukar/dikembalikan -->
        </div>
        
        <!-- Bank Logo Column -->
        <div class="column-float" style="width: 20%; text-align: left; margin-left: -35px;">
          <div style="height: 100px;">
          
          </div>
        </div>
        
        <!-- Signature Column -->
        <div class="column-float" style="width: 20%; text-align: center;">
          <br><br><br><br>
          .......................
          <br>
          Hormat Kami
        </div>

      </div>
    </div>
    

    <div id="footer">
      <div class="page-number"></div>
    </div>
</div>