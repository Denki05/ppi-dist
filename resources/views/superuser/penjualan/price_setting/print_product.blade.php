<?php 
    $explode = $request["checkbox"] ?? [];
    $cek_id_produk = $request["id_produk"] ?? null; 
    if($cek_id_produk != null && !in_array($cek_id_produk, $explode)){
      $explode[] = $cek_id_produk;
    }
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Product List</title>
    <style type="text/css">
      tbody,.detail{
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
        <h5 style="margin-top: 0;padding-top: 0;margin-bottom: 0;padding-bottom: 0;">PRODUCT LIST</h5>
      </div>
      @endif
    </div>

    <div style="margin-bottom: 5px;text-align: center;">
      {{date('F - Y')}}
    </div>
    <div>
      @foreach($brand_reference as $index => $value)
      <div class="row">
        <h9 style="font-size: 16px;"><b>{{$value->name}}</b></h9>
        <div class="table-responsive">
          <table>
            <thead>
              <tr class="text-center">
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Code</th>
                <th style="width: 40%;">Product</th>
                <th style="width: 40%;">Sub Brand Reference</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $no = 0;
              ?>
              @if(count($value->products) <= 0)
              <tr class="text-center">
                <td colspan="4">Data tidak ditemukan</td>
              </tr>
              @endif
              @foreach($value->products as $i => $v)
                @if(count($explode) > 0)
                    @if(in_array($v->id,$explode))
                    <tr class="text-center">
                      <td>{{$no+1}}</td>
                      <td>{{$v->code}}</td>
                      <td>{{$v->name}}</td>
                      <td>{{$v->sub_brand_reference->name ?? ''}}</td>
                    </tr>
                    <?php
                      $no++;
                    ?>
                    @endif
                @else
                <tr class="text-center">
                  <td>{{$i+1}}</td>
                  <td>{{$v->code}}</td>
                  <td>{{$v->name}}</td>
                  <td>{{$v->sub_brand_reference->name ?? ''}}</td>
                </tr>
                <?php
                  $no++;
                ?>
                @endif
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endforeach
    </div>


    <div id="footer">
      <div class="page-number"></div>
    </div>

  </body>
</html>