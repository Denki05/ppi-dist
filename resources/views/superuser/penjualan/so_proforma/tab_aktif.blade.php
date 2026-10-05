<!-- <div class="mb-3">
    <h5>Proforma Aktif</h5>
</div> -->

<table class="table table-sm table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Created At</th>
            <th>Code</th>
            <th>Brand</th>
            <th>Customer</th>
            <th>Created By</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($aktif as $row)

    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $row->created_at }}</td>

        <td>{{ $row->code ?? '-' }}</td>

        <td>{{ $row->so_brand_name ?? '-' }}</td>

        <td>{{ $row->member->name ?? '-' }} {{ $row->member->text_kota ?? '-' }}</td>

        <td>{{ $row->createdBySuperuser() ?? '-' }}</td>

        <td>
            <span class="badge badge-warning">Aktif</span>
        </td>

        <td>
            @php $pgId = ($pengajuanMap ?? [])[(string) optional($row->member)->id] ?? null; @endphp
            {{-- Paket A poin 10: Revisi vs Batal eksplisit, hanya admin sales / management --}}
            @if($canRevisiBatal ?? false)
            <a href="{{ route('superuser.penjualan.so_proforma.edit', $row->id) }}"
               title="Revisi — tambah/ubah produk, pengajuan TETAP berlaku">
                <button type="button" class="btn btn-sm btn-circle btn-alt-warning">
                  <i class="fa fa-plus-circle"></i>
                </button>
            </a>

            @if($pgId)
                @include('superuser.penjualan.so_proforma._batalkan', ['pgId' => $pgId])
            @else
            <button type="button" class="btn btn-sm btn-circle btn-alt-danger btn-status-rollback"
                    data-id="{{ $row->so_id }}" title="Rollback — kembali ke SO awal, pengajuan dicabut">
                <i class="fa fa-undo"></i>
            </button>
            <button type="button"
                class="btn btn-sm btn-circle btn-alt-danger btn-delete-proforma"
                data-id="{{ $row->id }}"
                title="Hapus — proforma dihapus, pengajuan dicabut">
                <i class="fa fa-trash"></i>
            </button>
            @endif
            @endif
        </td>
    </tr>

    @endforeach
    </tbody>
</table>