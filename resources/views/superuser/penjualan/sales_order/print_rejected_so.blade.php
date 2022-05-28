<!DOCTYPE html>
<html>
<head>
  <title>{{$result->code}}</title>
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
      content: '<?= $result->so->code ?? null; ?>' " | Page " counter(page);
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
        <h2 style="text-align: center;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;">Rejected Item</h2>
        <h3 style="text-align: center;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;">{{date('d-m-Y')}}</h3>
      </div>
    </div>

    <div style="margin-bottom: 15px !important;font-size: 13px;">
      <div class="row-float">
        <div class="column-float" style="width: 50%;">
          <table class="table borderless info" style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 35% !important;"><strong>Referensi</strong></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->code ?? ''}}</td>
              </tr>
              <tr>
                <td style="width: 35% !important;"><strong>Customer</strong></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->customer->name ?? ''}}</td>
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
            <th style="width: 6%;">No</th>
            <th style="width: 22%;">Nama</th>
            <th style="width: 22%;">Kategori</th>
            <th style="width: 7%;">Qty</th>
          </tr>
        </thead>
        <tbody>
          <?php 
            $no = 0;
            foreach($result->so_detail as $index => $row) {
              if ($row->qty == 0) continue; 

              echo "<tr>";
              echo "  <td>" . ($no + 1) . "</td>";
              echo "  <td>" . ($row->product->code ?? '') . " - " . ($row->product->name ?? '') . "</td>";
              echo "  <td>" . ($row->product->category->name ?? '') . "</td>";
              echo "  <td>" . ($row->qty - $row->qty_worked) . " kg</td>";
              echo "</tr>";
              $no++;
            }
          ?>
        </tbody>
      </table>
    </div>

    <div style="margin-bottom: 15px !important;font-size: 13px;">
      <div class="row-float">
        <div class="column-float" style="width: 80%;">
          <table class="table borderless info" style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 35% !important;"><strong>Diciptakan Tanggal</strong></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{date('d-m-Y',strtotime($result->created_at))}}</td>
              </tr>
              <tr>
                <td style="width: 35% !important;"><strong>Oleh</strong></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->user_update->username ?? ''}}</td>
              </tr>
              <tr>
                <td style="width: 35% !important;"><strong>Sales Senior</strong></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->sales_senior->name ?? ''}}</td>
              </tr>
              <tr>
                <td style="width: 35% !important;"><strong>Sales</strong></td>
                <td style="width: 2% !important;">:</td>
                <td style="width: 63% !important;">{{$result->sales->name ?? ''}}</td>
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