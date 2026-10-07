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
        <td><span class="font-weight-bold text-primary">{{ $row->code }}</span></td>
        <td>{{ $row->member->name ?? '-' }} <small class="text-muted">{{ $row->member->text_kota ?? '' }}</small></td>
        <td>{{ optional($row->details_cost)->grand_total_idr ? number_format($row->details_cost->grand_total_idr,0,',','.') : '-' }}</td>
        <td><small class="text-muted">{{ $row->created_at ? $row->created_at->format('d/m/Y H:i') : '-' }}</small></td>
        <td>
            <button type="button" class="btn btn-sm btn-circle btn-outline-success btn-status-siap" 
                    data-id="{{ $row->id }}" title="Tandai Siap" aria-label="Tandai siap {{ $row->code }}">
                <i class="fa fa-check"></i>
            </button>

            <a href="{{ route('superuser.penjualan.so_proforma.edit', $row->id) }}"
               title="Edit — kalkulasi (tanpa tambah varian)" aria-label="Edit kalkulasi {{ $row->code }}">
                <button type="button" class="btn btn-sm btn-circle btn-outline-secondary">
                  <i class="fa fa-pencil"></i>
                </button>
            </a>

            {{-- Baris ADA pengajuan: Revisi (tanpa redirect).
                 Baris TANPA pengajuan (existing): tanpa Revisi/Batal. --}}
            @php $pgIdRv = ($pengajuanMap ?? [])[(string) optional($row->member)->id] ?? null; @endphp
            @if(($canRevisiBatal ?? false) && $pgIdRv)
            <button type="button" class="btn btn-sm btn-circle btn-outline-warning"
                    onclick="submitKembalikanAoTab({{ $pgIdRv }})"
                    title="Revisi — kembalikan ke AO, proforma diperbarui otomatis tanpa pengajuan/mutasi ulang" aria-label="Revisi {{ $row->code }} ke AO">
                <i class="fa fa-reply"></i>
            </button>
            @endif

            <a href="{{ route('superuser.penjualan.so_proforma.print_so_proforma', $row->id) }}"
            target="_blank"
            rel="noopener noreferrer">
                <button type="button" class="btn btn-sm btn-circle btn-outline-info" title="Print proforma" aria-label="Print {{ $row->code }}">
                    <i class="fa fa-print"></i>
                </button>
            </a>

            {{-- Batal langsung HANYA baris ada pengajuan; existing tanpa pengajuan: tanpa tombol --}}
            @if(($canRevisiBatal ?? false) && ($pgIdRv ?? null))
            <button type="button"
                class="btn btn-sm btn-circle btn-outline-danger btn-batalkan-prospek"
                data-pgid="{{ $pgIdRv }}"
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