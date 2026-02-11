<div class="table-wrapper">
    <table class="table table-sm mutasi-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal - Kode</th>
                <th>Customer</th>
                <th>Type</th>
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
                        {{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/y') }}<br>
                        <strong>
                            {{ $row->kode }}
                            {{ optional($row->so)->code ? ' / '.optional($row->so)->code : '' }}
                        </strong>
                    </td>
                    <td>
                        {{
                            optional($row->customer_other_address)->name
                                ? optional($row->customer_other_address)->name
                                    . ' ' . optional($row->customer_other_address)->text_kota
                                : 'SHOWROOM'
                        }}
                    </td>
                    <td>
                        {{ $row->type == 5 ? 'PROMOSI' : $row->type() }}
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