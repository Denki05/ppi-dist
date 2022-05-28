<!doctype html>
<html lang="en">
  <head>
    <title>Product Price</title>
    <style type="text/css">
      tbody{
        font-size: 15px;
      }
      table, td, th {
        border: 1px solid black;
      }
      table th{
        font-size: 15px;
      }

      table {
        width: 100%;
        border-collapse: collapse;
        color: #333;
      }
      table tbody{
        text-align: center;
      }
      @page{
        margin-top: 15px;
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
      /*Footer*/
      #footer {
        position: fixed;
        left: 0;
        right: 0;
        color: #aaa;
        font-size: 11px;
        text-align: right;
      }
      
      #footer {
        bottom: 0;
      }
      .page-number:before {
        content: "{{date('d-m-Y')}} | Page " counter(page);
      }
      
    </style>
  </head>
  <body>
    <div style="margin-bottom: 5px;">
      @if(!empty($header))
      <div style="height: 150px;">
        <img src="{{$header}}" style="width: 100%;height: 100%;">
      </div>
      @else
      <div class="row-float">
        <div class="column-float note" style="width: 45%;">
            <div style="height: 100px;">
              <img src="<?= base_path('public/superuser_assets/media/master/company/'.$company->logo) ?>" style="width: 100%;height: 100%;">
            </div>
        </div>
        <div class="column-float" style="width: 55%;">
        </div>
      </div>
      <div style="text-align: right;letter-spacing: 5px;font-weight: 500;font-size: 30px;">
        <h5 style="margin-top: 0;padding-top: 0;margin-bottom: 0;padding-bottom: 0;">PRICE LIST</h5>
      </div>
      @endif
    </div>

    <div style="margin-bottom: 5px;text-align: center;">
      {{date('F - Y')}}
    </div>

    <div class="row">
      <div class="col-12">
        <table>
          <thead>
            <tr class="text-center">
              <th style="width: 5%;">No</th>
              <th style="width: 17%;">Product</th>
              <th style="width: 12%;">Code</th>
              <th style="width: 20%;">Brand Reference</th>
              <th style="width: 33%;">Sub Brand Reference</th>
              <th style="width: 13%;">Price (USD)</th>
            </tr>
          </thead>
          <tbody>
            @foreach($table as $index => $row)
              <tr class="text-center">
                <td>{{$index+1}}</td>
                <td>{{$row->name}}</td>
                <td>{{$row->code}}</td>
                <td>{{$row->brand_reference->name ?? ''}}</td>
                <td>{{$row->sub_brand_reference->name ?? ''}}</td>
                <td>{{$row->selling_price}}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div id="footer">
      <div class="page-number"></div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  </body>
</html>