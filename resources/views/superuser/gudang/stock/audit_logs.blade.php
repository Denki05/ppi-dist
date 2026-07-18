<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px; vertical-align: top; }
        .group-1 { 
            background-color: #dbeafe;
            font-weight: bold;
            font-size: 16px;
            padding: 10px 8px !important;
            color: #111827;
            border-top: 2px solid #94a3b8;
            border-bottom: 2px solid #94a3b8;
        }
        .group-2 { 
            background-color: #f8fafc;
            font-weight: bold;
            padding: 8px 8px 8px 30px !important;
            font-size: 13px;
            color: #334155;
        }
        .detail-row td { border-bottom: 1px dotted #ccc; }
        .text-right { text-align: right; }
        .title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 20px; }
        /* Tambahkan agar tabel lebih cantik */
        .group-1 { background-color: #e2e8f0; font-weight: bold; }
        .group-2 { background-color: #f8fafc; padding-left: 30px !important; }
    </style>
</head>
<body>

    <div class="title">
        REPORT LOGS STOCK BY {{ strtoupper($tipeExport) }}
    </div>

    <table>
        <thead>
            <tr style="border-bottom: 2px solid #000;">
                <th style="text-align: left;">Waktu</th>
                <th style="text-align: right;">Qty</th>
                @if($tipeExport === 'product')
                    <th style="text-align: left;">Customer</th>
                @endif
                <th style="text-align: left;">Status</th>
                <th style="text-align: left;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $group1 => $group2Data)
                <tr>
                    <td colspan="{{ $colspan }}" class="group-1">{{ $group1 }}</td>
                </tr>
                @foreach($group2Data as $group2 => $details)
                    <tr>
                        <td colspan="{{ $colspan }}" class="group-2">{{ $group2 }}</td>
                    </tr>
                    @foreach($details as $row)
                        <tr class="detail-row">
                            <td style="padding-left: 40px;">{{ $row['time'] }}</td>
                            <td class="text-right">{{ $row['qty'] }}</td>
                            @if($tipeExport === 'product')
                                <td>{{ $row['cust'] }}</td>
                            @endif
                            <td>{{ $statusLabel[$row['status']] ?? $row['status'] }}</td>
                            <td>{{ $row['note'] }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>

</body>
</html>