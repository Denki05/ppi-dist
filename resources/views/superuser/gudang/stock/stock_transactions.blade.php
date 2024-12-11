<table>
    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama</th>
            <th>Kemasan</th>
            <th>Brand</th>
            <th>Tanggal</th>
            <th>Transaksi</th>
            <th>Masuk</th>
            <th>Keluar</th>
            <th>Saldo</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $transaction)
        <tr>
            <td>{{ $transaction['product_code'] }}</td>
            <td>{{ $transaction['product_name'] }}</td>
            <td>{{ $transaction['product_pack'] }}</td>
            <td>{{ $transaction['brand'] }}</td>
            <td>{{ $transaction['created_at'] }}</td>
            <td>{{ $transaction['transaction'] }}</td>
            <td>{{ $transaction['in'] }}</td>
            <td>{{ $transaction['out'] }}</td>
            <td>{{ $transaction['balance'] }}</td>
            <td>{{ $transaction['description'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>