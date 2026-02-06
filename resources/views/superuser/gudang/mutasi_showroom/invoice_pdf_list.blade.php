<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Mutasi - {{ $history->kode }}</title>

    <style>
        @page {
            size: A4 potrait;
            margin: 12px 15px;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.25;
            color: #222;
        }

        h2 {
            font-size: 12pt;
            margin: 0;
            text-align: center;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .periode {
            text-align: center;
            font-size: 9pt;
            color: #666;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* ===== MUTASI CONTAINER ===== */
        .mutasi-box {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #ccc;
            page-break-inside: avoid;
        }

        /* Paksa 2 mutasi per halaman */
        .mutasi-box:nth-child(2n) {
            page-break-after: always;
        }

        .mutasi-number {
            background: #222;
            color: #fff;
            padding: 2px 6px;
            font-size: 8pt;
            font-weight: bold;
            display: inline-block;
            border-radius: 3px;
            margin-bottom: 3px;
        }

        /* ===== HEADER ===== */
        .mutasi-header {
            border-top: 1px solid #333;
            border-bottom: 1px solid #ddd;
            padding: 4px 0;
            margin-bottom: 3px;
        }

        .info-td {
            vertical-align: top;
            font-size: 8pt;
        }

        .label {
            color: #777;
            display: inline-block;
            width: 65px;
        }

        .value {
            font-weight: bold;
        }

        /* ===== ITEM TABLE ===== */
        .item-table th {
            font-size: 7pt;
            color: #555;
            border-bottom: 1px solid #333;
            padding: 4px 3px;
            text-transform: uppercase;
        }

        .item-row td {
            padding: 3px 3px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .prod-code {
            font-weight: bold;
            font-size: 8pt;
            display: block;
        }

        .prod-name {
            font-size: 7pt;
            color: #666;
            display: block;
        }

        /* ===== FOOTER TABLE ===== */
        .footer-row td {
            padding: 4px 3px;
            font-size: 8pt;
            border-top: 1px solid #333;
            font-weight: bold;
        }

        .kurs-text {
            font-weight: normal;
            font-style: italic;
            color: #888;
            font-size: 7pt;
        }

        /* ===== BRAND & GRAND TOTAL ===== */
        .brand-total {
            margin: 6px 0 10px;
            padding: 5px;
            font-size: 9pt;
            text-align: right;
            border: 1px dashed #ccc;
        }

        .grand-total {
            padding: 8px;
            font-size: 10pt;
            font-weight: bold;
            text-align: right;
            background: #222;
            color: #fff;
        }
        
        .kv-line {
            white-space: nowrap;
            line-height: 1.2;
        }
        
        .kv-label {
            display: inline-block;
            width: auto;
            color: #777;
        }
        
        .kv-sep {
            margin: 0 2px;
        }
        
        .kv-value {
            font-weight: bold;
        }

    </style>
</head>

<body>

<h2>Laporan Mutasi Showroom</h2>
<div class="periode">
    Periode: {{ \Carbon\Carbon::parse($history->tanggal)->format('F Y') }}
</div>

@php
    $grandTotalAll = 0;
    $globalIteration = 1;

    $groupedByBrand = $mutasiList->groupBy(function($mutasi) {
        return $mutasi->details->first()->product_packaging->product->brand_name ?? 'TANPA BRAND';
    });
@endphp

@foreach($groupedByBrand as $brand => $mutasiGroup)
    @php $totalBrand = 0; @endphp

    @foreach($mutasiGroup as $mutasi)
        <div class="mutasi-box">

            <div class="mutasi-number">MUTASI #{{ $globalIteration++ }}</div>

            <div class="mutasi-header">
                <table>
                    <tr>
                        <td class="info-td" width="60%">
                            <div><span class="label">Tgl / Kode</span>: <span class="value">{{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d/m/Y') }} —  {{ $mutasi->kode }}{{ optional($mutasi->so)->code ? ' / '.optional($mutasi->so)->code : '' }}</span></div>
                            <div><span class="label">Customer</span>: <span class="value">{{ $mutasi->customer_other_address->name ?? '-' }}</span></div>
                        </td>
                        <td class="info-td" width="40%" style="text-align:left;">
                            <div class="kv-line">
                                <span class="kv-label">Type</span>
                                <span class="kv-sep">:</span>
                                <span class="kv-value">{{ $mutasi->type == 5 ? 'FREE PRODUCT' : $mutasi->type() }}</span>
                            </div>
                            <div class="kv-line">
                                <span class="kv-label">Brand</span>
                                <span class="kv-sep">:</span>
                                <span class="kv-value">{{ $brand }}</span>
                            </div>
                        </td>

                    </tr>
                </table>
            </div>

            <table class="item-table">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="45%">Produk</th>
                        <th width="10%" class="text-center">Qty</th>
                        <th width="20%" class="text-right">Harga (USD)</th>
                        <th width="20%" class="text-right">Subtotal (IDR)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $subTotalMutasi = 0; @endphp
                    @foreach($mutasi->details as $row)
                        @php
                            $subtotal = $row->qty * $row->price_idr;
                            $subTotalMutasi += $subtotal;
                        @endphp
                        <tr class="item-row">
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-center">
                                <b>{{ $row->product_packaging->product->code ?? '-' }} - {{ $row->product_packaging->product->code ?? '-' }}</b>
                            </td>
                            <td class="text-center">{{ $row->qty }}</td>
                            <td class="text-right">{{ number_format($row->price_usd, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="footer-row">
                        <td colspan="2" class="kurs-text">
                            Kurs: {{ number_format($mutasi->kurs ?? 0, 0, ',', '.') }}
                        </td>
                        <td colspan="2" class="text-right">
                            Total {{ $mutasi->kode }}
                        </td>
                        <td class="text-right">
                            IDR {{ number_format($subTotalMutasi, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>

            @php $totalBrand += $subTotalMutasi; @endphp
        </div>
    @endforeach

    <div class="brand-total">
        Total {{ strtoupper($brand) }} :
        <strong>IDR {{ number_format($totalBrand, 0, ',', '.') }}</strong>
    </div>

    @php $grandTotalAll += $totalBrand; @endphp
@endforeach

<div class="grand-total">
    GRAND TOTAL : IDR {{ number_format($grandTotalAll, 0, ',', '.') }}
</div>

</body>
</html>
