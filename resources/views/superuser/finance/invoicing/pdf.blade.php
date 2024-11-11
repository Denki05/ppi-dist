<?php
  $idr_total = 0; 
  $code = $result->code;
?>
<style type="text/css">
    body{
      color: #333;
      font-family: Arial,sans-serif;
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

    /*Table*/
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
    border: none; /* Ensures <td> elements have no border */
    }

    table.table-data tbody {
    text-align: center;
    font-size: 12px;
    }

    @page{
      margin-top: 0px;
    }

    .text-right{
      text-align: right;
    }
    .text-left{
      text-align: left;
    }

    .p-note{
      padding: 2px;
      width: 100%;
      word-wrap: break-word;
      box-sizing: border-box;
      text-align: justify;
      border:1px solid grey;
      font-size: 70%;
    }
    .p-note p {
      line-height: 1;
      margin: 5px 0;
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
        left: 40%;
        transform:  translateX(-70%) translateY(-55%) rotate(-20deg);
    }
    .header.paid {
      left: 50%!important;
    }

    /*Footer*/
    #footer {
      position: fixed;
      left: 0;
      right: 0;
      color: #aaa;
      font-size: 11px;
    }
    
    #footer {
      bottom: 0;
    }
    .page-number:before {
      content: '<?= $code." | ".$result->do->customer->name ?? null; ?>' " | Page " counter(page);
    }

    .page-break {
        page-break-after: always;
        break-after: always;
    }
  </style>

@php
  // Limit per page
  $limit = 12;

  // Get the total items as a collection
  $doDetails = $result->do->do_detail;

  // Sort the collection by product_pack name in ascending order
  $doDetails = $doDetails->sortBy(function($row) {
      return $row->product_pack->name ?? '';
  });

  // Count total items
  $totalItems = $doDetails->count();

  // Calculate total pages
  $totalPages = ceil($totalItems / $limit);
@endphp

@for ($page = 0; $page < $totalPages; $page++)
    <div>
      <div class="header {{ $watermark == 'Paid' ? 'paid' : '' }}">
        {{$watermark}}
      </div>
      <div class="row-float">
          <h2 style="text-align: center;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;"><u>INVOICE</u></h2>
      </div>

      <div style="margin-bottom: 15px !important;font-size: 11px;">
      <div class="row-float">
        <div class="column-float" style="width: 40%;margin-top: 10px;">
            <table class="table borderless info" style="width: 100%">
                <tbody>
                    <tr>
                        <td style="width: 35% !important;">Code</td>
                        <td style="width: 2% !important;">:</td>
                        <td style="width: 63% !important;">{{ $code }}</td>
                    </tr>
                    <tr>
                        <td style="width: 35% !important;">Tanggal</td>
                        <td style="width: 2% !important;">:</td>
                        <td style="width: 63% !important;">{{date('d-m-Y',strtotime($result->do->created_at))}}</td>
                    </tr>
                    <tr>
                        <td style="width: 35% !important;">Jatuh Tempo</td>
                        <td style="width: 2% !important;">:</td>
                        <td style="width: 63% !important;">-</td>
                    </tr>
                    <tr>
                        <td style="width: 35% !important;">Sales</td>
                        <td style="width: 2% !important;">:</td>
                        <td style="width: 63% !important;">{{ $result->do->so->sales() }}</td>
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
                <td style="width: 63% !important;">{{$result->do->customer->address ?? ''}}</td>
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
        <table class="table-data" style="width: 100%;">
          <thead>
            <tr>
              <th style="width: 5%;">No</th>
              <th style="width: 10%;">Kode</th>
              <th style="width: 15%;">Variant</th>
              <th style="width: 7%;">Acuan</th>
              <th style="width: 6%;">Qty</th>
              <th style="width: 13%;">kemasan</th>
              <th class="text-right" style="width: 10%;">Harga</th>
              <th class="text-right" style="width: 10%;">Disc</th>
              <th class="text-right" style="width: 10%;">Netto</th>
              <th class="text-right" style="width: 15%;">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            @foreach($doDetails->slice($page * $limit, $limit) as $index => $row)
              <?php
                $harga = ceil($result->do->idr_rate * $row->price);
                $disc_cash = ceil($result->do->idr_rate * ($row->total_disc/$row->qty));
                $neto = $harga - $disc_cash;
                $sub_total = ceil($neto * $row->qty);
                $idr_total += $sub_total; 
              ?>
              <tr>
                <td class="text-center">{{$index + 1}}</td>
                <td class="text-center">{{$row->product_pack->code ?? ''}}</td>
                <td class="text-center">{{$row->product_pack->name ?? ''}}</td>
                <td>{{number_format($row->price,2,',','.')}}</td>
                <td class="text-center">{{$row->qty}}</td>
                <td class="text-center">{{$row->packaging->pack_name ?? ''}}</td>
                <td class="text-right">
                  {{number_format($harga,0,',','.')}}
                </td>
                <td class="text-right">
                  {{number_format($disc_cash,0,',','.')}}
                </td>
                <td class="text-right">
                  {{number_format($neto,0,',','.')}}
                </td>
                <td class="text-right">
                  {{number_format($sub_total,0,',','.')}}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
    </div>

    @if ($page < $totalPages - 1)
        <div class="page-break"></div> <!-- Page break here -->
    @endif
@endfor

<div>
<div style="font-size: 12px; position: absolute; bottom: 0px; width: 100%;">
      <div class="row-float" style="display: flex; justify-content: space-between;">
        
        <!-- Left Column -->
        <div class="column-float" style="width: 70%">
          Terbilang: {{ $result->do->do_detail_cost[0]->terbilang }}<br><br>
          <b>*Kurs USD: {{ number_format($result->do->idr_rate, 0, ',', '.') }}</b>
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
                <td style="width: 60%; text-align: right;">Disc Persen ({{ $result->do->do_detail_cost[0]->discount_1 }})</td>
                <td style="width: 40%; text-align: left;">: {{ number_format($result->do->do_detail_cost[0]->discount_1_idr ?? 0, 0, ',', '.') }}</td>
              </tr>
              <tr>
                <td style="width: 60%; text-align: right;">Disc Kemasan ({{ $result->do->do_detail_cost[0]->discount_2 }})</td>
                <td style="width: 40%; text-align: left; border-bottom: 1px solid black;">: {{ $result->do->do_detail_cost[0]->discount_2_idr }}</td>
              </tr>
              @if($result->do->do_detail_cost[0]->discount_idr != null && $result->do->do_detail_cost[0]->discount_idr > 0)
              <tr>
                <td style="width: 60%; text-align: right;">Disc IDR</td>
                <td style="width: 40%; text-align: left;">: {{ number_format($result->do->do_detail_cost[0]->discount_idr ?? 0, 0, ',', '.') }}</td>
              </tr>
              @endif
              @if($result->do->do_detail_cost[0]->voucher_idr != null && $result->do->do_detail_cost[0]->voucher_idr > 0)
              <tr>
                <td style="width: 60%; text-align: right;">Cashback/Voucher</td>
                <td style="width: 40%; text-align: left;">: {{ number_format($result->do->do_detail_cost[0]->voucher_idr ?? 0, 0, ',', '.') }}</td>
              </tr>
              @endif
              @if($result->do->do_detail_cost[0]->purchase_total_idr != null && $result->do->do_detail_cost[0]->purchase_total_idr > 0)
              <tr>
                <td style="width: 60%; text-align: right;">Total</td>
                <td style="width: 40%; text-align: left;">: {{ number_format($result->do->do_detail_cost[0]->purchase_total_idr ?? 0, 0, ',', '.') }}</td>
              </tr>
              @endif
              <tr>
                <td style="width: 60%; text-align: right;">Ongkos Kirim</td>
                <td style="width: 40%; text-align: left; border-bottom: 1px solid black;">: {{ number_format($result->do->do_detail_cost[0]->delivery_cost_idr ?? 0, 0, ',', '.') }}</td>
              </tr>
              <tr>
                <td style="width: 60%; text-align: right;"><strong>Grand Total</strong></td>
                <td style="width: 40%; text-align: left;"><strong>: {{ number_format($result->do->do_detail_cost[0]->grand_total_idr ?? 0, 0, ',', '.') }}</strong></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="row-float" style="display: flex; justify-content: space-between; align-items: flex-start;">
        
        <!-- Payment Notes Column -->
        <div class="column-float" style="width: 60%; font-size: 9px; font-weight: bold;">
          - Pembayaran Cheque/Wesel/BG dianggap sah bila telah diuangkan <br>
          - Pembayaran TUNAI wajib disertai Tanda Terima Tunai resmi dari kantor <br>
          - Pembayaran diluar ketentuan diatas tidak diakui <br>
          - Barang yang sudah dibeli tidak dapat ditukar/dikembalikan
        </div>
        
        <!-- Bank Logo Column -->
        <div class="column-float" style="width: 20%; text-align: center;">
          <div style="height: 100px;">
            @if($result->do->so->rekening == 3)
              <img src="<?= base_path('public/cr/invoice/3.png') ?>" style="width: 85%; height: 60%;">
            @else
              <img src="<?= base_path('public/cr/invoice/4.png') ?>" style="width: 85%; height: 60%;">
            @endif
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
