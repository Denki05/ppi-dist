<style>
    .table { table-layout: fixed; }
    .table-sm td { padding: 4px 6px; vertical-align: middle; }
    .clickable-row:hover { background-color: #f1f3f5; cursor: pointer; }
    .table-success, .table-success td { background-color: #d4edda !important; }
    
    .compact-table .product-cell { max-width: 170px; width: 170px; padding-left: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.9rem; }
    .compact-table td:last-child { padding-right: 0; }

    .brand-header { font-size: 1.1rem; font-weight: bold; padding: 6px 10px; background: #e9ecef; border-left: 4px solid #28a745; margin-top: 12px; }
    .mutasi-card { border: 1px solid #dee2e6; border-radius: 6px; margin-bottom: 10px; overflow: hidden; margin-left: 12px; }
    .mutasi-header { background: #f8f9fa; border-left: 3px solid #ced4da; padding: 6px 10px; font-size: 0.85rem; display: flex; justify-content: space-between; font-weight: 600; cursor:pointer; }
    .mutasi-header:hover { background-color: #eef2f6; }

    .item-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; border-top: 1px solid #eee; }
    .item-row:hover { background: #f1f3f5; cursor: pointer; }
    .item-row.table-success { background-color: #d4edda !important; }
    .item-product { font-size: 0.85rem; line-height: 1.25; }
    .item-product strong { font-size: 0.8rem; }
    .item-qty { font-weight: bold; min-width: 40px; text-align: right; }
    .brand-body { display: none; }
    
    .mutasi-body { margin-left: 12px; }

    .compact-table th, .compact-table td { padding: 2px 4px; font-size: 0.8rem; }
    .compact-table th { font-weight: 600; color: #6c757d; }
    .compact-table td.product-cell { padding-left: 0; }
    .compact-table td:last-child { padding-right: 0; }
    .compact-table th:first-child, .compact-table td:first-child { text-align: left !important; padding-left: 4px; }
    .compact-table th:last-child, .compact-table td:last-child { text-align: right !important; padding-right: 8px; }
    .compact-table { table-layout: auto !important; width: auto !important; }
    .sticky-kalkulasi {
        position: sticky;
        top: 0;
        z-index: 1050;
        background: #fff;
        padding: 2px 0;              /* DIPERKECIL */
        border-bottom: 1px solid #e5e7eb;
    }
    .brand-total-qty {
        font-size: 0.75rem;
        padding: 4px 8px;
        letter-spacing: 0.3px;
    }



</style>

<div id="update-price-wrapper">
    <!-- <h5 class="mb-2">Update Harga Produk</h5> -->

    {{-- KURS GLOBAL --}}
    <div class="row mb-2 sticky-kalkulasi align-items-center gx-2">
        <div class="col-md-2 text-end fw-semibold">
            Kurs (Global)
        </div>

        <div class="col-md-3">
            <input type="number"
                id="globalKurs"
                class="form-control form-control-sm py-1">
        </div>

        <div class="col-md-3 d-flex gap-2">
            <!-- KALKULASI GLOBAL DIHILANGKAN -->
            <button class="btn btn-sm btn-success px-3" id="btnSettle" disabled>
                Settle
            </button>

            <button class="btn btn-sm btn-outline-warning" id="btnCancelUpdate">
                Cancel
            </button>
        </div>
    </div>

    @foreach($groups as $brand => $mutasis)
        @php
            $totalQtyBrand = $mutasis->flatten()->sum('qty');
        @endphp
        <div class="card mb-3">
            <div class="card-header brand-header d-flex justify-content-between align-items-center clickable-brand"
                data-target="#brand-{{ Str::slug($brand) }}">

                <span>{{ strtoupper($brand) }}</span>

                <div class="d-flex align-items-center gap-3">
                    <span class="brand-total-qty badge bg-secondary fw-semibold">
                        Total Qty: {{ number_format($totalQtyBrand, 2, ',', '.') }}
                    </span>

                    <span class="toggle-icon">▸</span>
                </div>
            </div>

            <div class="card-body p-2 brand-body" id="brand-{{ Str::slug($brand) }}">
                @foreach($mutasis as $kode => $items)
                    @php $mutasi = $items->first()->mutasi_showroom; @endphp
                    <div class="mutasi-card mb-2">
                        <div class="mutasi-header">
                            <strong>{{ $mutasi->tanggal->format('d/m/Y') }} - {{ $kode }}</strong>
                        </div>
                        <div class="mutasi-body pl-0 pr-2 pb-2">
                            <table class="table table-sm table-bordered compact-table mb-0">
                                <thead class="thead-light text-center">
                                    <tr>
                                        <th width="220">Produk</th>
                                        <th width="45">Qty</th>
                                        <th width="70">Price</th>
                                        <th width="60">Disc Awal</th>
                                        <th width="55">Disc %</th>
                                        <th width="60">Disc Akhir</th>
                                        <th width="75">Netto</th>
                                        <th width="90">Sub Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $i)
                                    @php
                                        $disc_awal = 0; $disc_percent = 0; $disc_akhir = 0;
                                        if($brand == 'Senses') { $disc_awal=2; $disc_percent=20; $disc_akhir=1.5; }
                                        if($brand == 'GCF') { $disc_awal=2; $disc_percent=10; $disc_akhir=6; }
                                        $price = $i->pl_usd - $disc_awal;
                                        $price -= $price*($disc_percent/100);
                                        $price -= $disc_akhir;
                                        $subtotal = max($price,0) * $i->qty;
                                    @endphp
                                    <tr data-id="{{ $i->id }}">
                                        <td class="product-cell">
                                            {{ $i->product_packaging->code }} - {{ $i->product_packaging->name }}
                                        </td>

                                        <td class="text-end qty">{{ $i->qty }}</td>

                                        <td>
                                            <input type="number"
                                                class="form-control form-control-sm pl_usd text-end bg-light"
                                                value="{{ $i->product_packaging->price }}"
                                                >
                                        </td>

                                        <td>
                                            <input type="number"
                                                class="form-control form-control-sm disc_awal text-center bg-light"
                                                value="{{ $disc_awal }}"
                                                >
                                        </td>

                                        <td>
                                            <input type="number"
                                                class="form-control form-control-sm disc_percent text-center bg-light"
                                                value="{{ $disc_percent }}"
                                                >
                                        </td>

                                        <td>
                                            <input type="number"
                                                class="form-control form-control-sm disc_akhir text-center bg-light"
                                                value="{{ $disc_akhir }}"
                                                >
                                        </td>

                                        <!-- NETTO (USD) -->
                                        <td class="text-end netto">0.00</td>

                                        <!-- SUBTOTAL (IDR) -->
                                        <td class="text-end subtotal">0</td>
                                    </tr>
                                    @endforeach
                                </tbody>

                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="7" class="text-end">Grand Total (IDR)</td>
                                        <td class="text-end grand-total-idr">0</td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="d-flex justify-content-end gap-2 mt-2 mutasi-action">
                                <button type="button"
                                    class="btn btn-sm btn-primary btn-kalkulasi-group">
                                    Kalkulasi
                                </button>

                                <button type="button"
                                    class="btn btn-sm btn-success btn-save-group"
                                    disabled>
                                    Save
                                </button>

                                <button type="button"
                                    class="btn btn-sm btn-warning btn-revisi-group"
                                    disabled>
                                    Revisi
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<script>
let groupState = {};

/* ===================== UTIL ===================== */
function updateSettleButton(){
    let hasSaved = Object.values(groupState).some(g => g.saved === true);
    $('#btnSettle').prop('disabled', !hasSaved);
}

/* ===================== PER HITUNGAN ===================== */
function calculateRow(row){
    let qty = parseFloat(row.find('.qty').text()) || 0;
    let pl  = parseFloat(row.find('.pl_usd').val()) || 0;
    let da  = parseFloat(row.find('.disc_awal').val()) || 0;
    let dp  = parseFloat(row.find('.disc_percent').val()) || 0;
    let dak = parseFloat(row.find('.disc_akhir').val()) || 0;

    let price = pl - da;
    price -= price * (dp / 100);
    price -= dak;
    price = Math.max(price, 0);

    row.find('.netto').text(price.toFixed(2));

    return {
        netto: price,
        subtotalUsd: price * qty
    };
}

function recalcGroup(card){
    let kurs = parseFloat($('#globalKurs').val()) || 0;
    if(kurs <= 0){
        swalWarning('Kurs Tidak Valid','Isi kurs global terlebih dahulu');
        return false;
    }

    let kode = card.data('kode');
    let totalIdr = 0;
    let items = {};

    card.find('tbody tr[data-id]').each(function(){
        let row = $(this);
        let id  = row.data('id');

        let result = calculateRow(row);
        let subIdr = result.subtotalUsd * kurs;

        row.find('.subtotal').text(subIdr.toLocaleString('id-ID'));
        totalIdr += subIdr;

        items[id] = {
            kurs: kurs,
            pl_usd: parseFloat(row.find('.pl_usd').val()) || 0,
            disc_awal: parseFloat(row.find('.disc_awal').val()) || 0,
            disc_percent: parseFloat(row.find('.disc_percent').val()) || 0,
            disc_akhir: parseFloat(row.find('.disc_akhir').val()) || 0,
            netto: result.netto
        };
    });

    card.find('.grand-total-idr').text(totalIdr.toLocaleString('id-ID'));

    groupState[kode] = {
        calculated: true,
        saved: false,
        items: items
    };

    toggleButtons(card, 'calculated');
    return true;
}

/* ===================== STATE BUTTON ===================== */
function toggleButtons(card, state){
    let btnCalc = card.find('.btn-kalkulasi-group');
    let btnSave = card.find('.btn-save-group');
    let btnRev  = card.find('.btn-revisi-group');
    let inputs  = card.find('.pl_usd, .disc_awal, .disc_percent, .disc_akhir');

    if(state === 'init'){
        btnCalc.prop('disabled', false);
        btnSave.prop('disabled', true);
        btnRev.prop('disabled', true);
        inputs.prop('disabled', false);
        card.removeClass('table-success');
    }

    if(state === 'calculated'){
        btnCalc.prop('disabled', true);
        btnSave.prop('disabled', false);
        btnRev.prop('disabled', true);
        inputs.prop('disabled', false);
    }

    if(state === 'saved'){
        btnCalc.prop('disabled', true);
        btnSave.prop('disabled', true);
        btnRev.prop('disabled', false);
        inputs.prop('disabled', true);
        card.addClass('table-success');
    }

    if(state === 'revisi'){
        btnCalc.prop('disabled', false);
        btnSave.prop('disabled', true);
        btnRev.prop('disabled', true);
        inputs.prop('disabled', false);
        card.removeClass('table-success');
    }

    updateSettleButton();
}

/* ===================== INIT ===================== */
$(document).ready(function(){
    groupState = {};

    $('#btnSettle').prop('disabled', true);

    $('.mutasi-card').each(function () {
        let card = $(this);
        let kode = card.find('.mutasi-header strong').text().trim();

        card.attr('data-kode', kode);

        groupState[kode] = {
            calculated: false,
            saved: false,
            items: {}
        };

        toggleButtons(card, 'init');
    });

    /* ===================== SETTLE ===================== */
    $(document).on('click', '#btnSettle', function () {

        let payload = {};

        Object.keys(groupState).forEach(kode => {
            if (groupState[kode].saved) {
                payload[kode] = groupState[kode].items;
            }
        });

        if (Object.keys(payload).length === 0) {
            swalWarning('Tidak Ada Data', 'Belum ada group yang disimpan');
            return;
        }

        Swal.fire({
            icon: 'question',
            title: 'Konfirmasi Settle',
            text: 'Settle hanya untuk group yang sudah disimpan. Lanjutkan?',
            showCancelButton: true,
            confirmButtonText: 'Ya, Settle'
        }).then(res => {

            if (!res.isConfirmed) return;

            $.ajax({
                url: '{{ route("superuser.gudang.mutasi_showroom.settle_prices") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    items: payload
                },
                beforeSend() {
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                },
                success(res) {
                    Swal.close();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message || 'Settle berhasil'
                    }).then(() => {
                        loadFrameB('{{ route("superuser.gudang.mutasi_showroom.done_index") }}');
                    });
                },
                error(xhr) {
                    Swal.close();

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan server'
                    });
                }
            });
        });
    });

    /* ===================== COLLAPSE BRAND ===================== */
    $(document).on('click', '.clickable-brand', function () {
        const target = $($(this).data('target'));
    
        target.stop(true, true).slideToggle(150);
    
        $(this)
            .find('.toggle-icon')
            .text(target.is(':visible') ? '▾' : '▸');
    });

    $(document).on('click', '#btnCancelUpdate', function () {

        // Aktifkan menu PROSES
        $('.menu-btn').removeClass('active');
        $('.menu-btn[data-menu="process"]').addClass('active');

        // Expand Frame A kembali
        if (typeof expandFrameA === 'function') {
            expandFrameA();
        }

        // Load kembali Done Partial
        loadFrameB('{{ route("superuser.gudang.mutasi_showroom.done_index") }}');
    });

    $(document).on('click', '.btn-kalkulasi-group', function () {
        let card = $(this).closest('.mutasi-card');
        recalcGroup(card);
    });

    $(document).on('click', '.btn-save-group', function () {
        let card = $(this).closest('.mutasi-card');
        let kode = card.data('kode');

        if (!groupState[kode]?.calculated) {
            swalWarning('Belum Dikalkulasi', 'Lakukan kalkulasi terlebih dahulu');
            return;
        }

        groupState[kode].saved = true;
        toggleButtons(card, 'saved');
    });

    $(document).on('click', '.btn-revisi-group', function () {
        let card = $(this).closest('.mutasi-card');
        let kode = card.data('kode');

        groupState[kode].calculated = false;
        groupState[kode].saved = false;

        toggleButtons(card, 'revisi');
    });

});
</script>