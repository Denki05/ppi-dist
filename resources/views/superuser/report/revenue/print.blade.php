<!DOCTYPE html>
<html>
<head>
  <title>Revenue Report</title>
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
      content: "Revenue Report | Page " counter(page);
    }

    .text-right{
      text-align: right;
    }

    .badge{
      width: 100px;
      height: 100%;
      border-radius: 10px;
    }
    .badge-warning{
      width: 100px;
      height: 100%;
      background: yellow;
      padding: 2px 3px;
    }
    .badge-danger{
      width: 100px;
      height: 100%;
      background: rgba(255, 0, 0, 0.7);;
      padding: 2px 3px;
    }
    .badge-warning{
      width: 100px;
      height: 100%;
      background: rgba(0, 255, 0, 1);
      padding: 2px 3px;
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
    <div class="column-float" style="width: 55%;margin-top: 10px;font-size: 15px;">
      <h3 style="text-align: right;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;">Revenue Report</h3>
      <h5 style="text-align: right;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;"><?= date('d-m-Y'); ?></h5>
    </div>
  </div>

  <div class="row-float" style="font-size: 13px;">
    <div class="column-float">
      @if(!empty(request()->get('period_from')))
      Period From <?= date('d F Y',strtotime(request()->get('period_from'))) ?>
      @endif
      @if(!empty(request()->get('period_from')) && !empty(request()->get('period_to')))
      -
      @endif
      @if(!empty(request()->get('period_to')))
      <?= date('d F Y',strtotime(request()->get('period_to'))) ?>
      @endif
      @if(!empty(request()->get('period_from')) || !empty(request()->get('period_to')))
      <br>
      @endif
      @if(!empty(request()->get('sales_senior_id')))
      Sales Senior : {{$sales_senior_filter->name}}<br>
      @else
      Sales Senior : All Sales Senior<br>
      @endif
  
    </div>
    <div class="column-float text-right">
      @if(!empty(request()->get('customer_id')))
        @if(!empty($customer_filter))
            Customer : {{$customer_filter->name}}<br>
        @endif
      @else
        Customer : All Customer<br>
      @endif
      @if(!empty(request()->get('sales_id')))
      Sales : {{$sales_filter->name}}<br>
      @else
      Sales : All Sales
      @endif
    </div>
  </div>

  <div>
    <table style="width: 100%;" class="table-data">
      <thead>
        <tr class="text-center">
          <th style="width: 5%;">#</th>
          <th style="width: 10%;">Date</th>
          <th style="width: 15%;">DO Number</th>
          <th style="width: 15%;">Invoice Number</th>
          <th style="width: 15%;">Total</th>
          <th style="width: 15%;">Payment</th>
          <th style="width: 15%;">Due Date</th>
          <th style="width: 10%;">Is Due Date</th>
        </tr>
      </thead>
      <tbody class="text-center">
        <?php 
          $total_invoice = 0;
          $total_paid = 0;

          $due_total_invoice = 0;
          $due_total_paid = 0;
          $no = 0;
        ?>
        @if(count($invoice) == 0)
        <tr>
          <td colspan="8">Data tidak ditemukan</td>
        </tr>
        @endif
        @foreach($invoice as $index => $row)
        <tr>
          <td>{{$no+1}}</td>
          <td>
            <?= date('d-m-Y',strtotime($row['invoice']['created_at'])); ?>
          </td>
          <td>{{$row['do']['do_code']}}</td>
          <td>{{$row['invoice']['code']}}</td>
          <td>{{number_format($row['invoice']['grand_total_idr'],0,',','.')}}</td>
          <td>
            <?php
              $payable = 0;
              $no++;
            ?>
            @foreach($row['payable'] as $payment)
              <?php
                $payable += $payment["total"];
              ?>
            @endforeach
            {{number_format($payable,0,',','.')}}
          </td>
          <td>
            <?php
              $due_date = date('Y-m-d',strtotime($row['invoice']['created_at']."+ 30 days"));
              $due_date_60 = date('Y-m-d',strtotime($row['invoice']['created_at']."+ 60 days"));

              if($due_date <= date('Y-m-d') && $row['invoice']['grand_total_idr'] > $payable){
                $due_payment = $row["invoice"]["grand_total_idr"] - $payable;
                if($due_payment < 0){
                  $due_payment = 0;
                }
                $due_total_invoice += $due_payment;
                foreach ($row["payable"] as $key => $value) {
                  $due_total_paid += $value["total"];
                }
              }
            ?>
            <?= date('d-m-Y',strtotime($due_date)); ?>
          </td>
          <td>
            @if($due_date <= date('Y-m-d') && $row['invoice']['grand_total_idr'] > $payable)
              <span class="badge badge-warning badge-xs">H+30</span>
            @elseif($due_date_60 <= date('Y-m-d') && $row['invoice']['grand_total_idr'] > $payable)
              <span class="badge badge-danger badge-xs">H+60</span>
            @endif
            @if($row['invoice']['grand_total_idr'] <= $payable)
              <span class="badge badge-success badge-xs">Paid Off</span>
            @endif
          </td>
          <?php
            $total_invoice += $row['invoice']['grand_total_idr'];
            foreach ($row["payable"] as $key => $value) {
              $total_paid += $value["total"];
            }
            
          ?>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div style="margin-bottom: 15px;">
    <table style="width: 100%;">
      <tbody>
        <tr>
          <td style="width: 20%;"><b>Total Due Date Revenue</b></td>
          <td style="width: 80%;">: {{number_format($due_total_invoice,0,',','.')}}</td>
        </tr>
        <tr>
          <td style="width: 20%;"><b>Total Invoice</b></td>
          <td style="width: 80%;">: {{number_format($total_invoice,0,',','.')}}</td>
        </tr>
        <tr>
          <td style="width: 20%;"><b>Total Payment</b></td>
          <td style="width: 80%;">: {{number_format($total_paid,0,',','.')}}</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div id="footer">
    <div class="row-float">
      <div class="column-float">
        <div class="page-number"></div>
      </div>
      <div class="column-float text-right">
        @if(!empty(request()->get('period_from')))
        Period From <?= date('d F Y',strtotime(request()->get('period_from'))) ?>
        @endif
        @if(!empty(request()->get('period_from')) && !empty(request()->get('period_to')))
        -
        @endif
        @if(!empty(request()->get('period_to')))
        <?= date('d F Y',strtotime(request()->get('period_to'))) ?>
        @endif
      </div>
    </div>
  </div>

</body>
</html>