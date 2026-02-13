<div class="card border-0 shadow-sm">
    <div class="card-body">

        {{-- JUDUL STEP HANYA JIKA BELUM DIAMBIL --}}
        @if($mutasi->status_barang != 2)
            <div class="alert alert-info">
                <strong>Step 3:</strong> Update Status Barang (1 Mutasi)
            </div>
        @endif

        <input type="hidden" name="mutasi_id" value="{{ $mutasi->id }}">

        {{-- JIKA BELUM DIAMBIL --}}
        @if($mutasi->status_barang != 2)

            <div class="mb-3">
                <label class="form-label">Update Status Barang</label>

                <select class="form-select" id="status_barang">
                    @if($mutasi->status_barang == 0)
                        <option value="">-- Pilih Status --</option>
                        <option value="1">BELUM_DIAMBIL</option>
                        <option value="2">DIAMBIL</option>

                    @elseif($mutasi->status_barang == 1)
                        <option value="2">DIAMBIL</option>
                    @endif
                </select>
            </div>

            <div class="d-flex justify-content-end">
                <button class="btn btn-primary" id="saveStep3">
                    Update Status
                </button>
            </div>

        @else
            {{-- SUDAH DIAMBIL --}}
            <div class="alert alert-success mb-3">
                <i class="bi bi-check-circle"></i>
                Barang sudah <strong>DIAMBIL</strong> dan stok telah tercatat.
            </div>

            {{-- ================= DETAIL MUTASI SESUAI TYPE ================= --}}
            {{-- HEADER DETAIL DENGAN STYLE --}}
            @if($type === 'showroom')
                <div class="mutasi-viewer-content">

                    {{-- HEADER DETAIL --}}
                    <div class="viewer-info-box mb-3 p-3 rounded shadow-sm" style="background-color:#f8f9fa;">
                        <div class="row g-3">
                            <div class="col-md-4 col-sm-6">
                                <div class="viewer-item">
                                    <span class="viewer-label fw-bold">Tanggal / Kode</span>
                                    <span class="viewer-value">: {{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d-m-Y') }} / {{ $mutasi->kode }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="viewer-item">
                                    <span class="viewer-label fw-bold">Customer</span>
                                    <span class="viewer-value">: {{ $mutasi->customer_other_address->name ?? 'SHOWROOM' }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="viewer-item">
                                    <span class="viewer-label fw-bold">Type</span>
                                    <span class="viewer-value">: 
                                        <span class="badge bg-primary text-white">{{ strtoupper($mutasi->type()) }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TABEL DETAIL PRODUK --}}
                    <div class="mutasi-viewer mb-3" style="padding:0;">
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
                                    @forelse($mutasi->details as $i => $item)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $item->product_packaging->code ?? '-' }} - {{ $item->product_pack->name ?? '-' }}</td>
                                            <td>{{ $item->product_packaging->packaging->pack_name ?? '-' }}</td>
                                            <td>{{ $item->qty }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">Tidak ada detail produk</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                {{-- TYPE INTERNAL / LAINNYA --}}
                <div class="mutasi-viewer-content">

                    {{-- HEADER DETAIL --}}
                    <div class="viewer-info-box mb-3 p-3 rounded shadow-sm" style="background-color:#eef2f7;">
                        <div class="row g-3">
                            <div class="col-md-3 col-sm-6">
                                <div class="viewer-item">
                                    <span class="viewer-label fw-bold">Kode</span>
                                    <span class="viewer-value">: {{ $mutasi->code }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="viewer-item">
                                    <span class="viewer-label fw-bold">Tanggal</span>
                                    <span class="viewer-value">: {{ \Carbon\Carbon::parse($mutasi->date)->format('d-m-Y') }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="viewer-item">
                                    <span class="viewer-label fw-bold">Gudang Asal</span>
                                    <span class="viewer-value">: {{ optional($mutasi->warehouse_from_attribute)->name ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="viewer-item">
                                    <span class="viewer-label fw-bold">Gudang Tujuan</span>
                                    <span class="viewer-value">: {{ optional($mutasi->warehouse_to_attribute)->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TABEL DETAIL PRODUK --}}
                    <div class="mutasi-viewer mb-3" style="padding:0;">
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
                                    @forelse($mutasi->mutasiOutDetails as $i => $item)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $item->product_pack->code ?? '-' }} - {{ $item->product_pack->name ?? '-' }}</td>
                                            <td>{{ $item->product_pack->packaging->pack_name ?? '-' }}</td>
                                            <td>{{ $item->quantity }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">Tidak ada detail produk</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endif

    </div>
</div>