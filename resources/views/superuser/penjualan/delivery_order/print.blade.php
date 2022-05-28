<?php
    $code = $result->do_code;
    $sales = null;
    foreach ($result->do_detail as $key => $row) {
      $sales = $row->so_item->so->sales->name ?? null;
    }
?>
<!DOCTYPE html>
<html>
<head>
  <title>Delivery Order</title>
  <style type="text/css">
    body{
      color: #333;
      font-family: Arial,sans-serif;
      font-size: 15px;
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
      border: 1px solid #333;
    }
    table.table-data th{
      font-size: 15px;
    }

    table.table-data {
      width: 100%;
      border-collapse: collapse;
      color: #333;
    }
    table.table-data tbody{
      text-align: center;
      font-size: 15px;
    }

    @page{
      margin-top: 15px;
    }

    /*Footer*/
    #footer {
      position: relative;
      left: 0;
      right: 0;
      color: #aaa;
      font-size: 11px;
      margin-top: 15px !important;
    }

    #footer-fixed {
      position: fixed;
      left: 0;
      right: 0;
      color: #aaa;
      font-size: 11px;
      bottom: 0;
    }
    
    .page-number:before {
      content: '<?= $code; ?>' " | Page " counter(page);
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
      <div class="column-float" style="width: 55%;margin-top: 10px;">
        <h3 style="text-align: right;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;">Delivery Order</h3>
        <h5 style="text-align: right;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;">{{$result->do_code}}</h5>
      </div>
    </div>

    <div class="row-float" style="margin-bottom: 15px !important;">
      <div class="column-float" style="width: 50%;">
        <table class="table borderless info" style="width: 100%;">
          <tbody>
            <tr>
              <td style="width: 35% !important;"><strong>Customer</strong></td>
              <td style="width: 2% !important;">:</td>
              <td style="width: 63% !important;">
                <div style="word-wrap: break-word;">{{$result->customer->name ?? ''}}</div>
              </td>
            </tr>
            <tr>
              <td style="width: 35% !important;"><strong>Address</strong></td>
              <td style="width: 2% !important;">:</td>
              <td style="width: 63% !important;">
                <div style="word-wrap: break-word;">{{$result->customer->address ?? ''}}</div>
              </td>
            </tr>
            <tr>
              <td style="width: 35% !important;"><strong>Phone</strong></td>
              <td style="width: 2% !important;">:</td>
              <td style="width: 63% !important;">
                <div style="word-wrap: break-word;">{{$result->customer->phone ?? ''}}</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="column-float" style="width: 50%;">
        <table class="table borderless info" style="width: 100%">
          <tbody>
            <tr>
              <td style="width: 35% !important;"><strong>Date</strong></td>
              <td style="width: 2% !important;">:</td>
              <td style="width: 63% !important;">
                <div style="word-wrap: break-word;"><?= date('d-m-Y',strtotime($result->created_at)); ?></div>
              </td>
            </tr>
            <tr>
              <td style="width: 35% !important;"><strong>Delivery</strong></td>
              <td style="width: 2% !important;">:</td>
              <td style="width: 63% !important;">
                <div style="word-wrap: break-word;">{{$result->customer_other_address->label ?? ''}}</div>
              </td>
            </tr>
            <tr>
              <td style="width: 35% !important;"><strong>Address</strong></td>
              <td style="width: 2% !important;">:</td>
              <td style="width: 63% !important;">
                <div style="word-wrap: break-word;">{{$result->customer_other_address->address ?? ''}}</div>
              </td>
            </tr>
            <tr>
              <td style="width: 35% !important;"><strong>Phone</strong></td>
              <td style="width: 2% !important;">:</td>
              <td style="width: 63% !important;">
                <div style="word-wrap: break-word;">{{$result->customer_other_address->phone ?? ''}}</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="row" style="margin-bottom: 15px;">
      <table style="width: 100%;" class="table-data">
        <thead>
          <tr class="text-center">
            <th style="width: 15%;">Code</th>
            <th style="width: 25%;">Product</th>
            <th style="width: 10%;">Qty</th>
            <th style="width: 10%;">Unit</th>
            <th style="width: 25%;">Packing</th>
            <th style="width: 15%;">Total Packing</th>
          </tr>
        </thead>
        <tbody>
          @foreach($result->do_detail as $index => $row)
            <tr>
              <td>{{$row->product->code ?? ''}}</td>
              <td>{{$row->product->name ?? ''}}</td>
              <td>{{$row->qty}}</td>
              <td>Kg</td>
              <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
              <td>
                @if($row->packaging == 7)
                Free
                @else
                <?php
                  $total_packing = $row->qty / floatval($row->packaging_val()->scalar ?? 0);
                ?>
                {{$total_packing}}
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="row">
      <div style="float: left;width: 25%;text-align: center;">
        Admin
        <br>
        <br>
        <br>
        (....................)
      </div>
      <div style="float: left;width: 25%;text-align: center;">
        Gudang
        <br>
        <br>
        <br>
        (....................)
      </div>
      <div style="float: left;width: 25%;text-align: center;">
        Pengirim
        <br>
        <br>
        <br>
        (....................)
      </div>
      <div style="float: left;width: 25%;text-align: center;">
        Penerima
        <br>
        <br>
        <br>
        (....................)
      </div>
      <div style="clear: both;"></div>
    </div>

    <div id="footer">
      <div class="page-number"></div>
    </div>

    <div id="footer-fixed">
      <div class="page-number"></div>
    </div>

</body>
</html>