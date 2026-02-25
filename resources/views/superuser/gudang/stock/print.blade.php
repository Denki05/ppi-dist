<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 11px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
        }

        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        th, td {
            border: 1px solid #555;
            padding: 5px;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .footer-total {
            font-weight: bold;
            background: #f9f9f9;
        }
        
        .info-table {
            border: none;
        }
        
        .info-table td {
            border: none !important;
            padding: 3px 0;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="title">KARTU STOCK</div>
</div>

<table class="info-table">
    <tr>
        <td width="15%">Product</td>
        <td width="35%">: {{ $product->code }} - {{ $product->name }} <br> / {{ $product->packaging->pack_name }}</td>
        <td width="15%">Warehouse</td>
        <td width="35%">: {{ $warehouse->name }}</td>
    </tr>
    <tr>
        <td>Periode</td>
        <td>
            :
            {{ $startDate ? $startDate->format('d/m/Y') : '-' }}
            -
            {{ $endDate ? $endDate->format('d/m/Y') : '-' }}
        </td>
        <td>Printed At</td>
        <td>: {{ now()->format('d/m/Y H:i') }}</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th width="15%">Date</th>
            <th width="20%">Transaction</th>
            <th width="10%">In</th>
            <th width="10%">Out</th>
            <th width="15%">Balance</th>
            <th width="30%">Description</th>
        </tr>
    </thead>
    <tbody>

        @php
            $totalIn = 0;
            $totalOut = 0;
            $lastBalance = $openingBalance;
        @endphp

        <tr>
            <td colspan="4" class="text-left"><b>Opening Balance</b></td>
            <td class="text-right">{{ number_format($openingBalance,2) }}</td>
            <td></td>
        </tr>

        @foreach($collects as $row)

            @php
                $in  = $row['in'] ?: 0;
                $out = $row['out'] ?: 0;
                $totalIn += $in;
                $totalOut += $out;
                $lastBalance = $row['balance'];
            @endphp

            <tr>
                <td>{{ $row['date'] }}</td>
                <td>{{ $row['transaction'] }}</td>
                <td class="text-right">{{ $in ? number_format($in,2) : '' }}</td>
                <td class="text-right">{{ $out ? number_format($out,2) : '' }}</td>
                <td class="text-right">{{ $row['balance'] }}</td>
                <td class="text-left">{{ $row['description'] }}</td>
            </tr>

        @endforeach

        <tr class="footer-total">
            <td colspan="2">TOTAL</td>
            <td class="text-right">{{ number_format($totalIn,2) }}</td>
            <td class="text-right">{{ number_format($totalOut,2) }}</td>
            <td class="text-right">{{ number_format($lastBalance,2) }}</td>
            <td></td>
        </tr>

    </tbody>
</table>

</body>
</html>