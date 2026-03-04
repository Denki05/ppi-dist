<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice Mutasi Showroom</title>

    <style>
        @page {
            size: A5 landscape;
            margin: 12px 16px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.35;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px 6px;
        }

        th {
            background: #f2f2f2;
            text-align: center;
        }

        .no-border td {
            border: none;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

@foreach($mutasiList as $mutasi)

    {{-- ================= HEADER ================= --}}
    <table class="no-border" style="margin-bottom:6px;">
        <tr>
            <td colspan="2" class="text-center">
                <h2 style="margin:0;">INVOICE MUTASI SHOWROOM</h2>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Tanggal :</strong>
                {{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d M Y') }}
            </td>
            <td>
                <strong>Gd. Asal :</strong>
                {{ $mutasi->warehouse_from->name }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Kode :</strong> {{ $mutasi->kode }}
            </td>
            <td>
                <strong>Type :</strong> {{ $mutasi->type() }}
            </td>
        </tr>
    </table>

    {{-- ================= TABLE ================= --}}
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="28%">Produk</th>
                <th width="18%">Kemasan</th>
                <th width="9%">Qty</th>
                <th width="12%">Harga (USD)</th>
                <th width="18%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp

            @foreach($mutasi->details as $i => $row)
                @php
                    $subtotal = $row->qty * $row->price_idr;
                    $grandTotal += $subtotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>
                        {{ $row->product_packaging->code }} -
                        {{ $row->product_packaging->product->name }}
                    </td>
                    <td class="text-center">
                        {{ $row->product_packaging->packaging->pack_name }}
                    </td>
                    <td class="text-center">{{ $row->qty }}</td>
                    <td class="text-end">
                        {{ number_format($row->price_usd, 2, ',', '.') }}
                    </td>
                    <td class="text-end">
                        {{ number_format($subtotal, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            <tr>
                <th colspan="5" class="text-end">GRAND TOTAL</th>
                <th class="text-end">
                    {{ number_format($grandTotal, 0, ',', '.') }}
                </th>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:6px;">
        <strong>Kurs :</strong>
        {{ number_format($mutasi->kurs ?? 0, 0, ',', '.') }}
    </div>

    @if(!$loop->last)
        <div class="page-break"></div>
    @endif

@endforeach

</body>
</html>