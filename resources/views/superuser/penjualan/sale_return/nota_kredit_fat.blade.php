<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>{{ $result->code }}</title>
  <style>
    @page {
      size: A5 landscape;
      margin: 20px;
    }
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      color: #000;
    }
    h2 {
      text-align: center;
      text-decoration: underline;
      margin-bottom: 10px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #000;
      padding: 5px;
    }
    .borderless td {
      border: none;
      padding: 3px;
    }
    .text-right {
      text-align: right;
    }
    .text-center {
      text-align: center;
    }
    .text-left {
      text-align: left;
    }
    .footer {
      margin-top: 30px;
    }
    .footer .signature-table td {
      text-align: center;
      padding-top: 40px;
    }
    .underline {
      text-decoration: underline;
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

    .signature {
      position: absolute;
      bottom: 20px;
      right: 30px;
      text-align: right;
      font-size: 12px;
    }
  </style>
</head>
<body>

  @php
    $getDo = $result->invoice;
    $getRetur = $result;
  @endphp

  <h2 style="text-align: center; margin: 0; padding: 0; margin-bottom: 5px;"><u>NOTA KREDIT FAT</u></h2>

  <div style="margin-bottom: 15px; font-size: 11px;">
    <div class="row-float">
      <div class="column-float" style="width: 40%; margin-top: 4px;">
        <table class="table borderless info" style="width: 100%;">
          <tbody>
            <tr>
              <td style="width: 35%;">Kode</td>
              <td style="width: 2%;">:</td>
              <td style="width: 63%;"><b>{{ $result->code }}</b></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="column-float" style="width: 60%;">
          <table class="table borderless info" style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 35% !important;">Customer</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">
                   {{ $getDo->customer->name ?? 'Tidak Diketahui' }} {{ $getDo->customer->text_kota ?? '' }}
                </td>
              </tr>
            </tbody>
          </table>
      </div>
    </div>
  </div>

  <!-- <hr> -->

  <h4>Referensi Dokumen Terkait:</h4>
  <table>
    <thead>
      <tr>
        <th>Jenis Dokumen</th>
        <th>Nomor</th>
        <th class="text-right">Nilai</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="text-align: center;">Nota Awal</td>
        <td style="text-align: center;">{{ $getDo->do_code }}</td>
        <td class="text-right">{{ number_format($getDo->do_detail_cost->purchase_total_idr, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td style="text-align: center;">Nota Kredit</td>
        <td style="text-align: center;">{{ $getRetur->code }}</td>
        <td class="text-right">{{ number_format($getRetur->cost->purchase_total_idr, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td colspan="2" class="text-right"><strong>Total Piutang Akhir :</strong></td>
        <td class="text-right"><strong>{{ number_format( $getDo->do_detail_cost->purchase_total_idr - $getRetur->cost->purchase_total_idr, 0, ',', '.') }}</strong></td>
      </tr>
    </tbody>
  </table>

  <!-- <h4>Detail Barang Retur (Ringkasan):</h4>
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Produk</th>
        <th>Kemasan</th>
        <th class="text-right">Qty</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($result->sale_return_details as $i => $item)
      <tr>
        <td class="text-center">{{ $i + 1 }}</td>
        <td class="text-center">{{ $item->product->code }} - {{ $item->product->name }}</td>
        <td class="text-center">{{ $item->product->packaging->pack_name ?? '' }}</td>
        <td class="text-right">{{ $item->qty }}</td>
      </tr>
      @endforeach
    </tbody>
  </table> -->

  <div class="signature">
    Mengetahui,<br><br><br><br>
    <span>.......................</span>
  </div>
</body>
</html>