<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>SURAT JALAN MUTASI BARANG</title>

<style>
@page {
    size: A5 landscape;
    margin: 18px 25px;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
    color: #000;
    line-height: 1.4;
}

/* ================= HEADER ================= */
.header {
    padding: 10px 12px;
    margin-bottom: 10px;
}

.header-title {
    text-align: right;
    font-size: 16px;
    font-weight: bold;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
    border-top: 1px solid #000;
    border-bottom: 1px solid #000;
}

.header-table td {
    padding: 4px 0;
    vertical-align: top;
    border: none; /* pastikan td tidak punya border */
}

.label {
    width: 70px;
    font-weight: bold;
}

/* ================= TABLE ================= */
table.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
}

.data-table th,
.data-table td {
    border: 1px solid #000;
    padding: 6px 5px;
}

.data-table th {
    background: #f5f5f5;
    font-weight: bold;
    text-transform: uppercase;
    text-align: center;
}

.data-table td {
    vertical-align: middle;
}

.text-center { text-align: center; }
.text-right  { text-align: right; }
.text-left   { text-align: left; }

/* ================= SIGNATURE ================= */
.signature-table {
    width: 100%;
    margin-top: 30px;
    border-collapse: collapse;
    page-break-inside: avoid;
}

.signature-table td {
    text-align: center;
    vertical-align: top;
    font-size: 10px;
}

.signature-label {
    margin-bottom: 65px;   /* ruang tanda tangan lebih tinggi */
    font-size: 14px;
    font-weight: bold;
    letter-spacing: 0.3px;
}

.signature-line {
    border-top: 1px solid #000;
    width: 80%;
    margin: 0 auto 4px auto;
}

.signature-note {
    margin-top: 15px;
    font-size: 10px;
}

@page {
    size: A5 landscape;
    margin: 18px 25px 90px 25px; /* tambah bottom margin */
}

/* FOOTER SIGNATURE */
.footer-signature {
    position: fixed;
    bottom: 30px;
    left: 25px;
    right: 25px;
}
</style>
</head>

<body>

<!-- ================= HEADER ================= -->
<div class="header">
    <div class="header-title">SURAT JALAN MUTASI BARANG</div>

    <table class="header-table">
        <tr>
            <!-- KOLOM KIRI -->
            <td style="width: 50%;">
                <table width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
                    <tr>
                        <td class="label">Kepada</td>
                        <td>: {{ $mutasi->customer_other_address->name ?? 'SHOWROOM' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Alamat</td>
                        <td>: {{ $mutasi->customer_other_address->address ?? 'SURABAYA' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Telp</td>
                        <td>: {{ $mutasi->customer_other_address->phone ?? '-' }}</td>
                    </tr>
                </table>
            </td>

            <!-- KOLOM KANAN -->
            <td style="width: 50%;">
                <table width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
                    <tr>
                        <td class="label">Brand</td>
                        <td>: {{ $mutasi->brand_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kode</td>
                        <td>: {{ $mutasi->kode }}{{ optional($mutasi->so)->code ? ' / '.optional($mutasi->so)->code : '' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal</td>
                        <td>: {{ $mutasi->tanggal->format('d-m-Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<!-- ================= TABLE ================= -->
<table class="data-table">
    <thead>
        <tr>
            <th width="30">No</th>
            <th class="text-center">Kode Barang</th>
            <th class="text-center">Nama Barang</th>
            <th width="60">Qty(KG)</th>
            <th width="140">Kemasan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($mutasi->details as $i => $row)
            <tr>
                <td class="text-center" style="font-size: 12px;">{{ $i + 1 }}</td>
                <td class="text-center" style="font-size: 12px;">
                    {{ $row->product_packaging->code }}
                </td>
                <td class="text-center" style="font-size: 12px;">
                    {{ $row->product_packaging->name }}
                </td>
                <td class="text-right" style="font-size: 12px;">
                    {{ number_format($row->qty, 2) }}
                </td>
                <td class="text-center" style="font-size: 12px;">
                    {{ $row->product_packaging->packaging->pack_name }}
                </td>
                
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">Tidak ada data</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- ================= FOOTER SIGNATURE ================= -->
<div class="footer-signature">

    <table class="signature-table">
        <tr>
            <td width="33%">
                <div class="signature-label">DIBUAT OLEH,</div>
                <div class="signature-line"></div>
            </td>
            <td width="33%">
                <div class="signature-label">GUDANG,</div>
                <div class="signature-line"></div>
            </td>
            <td width="33%">
                <div class="signature-label">DITERIMA,</div>
                <div class="signature-line"></div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>