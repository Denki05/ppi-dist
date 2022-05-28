<?php
    $code = explode("SM", $result->code ?? '');
    $date = date('my',strtotime($result->created_at)); 
    $code = 'SM-'.$date.'-'.$code[1];

?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>{{$result->code}}</title>
    <style type="text/css">
      body{
        font-size: 12px;
        color: #333;
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

      @page{
        margin-top: 15px;
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
        content: '<?= $code; ?>' "| Page " counter(page);
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
        <h3 style="text-align: right;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;">Canvasing</h3>
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
            <td style="width: 20%;"><b>Warehouse</b></td>
            <td style="width: 80%;">: {{$result->warehouse->name ?? ''}}</td>
          </tr>
          <tr>
            <td style="width: 20%;"><b>Sales</b></td>
            <td style="width: 80%;">: {{$result->sales->name ?? ''}}</td>
          </tr>
          <tr>
            <td style="width: 20%;"><b>Address</b></td>
            <td style="width: 80%;">: {{$result->address ?? ''}}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div>
      <table style="width: 100%;" class="table-data">
        <thead>
          <tr>
            <th style="padding: 6px 2px !important;">No</th>
            <th style="padding: 6px 2px !important;">Code</th>
            <th style="padding: 6px 2px !important;">Product</th>
            <th style="padding: 6px 2px !important;">Qty</th>
          </tr>
        </thead>
        <tbody>
          @if(count($result->canvasing_item) == 0)
            <tr>
              <td colspan="4">Data tidak ditemukan</td>
            </tr>
          @endif
          @foreach($result->canvasing_item as $index => $row)
            <tr>
              <td style="padding: 5px 2px;">{{$index+1}}</td>
              <td style="padding: 5px 2px;">{{$row->product->code ?? ''}}</td>
              <td style="padding: 5px 2px;">{{$row->product->name ?? ''}}</td>
              <td style="padding: 5px 2px;">{{$row->qty ?? ''}}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div id="footer">
      <div class="page-number"></div>
    </div>

  </body>
</html>