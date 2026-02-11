{{-- Tab Aktif --}}
        <div id="tab-aktif" class="mutasi-tab-content">
            <div class="table-wrapper">
                <table class="table table-hover table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Gudang Tujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasiAktif as $i => $m)
                        <tr data-id="{{ $m->id }}" class="mutasi-row" data-tab="aktif">
                            <td>{{ $i + $mutasiAktif->firstItem() }}</td>
                            <td>{{ $m->code }}</td>
                            <td>{{ \Carbon\Carbon::parse($m->date)->format('d-m-Y') }}</td>
                            <td>{{ optional($m->warehouse_to_attribute)->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="compactPaginationAktif" class="mt-2 d-flex justify-content-center align-items-center"></div>
        </div>

        {{-- Tab Proses --}}
        <div id="tab-proses" class="mutasi-tab-content d-none">
            <div class="table-wrapper">
                <table class="table table-hover table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Gudang Tujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasiProses as $i => $m)
                        <tr data-id="{{ $m->id }}" class="mutasi-row" data-tab="proses">
                            <td>{{ $i + $mutasiProses->firstItem() }}</td>
                            <td>{{ $m->code }}</td>
                            <td>{{ \Carbon\Carbon::parse($m->date)->format('d-m-Y') }}</td>
                            <td>{{ optional($m->warehouse_to_attribute)->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="compactPaginationProses" class="mt-2 d-flex justify-content-center align-items-center"></div>
        </div>

        {{-- Tab Selesai --}}
        <div id="tab-selesai" class="mutasi-tab-content d-none">
            <div class="table-wrapper">
                <table class="table table-hover table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Gudang Tujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasiSelesai as $i => $m)
                        <tr data-id="{{ $m->id }}" class="mutasi-row text-muted" data-tab="selesai">
                            <td>{{ $i + $mutasiSelesai->firstItem() }}</td>
                            <td>{{ $m->code }}</td>
                            <td>{{ \Carbon\Carbon::parse($m->date)->format('d-m-Y') }}</td>
                            <td>{{ optional($m->warehouse_to_attribute)->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="compactPaginationSelesai" class="mt-2 d-flex justify-content-center align-items-center"></div>
        </div>