<!-- <div class="mb-3">
    <h5>Proforma Terbuat</h5>
    <small class="text-muted">Proforma yang sudah dihitung oleh admin</small>
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

    @forelse($terbuat as $row)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $row->code }}</td>
        <td>{{ $row->member->name ?? '-' }} {{ $row->member->text_kota ?? '-' }}</td>
        <td>{{ number_format($row->details_cost->grand_total_idr,2,',','.') }}</td>
        <td>{{ $row->created_at }}</td>
        <td>
            <button type="button" class="btn btn-sm btn-circle btn-alt-danger btn-status-siap" 
                    data-id="{{ $row->id }}" title="Siap">
                <i class="fa fa-check"></i>
            </button>

            <a href="{{ route('superuser.penjualan.so_proforma.edit', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Edit">
                  <i class="fa fa-pencil"></i>
                </button>
            </a>

            <a href="{{ route('superuser.penjualan.so_proforma.print_so_proforma', $row->id) }}" 
            target="_blank" 
            rel="noopener noreferrer">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Print proforma">
                    <i class="fa fa-print"></i>
                </button>
            </a>

            <button type="button"
                class="btn btn-sm btn-circle btn-alt-danger btn-delete-proforma"
                data-id="{{ $row->id }}"
                title="Delete">
                <i class="fa fa-trash"></i>
            </button>

            <button type="button" class="btn btn-sm btn-circle btn-alt-danger btn-status-cancel" 
                    data-id="{{ $row->id }}" title="Cancel">
                <i class="fa fa-undo"></i>
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