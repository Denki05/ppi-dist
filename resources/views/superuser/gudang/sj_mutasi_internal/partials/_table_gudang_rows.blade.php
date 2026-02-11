<div class="table-wrapper">
    <table class="table table-sm mutasi-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal - Kode</th>
                <th>Gudang Asal → Tujuan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr
                    data-id="{{ $row->id }}"
                    class="{{ $muted ? 'text-muted' : '' }}"
                >
                    <td>
                        {{ $loop->iteration + $rows->firstItem() - 1 }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($row->date)->format('d/m/y') }}<br>
                        <strong>{{ $row->code }}</strong>
                    </td>

                    <td>
                        {{ optional($row->warehouse_from_attribute)->name ?? '-' }}
                        →
                        {{ optional($row->warehouse_to_attribute)->name ?? '-' }}
                    </td>

                    <td>
                        {!! $row->statusLabel() !!}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        Tidak ada data
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>