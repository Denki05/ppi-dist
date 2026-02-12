@extends('superuser.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    body { background-color: #1f242a; }

    .crm-wrapper {
        max-width: 1050px; /* DITAMBAH */
        margin: 0 auto;
        height: calc(100vh - 90px);
    }

    .crm-row {
        display: flex;
        gap: 10px;
        height: 100%;
    }

    .card {
        border-radius: 14px;
        border: none;
        height: 100%;
        background: #fff;
    }

    /* FRAME A */
    .frame-a {
        flex: 0 0 250px; /* DITAMBAH */
        display: flex;
        flex-direction: column;
    }

    /* FRAME B */
    .frame-b {
        flex: 1;
        min-width: 0;
    }

    .frame-b .card-body {
        overflow-y: auto;
        padding-bottom: 80px;
    }

    .frame-title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    /* TABS */
    .mutasi-tabs {
        display: flex;
        gap: 6px;
        margin-bottom: 8px;
    }

    .mutasi-tabs .tab-btn {
        padding: 5px 12px;
        font-size: 12px;
        border-radius: 20px;
        border: 1px solid #ddd;
        cursor: pointer;
        background: #f8f9fa;
        color: #555;
    }

    .mutasi-tabs .tab-btn.active {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }

    /* TABLE */
    .mutasi-table {
        font-size: 12px;
        margin-bottom: 0;
    }

    .mutasi-table thead th {
        font-weight: 600;
        color: #666;
        background: #f8f9fa;
        border-bottom: 1px solid #ddd;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .mutasi-table tbody tr {
        cursor: pointer;
        transition: background .15s;
    }

    .mutasi-table tbody tr:hover {
        background: #f1f3f5;
    }

    .mutasi-table tbody tr.active {
        background: #e7f1ff;
    }

    .table-wrapper {
        overflow-y: auto;
        max-height: calc(100vh - 220px);
    }

    @media (max-width: 768px) {
        .crm-wrapper {
            max-width: 100%;
        }

        .frame-a {
            flex: 0 0 250px;
        }
    }

    .modal-content {
        border-radius: 18px;
    }

    .modal-header {
        padding-bottom: 0;
    }

    .modal-body {
        padding-top: 10px;
    }

    .modal-footer {
        padding-top: 0;
    }

</style>

<div class="container-fluid px-2">
    <div class="crm-wrapper">

        <div class="crm-row">

            {{-- ================= FRAME A ================= --}}
            <div class="frame-a">
                <div class="card">
                    <div class="card-body p-2 text-center">

                        <!-- <div class="frame-title mb-2">
                            Pilih Jenis Mutasi
                        </div> -->
                        <button 
                            class="btn btn-primary w-100 mb-2 text-left mutasi-type-btn active"
                            data-type="showroom"
                            id="btnMutasiShowroom">
                            Mutasi Showroom
                        </button>
                        
                        <button 
                            class="btn btn-outline-primary w-100 text-left mutasi-type-btn"
                            data-type="gudang"
                            id="btnMutasiGudang">
                            Mutasi Gudang Utama
                        </button>
                    </div>
                </div>
            </div>

            {{-- ================= FRAME B ================= --}}
            <div class="frame-b">
                <div class="card">
                    <div class="card-body">
                        {{-- INI TEMPAT TABLE + TAB --}}
                        <div id="frameBContent"></div>
                        {{-- INI KHUSUS DETAIL --}}
                        <div id="frameBDetail" class="d-none">
                            <div class="text-center text-muted mt-5">
                                <i class="bi bi-inbox" style="font-size:32px;"></i>
                                <h6 class="mt-2">Pilih mutasi dari tabel</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL FILTER SELESAI ================= -->
<div class="modal fade" id="modalFilterSelesai" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">Filter Mutasi Selesai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-4">
                    <label class="form-label text-muted small">Kode Mutasi</label>
                    <input type="text" class="form-control form-control-lg"
                           id="filterKode"
                           placeholder="Masukkan kode mutasi">
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small">Range Tanggal</label>
                    <input type="text"
                           class="form-control form-control-lg"
                           id="filterDateRange"
                           placeholder="Pilih rentang tanggal">
                </div>

            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light flex-fill me-2" id="resetFilter">
                    Reset
                </button>

                <button type="button" class="btn btn-primary flex-fill" id="applyFilter">
                    Terapkan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>

let CURRENT_MUTASI_TYPE = null;   // showroom | gudang
let CURRENT_TAB = 'aktif';        // aktif | belum | selesai

let filterModal = null;
let dateRangePicker = null;

$(document).ready(function () {
    filterModal = new bootstrap.Modal(
        document.getElementById('modalFilterSelesai')
    );

    dateRangePicker = flatpickr("#filterDateRange", {
        mode: "range",
        dateFormat: "Y-m-d"
    });


    const activeBtn = $('.mutasi-type-btn.active').data('type');

    if (activeBtn) {
        CURRENT_MUTASI_TYPE = activeBtn;
        loadMutasiTable(activeBtn); // 🔥 penting supaya langsung load
    }
});


/* =========================================================
   LOAD MUTASI (SHOWROOM / GUDANG)
========================================================= */
$(document).on('click', '.mutasi-type-btn', function () {

    // pindahkan active button
    $('.mutasi-type-btn')
        .removeClass('btn-primary active')
        .addClass('btn-outline-primary');

    $(this)
        .removeClass('btn-outline-primary')
        .addClass('btn-primary active');

    // ambil type dari data-type
    const type = $(this).data('type');

    CURRENT_MUTASI_TYPE = type;

    // reset tab default
    CURRENT_TAB = 'aktif';

    loadMutasiTable(type);
});


function loadMutasiTable(type) {
    resetFrameB(); // ðŸ”§ hanya reset detail

    $('#frameBContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border"></div>
            <p class="mt-2 mb-0">Memuat data...</p>
        </div>
    `);

    $.get(
        "{{ route('superuser.gudang.sj_mutasi_internal.table') }}",
        { type: type },
        function (html) {
            $('#frameBContent').html(html);
            $('.filter-wrapper').hide();
        }
    );
}

/* =========================================================
   TAB MUTASI (AKTIF / BELUM / SELESAI)
========================================================= */
$(document).on('click', '.tab-btn', function (e) {
    e.preventDefault();

    $('.tab-btn').removeClass('active');
    $(this).addClass('active');

    CURRENT_TAB = $(this).data('tab');

    const tab = CURRENT_TAB;

    $('.mutasi-tab-content').addClass('d-none');
    $('#tab-' + tab).removeClass('d-none');

    // ===== CONTROL FILTER BUTTON DI SINI =====
    if (tab === 'selesai') {
        $('.filter-wrapper').fadeIn(150);
    } else {
        $('.filter-wrapper').hide();
    }

    resetFrameB();
});

/* =========================================================
   CLICK ROW MUTASI (LOAD DETAIL)
========================================================= */
$(document).on('click', '.mutasi-table tbody tr', function () {
    $('.mutasi-table tbody tr').removeClass('active');
    $(this).addClass('active');

    const id = $(this).data('id');

    $('#frameBDetail').html(`
        <div class="text-center text-muted mt-5">
            <div class="spinner-border spinner-border-sm"></div>
            <p class="mt-2 mb-0">Memuat detail...</p>
        </div>
    `);

    $.get(
        "{{ route('superuser.gudang.sj_mutasi_internal.show', ':id') }}"
            .replace(':id', id),
        { type: CURRENT_MUTASI_TYPE }, // ðŸ”¥ kirim type
        function (html) {
            $('#frameBContent').addClass('d-none');
            $('#frameBDetail').removeClass('d-none');
            $('#frameBDetail').html(html);
        }
    );
});


/* =========================================================
   STEP 1 - SAVE
========================================================= */
$(document).on('submit', '#formStep1', function(e){
    e.preventDefault();

    let checkedCount = $('#formStep1 input[type="checkbox"]:checked').length;
    let totalCount = $('#formStep1 input[type="checkbox"]').length;

    if (checkedCount !== totalCount) {
        Swal.fire({
            icon: 'warning',
            title: 'Checklist belum lengkap',
            text: 'Semua produk dalam mutasi harus dicentang untuk melanjutkan.'
        });
        return;
    }

    Swal.fire({
        icon: 'question',
        title: 'Konfirmasi',
        text: `Anda akan memproses ${checkedCount} produk. Lanjutkan?`,
        showCancelButton: true,
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(
                '{{ route("superuser.gudang.sj_mutasi_internal.step1Save") }}',
                $('#formStep1').serialize() + `&type=${CURRENT_MUTASI_TYPE}`,
            )
            .done(function(res){
                if(res.success){
                    $('#frameBDetail').html(res.html);

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Checklist berhasil disimpan.'
                    });
                }
            })
            .fail(function(xhr){

                let message = 'Terjadi kesalahan sistem.';

                if(xhr.responseJSON && xhr.responseJSON.message){
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message
                });
            });
        }
    });
});

/* =========================================================
   STEP 1 - CANCEL
========================================================= */
$(document).on('click', '#cancelStep1', function(){
    Swal.fire({
        icon: 'warning',
        title: 'Batalkan proses?',
        text: 'Checklist akan dikosongkan.',
        showCancelButton: true,
        confirmButtonText: 'Ya, batalkan',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(
                '{{ route("superuser.gudang.sj_mutasi_internal.step1Cancel") }}',
                {
                    _token: '{{ csrf_token() }}',
                    mutasi_id: $('input[name=mutasi_id]').val(),
                    type: CURRENT_MUTASI_TYPE // ðŸ”¥ tambahkan type
                },
                function(res){
                    if(res.success){
                        $('#frameBDetail').html(res.html); // tetap pakai html baru
                    }
                }
            );
        }
    });
});

/* =========================================================
   STEP 2 - CANCEL
========================================================= */
$(document).on('click', '#cancelStep2', function () {
    Swal.fire({
        icon: 'warning',
        title: 'Batalkan Step 2?',
        text: 'Proses akan kembali ke Step 1.',
        showCancelButton: true,
        confirmButtonText: 'Ya, batalkan',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(
                '{{ route("superuser.gudang.sj_mutasi_internal.step2Cancel") }}',
                {
                    _token: '{{ csrf_token() }}',
                    mutasi_id: $('input[name=mutasi_id]').val(),
                    type: CURRENT_MUTASI_TYPE // ðŸ”¥ wajib dikirim
                },
                function () {
                    $('.mutasi-table tr.active').trigger('click');
                }
            )
            .done(function(res){
                if(res.success){
                    $('#frameBDetail').html(res.html);

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Berhasil dicancel.'
                    });
                }
            })
            .fail(function(xhr){

                let message = 'Terjadi kesalahan sistem.';

                if(xhr.responseJSON && xhr.responseJSON.message){
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message
                });
            });
        }
    });
});

/* =========================================================
   STEP 2 - NEXT
========================================================= */
$(document).on('click', '#nextStep2', function () {
    Swal.fire({
        icon: 'question',
        title: 'Lanjut ke Step 3?',
        text: 'Pastikan data sudah benar sebelum melanjutkan.',
        showCancelButton: true,
        confirmButtonText: 'Ya, lanjut',
        cancelButtonText: 'Batal'
    }).then((result) => {

        if (!result.isConfirmed) return;

        $.post(
            '{{ route("superuser.gudang.sj_mutasi_internal.step2Next") }}',
            {
                _token: '{{ csrf_token() }}',
                mutasi_id: $('input[name=mutasi_id]').val(),
                type: CURRENT_MUTASI_TYPE // ðŸ”¥ kirim type
            }
        ).done(function (res) {
            if (res.success) {
                const id = $('input[name=mutasi_id]').val();
                $.get(
                    "{{ route('superuser.gudang.sj_mutasi_internal.show', ':id') }}"
                        .replace(':id', id),
                    { type: CURRENT_MUTASI_TYPE },
                    function (html) {
                        $('#frameBDetail').html(html);
                    }
                );
            }
        });
    });
});

/* =========================================================
   STEP 3 - SAVE
========================================================= */
$(document).on('click', '#saveStep3', function () {

    let status = $('#status_barang').val();

    if (!status) {
        Swal.fire({
            icon: 'warning',
            title: 'Status belum dipilih',
            text: 'Silakan pilih status barang terlebih dahulu.'
        });
        return;
    }

    Swal.fire({
        icon: 'warning',
        title: 'Proses seluruh mutasi?',
        text: 'Aksi ini akan memproses SEMUA barang dalam satu kode mutasi.',
        showCancelButton: true,
        confirmButtonText: 'Ya, proses',
        cancelButtonText: 'Batal'
    }).then((result) => {

        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Memproses...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.post('{{ route("superuser.gudang.sj_mutasi_internal.step3Update") }}', {
            _token: '{{ csrf_token() }}',
            mutasi_id: $('input[name=mutasi_id]').val(),
            status_barang: status,
            type: CURRENT_MUTASI_TYPE
        })
        .done(function (res) {

            Swal.close();

            if(!res.success) return;

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Status mutasi berhasil diperbarui.'
            });

            refreshMutasiTabs();

            if (res.to_selesai) {

                // PINDAH KE TAB SELESAI
                hotReloadTab('selesai');

                $('.tab-btn').removeClass('active');
                $('.tab-btn[data-tab="selesai"]').addClass('active');

                $('.mutasi-tab-content').addClass('d-none');
                $('#tab-selesai').removeClass('d-none');

                resetFrameB();

            } else {

                // TETAP DI TAB SAAT INI (BELUM DIAMBIL)
                hotReloadTab(CURRENT_TAB);
                resetFrameB();
            }
        })
        .fail(function(xhr){

            Swal.close();

            let message = 'Terjadi kesalahan sistem.';
            if(xhr.responseJSON && xhr.responseJSON.message){
                message = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: message
            });
        });

    });
});

/* =========================================================
   RESET DETAIL FRAME (AMAN)
========================================================= */
function resetFrameB() {
    $('.mutasi-table tbody tr').removeClass('active');

    $('#frameBDetail').addClass('d-none').html(`
        <div class="text-center text-muted mt-5">
            <i class="bi bi-inbox" style="font-size:32px;"></i>
            <h6 class="mt-2">Pilih mutasi dari tabel</h6>
            <p class="mb-0">Detail akan ditampilkan di sini</p>
        </div>
    `);

    $('#frameBContent').removeClass('d-none'); // ðŸ”¥ BALIK KE LIST
}

/* =========================================================
   REFRESH TAB COUNT
========================================================= */
function refreshMutasiTabs() {
    if(!CURRENT_MUTASI_TYPE) return;

    $.get('{{ route("superuser.gudang.sj_mutasi_internal.refreshTabs") }}', { type: CURRENT_MUTASI_TYPE }, function (res) {
        $('#count-aktif').text(res.aktif);
        $('#count-belum').text(res.belum);
        $('#count-selesai').text(res.selesai);
    });
}

function hotReloadTab(tab) {
    if (!CURRENT_MUTASI_TYPE || !tab) return;

    const container = $('#tab-' + tab);

    container.html(`
        <div class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm"></div>
            <p class="mt-2 mb-0">Memuat ulang data...</p>
        </div>
    `);

    $.get(
        "{{ route('superuser.gudang.sj_mutasi_internal.table') }}",
        { type: CURRENT_MUTASI_TYPE },
        function (html) {
            const newContent = $(html).find('#tab-' + tab).html();
            container.html(newContent);
        }
    );
}

$('#applyFilter').on('click', function () {

    let kode = $('#filterKode').val();
    let dateRange = $('#filterDateRange').val();

    let dateFrom = null;
    let dateTo   = null;

    if (dateRange) {
        let dates = dateRange.split(" to ");
        dateFrom = dates[0] ?? null;
        dateTo   = dates[1] ?? dates[0];
    }

    $.ajax({
        url: "{{ route('superuser.gudang.sj_mutasi_internal.filterSelesai') }}",
        type: "GET",
        data: {
            kode: kode,
            date_from: dateFrom,
            date_to: dateTo,
            type: CURRENT_MUTASI_TYPE
        },
        success: function (response) {

            $('#tab-selesai').html(response);

            filterModal.hide();
        }
    });
});

$('#resetFilter').on('click', function () {
    $('#filterKode').val('');
    $('#filterDateRange').val('');

    if (dateRangePicker) {
        dateRangePicker.clear();
    }
});

$(document).on('click', '#btnAdvanceSearch', function () {
    filterModal.show();
});

</script>
@endpush