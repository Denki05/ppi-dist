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

            {{-- Paket A poin 10: Revisi (pengajuan tetap) vs Batal (pengajuan dicabut) --}}
            @if($canRevisiBatal ?? false)
            <a href="{{ route('superuser.penjualan.so_proforma.edit', $row->id) }}"
               title="Revisi — tambah/ubah produk, pengajuan TETAP berlaku">
                <button type="button" class="btn btn-sm btn-circle btn-alt-warning">
                  <i class="fa fa-plus-circle"></i>
                </button>
            </a>

            @php $pgId = ($pengajuanMap ?? [])[(string) optional($row->member)->id] ?? null; @endphp
            @if($pgId)
                @include('superuser.penjualan.so_proforma._batalkan', ['pgId' => $pgId])
            @else
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

    @empty

    <tr>
        <td colspan="6" class="text-center text-muted">
            Tidak ada data
        </td>
    </tr>

    @endforelse

    </tbody>

</table>