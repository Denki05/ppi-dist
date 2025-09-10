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
            border: none !important;
            padding: 3px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }

        .footer {
            margin-top: 15px; /* dorong ke bawah */
        }
        .footer .signature-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }
        .footer .signature-table td {
            border: none;
            text-align: center;
            padding-top: 86px;
            font-size: 12px;
        }
        .underline {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2 style="margin: 0; padding: 0; margin-bottom: 5px;"><u>NOTA REFUND</u></h2>

<div style="margin-bottom: 15px; font-size: 11px;">
    <div style="float: left; width: 40%; margin-top: 4px;">
        <table class="borderless" style="width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 35%;">Kode</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 63%;"><b>{{ $result->code }}</b></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div style="float: left; width: 60%;">
        <table class="borderless" style="width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 35%;">Customer</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 63%;">
                        {{ $result->customer->name ?? 'Tidak Diketahui' }} {{ $result->customer->text_kota ?? '' }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 35%;">Nota Acuan</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 63%;">
                        {{ $result->invoice->do_code }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 35%;">Tanggal Nota</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 63%;">
                        {{ \Carbon\Carbon::parse($result->invoice->so->so_date)->format('d-m-Y') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>

<hr style="border: 1px solid #000; margin: 10px 0;">

<h4>Detail Refund:</h4>
<table>
    <thead>
        <tr>
            <th class="text-center">No</th>
            <th class="text-left">Keterangan Barang</th>
            <th class="text-center">Kemasan</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Jumlah Refund</th>
        </tr>
    </thead>
    <tbody>
        @php $totalRefund = 0; @endphp
        @foreach($result->sale_return_details as $index)
            @php
                $totalRefund += $result->cost->purchase_total_idr;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>
                    {{ $index->product->code }} - {{ $index->product->name }}
                </td>
                <td class="text-center">
                    {{ $index->product->packaging->pack_name }}
                </td>
                 <td class="text-right">
                    {{ $index->qty }}
                </td>
                <td class="text-right">
                    Rp. {{ number_format($result->cost->purchase_total_idr, 0, ',', '.') }}
                </td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4" class="text-right"><strong>TOTAL REFUND:</strong></td>
            <td class="text-right"><strong>Rp. {{ number_format($totalRefund, 0, ',', '.') }}</strong></td>
        </tr>
    </tbody>
</table>

<!-- Informasi Rekening Tujuan -->
<div style="margin-top:20px; font-size: 11px;">
    <table class="borderless" style="width: 60%;">
        <tbody>
            <tr>
                <td style="width: 30%;">Nomor Rekening</td>
                <td style="width: 2%;">:</td>
                <td style="width: 68%;"><b>{{ $result->bank_account ?? '' }}</b></td>
            </tr>
            <tr>
                <td>Pemilik Rekening</td>
                <td>:</td>
                <td><b>{{ $result->account_owner ?? '' }}</b></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Tanda Tangan -->
<div class="footer">
    <table class="signature-table">
        <tr>
            <td>
                <span>Mengajukan,</span><br><br><br><br>
                <span>.......................</span>
            </td>
            <td>
                <span>Mengetahui,</span><br><br><br><br>
                <span>.......................</span>
            </td>
            <td>
                <span>Menyetujui,</span><br><br><br><br>
                <span>.......................</span>
            </td>
        </tr>
    </table>
</div>

</body>
</html>