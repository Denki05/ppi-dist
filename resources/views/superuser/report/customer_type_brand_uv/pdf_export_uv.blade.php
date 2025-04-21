<!DOCTYPE html>
<html>
<head>
    <title>Laporan Customer Type Brand UV</title>
    <style>
        @page {
            margin: 15pt;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9pt;
        }

        h3 {
            text-align: center;
            margin-bottom: 10pt;
            font-size: 11pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        th, td {
            border: 0.5pt solid #000;
            padding: 4pt 3pt;
            word-break: break-word;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .bg-total-kategori { background-color: #eaf1ff; }
        .bg-total-global { background-color: #dbeeff; font-weight: bold; }

        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <h3>
        REPORT REGISTER BRAND UV <br>
        Periode : {{ \Carbon\Carbon::parse($startDate)->format('Y-m-d') }} - {{ \Carbon\Carbon::parse($endDate)->format('Y-m-d') }}
    </h3>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Kategori</th>
                <th rowspan="2">Customer</th>
                @foreach (['GCF', 'Senses', 'PPI FF', 'PPI NON FF', 'PPI X'] as $brand)
                    <th colspan="2">{{ $brand }}</th>
                @endforeach
                <th colspan="2">Total</th>
            </tr>
            <tr>
                @foreach (['GCF', 'Senses', 'PPI FF', 'PPI NON FF', 'PPI X'] as $brand)
                    <th>Qty</th>
                    <th>Omset</th>
                @endforeach
                <th>Qty</th>
                <th>Omset</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedData as $kategori => $group)
                @php
                    $customers = $group['items']->groupBy('customer_name');
                @endphp

                @foreach($customers as $customer_name => $items)
                    <tr>
                        @if ($loop->first)
                            <td rowspan="{{ $customers->count() }}" class="text-left bold">{{ $kategori }}</td>
                        @endif
                        <td class="text-left">{{ $customer_name }} ({{ $items->first()->customer_kota }})</td>
                        @foreach (['GCF', 'Senses', 'PPI FF', 'PPI NON FF', 'PPI X'] as $brand)
                            <td class="text-right nowrap">{{ number_format($items->where('invoice_brand', $brand)->sum('total_qty')) }}</td>
                            <td class="text-right nowrap">{{ number_format($items->where('invoice_brand', $brand)->sum('total_purchase'), 2, ',', '.') }}</td>
                        @endforeach
                        <td class="text-right bold nowrap">{{ number_format($items->sum('total_qty')) }}</td>
                        <td class="text-right bold nowrap">{{ number_format($items->sum('total_purchase'), 2, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr class="bg-total-kategori">
                    <td colspan="2" class="text-right bold" style="color: blue;">Total {{ $kategori }}</td>
                    @foreach (['GCF', 'Senses', 'PPI FF', 'PPI NON FF', 'PPI X'] as $brand)
                        <td class="text-right bold nowrap" style="color: blue;">{{ number_format($group['totals']["total_{$brand}_qty"]) }}</td>
                        <td class="text-right bold nowrap" style="color: blue;">{{ number_format($group['totals']["total_{$brand}_purchase"], 2, ',', '.') }}</td>
                    @endforeach
                    <td class="text-right bold nowrap">{{ number_format($group['totals']['total_customer_qty']) }}</td>
                    <td class="text-right bold nowrap">{{ number_format($group['totals']['total_customer_purchase'], 2, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr class="bg-total-global">
                <td colspan="2" class="text-right bold" style="color: red;">Total Keseluruhan</td>
                @foreach (['GCF', 'Senses', 'PPI FF', 'PPI NON FF', 'PPI X'] as $brand)
                    <td class="text-right bold nowrap" style="color: red;">{{ number_format($globalTotals["total_{$brand}_qty"]) }}</td>
                    <td class="text-right bold nowrap" style="color: red;">{{ number_format($globalTotals["total_{$brand}_purchase"], 2, ',', '.') }}</td>
                @endforeach
                <td class="text-right bold nowrap" style="color: red;">{{ number_format($globalTotals['total_customer_qty']) }}</td>
                <td class="text-right bold nowrap" style="color: red;">{{ number_format($globalTotals['total_customer_purchase'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>