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
</style>

<div>
  <h2 style="text-align: center; margin-bottom: 10px;"><u>SJ RECEIVING - TRANSAKSI</u></h2>

  <div class="row-float clearfix" style="margin-bottom: 15px;">
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
    <div class="column-float" style="width: 50%;">
      <table class="info">
        @php
            $get_date_qc = DB::table('receiving_detail')
                ->leftJoin('penjualan_retur', 'penjualan_retur.id', '=', 'receiving_detail.po_id')
                ->leftJoin('receiving', 'receiving.id', '=', 'receiving_detail.receiving_id')
                ->where('receiving_detail.po_id', $result->id)
                ->select('receiving.pbm_date')
                ->first();
        @endphp
        <tr>
            <td style="width: 35%;">Kode</td><td style="width: 5%;">:</td><td>{{ $code }} / {{ $result->invoice->do_code }}</td>
        </tr>
        <tr>
            <td style="width: 35%;">Tanggal</td><td style="width: 5%;">:</td><td>{{ date('d-m-Y', strtotime($get_date_qc->pbm_date)) }}</td>
        </tr>
        <tr>
            <td style="width: 35%;">Warehouse</td><td style="width: 5%;">:</td><td>{{ $result->warehouse->name }}</td>
        </tr>
      </table>
    </div>
    
  </div>

  <table class="table-data">
    <thead>
      <tr>
        <th style="width: 5%;">No</th>
        <th style="width: 25%;">Product</th>
        <th style="width: 8%;">Qty</th>
        <th style="width: 15%;">Kemasan</th>
        <th style="width: 15%;">Jumlah</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($result->sale_return_details as $index => $row)
        @php
            $jumlah = $row->qty / $row->product->packaging->pack_value;
        @endphp
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td class="text-left">{{ $row->product->code }} - {{ $row->product->name }}</td>
          <td>{{ $row->qty }}</td>
          <td>{{ $row->product->packaging->pack_name ?? '' }}</td>
          <td>{{ $jumlah }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  

  

    <div style="margin-top: 188px;">
      <table style="width: 100%; text-align: center;">
          <tr>
              <td style="width: 50%;">
                  Dibuat Oleh<br><br><br><br>
                  <span>.......................</span>
              </td>
              <td style="width: 50%;">
                  Mengetahui<br><br><br><br>
                  <span>.......................</span>
              </td>
          </tr>
      </table>
  </div>
</div>