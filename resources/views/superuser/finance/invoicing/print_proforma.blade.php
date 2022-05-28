<?php
    $sales = null;
    foreach ($result->do->do_detail as $key => $row) {
      $sales = $row->so_item->so->sales->name ?? null;
    }

    $idr_total = 0;
    $code = $result->do->do_code ?? null;
?>
<!DOCTYPE html>
<html>
<head>
  <title>{{$code}}</title>
  <style type="text/css">
    body{
      font-size: 12px;
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
      border:1px solid grey;
      word-wrap: break-word;
      box-sizing:
      border-box;
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
  </style>
</head>
<body>
    <div class="row-float" style="margin-bottom: 20px !important;">
      <div class="column-float note" style="width: 45%;">
          <div style="height: 100px;">
            <img src="<?= base_path('public/superuser_assets/media/master/company/'.$company->logo) ?>" style="width: 100%;height: 100%;">
          </div>
      </div>
      <div class="column-float" style="width: 55%;margin-top: 15px;">
        <h2 style="text-align: center;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;">PROFORMA INVOICE</h2>
        <h3 style="text-align: center;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;">{{date('d-m-Y',strtotime($result->do->created_at))}}</h3>
        <div style="font-size: 15px;">
          <div style="float: left;width: 50%;display: block;text-align: center;">
            Sales : {{$sales}}
          </div>
          <div style="float: right;width: 50%;display: block;text-align: center;">
            Order : {{$code}}
          </div>
          <div style="clear: both;"></div>
        </div>
      </div>
    </div>

    <div style="margin-bottom: 15px !important;font-size: 13px;">
      <div class="row-float">
        <div class="column-float" style="width: 50%;">
          <table class="table borderless info" style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 35% !important;"><strong>Customer</strong></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->do->customer->name ?? ''}}</td>
              </tr>
              <tr>
                <td style="width: 35% !important;"><strong>Up</strong></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->do->customer->owner_name ?? ''}}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="column-float" style="width: 50%;">
          <table class="table borderless info" style="width: 100%">
            <tbody>
              <tr>
                <td style="width: 35% !important;"><strong>Telepon</strong></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->do->customer->phone ?? ''}}</td>
              </tr>
              <tr>
                <td style="width: 35% !important;"><strong>NIK/NPWP</strong></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->do->customer->npwp ?? ''}}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <table class="table borderless info" style="width: 100%;">
          <tbody>
            <tr>
              <td style="width: 17.5% !important;"><strong>Alamat</strong></td>
              <td style="width: 2% !important;">:</td>
              <td style="width: 81.5% !important;">{{$result->do->customer->address ?? ''}}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="row-float">
        <div class="column-float" style="width: 50%;">
          <table class="table borderless info" style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 35% !important;"><strong>Kirim</strong></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->do->ekspedisi->name ?? ''}}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="column-float" style="width: 50%;">
          <table class="table borderless info" style="width: 100%">
            <tbody>
              <tr>
                <td style="width: 35% !important;"><strong>Telepon</strong></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->do->customer_other_address->phone ?? ''}}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <table class="table borderless info" style="width: 100%;">
          <tbody>
            <tr>
              <td style="width: 17.5% !important;"><strong>Alamat Kirim</strong></td>
              <td style="width: 2% !important;">:</td>
              <td style="width: 81.5% !important;">{{$result->do->customer_other_address->address ?? ''}}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div style="margin-bottom: 15px !important;">
      <table class="table-data" style="width: 100%;">
        <thead>
          <tr>
            <th style="width: 22%;">Product</th>
            <th style="width: 7%;">Acuan</th>
            <th style="width: 6%;">Qty</th>
            <th style="width: 13%;">Packaging</th>
            <th style="width: 13%;">Harga</th>
            <th style="width: 10%;">Disc Cash</th>
            <th style="width: 15%;">Netto</th>
            <th style="width: 14%;">Sub Total</th>
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
              <td>{{$row->product->code ?? ''}} - {{$row->product->name ?? ''}}</td>
              <td>{{$row->price}}</td>
              <td>{{$row->qty}}</td>
              <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
              <td>
                {{number_format($harga,0,',','.')}}
              </td>
              <td>
                {{number_format($disc_cash,0,',','.')}}
              </td>
              <td>
                {{number_format($neto,0,',','.')}}
              </td>
              <td>
                {{number_format($sub_total,0,',','.')}}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if($result->do->type_transaction == 1 || $result->do->type_transaction == 3)
    <div class="row-float" style="font-size: 12px;">
      <div class="column-float note" style="width: 65%;">
        @if(!empty($result->do->note ?? ''))
        <div class="p-note">
          <?= htmlspecialchars_decode($result->do->note ?? ''); ?>
        </div>
        @else
        <p style="width: 100%;border:1px solid grey;height: 100px;">
        </p>
        @endif

      </div>
      <div class="column-float" style="width: 35%;">
        <div class="row">
          <table style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 60%;text-align: right;">IDR Total</td>
                <td style="width: 40%;text-align: left;">: {{number_format($idr_total,0,',','.')}}</td>
              </tr>
              @if($result->do->do_cost->discount_1 > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Discount 1 (%)</td>
                <td style="width: 40%;text-align: left;">: {{$result->do->do_cost->discount_1}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->discount_2 > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Discount 2 (%)</td>
                <td style="width: 40%;text-align: left;">: {{$result->do->do_cost->discount_2}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->discount_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Discount (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->discount_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->total_discount_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Total Discount (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->total_discount_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->ppn > 0)
              <tr>
                <td style="width: 60%;text-align: right;">PPN</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->ppn ?? 0 ,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->voucher_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Voucher (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->voucher_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->cashback_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Cashback (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->cashback_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->purchase_total_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Purchase Total (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->purchase_total_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->delivery_cost_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Delivery Cost (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->delivery_cost_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              @if($result->do->do_cost->other_cost_idr > 0)
              <tr>
                <td style="width: 60%;text-align: right;">Other Cost (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->other_cost_idr ?? 0,0,',','.')}}</td>
              </tr>
              @endif
              <tr>
                <td style="width: 60%;text-align: right;">Grand Total (IDR)</td>
                <td style="width: 40%;text-align: left;">: {{number_format($result->do->do_cost->grand_total_idr ?? 0,0,',','.')}}</td>
              </tr>
            </tbody>
          </table>
        </div>
        
      </div>
    </div>
    @else
    <div class="row-float" style="font-size: 12px;">
      <div class="column-float" style="width: 65%">
        @if(!empty($result->do->note ?? ''))
        <div class="p-note">
          <?= htmlspecialchars_decode($result->do->note ?? ''); ?>
        </div>
        @else
        <p style="width: 100%;border:1px solid grey;height: 100px;margin-top: 0;padding-top: 0;">
        </p>
        @endif
      </div>
    </div>
    @endif

    <div id="footer">
      <div class="page-number"></div>
    </div>

</body>
</html>