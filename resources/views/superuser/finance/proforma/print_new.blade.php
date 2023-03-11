<?php $idr_total = 0; ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Test</title>
  <link rel="stylesheet" id="css-main" href="{{ asset('superuser_assets/css/codebase.min.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="{{ asset('superuser_assets/css/codebase.custom.css') }}">
  <link rel="stylesheet" id="css-main" href="{{ asset('superuser_assets/css/codebase.min.css') }}">
  <script src="{{ asset('superuser_assets/js/codebase.core.min.js') }}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/11.0.2/bootstrap-slider.min.js" integrity="sha512-f0VlzJbcEB6KiW8ZVtL+5HWPDyW1+nJEjguZ5IVnSQkvZbwBt2RfCBY0CBO1PsMAqxxrG4Di6TfsCPP3ZRwKpA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <style type="text/css">


    body {
      background-color: #fff7b3;
      /*font-size: 20px;*/
    }

    #border_setting {
      border-style: solid;
      border-color: #847259;
      border-radius: 20px;
      padding-bottom: 9px;
      padding-right: 20px;
    }

    #font_white {
      color: #ffffff;
    }

    #logo_setting{
      padding-left: 50px;
    }

    #personal_table {
      margin-top:25px;
      padding-top:25px;
    }
    table, td, th {    
      border: 1px solid #847259;
      text-align: left;
    }

    table {
      border-collapse: collapse;
      width: 100%;
      position: relative;
    }

    th, td {
      padding: 15px;
    }


    @media print {
    thead {
      page-break-inside:avoid;
        /*page-break-inside:always;*/
        /*page-break-before: avoid;*/
        /*page-break-after: avoid;*/
        /*position:initial;*/
    }


}

    /*table { page-break-inside:always; page-break-after:always; }*/

    /* div   { page-break-inside:avoid; }*/ /* This is the key */

    /*tr    { page-break-inside:always; page-break-after:always; }
    td    { page-break-inside:always; page-break-after:always; }
*/

    /*thead { display:table-header-group; }
    tfoot { display:table-footer-group; }*/
    #invoice_border {
      border-radius: 25px;
      border-color: black;
      background-repeat: repeat;
    }
  </style>
</head>
<body onload="window.print();">
<!-- info row -->
<div class="row">
  

  <br>
  <br><br>

  <div class="col-xs-10 invoice-col">
    To ,
    <address>
      <strong>{{ $result->do->member->name }}</strong>
      <br>{{ $result->do->member->address }}
      <br><b>Phone: </b>{{ $result->do->member->phone }}
    </address>
  </div>
  <!-- /.col -->
  <div class="pull-right invoice-col"  id="invoice_border">
    <b>{{$result->code}}</b><br>
    <b>Date :</b> <?php echo date('d-m-Y', strtotime($result->created_at));?>
  </div>

  <!-- /.col -->
</div>
<!-- /.row -->

<!-- Table row -->
     <!--  <div class="row">
     <div class="col-xs-12"> -->


      <table class="table table-striped">
        <thead>
          <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Produk</th>
            <th>Acuan(USD)</th>
            <th>Qty(KG)</th>
            <th>Kemasan</th>
            <th>Harga</th>
            <th>Disc(CASH)</th>
            <th>Netto</th>
            <th>Jumlah</th>
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
            <td>{{ $loop->iteration }}</td>
            <td>{{ $row->product->code }}</td>
            <td>{{ $row->product->name }}</td>
            <td>{{number_format($row->price,2,',','.')}}</td>
            <td>{{ $row->qty }}</td>
            <td class="text-left">{{$row->packaging_txt()->scalar ?? ''}}</td>
            <td>{{number_format($harga,0,',','.')}}</td>
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


      <!-- </div> -->
      <!-- /.col -->
      <!-- </div> -->
      <!-- /.row -->

      <div class="row">
        <div class="col-xs-6"></div>
        <div class="col-xs-6">
          <div class="table-responsive" style="margin-left:100px">
            <table class="table">
              <tr>
                <th style="width:68%">Grass Total</th>
                <td></td>
              </tr>
              <tr>
                <th>Vat (4%)</th>
                <td></td>
              </tr>

              <tr>
                <th>Tax (1%)</th>
                <td></td>
              </tr>           
              <tr>
                <th>Total:</th>
                <td></td>
              </tr>
            </table>
            <br>
          </div>
        </div>
        <!-- /.col -->
      </div>

      <div class="row"> 
        <div class="col-xs-4" style="margin-top:50px">
          <h4><b>Rules</b></h4>
          <p>1. Payment should be made with in 30 days otherwise.</p>
          <p>2. 18% interest will be chenged extra.</p>
          <p>3. All taxes will be collected as abd when levide.</p>
          <p>4. Subject to Ahmedabad Jurisdiction.</p>
        </div>

        <div class="col-xs-4" style="margin-top:220px">
          <br>
          <br>
          <h6><b>E.&O.E.</b></h6>
        </div>

        <div class="col-xs-4" style="margin-top:140px">
          <h5><b>For, ATOP</b></h5>
          <br>
          <br>
          <h6>Autho.  Signature</h6>
        </div>
      </div> 

      <!-- /.row -->

      <!-- this row will not appear when printing -->


    <!-- /.content -->  
  <!-- </div> -->
  <!-- /.content-wrapper -->
<!-- </div> -->
<!-- ./wrapper -->

<!-- AdminLTE App -->
<script src="{{ asset('superuser_assets/js/codebase.app.min.js') }}"></script>
</body>
</html>