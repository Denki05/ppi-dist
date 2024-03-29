<?php
    $sales = null;
    foreach ($result->do->do_detail as $key => $row) {
      $sales = $row->so_item->so->sales->name ?? null;
    }
    $idr_total = 0; 
    $code = $result->code;
?>
<!DOCTYPE html>
<html>
<head>
  <title>{{$code}}</title>
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
    table.table-data, .table-data td, .table-data th {
      border: 1px solid black;
    }
    table.table-data th{
      font-size: 12px;
    }

    table.table-data {
      width: 100%;
      border-collapse: collapse;
      color: #333;
    }
    table.table-data tbody{
      text-align: center;
      font-size: 12px;
    }

    @page{
      margin-top: 15px;
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
      page-break-inside:avoid; page-break-after:always;
    }
  </style>
</head>
<body>
    <div class="header {{ $watermark == 'Paid' ? 'paid' : '' }}">
      {{$watermark}}
    </div>
    
    <div class="row-float" style="margin-bottom: 20px !important;">
      <div class="column-float note" style="width: 45%;">
          <div style="height: 100px;">
            <img src="<?= base_path('public/superuser_assets/media/master/company/'.$company->logo) ?>" style="width: 100%;height: 100%;">
          </div>
      </div>
      <div class="column-float note" style="width: 20%;">
      </div>
      <div class="column-float" style="width: 35%;margin-top: 10px;">
        <h3 style="text-align: center;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;">INVOICE - No. {{$code}}</h2>
        <table class="table borderless info" style="width: 100%">
          <tbody>
            <tr>
              <td style="width: 35% !important;">Tanggal</td>
              <td style="width: 2% !important;">:</td>
              <td style="width: 63% !important;">{{date('d-m-Y',strtotime($result->do->created_at))}}</td>
            </tr>
            <tr>
              <td style="width: 35% !important;">Order</td>
              <td style="width: 2% !important;">:</td>
              <td style="width: 63% !important;">{{$result->do->do_code ?? null}}</td>
            </tr>
            <tr>
              <td style="width: 35% !important;">Sales</td>
              <td style="width: 2% !important;">:</td>
              <td style="width: 63% !important;">{{$sales}}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div style="margin-bottom: 15px !important;font-size: 13px;">
      <div class="row-float">
        <div class="column-float" style="width: 50%;">
          <table class="table borderless info" style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 35% !important;">Pelanggan</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">
                  @if($result->do->other_address == 0)
                    {{$result->do->customer->name ?? ''}}
                  @elseif($result->do->other_address == 1)
                    {{$result->do->member->name ?? ''}}
                  @endif
                </td>
              </tr>
              <tr>
                <td style="width: 35% !important;">Up</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">
                  @if($result->do->other_address == 0)
                    {{$result->do->customer->owner_name ?? ''}}
                  @elseif($result->do->other_address == 1)
                    {{$result->do->member->contact_person ?? ''}}
                  @endif
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="column-float" style="width: 50%;">
          <table class="table borderless info" style="width: 100%">
            <tbody>
              <tr>
                <td style="width: 35% !important;">Telepon</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">
                  @if($result->do->other_address == 0)
                    {{$result->do->customer->phone ?? ''}}
                  @elseif($result->do->other_address == 1)
                    {{$result->do->member->phone ?? ''}}
                  @endif
                </td>
              </tr>
              <tr>
                <td style="width: 35% !important;">Jenis</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->do->do_type_transaction()->scalar ?? ''}}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="row-float">
        <div class="column-float" style="width: 50%;">
          <table class="table borderless info" style="width: 100%;">
            <tbody>
                <tr>
                <td style="width: 35% !important;">NIK/NPWP</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->do->customer->npwp ?? ''}}</td>
              </tr>
              <tr>
                <td style="width: 35% !important;">Alamat</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->do->customer->address ?? ''}}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="column-float" style="width: 50%;">
          <table class="table borderless info" style="width: 100%">
            <tbody>
              <tr>
                <td style="width: 35% !important;">Alamat Kirim</td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">
                  @if($result->do->other_address == 0)
                    {{$result->do->customer->address ?? ''}}
                  @elseif($result->do->other_address == 1)
                    {{$result->do->member->address ?? ''}}
                  @endif
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="row-float">
        <div class="column-float" style="width: 100%;">
          <table class="table borderless info" style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 17.5% !important;">Note</td>
                <td style="width: 1% !important;">:</td>
                <td style="width: 81.5% !important;">{{$result->do->so->note ?? ''}}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div style="margin-bottom: 15px !important;">
      <table class="table-data" style="width: 100%;">
        <thead>
          <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 10%;">KODE</th>
            <th style="width: 15%;">DESKRIPSI</th>
            <th style="width: 7%;">ACUAN (USD)</th>
            <th style="width: 6%;">JUMLAH (Kg)</th>
            <th style="width: 13%;">KEMASAN</th>
            <th style="width: 10%;">HARGA</th>
            <th style="width: 10%;">DISKON (CASH)</th>
            <th style="width: 10%;">NETTO</th>
            <th style="width: 15%;">JUMLAH</th>
          </tr>
        </thead>
        <tbody>
          @foreach($result->do->do_detail as $index => $row)
            <?php
              $harga = ceil($result->do->idr_rate * $row->price);
              $disc_cash = ceil($result->do->idr_rate * ($row->total_disc/$row->qty));
              $neto = $harga - $disc_cash;
              $sub_total = ceil($neto * $row->qty);
              $idr_total += $sub_total; 
            ?>
            <tr>
              <td class="text-right">{{$index + 1}}</td>
              <td class="text-left">{{$row->product->code ?? ''}}</td>
              <td class="text-left">{{$row->product->name ?? ''}}</td>
              <td>{{number_format($row->price,2,',','.')}}</td>
              <td>{{$row->qty}}</td>
              <td class="text-left">{{$row->packaging_txt()->scalar ?? ''}}</td>
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

    <div class="row-float" style="font-size: 12px;">
      <div class="column-float note" style="width: 65%;">
        Terbilang : {{\CustomHelper::terbilang($result->do->do_cost->grand_total_idr)}}<br><br>
        *Kurs USD : {{number_format($result->do->idr_rate,0,',','.')}}
        
        <div class="row-float">
          <div class="column-float note" style="width: 65%">
            @if(!empty($result->do->note ?? ''))
            <div class="p-note">
              <?= htmlspecialchars_decode($result->do->note ?? ''); ?>
            </div>
            @else
            <p style="width: 100%;border:1px solid grey;height: 100px;">
            </p>
            @endif
          </div>
          <div style="width: 25%; float: right;">
            Hormat Kami,
            <br>
            <br>
            <br>
            <br>
            (....................)
          </div>
        </div>
      </div>

      <div class="column-float" style="width: 35%;">
        <div class="row">
          <table style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 60%;text-align: right;">IDR Total</td>
                <td style="width: 40%;text-align: left;">: {{number_format($idr_total,0,',','.')}}</td>
              </tr>
              @if($result->do->do_cost->discount_1 != null && $result->do->do_cost->discount_1 > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Discount 1 (%)</td>
                <td style="width: 40%;text-align: left;">: {{$result->do->do_cost->discount_1}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->discount_2 != null && $result->do->do_cost->discount_2 > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Discount 2 (%)</td>
                <td style="width: 40%;text-align: left;">: {{$result->do->do_cost->discount_2}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->discount_idr != null && $result->do->do_cost->discount_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Discount (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->discount_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->total_discount_idr != null && $result->do->do_cost->total_discount_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Total Discount (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->total_discount_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->ppn != null && $result->do->do_cost->ppn > 0)
              <tr>
                <td style="width: 60%;text-align: right;">PPN</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->ppn ?? 0 ,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->voucher_idr != null && $result->do->do_cost->voucher_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Voucher (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->voucher_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->cashback_idr != null && $result->do->do_cost->cashback_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Cashback (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->cashback_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->purchase_total_idr != null && $result->do->do_cost->purchase_total_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Purchase Total (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->purchase_total_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->delivery_cost_idr != null && $result->do->do_cost->delivery_cost_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Biaya Kirim (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->delivery_cost_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->other_cost_idr != null && $result->do->do_cost->other_cost_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Biaya Lain-lain (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->other_cost_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              <tr>
                <td style="width: 60%;text-align: right;"><strong>Grand Total (IDR)</strong></td>
                <td style="width: 40%;text-align: left;"><strong>: {{number_format($result->do->do_cost->grand_total_idr ?? 0,0,',','.')}}</strong></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="footer">
      <div class="page-number"></div>
    </div>
</body>
</html>