<!-- <div class="mb-3">
    <h5>Proforma Aktif</h5>
</div> -->

<table class="table table-sm table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Kode</th>
            <th>Customer</th>
            <th>Brand</th>
            <th>Grand Total</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($aktif as $row)

    <tr>
        <td>{{ $loop->iteration }}</td>

        <td><span class="font-weight-bold text-primary">{{ $row->code ?? '-' }}</span></td>

        <td>{{ $row->member->name ?? '-' }} <small class="text-muted">{{ $row->member->text_kota ?? '' }}</small></td>

        <td>{{ $row->so_brand_name ?? '-' }}</td>

        <td>{{ optional($row->details_cost)->grand_total_idr ? number_format($row->details_cost->grand_total_idr,0,',','.') : '-' }}</td>

        <td><small class="text-muted">{{ $row->created_at ? $row->created_at->format('d/m/Y H:i') : '-' }}</small></td>

        <td>
            <span class="badge badge-warning">Aktif</span>
        </td>

        <td>
            @php $pgId = ($pengajuanMap ?? [])[(string) optional($row->member)->id] ?? null; @endphp
            {{-- Edit = kalkulasi saja (tanpa tambah varian), seperti semula --}}
            <a href="{{ route('superuser.penjualan.so_proforma.edit', $row->id) }}"
               title="Edit — kalkulasi (tanpa tambah varian)" aria-label="Edit kalkulasi {{ $row->code }}">
                <button type="button" class="btn btn-sm btn-circle btn-outline-secondary">
                  <i class="fa fa-pencil"></i>
                </button>
            </a>

            {{-- Baris ADA pengajuan: Revisi + Batal langsung (tanpa redirect).
                 Baris TANPA pengajuan (existing): tanpa Revisi/Batal. --}}
            @if($canRevisiBatal ?? false)
            @if($pgId)
            <button type="button" class="btn btn-sm btn-circle btn-outline-warning"
                    onclick="submitKembalikanAoTab({{ $pgId }})"
                    title="Revisi — kembalikan ke AO, proforma diperbarui otomatis tanpa pengajuan/mutasi ulang" aria-label="Revisi {{ $row->code }} ke AO">
                <i class="fa fa-reply"></i>
            </button>
            <button type="button"
                class="btn btn-sm btn-circle btn-outline-danger btn-batalkan-prospek"
                data-pgid="{{ $pgId }}"
                data-code="{{ $row->code }}"
                title="Batalkan langsung — pengajuan dicabut + log (tanpa pindah halaman)" aria-label="Batalkan {{ $row->code }}">
                <i class="fa fa-ban"></i>
            </button>
            @endif
            @endif
        </td>
    </tr>

    @endforeach
    </tbody>
</table>