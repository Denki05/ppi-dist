<div class="mutasi-viewer-content">

    {{-- HEADER DETAIL --}}
    <div class="viewer-info-box mb-3">
        <div class="viewer-row">
            <div class="viewer-group">
                <div class="viewer-item">
                    <span class="viewer-label">Kode</span>
                    <span class="viewer-value">: {{ $mutasi->code }}</span>
                </div>
                <div class="viewer-item">
                    <span class="viewer-label">Tanggal</span>
                    <span class="viewer-value">: {{ \Carbon\Carbon::parse($mutasi->date)->format('d-m-Y') }}</span>
                </div>
                <div class="viewer-item">
                    <span class="viewer-label">Gudang Asal</span>
                    <span class="viewer-value">: {{ optional($mutasi->warehouse_from_attribute)->name ?? '-' }}</span>
                </div>
                <div class="viewer-item">
                    <span class="viewer-label">Gudang Tujuan</span>
                    <span class="viewer-value">: {{ optional($mutasi->warehouse_to_attribute)->name ?? '-' }}</span>
                </div>

                <div class="viewer-item" style="display:none;">
                    <span class="viewer-label">Status</span>
                    <span class="viewer-value" id="mutasiStatus">{{ $mutasi->status }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL DETAIL PRODUK --}}
    <div class="mutasi-viewer" style="padding:0;">
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover detail-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">No</th>
                        <th>Produk</th>
                        <th>Kemasan</th>
                        <th width="80">Qty (KG)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mutasi->mutasiOutDetails as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->product_pack->code ?? '-' }} - {{ $item->product_pack->name ?? '-' }}</td>
                            <td>{{ $item->product_pack->packaging->pack_name ?? '-' }}</td>
                            <td>{{ $item->quantity }}</td>
                        </tr>
                    @endforeach
                    @if($mutasi->mutasiOutDetails->isEmpty())
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Tidak ada detail produk</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>