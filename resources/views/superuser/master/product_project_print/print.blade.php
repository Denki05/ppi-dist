<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Price List - Fine Fragrance</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; font-size: 12pt; }
        th { background-color: #f2f2f2; text-align: center; }
        td { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2 style="text-align: center;">PRICE LIST - FINE FRAGRANCE</h2>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Kode</th>
                <th style="width: 25%;">Nama</th>
                <th style="width: 10%;">Gender</th>
                <th style="width: 15%;" class="text-right">Harga / KG</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $product)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $product->kode_produk }}</td>
                <td>{{ $product->nama_produk }}</td>
                <td class="text-center">{{ $product->sex }}</td>
                <td class="text-right">${{ number_format($product->harga, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Data tidak tersedia.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p>* Tidak termasuk ongkos kirim. Kurs dapat berubah sewaktu-waktu.</p>
    <p style="text-align: right;">Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
</body>
</html>