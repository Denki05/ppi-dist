<?php
    $code = explode("PY", $result->code ?? null);
    $date = date('my',strtotime($result->created_at));
    $code = 'PY-'.$date.'-'.$code[1];
?>
<!DOCTYPE html>
<html>
<head>
  <title>{{$code}}</title>
  <style type="text/css">
    body{
      color: #333;
      font-size: 12px;
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

    .header {
        width: 100%;
        position: fixed;
        z-index: 99999;
        letter-spacing: 5px;
        font-size: 150px;
        font-weight: 800;
        opacity: 0.3;
        color: #404040;
        text-transform: uppercase;
        top: 30%;
        left: 45%;
        transform:  translateX(-50%) translateY(-75%) rotate(-20deg);
    }

    @page{
      margin-top: 15px;
    }

    /*Footer*/
    #footer {
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

    .text-right{
      text-align: right;
    }
  </style>
</head>
<body>
  <div class="header">
      Paid
  </div>

  <div class="row-float" style="margin-bottom: 20px !important;">
    <div class="column-float note" style="width: 45%;">
        <div style="height: 100px;">
          <img src="<?= base_path('public/superuser_assets/media/master/company/'.$company->logo) ?>" style="width: 100%;height: 100%;">
        </div>
    </div>
    <div class="column-float" style="width: 55%;margin-top: 10px;font-size: 15px;">
      <h3 style="text-align: right;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;">Payment Receipt</h3>
      <h5 style="text-align: right;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;">{{$code}}</h5>
    </div>
  </div>

  <div style="margin-bottom: 15px;font-size: 13px;">
    <table style="width: 100%;">
      <tbody>
        <tr>
          <td style="width: 20%;"><b>Date</b></td>
          <td style="width: 80%;">: <?= date('d-m-Y',strtotime($result->created_at)); ?></td>
        </tr>
        <tr>
          <td style="width: 20%;"><b>Customer</b></td>
          <td style="width: 80%;">: {{$result->customer->name ?? ''}}</td>
        </tr>
        <tr>
          <td style="width: 20%;"><b>Address</b></td>
          <td style="width: 80%;">: {{$result->customer->address ?? ''}}</td>
        </tr>
        <tr>
          <td style="width: 20%;"><b>Phone</b></td>
          <td style="width: 80%;">: {{$result->customer->phone ?? ''}}</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div>
    <table style="width: 100%;" class="table-data">
      <thead>
        <tr>
          <th style="width: 20%;">Invoice</th>
          <th style="width: 20%;">IDR Rate</th>
          <th style="width: 20%;">Total</th>
          <th style="width: 20%;">Paid</th>
          <th style="width: 20%;">Unpaid</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $total_paid = 0;
          $total_unpaid = 0;
        ?>
        @foreach($result->payable_detail as $index => $row)
          <?php
            $total_invoice = $row->invoice->grand_total_idr ?? 0;
            $payable = floatval($row->where('invoice_id',$row->invoice_id)->sum('total'));
            $account_receivable = $total_invoice - $payable;

            $unpaid = ceil($row->invoice->grand_total_idr - $payable);
            $total_paid += $row->total;
            $total_unpaid += $unpaid;
          ?>
          <tr>
            <td>{{$row->invoice->code ?? ''}}</td>
            <td>{{number_format($row->invoice->do->idr_rate ?? 0,0,',','.') ?? ''}}</td>
            <td>{{number_format($row->invoice->grand_total_idr ?? 0,0,',','.') ?? ''}}</td>
            <td>{{number_format($row->total,0,',','.')}}</td>
            <td>{{number_format($unpaid,0,',','.')}}</td>
          </tr>
        @endforeach
        <tr>
          <td colspan="3" class="text-right">Total</td>
          <td>{{number_format($total_paid,0,',','.')}}</td>
          <td>{{number_format($total_unpaid,0,',','.')}}</td>
        </tr>
      </tbody>
    </table>
  </div>

</body>
</html>