    @php
        use App\Entities\Gudang\MutasiShowroom;

        $mutasi = $mutasi_showroom;
        $status = $mutasi->status;
        $userId = auth()->id();

        /* ================= USER ROLE ================= */
        $isDeveloper     = $userId == 1;
        $isFinanceStaff  = $userId == 31;
        $isFinanceSpv    = $userId == 36;

        /* ================= STATUS FLAG ================= */
        $isActive  = $status == MutasiShowroom::STATUS['ACTIVE'];
        $isPublish = $status == MutasiShowroom::STATUS['PUBLISH'];
        $isSent    = $status == MutasiShowroom::STATUS['SENT'];

        /* ================= HAK AKSES ================= */
        $canPrint   = $isActive && $isFinanceStaff;
        $canPublish = $isActive && $isFinanceStaff;
        $canSent    = $isPublish && $isFinanceStaff;
        $canInputPrice = $isSent && $isFinanceSpv;

        /* ================= DEVELOPER OVERRIDE ================= */
        if ($isDeveloper) {
            $canPrint = $canPublish = $canSent = $canInputPrice = true;
        }

        $printCount = $mutasi->print_count ?? 0;
        $maxPrint   = 2;
        $canPrintMore = $printCount < $maxPrint;
    @endphp

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0">Mutasi Showroom – {{ $mutasi->kode }}</h5>
            <large class="text-muted">
                {{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d M Y') }}
                • {{ $mutasi->details->count() }} Item
            </large>
        </div>

        <div class="d-flex gap-2">

            {{-- BACK --}}
            <button type="button"
                    class="btn btn-sm btn-outline-warning"
                    id="btnBackToList">
                <i class="bi bi-arrow-left"></i> Back
            </button>

            {{-- ACTIVE --}}
            @if($isActive)
                @if($canPrint)
                    <button type="button"
                            class="btn btn-sm btn-outline-primary"
                            id="btnPrint"
                            {{ (!$canPrintMore && !$isDeveloper) ? 'disabled' : '' }}
                            title="Dicetak {{ $printCount }} / {{ $maxPrint }} kali">
                        <i class="fa fa-print"></i>
                        Print ({{ $printCount }}/{{ $maxPrint }})
                    </button>
                @endif

                @if($canPublish)
                    <button type="button"
                            class="btn btn-sm btn-primary"
                            id="btnPublish"
                            {{ empty($mutasi->printed_at) && !$isDeveloper ? 'disabled' : '' }}>
                        Publish
                    </button>
                @endif
            @endif

            {{-- PUBLISH --}}
            @if($isPublish && $canSent)
                <button type="button"
                        class="btn btn-sm btn-success"
                        id="btnSent">
                    <i class="fa fa-paper-plane"></i> Sent
                </button>
            @endif


        </div>
    </div>

    {{-- ================= FORM HEADER (COMPACT) ================= --}}
    <form>

    <div class="row">
        <div class="col-md-4 mb-2 d-flex align-items-center">
            <label class="me-2 mb-0" style="min-width:80px;">Kode</label>
            <input type="text"
                class="form-control"
                value="{{ $mutasi->kode }}"
                readonly>
        </div>

        <div class="col-md-4 mb-2 d-flex align-items-center">
            <label class="me-2 mb-0" style="min-width:80px;">Tanggal</label>
            <input type="text"
                class="form-control"
                value="{{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d M Y') }}"
                readonly>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-2 d-flex align-items-center">
            <label class="me-2 mb-0" style="min-width:80px;">Gudang Asal</label>
            <input type="text"
                class="form-control"
                value="{{ $mutasi->warehouse_from->name ?? '-' }}"
                readonly>
        </div>

        <div class="col-md-4 mb-2 d-flex align-items-center">
            <label class="me-2 mb-0" style="min-width:80px;">Type</label>
            <input type="text"
                class="form-control"
                value="{{ $mutasi->type() ?? '-' }}"
                readonly>
        </div>
    </div>

    </form>

    <hr>

    {{-- ================= MODE ACTIVE & PUBLISH ================= --}}
    @if($isActive || $isPublish)

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Product List</h6>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="table-light text-center">
                <tr>
                    <th width="40">#</th>
                    <th width="190">Product</th>
                    <th width="120">Kemasan</th>
                    <th width="50" class="text-right">Qty</th>
                    <th width="280">Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mutasi->details as $i => $d)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $d->product_packaging->product->code ?? '-' }} - {{ $d->product_packaging->product->name ?? '-' }}</td>
                    <td>{{ $d->product_packaging->packaging->pack_name ?? '-' }}</td>
                    <td class="text-end">{{ $d->qty }}</td>
                    <td>{{ $d->note ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @endif

    {{-- ================= MODE SENT (INPUT HARGA) ================= --}}
    @if($isSent && $canInputPrice)

    @php
        $priceDetails = $mutasi->details
            ->whereNull('price')
            ->whereNull('total_price')
            ->unique(function ($d) {
                return $d->product_packaging_id;
            });
    @endphp

    <!-- <form id="formUpdatePrice">

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Input Harga Produk</h6>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="table-light text-center">
                <tr>
                    <th>Product</th>
                    <th width="150">Kemasan</th>
                    <th width="100">Qty</th>
                    <th width="150">Harga</th>
                </tr>
            </thead>
            <tbody>

            @forelse($priceDetails as $d)
                <tr>
                    <td>{{ $d->product_packaging->product->name }}</td>
                    <td>{{ $d->product_packaging->packaging->name }}</td>
                    <td class="text-end">{{ $d->qty }}</td>
                    <td>
                        <input type="number"
                            name="price[{{ $d->id }}]"
                            class="form-control form-control-sm text-end"
                            min="0"
                            required>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        Semua produk sudah memiliki harga
                    </td>
                </tr>
            @endforelse

            </tbody>
        </table>
    </div>

    <div class="text-end mt-3">
        <button type="submit" class="btn btn-success btn-sm">
            <i class="fa fa-save"></i> Simpan Harga
        </button>
    </div>

    </form> -->
    @endif

<script>
    $(document)
        .off('click', '#btnBackToList')
        .on('click', '#btnBackToList', function () {

            exitLockedMode();

            loadFrameB(
                '{{ route("superuser.gudang.mutasi_showroom.list_partial") }}'
            );
    });

    $(document)
        .off('click', '#btnPrint')
        .on('click', '#btnPrint', function () {
    
            const mutasiId = {{ $mutasi->id }};
            const url = '{{ route("superuser.gudang.mutasi_showroom.print_pdf", $mutasi->id) }}';
    
            // 🔒 Tetap di mode SHOW
            uiState.mode = 'show';
    
            // 1. Buka PDF
            window.open(url, '_blank');
    
            // 2. Reload SHOW partial agar counter & tombol update
            setTimeout(() => {
                $('#frameBContent').load(
                    '{{ route("superuser.gudang.mutasi_showroom.show_partial", $mutasi->id) }}'
                );
            }, 800);
        });



    $(document).on('click', '#btnPublish', function () {

        Swal.fire({
            title: 'Publish Mutasi?',
            text: 'Data yang sudah dipublish tidak dapat diubah.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Publish'
        }).then((result) => {

            if (!result.isConfirmed) return;

            $.ajax({
                url: '{{ route("superuser.gudang.mutasi_showroom.publish", $mutasi->id) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (res) {
                    Swal.fire('Berhasil', res.message, 'success');

                    // reload show partial
                    $('#frameBContent').load(
                        '{{ route("superuser.gudang.mutasi_showroom.show_partial", $mutasi->id) }}'
                    );
                },
                error: function (xhr) {
                    Swal.fire(
                        'Gagal',
                        xhr.responseJSON?.message ?? 'Terjadi kesalahan',
                        'error'
                    );
                }
            });

        });
    });

    $(document).on('click', '#btnSent', function () {

        Swal.fire({
            title: 'Kirim Mutasi?',
            text: 'Setelah dikirim, data tidak dapat diubah.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim'
        }).then(function (result) {

            if (!result.value) return;

            $.ajax({
                url: '{{ route("superuser.gudang.mutasi_showroom.sent", $mutasi->id) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (res) {

                    Swal.fire(
                        'Berhasil',
                        res.message,
                        'success'
                    );

                    // reload show partial agar status & tombol update
                    uiState.mode = 'list';
                    expandFrameA();
                    unlockFrameANavigation();

                    $('#frameBContent').load(
                        '{{ route("superuser.gudang.mutasi_showroom.list_partial") }}'
                    );
                },
                error: function (xhr) {

                    Swal.fire(
                        'Gagal',
                        xhr.responseJSON?.message ?? 'Terjadi kesalahan',
                        'error'
                    );
                }
            });

        });
    });
</script>
