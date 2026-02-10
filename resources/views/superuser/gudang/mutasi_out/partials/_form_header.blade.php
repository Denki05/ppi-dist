<div class="card mb-3">
    <div class="card-body">

        <h6 class="fw-semibold mb-3">Informasi Mutasi</h6>

        <div class="row g-3">

            {{-- Gudang Asal --}}
            <div class="col-md-6">
                <select name="warehouse_from"
                        class="form-select select2"
                        required>
                    <option value="">Pilih Gudang Asal</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Gudang Tujuan --}}
            <div class="col-md-6">
                <select name="warehouse_to" class="form-select select2" required>
                    <option value="">Pilih Gudang Tujuan</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <select name="brand_name"
                        class="form-select select2"
                        required>
                    <option value="">Pilih Brand</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->brand_name }}">
                            {{ $b->brand_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Catatan --}}
            <div class="col-6">
                <textarea name="note"
                          class="form-control"
                          rows="2"
                          placeholder="Keterangan tambahan (opsional)"></textarea>
            </div>

        </div>

    </div>
</div>