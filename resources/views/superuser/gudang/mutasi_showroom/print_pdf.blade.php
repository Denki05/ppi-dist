<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>SURAT JALAN MUTASI BARANG</title>

<style>
    @page {
        size: A5 landscape;
        margin: 18px 25px 120px 25px; /* Margin bawah besar untuk tempat footer */
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
        color: #000;
        line-height: 1.4;
    }

    /* Memaksa ganti halaman */
    .page-break {
        page-break-after: always;
    }

    /* ================= HEADER ================= */
    .header {
        padding: 5px 12px;
        margin-bottom: 5px;
    }

    .header-title {
        text-align: right;
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .header-table {
        width: 100%;
        border-collapse: collapse;
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
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
        padding: 3px 4px; /* Kurangi padding dari 5px ke 3px */
        font-size: 11px;
    }

    .data-table th {
        background: #f5f5f5;
        font-weight: bold;
        text-transform: uppercase;
        text-align: center;
    }

    .text-center { text-align: center; }
    .text-right  { text-align: right; }

    /* ================= FOOTER SIGNATURE ================= */
    .footer-signature {
        position: fixed;
        bottom: -20px; /* Posisi di margin bawah @page */
        left: 0;
        right: 0;
        width: 100%;
    }

    .signature-table {
        width: 100%;
        border-collapse: collapse;
    }

    .signature-table td {
        text-align: center;
        vertical-align: top;
    }

    .signature-label {
        margin-bottom: 50px; 
        font-size: 12px;
        font-weight: bold;
    }

    .signature-line {
        border-top: 1px solid #000;
        width: 70%;
        margin: 0 auto;
    }
</style>
</head>

<body>

@php
    // Ubah chunk menjadi 10 menyesuaikan batas area fisik A5
    $chunkSize = 10;
    $chunks = $mutasi->details->chunk($chunkSize);
    $totalChunks = count($chunks);
@endphp

@foreach($chunks as $index => $chunk)
    <div class="header">
        <div class="header-title">SURAT JALAN MUTASI BARANG</div>
        <table class="header-table">
            <tr>
                <td style="width: 50%; padding: 4px 0;">
                    <table width="100%" style="font-size: 11px;">
                        <tr><td class="label">Kepada</td><td>: {{ $mutasi->customer_other_address->name ?? 'SHOWROOM' }}</td></tr>
                        <tr><td class="label">Alamat</td><td>: {{ $mutasi->customer_other_address->address ?? 'SURABAYA' }}</td></tr>
                        <tr><td class="label">Telp</td><td>: {{ $mutasi->customer_other_address->phone ?? '-' }}</td></tr>
                    </table>
                </td>
                <td style="width: 50%; padding: 4px 0;">
                    <table width="100%" style="font-size: 11px;">
                        <tr><td class="label">Brand</td><td>: {{ $mutasi->brand_name }}</td></tr>
                        <tr><td class="label">Kode</td><td>: {{ $mutasi->kode }}{{ optional($mutasi->so)->code ? ' / '.optional($mutasi->so)->code : '' }}</td></tr>
                        <tr><td class="label">Tanggal</td><td>: {{ $mutasi->tanggal->format('d-m-Y') }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th width="60">Qty(KG)</th>
                <th width="140">Kemasan</th>
            </tr>
        </thead>
        <tbody>
            {{-- Hapus $i as key, gunakan $loop->iteration untuk penomoran --}}
            @foreach($chunk as $row)
                <tr>
                    <td class="text-center">{{ ($index * $chunkSize) + $loop->iteration }}</td>
                    <td class="text-center">{{ $row->product_packaging->code }}</td>
                    <td>{{ $row->product_packaging->name }}</td>
                    <td class="text-right">{{ number_format($row->qty, 2) }}</td>
                    <td class="text-center">{{ $row->product_packaging->packaging->pack_name }}</td>
                </tr>
            @endforeach
            
            {{-- Pengecekan sisa row kosong dinamis menggunakan $chunkSize --}}
            @for($emptyRow = count($chunk); $emptyRow < $chunkSize; $emptyRow++)
                <tr>
                    <td class="text-center">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div style="text-align: right; font-size: 8px; margin-top: 5px;">
        Halaman {{ $index + 1 }} dari {{ $totalChunks }}
    </div>

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

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