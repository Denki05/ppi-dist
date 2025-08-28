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
        margin-top: -40px; /* Tambahkan ini */
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    .info td {
        padding: 2px 4px;
        font-size: 11px;
        vertical-align: top;
    }

    .table-data thead th {
        background: #eee;
        border: 1px solid #ccc;
        padding: 5px;
        font-size: 11px;
    }

    .table-data tbody td {
        border: 1px solid #ccc;
        padding: 5px;
        font-size: 11px;
        text-align: center;
    }

  .text-right {
    text-align: right;
  }

    .text-left {
        text-align: left;
    }

    .clearfix::after {
        content: "";
        display: table;
        clear: both;
    }

    .column-float {
        float: left;
        box-sizing: border-box;
    }

    .row-float {
        width: 100%;
        clear: both;
    }

    .page-container {
        position: relative;
        min-height: 100%; /* Pastikan tinggi penuh */
        padding-bottom: 80px; /* Hindari tabrakan dengan signature */
    }

    .signature {
      position: absolute;
      bottom: 20px;
      right: 30px;
      text-align: right;
      font-size: 12px;
    }
</style>

<div>
  <h2 style="text-align: center; margin-bottom: 10px;"><u>NOTA KREDIT</u></h2>

  <div class="row-float clearfix" style="margin-bottom: 15px;">
    <div class="column-float" style="width: 50%;">
      <table class="info">
        <tr><td style="width: 35%;">Code</td><td style="width: 5%;">:</td><td>{{ $code }}</td></tr>
        <tr><td>Tanggal Retur</td><td>:</td><td>{{ date('d-m-Y', strtotime($result->retur_date)) }}</td></tr>
        <tr><td>Ref DO</td><td>:</td><td>{{ $result->invoice->do_code }}</td></tr>
      </table>
    </div>
    <div class="column-float" style="width: 50%;">
      <table class="info">
        <tr>
          <td style="width: 30%;">Customer</td><td style="width: 5%;">:</td>
          <td>{{ $result->customer->name }} {{ $result->customer->text_kota }}</td>
        </tr>
        <tr>
          <td>Alamat</td><td>:</td>
          <td>{{ $result->customer->address }}, {{ $result->customer->text_kelurahan }}, {{ $result->customer->text_kecamatan }}, {{ $result->customer->text_kota }}, {{ $result->customer->text_provinsi }}</td>
        </tr>
        <tr><td>Telepon</td><td>:</td><td>{{ $result->customer->phone ?? '-' }}</td></tr>
      </table>
    </div>
  </div>

  <table class="table-data">
    <thead>
      <tr>
        <th style="width: 5%;">No</th>
        <th style="width: 25%;">Product</th>
        <th style="width: 10%;">Acuan</th>
        <th style="width: 8%;">Qty</th>
        <th style="width: 15%;">Kemasan</th>
        <th style="width: 12%;" class="text-right">Harga</th>
        <th style="width: 12%;" class="text-right">Disc</th>
        <th style="width: 13%;" class="text-right">Jumlah</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($result->sale_return_details as $index => $row)
        @php
          $harga = $row->price * $kurs;
          $disc_usd = $row->disc_usd * $kurs;
          $jumlah = ($row->price - $row->disc_usd) * $row->qty * $kurs;
          $idr_total += $jumlah;
        @endphp
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td class="text-left">{{ $row->product->code }} - {{ $row->product->name }}</td>
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

  @php
    use App\Helper\CustomHelper;

    $disc_1 = $result->cost->discount_1 ?? 0;
    $disc_2 = $result->cost->discount_2 ?? 0;
    $disc_idr = $result->cost->discount_idr ?? 0;
    $grand_total = $result->cost->purchase_total_idr ?? ($idr_total - $disc_1 - $disc_2 - $disc_idr);

    $terbilang = ucwords(CustomHelper::terbilang($grand_total)) . ' Rupiah';
  @endphp

  <div class="row-float clearfix" style="margin-top: 25px;">
    <div class="column-float" style="width: 60%; font-size: 12px;">
      Terbilang: {{ $terbilang }} <br>
      <br>
      <b>*Kurs USD: {{ number_format($kurs, 0, ',', '.') }}</b>
    </div>
    <div class="column-float" style="width: 40%;">
      <table style="width: 100%; font-size: 11px;">
        <tr><td style="text-align:right;">Sub Total</td><td class="text-left">: {{ number_format($idr_total, 0, ',', '.') }}</td></tr>
        <tr><td style="text-align:right;">Disc Persen</td><td class="text-left">: {{ number_format($disc_1, 0, ',', '.') }}</td></tr>
        <tr><td style="text-align:right;">Disc Kemasan</td><td class="text-left">: {{ number_format($disc_2, 0, ',', '.') }}</td></tr>
        <tr><td style="text-align:right;">Disc IDR</td><td class="text-left">: {{ number_format($disc_idr, 0, ',', '.') }}</td></tr>
        <tr><td style="text-align:right;"><strong>Grand Total</strong></td><td class="text-left"><strong>: {{ number_format($grand_total, 0, ',', '.') }}</strong></td></tr>
      </table>
    </div>
  </div>

  <div class="signature">
    Hormat Kami<br><br><br><br>
    <span>.......................</span>
  </div>
</div>