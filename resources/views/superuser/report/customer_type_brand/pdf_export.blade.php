<!DOCTYPE html>
<html>
<head>
    <title>Laporan PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .total-kategori {
            color: #0000FF;
            font-weight: bold;
        }
        .total-global {
            color: #FF0000;
            font-weight: bold;
        }
        thead {
            display: table-header-group;
        }
        tr {
            page-break-inside: avoid;
            orphans: 3;
            widows: 3;
        }
        .kategori-row {
            page-break-before: always; /* Memastikan kategori baru dimulai di halaman baru jika perlu */
            page-break-after: avoid; /* Mencegah kategori terpecah */
        }
        .customer-row {
            page-break-inside: avoid; /* Mencegah baris customer terpisah */
        }
    </style>
</head>
<body>
    <h3>
        Report Customer By Type <br>
        Periode: {{ $startDate }} - {{ $endDate }}
    </h3>
    <table>
        <thead>
            <tr>
                <th rowspan="2">Kategori</th>
                <th rowspan="2">Customer</th>
                @foreach (['GCF', 'Senses', 'PPI FF', 'PPI NON FF', 'PPI X'] as $brand)
                    <th colspan="2">{{ $brand }}</th>
                @endforeach
                <th colspan="2">Total Customer</th>
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
                    $totalCustomers = $customers->count();
                @endphp

                @foreach($customers as $customer_name => $items)
                    <tr class="customer-row">
                        @if ($loop->first)
                            <td rowspan="{{ $totalCustomers }}" class="kategori-row"><strong>{{ $kategori }}</strong></td>
                        @endif
                        <td>{{ $customer_name }} ({{ $items->first()->customer_kota }})</td>
                        @foreach (['GCF', 'Senses', 'PPI FF', 'PPI NON FF', 'PPI X'] as $brand)
                            <td>{{ $items->where('invoice_brand', $brand)->sum('total_qty') }}</td>
                            <td>{{ number_format($items->where('invoice_brand', $brand)->sum('total_purchase'), 0, ',', '.') }}</td>
                        @endforeach
                        <td>{{ $items->sum('total_qty') }}</td>
                        <td>{{ number_format($items->sum('total_purchase'), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                
                <!-- Total per Kategori -->
                <tr class="total-row total-kategori">
                    <td colspan="2" style="text-align: right; page-break-before: avoid;"><b>Total {{ $kategori }}</b></td>
                    @foreach (['GCF', 'Senses', 'PPI FF', 'PPI NON FF', 'PPI X'] as $brand)
                        <td>{{ $group['totals']["total_{$brand}_qty"] }}</td>
                        <td>{{ number_format($group['totals']["total_{$brand}_purchase"], 0, ',', '.') }}</td>
                    @endforeach
                    <td>{{ $group['totals']['total_customer_qty'] }}</td>
                    <td>{{ number_format($group['totals']['total_customer_purchase'], 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <!-- Total Keseluruhan -->
            <tr class="total-row total-global">
                <td colspan="2" style="text-align: right;"><b>Total Keseluruhan</b></td>
                @foreach (['GCF', 'Senses', 'PPI FF', 'PPI NON FF', 'PPI X'] as $brand)
                    <td>{{ $globalTotals["total_{$brand}_qty"] }}</td>
                    <td>{{ number_format($globalTotals["total_{$brand}_purchase"], 0, ',', '.') }}</td>
                @endforeach
                <td>{{ $globalTotals['total_customer_qty'] }}</td>
                <td>{{ number_format($globalTotals['total_customer_purchase'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>