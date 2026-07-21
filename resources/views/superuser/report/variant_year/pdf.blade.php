<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Variant Year</title>
    <style>
        @page { margin: 1.5cm 1cm; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt; 
            color: #111;
            line-height: 1.3;
        }
        .header { text-align: center; margin-bottom: 25px; }
        .header h3 { margin: 0; font-size: 14pt; letter-spacing: 1px; font-weight: bold;}
        .header p { margin: 5px 0; color: #555; }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        /* GAYA MODERN FINANCIAL: Hanya garis atas dan bawah pada Header */
        th {
            border-top: 1.5pt solid #000;
            border-bottom: 1.5pt solid #000;
            padding: 8px 4px;
            font-weight: bold;
            text-align: left;
        }
        
        /* Baris Data Polos tanpa border */
        td {
            padding: 4px;
            vertical-align: middle;
            border: none; 
        }

        /* Penanda Baris Induk (Brand, Bahan, Variant) */
        .parent-row { font-weight: bold; }

        /* Garis batas khusus Baris Total di bawah */
        .total-row td {
            border-top: 0.5pt solid #aaa;
            font-weight: bold;
            font-style: italic;
            padding-top: 6px;
            padding-bottom: 12px; /* Jarak lega antar grup */
        }
        
        /* Grand Total bawah */
        .grand-total-row td {
            border-top: 1.5pt solid #000;
            border-bottom: 3pt double #000; /* Garis Dobel */
            font-weight: bold;
            font-size: 9pt;
            padding: 8px 4px;
            margin-top: 15px;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Indentasi yang disesuaikan dengan kebutuhan Anda */
        .indent-0 { padding-left: 2px; font-size: 9pt; color: #000; } /* BRAND */
        .indent-1 { padding-left: 15px; } /* Bahan Baku */
        .indent-2 { padding-left: 30px; } /* Variant */
        .indent-3 { padding-left: 45px; color: #444; } /* Kemasan */
        
        /* Indentasi ekstra untuk tulisan Total agar mudah terlihat */
        .indent-total-0 { padding-left: 15px; }
        .indent-total-1 { padding-left: 30px; }
        .indent-total-2 { padding-left: 45px; }

        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
    </style>
</head>
<body>
    <div class="header">
        <h3>LAPORAN VARIAN TAHUNAN</h3>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 45%;">Deskripsi Item</th>
                @foreach($years as $y)
                    <th class="text-right">{{ $y }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{-- Loop menembus semua Brand tanpa terputus-putus tabelnya --}}
            @foreach($groupedData as $brandName => $data)
                @foreach($data['rows'] as $row)
                    @php
                        $rowClass = '';
                        $indentClass = '';
                        
                        if ($row['is_total']) {
                            $rowClass = 'total-row';
                            $indentClass = 'indent-total-' . $row['level'];
                        } else {
                            if ($row['level'] < 3) $rowClass = 'parent-row';
                            $indentClass = 'indent-' . $row['level'];
                        }
                    @endphp

                    <tr class="{{ $rowClass }}">
                        <td class="{{ $indentClass }}">
                            {{ $row['label'] }}
                        </td>
                        
                        @if($row['qty_per_year'] !== null)
                            @foreach($years as $y)
                                <td class="text-right">
                                    {{ $row['qty_per_year'][$y] > 0 ? number_format($row['qty_per_year'][$y], 0, ',', '.') : '-' }}
                                </td>
                            @endforeach
                        @else
                            <td colspan="{{ count($years) }}"></td>
                        @endif
                    </tr>
                @endforeach
            @endforeach
            
            {{-- Grand Total ditempel di ujung bawah tabel --}}
            <tr class="grand-total-row">
                <td>GRAND TOTAL KESELURUHAN</td>
                @foreach($years as $y)
                    <td class="text-right">
                        {{ number_format($grandTotal[$y], 0, ',', '.') }}
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>
</body>
</html>