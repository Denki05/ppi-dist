<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>Some Random Title</title>
    <style>
        body{
            font-family: "Courier New", Courier, "Lucida Sans Typewriter", "Lucida Typewriter", monospace !important;
            letter-spacing: -0.3px;
        }
        .invoice-wrapper{ width: 100%; margin: auto; }
        .nav-sidebar .nav-header:not(:first-of-type){ padding: 1.7rem 0rem .5rem; }
        .logo{ font-size: 50px; }
        .sidebar-collapse .brand-link .brand-image{ margin-top: -33px; }
        .content-wrapper{ margin: auto !important; }
        .billing-company-image { width: 50px; }
        .billing_name { text-transform: uppercase; }
        .billing_address { text-transform: capitalize; }
        .table{ width: 100%; border-collapse: collapse; }
        th{ text-align: left; padding: 10px; }
        td{ padding: 2px; vertical-align: top; }
        .row{ display: block; clear: both; }
        .text-right{ text-align: right; }
        .table-hover thead tr{ background: #eee; }
        .table-hover tbody tr:nth-child(even){ background: #fbf9f9; }
        address{ font-style: normal; }
    </style>
</head>
<body>
    <div class="row invoice-wrapper">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <table class="table">
                        <tr>
                            <td align="center">
                              @if($result->status == 1 && $result->status == 2)
                                <b><u>INVOICE PROFORMA</u></b>
                              @elseif($result->status == 3)
                                <b><u>INVOICE</u></b>
                              @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <br><br>
            <div class="row invoice-info">
                <div class="col-md-12">
                    <table class="table">
                        <tr>
                          <td>
                            <table>
                              <tr>
                                <td style="width: 15%;">Pelanggan</td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 95%;">{{$result->so->member->name}}</td>
                              </tr>
                              <tr>
                                <td style="width: 15%;">UP</td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 95%;">{{$result->so->member->contact_person}}</td>
                              </tr>
                              <tr>
                                <?php 
                                  $address = 'JL.Kendung Indah 1B/12,Kelurahan Sememi,Kecamatan Benowo,Kota Surabaya';
                                ?>
                                <td style="width: 15%;">Alamat Kirim</td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 95%;"><?php echo wordwrap($address, 35, "<br>\n") ?></td>
                              </tr>
                              <tr>
                                <td style="width: 15%;">Telp</td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 95%;">{{$result->so->member->phone}}</td>
                              </tr>
                            </table>
                          </td>
                          <td>
                            <table>
                              <tr>
                                <td style="width: 15%;">No.Invoice</td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 95%;">{{$result->so->code}}</td>
                              </tr>
                              <tr>
                                <td style="width: 15%;">Tanggal</td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 95%;"><?php echo date('d-m-Y', strtotime($result->created_at)) ?></td>
                              </tr>
                              <tr>
                                <td style="width: 15%;">NIK/NPWP</td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 95%;">-</td>
                              </tr>
                              <tr>
                                <td style="width: 15%;">Jatuh Tempo</td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 95%;"><?php echo date('d-m-Y', strtotime($result->created_at."+ 3 days")) ?></td>
                              </tr>
                              <tr>
                                <td style="width: 15%;">Sales</td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 95%;">-</td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                    </table>
                </div>
            </div>
            <hr>
            <br>
            <div class="row">
                <div class="col-md-12 table-responsive">
                    <table class="table table-condensed table-hover">
                        <thead>
                            <tr>
                                <th>Qty</th>
                                <th>Product</th>
                                <th>Description</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Product Name</td>
                                <td>Amount paid for Product Name</td>
                                <td class="text-right">&#8377; 1000</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right">Sub Total</td>
                                <td class="text-right"><strong>&#8377; 1000</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right">TAX (18%)</td>
                                <td class="text-right"><strong>&#8377; 180</strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right">Total Payable</td>
                                <td class="text-right"><strong>&#8377; 1180</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- /.col -->
            </div>
            <br><br><br>
            <div>
                <small><small>NOTE: This is system generate invoice no need of signature</small></small>
            </div>
        </div>
    </div>    
</body>
</html>