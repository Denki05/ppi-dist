<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Mutasi Showroom</title>

<style>
@page {
    size: A5 landscape;
    margin: 12px 16px;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    line-height: 1.4;
}

table {
    width: 100%;
    border-collapse: collapse;
}

.no-border td, .no-border th {
    border: none;
    padding: 3px 4px;
}

.text-center { text-align: center; }
.text-end { text-align: right; }

.brand-title {
    font-size: 14px;
    font-weight: bold;
    margin-top: 12px;
}

.mutasi-title {
    font-weight: bold;
    margin-top: 6px;
    font-size: 12px;
}

.item-row td {
    padding-left: 18px;
}

/* ===== SUBTOTAL ===== */
.subtotal-row td {
    padding-top: 4px;
    font-weight: bold;
}

/* ===== DIVIDER ===== */
.divider {
    margin: 6px 0;
    border-top: 1px dashed #000;
}

/* ===== HEADER TABLE ===== */
.header-table th,
.header-table td {
    border: 0px solid #000;
    padding: 4px 6px;
}

.header-table th {
    background: #f2f2f2;
    font-weight: bold;
    text-align: center;
}

/* ===== KOLOM PRODUK ===== */
.col-produk {
    text-align: center;
    width: 25%;
    word-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    padding-left: 6px !important;
    padding-right: 6px !important;
}

/* ===== KOLOM ANGKA (TH & TD) ===== */
.col-number {
    text-align: right !important;
    white-space: nowrap;
}

.header-table th.col-number {
    text-align: right !important;
}

/* ===== RAPATKAN ITEM ===== */
.item-row td {
    padding-top: 3px;
    padding-bottom: 3px;
}
</style>
</head>
<body>

{{-- ================= HEADER PDF ================= --}}
<table class="no-border">
<tr>
    <td class="text-center">
        <h2 style="margin:0;">MUTASI SHOWROOM</h2>
        <div>
            Periode : {{ \Carbon\Carbon::parse($history->tanggal)->format('F Y') }}
        </div>
    </td>
</tr>
</table>

@php
    $grandTotalAll = 0;

    // ================= GROUP BY BRAND =================
    $groupedByBrand = $mutasiList->groupBy(function($mutasi) {
        $brand = 'TANPA BRAND';
        if ($mutasi->details->isNotEmpty() &&
            $mutasi->details->first()->product_packaging &&
            $mutasi->details->first()->product_packaging->product &&
            $mutasi->details->first()->product_packaging->product->brand_name
        ) {
            $brand = $mutasi->details->first()->product_packaging->product->brand_name;
        }
        return $brand;
    });
@endphp

{{-- ================= LOOP BRAND ================= --}}
@foreach($groupedByBrand as $brand => $mutasiGroup)

<div class="brand-title">{{ $brand }}</div>
@php $totalBrand = 0; @endphp

{{-- ================= LOOP MUTASI ================= --}}
@foreach($mutasiGroup as $mutasi)

<div class="mutasi-title">
    {{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d M Y') }} - {{ $mutasi->kode }}
    <span style="float:right;">
        Kurs: {{ number_format($mutasi->kurs ?? 0, 0, ',', '.') }}
    </span>
</div>

{{-- ================= TABLE PER MUTASI ================= --}}
<table class="no-border header-table">
    <thead>
        <tr>
            <th width="4%">#</th>
            <th width="25%">Produk</th>
            <th width="10%" class="text-center">Qty</th>
            <th width="15%" class="col-number">Harga (USD)</th>
            <th width="20%" class="col-number">Subtotal</th>
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
                <td class="col-produk">
                    {{ $row->product_packaging->product->code ?? '-' }}
                    -
                    {{ $row->product_packaging->product->name ?? '-' }}
                </td>
                <td class="text-center">{{ $row->qty }}</td>
                <td class="col-number">
                    {{ number_format($row->price_usd, 2, ',', '.') }}
                </td>
                <td class="col-number">
                    {{ number_format($subtotal, 2, ',', '.') }}
                </td>
            </tr>
        @endforeach

        {{-- SUBTOTAL MUTASI --}}
        <tr class="subtotal-row">
            <td colspan="4" class="text-end">Subtotal</td>
            <td class="col-number">
                {{ number_format($subTotalMutasi, 2, ',', '.') }}
            </td>
        </tr>
    </tbody>
</table>

@php $totalBrand += $subTotalMutasi; @endphp

@endforeach {{-- END MUTASI --}}

{{-- TOTAL BRAND --}}
<div class="subtotal-row">
    TOTAL {{ $brand }} : {{ number_format($totalBrand, 2, ',', '.') }}
</div>

<div class="divider"></div>

@php $grandTotalAll += $totalBrand; @endphp

@endforeach {{-- END BRAND --}}

{{-- GRAND TOTAL --}}
<div class="brand-title">
    GRAND TOTAL : {{ number_format($grandTotalAll, 2, ',', '.') }}
</div>

</body>
</html>