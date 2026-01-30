<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Form Permintaan Barang ke Showroom</title>

<style>
@page {
    size: A5 landscape;
    margin: 20px 30px; 
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px; /* Ukuran font sedikit dikecilkan agar lebih padat */
    color: #000;
    line-height: 1.2;
}

/* ===== HEADER ===== */
.header {
    margin-bottom: 10px;
    border-bottom: 1px double #000; /* Garis pemisah header */
    padding-bottom: 5px;
}

.header-table {
    width: 100%;
}

.header-table td {
    vertical-align: top;
    padding: 2px 0;
}

h3 {
    margin: 0 0 5px 0;
    text-transform: uppercase;
    font-size: 14px;
}

/* ===== TABLE ===== */
table {
    width: 100%;
    border-collapse: collapse;
    /* Menghapus table-layout: fixed agar kolom otomatis menyesuaikan jika perlu */
}

th, td {
    border: 1px solid #000;
    padding: 5px 4px;
    vertical-align: middle; /* Vertikal center agar lebih rapi */
}

th {
    background-color: #f2f2f2; /* Memberi warna sedikit agar header tabel menonjol */
    text-align: center;
    font-weight: bold;
    text-transform: uppercase;
}

.no-border td {
    border: none;
}

/* ===== ALIGN ===== */
.text-center { text-align: center; }
.text-right { text-align: right; }
.text-left  { text-align: left; }

/* ===== SIGNATURE ===== */
/* Menggunakan flow normal, bukan fixed agar tidak menabrak tabel jika datanya banyak */
.signature-wrapper {
    margin-top: 20px;
    width: 100%;
}

.signature-box {
    float: right;
    width: 150px;
    text-align: center;
}

.signature-line {
    margin-top: 45px;
    border-top: 1px solid #000;
}

.clearfix::after {
    content: "";
    clear: both;
    display: table;
}
</style>
</head>
<body>

<div class="header">
    <table class="header-table no-border">
        <tr>
            <td class="text-center" colspan="2">
                <h3>Form Permintaan Barang ke Showroom</h3>
            </td>
        </tr>
        <tr>
            <td width="50%">
                <strong>Tanggal / Kode :</strong> {{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d-m-y') }} / {{ $mutasi->kode }}
            </td>
            <td class="text-right" width="50%">
                <strong>Type :</strong> {{ $mutasi->type() }}
            </td>
        </tr>
        <tr>
            <td>    
                <strong>Brand :</strong> {{ $mutasi->brand_name }}
            </td>
            <td class="text-right">  
                <strong>Customer :</strong> {{ $mutasi->customer_other_address->name }} {{ $mutasi->customer_other_address->text_kota }}
            </td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th width="30px">No</th>
            <th class="text-left">Produk</th> {{-- Produk dibuat rata kiri agar mudah dibaca --}}
            <th width="150px">Packaging</th>
            <th width="60px">Qty</th>
        </tr>
    </thead>
    <tbody>
        @forelse($mutasi->details as $i => $row)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="text-left">
                {{ $row->product_packaging->code }} - {{ $row->product_packaging->name }}
            </td>
            <td class="text-center">
                {{ $row->product_packaging->packaging->pack_name }}
            </td>
            <td class="text-right" style="padding-right: 8px;">
                {{ number_format($row->qty, 2) }}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">Tidak ada data</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="signature-wrapper clearfix">
    <div class="signature-box">
        Mengetahui,<br><br><br><br>
        

        
        ( ................................. )
    </div>
</div>

</body>
</html>