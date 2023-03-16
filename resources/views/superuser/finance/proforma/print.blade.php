<?php 
  $idr_total = 0;
?>
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
        .invoice-wrapper{ width: auto; margin: auto; }
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
          font-size: 14px;
        }
        table.table-data td {
          padding: 0 5px;
        }
        .column-float {
          float: left;
          width: 50%;
        }
        .row-float {
          position: relative;
        }
        .row-float:after {
          content: "";
          display: block;
          clear: both;
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
    </style>
    
</head>
<body>
    <div class="row invoice-wrapper">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <table class="table">
                        <tr>
                            <td align="center" style="font-size: 18pt;">
                              @if($result->status === 1 || $result->status === 2)
                                <b><u>INVOICE PROFORMA</u></b>
                              @elseif($result->status === 3)
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
                <div class="col-md-12">
                  <table class="table-data" style="width: 100%;">
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
                                <td>{{number_format($harga,2,',','.')}}</td>
                                <td class="text-right">
                                  {{number_format($disc_cash,2,',','.')}}
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
                </div>
                <!-- /.col -->
            </div>
            <div class="row">
              <div class="col-md-12">
                <table style="width: 100%; font-size: 14px; margin-top: -12px;" border="0">
                  <tr>
                    <td style="width: 50%;" scope="col"><div align="left">
                      <table style="width: 60%; font-size: 14px; margin-top: 10px;" border="0">
                        <tr>
                          <td style="width: 10%;" scope="col"><div align="left">Terbilang</div></td>
                        </tr>
                        <tr>
                          <td colspan="3">{{\CustomHelper::terbilang($result->do->do_cost->grand_total_idr)}}</td>
                          </tr>
                          <!-- <tr style="height: 1px;">
                          <td colspan="3" style="height: 1px;">&nbsp;</td>
                          </tr> -->
                          <tr>
                          <td>*Kurs USD</td>
                          <td>:</td>
                          <td>{{ $result->do->idr_rate }}</td>
                          </tr>
                        </table>
                        <table style="width: 80%;" cellspacing="0" class="note">
                        <tr>
                            <td style="width: 5%;"> - </td>
                            <td><strong>Pembayaran Cheque / Wesel / BG dianggap sah bila telah diuangkan</strong></td>
                          </tr>
                        <tr>
                            <td style="width: 5%;"> - </td>
                          <td><strong>Pembayaran TUNAI wajib disertai TANDA TERIMA TUNAI resmi dari PPI</strong></td>
                          </tr>
                        
                        <tr>
                            <td style="width: 5%;"> - </td>
                          <td><strong>Pembayaran diluar ketentuan diatas tidak diakui</strong></td>
                          </tr>
                        <tr>
                            <td style="width: 5%;"> - </td>
                          <td><strong>Barang yang sudah dibeli tidak dapat ditukar / dikembalikan</strong></td>
                        </tr>
                      </table>
                    </div></td>
                    <br>
                    <td style="width: 30%;" scope="col"><div align="left">
                      <table style="width: 100%; margin-top: 10px; font-size: 14px;" border="1">
                        <tr>
                          <td width="187" scope="col"><div align="left">Sub Total</div></td>
                          <td width="11" scope="col">:</td>
                          <td width="101" scope="col" style="text-align: right;"><div align="right">-</div></td>
                        </tr>
                        <tr>
                          <td><div align="left">Diskon </div></td>
                          <td>:</td>
                          <td style="text-align: right;"><div align="right">-</div></td>
                        </tr>
                        <tr>
                          <td><div align="left">Voucher</div></td>
                          <td>:</td>
                          <td style="border-bottom: solid 1px; text-align: right;"><div align="right"></div></td>
                        </tr>
                        <tr>
                          
                          <td><div align="left"></div></td>
                          <td>:</td>
                          <td style="text-align: right;"><div align="right"></div></td>
                        </tr>
                        <tr>
                          
                          <td><div align="left"></div></td>
                          <td>:</td>
                          <td style="border-bottom: solid 1px; text-align: right;"><div align="right"></div></td>
                        </tr>
                        <tr>
                          
                          <td><div align="left">Total</div></td>
                          <td>:</td>
                          <td style="text-align: right;"><div align="right"></div></td>
                        </tr>
                        <tr>
                          
                          <td><div align="left">Biaya Kirim</div></td>
                          <td>:</td>
                          <td align="right" style="text-align: right;"></td>
                        </tr>
                        <tr>
                          
                          <td><div align="left"><strong>Grand Total</strong></div></td>
                          <td>:</td>
                          <td style="text-align: right;"><div align="right"><strong></strong></div></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
        </div>
    </div>
  </body>
</html>