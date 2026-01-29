<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Form Permintaan Barang ke Showroom</title>

<style>
@page {
    size: A5 landscape;
    margin: 12px 18px 38px 18px; /* margin bawah diperbesar */
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #000;
}

/* ===== HEADER ===== */
h3 {
    margin: 0;
    padding: 0;
}

.header {
    margin-bottom: 6px;
}

.header-table {
    width: 100%;
}

.header-table td {
    vertical-align: top;
    padding: 3px 4px;
}

/* ===== TABLE ===== */
table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

th, td {
    border: 1px solid #000;
    padding: 4px;
    vertical-align: top;
}

th {
    text-align: center;
    font-weight: bold;
}

.no-border td {
    border: none;
}

/* ===== ALIGN ===== */
.text-center { text-align: center; }
.text-right { text-align: right; }
.text-left  { text-align: left; }

/* ===== PRODUK ===== */
.col-produk {
    word-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    font-size: 10px;
    line-height: 1.3;
}

/* ===== SIGNATURE FIXED ===== */
.signature-fixed {
    position: fixed;
    bottom: 42px;   /* naik ke area aman */
    right: 18px;
    width: 150px;
    text-align: center;
    font-size: 11px;
}

.signature-line {
    margin-top: 15px; /* sebelumnya 40px */
    border-top: 1px solid #000;
}
</style>
</head>
<body>

{{-- ================= HEADER ================= --}}
<div class="header">
    <table class="header-table no-border">
        <tr>
            <td class="text-center" colspan="2">
                <h3>Form Permintaan Barang ke Showroom</h3>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Tanggal / Kode :</strong> {{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d-m-y') }} / {{ $mutasi->kode }}
            </td>
            <td class="text-right">
                <strong>Type :</strong>
                {{ $mutasi->type() }}
            </td>
        </tr>
        <tr>
            <td>    
                <strong>Brand :</strong>
                {{ $mutasi->brand_name }}
            </td>
            
        </tr>
    </table>
</div>

{{-- ================= TABLE DATA ================= --}}
<table>
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="30%">Produk</th>
            <th width="8%" class="text-right">Qty</th>
        </tr>
    </thead>
    <tbody>
        @forelse($mutasi->details as $i => $row)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>

            <td class="text-center col-produk">
                {{ $row->product_packaging->code }}
                -
                {{ $row->product_packaging->name }}
            </td>

            <td class="text-right">
                {{ number_format($row->qty, 2) }}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">
                Tidak ada data
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- ================= SIGNATURE ================= --}}
<div class="signature-fixed">
    Mengetahui,<br><br>
    <div class="signature-line"></div>
</div>

</body>
</html>