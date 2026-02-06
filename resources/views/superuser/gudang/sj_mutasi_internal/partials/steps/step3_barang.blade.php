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
            <div class="alert alert-success mb-0">
                <i class="bi bi-check-circle"></i>
                Barang sudah <strong>DIAMBIL</strong> dan stok telah tercatat.
            </div>
        @endif

    </div>
</div>