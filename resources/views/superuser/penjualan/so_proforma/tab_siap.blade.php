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
        <td><span class="font-weight-bold text-primary">{{ $row->code }}</span></td>
        <td>{{ $row->member->name ?? '-' }} <small class="text-muted">{{ $row->member->text_kota ?? '' }}</small></td>
        <td>{{ optional($row->details_cost)->grand_total_idr ? number_format($row->details_cost->grand_total_idr,0,',','.') : '-' }}</td>
        <td><small class="text-muted">{{ $row->created_at ? $row->created_at->format('d/m/Y H:i') : '-' }}</small></td>
        <td>
            <button type="button" class="btn btn-sm btn-circle btn-outline-success btn-status-acc" 
                    data-id="{{ $row->id }}" title="ACC — lanjut ke DO/Packing" aria-label="ACC {{ $row->code }}">
                <i class="fa fa-check"></i>
            </button>

            {{-- Tab siap: Batal langsung HANYA baris ada pengajuan; existing: tanpa tombol --}}
            @php $pgId = ($pengajuanMap ?? [])[(string) optional($row->member)->id] ?? null; @endphp
            @if(($canRevisiBatal ?? false) && $pgId)
            <button type="button"
                class="btn btn-sm btn-circle btn-outline-danger btn-batalkan-prospek"
                data-pgid="{{ $pgId }}"
                data-code="{{ $row->code }}"
                title="Batalkan langsung — pengajuan dicabut + log (tanpa pindah halaman)" aria-label="Batalkan {{ $row->code }}">
                <i class="fa fa-ban"></i>
            </button>
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