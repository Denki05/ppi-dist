<!-- <div class="mb-3">
    <h5>Proforma Siap ACC</h5>
    <small class="text-muted">Menunggu persetujuan untuk membuat DO / Packing Order</small>
</div> -->

<table class="table table-sm table-striped">

    <thead>
        <tr>
            <th>#</th>
            <th>Kode SO</th>
            <th>Customer</th>
            <th>Grand Total</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>
    </thead>

    <tbody>

    @forelse($siap as $row)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $row->code }}</td>
        <td>{{ $row->member->name ?? '-' }} {{ $row->member->text_kota ?? '-' }}</td>
        <td>{{ number_format($row->details_cost->grand_total_idr,2,',','.') }}</td>
        <td>{{ $row->created_at }}</td>
        <td>
            <button type="button" class="btn btn-sm btn-circle btn-alt-danger btn-status-acc" 
                    data-id="{{ $row->id }}" title="ACC">
                <i class="fa fa-check"></i>
            </button>

            <button type="button"
                class="btn btn-sm btn-circle btn-alt-danger btn-delete-proforma"
                data-id="{{ $row->id }}"
                title="Delete">
                <i class="fa fa-trash"></i>
            </button>
        </td>

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