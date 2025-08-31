<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Refund - {{ $result->code }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 20px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }
        h2 {
            text-align: center;
            text-decoration: underline;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
        }
        .borderless td {
            border: none;
            padding: 3px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .footer {
            margin-top: 30px;
        }
        .footer .signature-table td {
            text-align: center;
            padding-top: 40px;
        }
        .underline {
            text-decoration: underline;
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
        .signature {
            position: absolute;
            bottom: 20px;
            right: 30px;
            text-align: right;
            font-size: 12px;
        }
    </style>
</head>
<body>

<h2 style="text-align: center; margin: 0; padding: 0; margin-bottom: 5px;"><u>NOTA REFUND</u></h2>

<div style="margin-bottom: 15px; font-size: 11px;">
    <div class="row-float">
        <div class="column-float" style="width: 40%; margin-top: 4px;">
            <table class="table borderless info" style="width: 100%;">
                <tbody>
                    <tr>
                        <td style="width: 35%;">Kode</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 63%;"><b>{{ $result->code }}</b></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="column-float" style="width: 60%;">
            <table class="table borderless info" style="width: 100%;">
                <tbody>
                    <tr>
                        <td style="width: 35% !important;">Customer</td>
                        <td style="width: 2% !important;">:</td>
                        <td style="width: 63% !important;">
                            {{ $result->customer->name ?? 'Tidak Diketahui' }} {{ $result->customer->text_kota ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<hr style="border: 1px solid #000; margin: 10px 0;">

<h4>Detail Refund:</h4>
<table>
    <thead>
        <tr>
            <th class="text-center">No</th>
            <th class="text-left">Keterangan</th>
            <th class="text-right">Jumlah Refund</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalRefund = 0;
        @endphp
        
        <tr>
            <td class="text-center"></td>
            <td></td>
            <td class="text-right"></td>
        </tr>
       
        <tr>
            <td colspan="2" class="text-right"><strong>TOTAL REFUND:</strong></td>
            <td class="text-right"><strong></strong></td>
        </tr>
    </tbody>
</table>

<div class="signature">
    Mengetahui,<br><br><br><br>
    <span>(.......................)</span>
</div>

</body>
</html>