{{-- Tambahkan ini di baris paling atas viewer.blade.php --}}
<input type="hidden" id="status_viewer" value="{{ $mutasi->status() }}">

<div>
    <table class="table table-sm table-bordered">
        <thead>
        <tr>
            <th>#</th>
            <th>Produk</th>
            <th>Qty</th>
        </tr>
        </thead>
        <tbody>
        @foreach($mutasi->details as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product_packaging->code }} - {{ $item->product_packaging->name }}</td>
                <td>{{ $item->qty }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>