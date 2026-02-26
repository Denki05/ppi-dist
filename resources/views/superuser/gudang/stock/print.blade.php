<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 10px;
            border: none;
        }

        .info-table td {
            border: none !important;
            padding: 3px 0;
            vertical-align: top;
        }

        .info-table .label {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }

        th {
            background: #0d6efd;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #ccc;
        }

        td {
            border: 1px solid #ccc;
            padding: 5px 8px;
            vertical-align: top;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right { text-align: right; }
        .text-left  { text-align: left; }
        .footer-total {
            font-weight: bold;
            background-color: #e9ecef;
        }

        .opening-balance {
            font-style: italic;
            background-color: #f1f3f5;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="title">KARTU STOCK</div>
    <hr style="border:1px solid #0d6efd; margin: 5px 0 10px 0;">
</div>

<table class="info-table">
    <tr>
        <td class="label" width="15%">Product</td>
        <td width="35%">: {{ $product->code }} - {{ $product->name }} <br> / {{ $product->packaging->pack_name }}</td>
        <td class="label" width="15%">Warehouse</td>
        <td width="35%">: {{ $warehouse->name }}</td>
    </tr>
    <tr>
        <td class="label">Periode</td>
        <td>
            : {{ $startDate ? $startDate->format('d/m/Y') : '-' }} - {{ $endDate ? $endDate->format('d/m/Y') : '-' }}
        </td>
        <td class="label">Printed At</td>
        <td>: {{ now()->format('d/m/Y H:i') }}</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th width="18%">Date</th>
            <th width="20%">Transaction</th>
            <th width="10%" class="text-right">In</th>
            <th width="10%" class="text-right">Out</th>
            <th width="12%" class="text-right">Balance</th>
            <th width="36%" class="text-center">Description</th>
        </tr>
    </thead>
    <tbody>

        @php
            $totalIn = 0;
            $totalOut = 0;
            $lastBalance = $openingBalance;
        @endphp

        <tr class="opening-balance">
            <td colspan="4"><b>Opening Balance</b></td>
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
                <td class="text-right">{{ $in ? number_format($in,2) : 0 }}</td>
                <td class="text-right">{{ $out ? number_format($out,2) : 0 }}</td>
                <td class="text-right">{{ $row['balance'] }}</td>
                <td class="text-center">{{ $row['description'] }}</td>
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