<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Jalan Mutasi Barang</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .no-border td {
            border: none;
            padding: 4px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
        }
        th {
            text-align: center;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .signature td {
            height: 70px;
            vertical-align: bottom;
            text-align: center;
        }
        .small {
            font-size: 10px;
        }
    </style>
</head>
<body>

    <div class="title">SURAT JALAN MUTASI BARANG</div>

    {{-- HEADER --}}
    <table class="no-border">
        <tr>
            <td width="50%">
                <strong>Kepada:</strong> <br>
                <strong>Brand:</strong> {{ $mutasi->brand_name }}
            </td>
            <td width="50%">
                <div style="text-align: left;">
                    <strong>No Sj:</strong> {{ $mutasi->kode }}<br>
                    <strong>Tanggal:</strong> {{ $mutasi->tanggal->format('d-m-Y') }}
                </div>
            </td>
        </tr>
    </table>

    <br>

    {{-- TABLE BARANG --}}
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Barang</th>
                <th width="25%">Nama Barang</th>
                <th width="10%">Qty (KG)</th>
                <th width="15%">Packing</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mutasi->details as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item->product_packaging->code }}</td>
                <td>{{ $item->product_packaging->name }}</td>
                <td class="text-right">{{ $item->qty }}</td>
                <td>{{ $item->product_packaging->packaging->pack_name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <br><br>

    {{-- TANDA TANGAN --}}
    <table class="signature">
        <tr>
            <td>Diajukan Oleh</td>
            <td>Dibuat Oleh</td>
            <td>Gudang</td>
            <td>Diterima</td>
        </tr>
        <tr>
            <td>{{ $footer['diajukan'] ?? '' }}</td>
            <td>{{ $footer['dibuat'] ?? '' }}</td>
            <td>{{ $footer['gudang'] ?? '' }}</td>
            <td>{{ $footer['diterima'] ?? '' }}</td>
        </tr>
    </table>

    <br>
    <div class="small">
        Putih: Admin, Merah: Nginden, Kuning: Accounting, Hijau: Gudang
    </div>

</body>
</html>