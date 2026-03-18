<!-- <div class="mb-3">
    <h5>Proforma Selesai</h5>
    <small class="text-muted">Daftar proforma yang sudah selesai diproses</small>
</div> -->

<table class="table table-sm table-striped">

    <thead>
        <tr>
            <th>#</th>
            <th>Kode SO</th>
            <th>Customer</th>
            <th>Grand Total</th>
            <th>Status</th>
            <th>Tanggal</th>
        </tr>
    </thead>

    <tbody>

    @forelse($tutup as $row)

    <tr>

        <td>{{ $loop->iteration }}</td>
        <td>{{ $row->code }}</td>
        <td>{{ $row->member->name ?? '-' }} {{ $row->member->text_kota ?? '-' }}</td>
        <td>{{ number_format($row->details_cost->grand_total_idr,2,',','.') }}</td>
        <td>
            <span class="badge bg-success">
                Selesai
            </span>
        </td>
        <td>{{ $row->updated_at }}</td>
    </tr>

    @empty

    <tr>
        <td colspan="6" class="text-center text-muted">
            Tidak ada data
        </td>
    </tr>

    @endforelse

    </tbody>

</table>