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

    /* ================= MODAL FILTER CLEAN STYLE ================= */

    .filter-modal {
        border-radius: 18px;
        border: none;
    }

    .filter-modal .modal-header {
        padding: 18px 20px;
        border-bottom: 1px solid #f1f1f1;
    }

    .filter-modal .modal-title {
        font-size: 16px;
        font-weight: 600;
    }

    .filter-modal .modal-body {
        padding: 20px;
    }

    .filter-modal .modal-footer {
        padding: 16px 20px;
        border-top: 1px solid #f1f1f1;
    }

    /* Card-style field */
    .filter-card {
        margin-bottom: 18px;
    }

    .filter-card label {
        font-size: 12px;
        font-weight: 500;
        color: #6c757d;
        margin-bottom: 6px;
        display: block;
    }

    /* Input style */
    .filter-modal .form-control,
    .filter-modal .form-select,
    .filter-modal .select2-selection {
        border-radius: 12px !important;
        min-height: 46px;
        border: 1px solid #e5e7eb;
        font-size: 14px;
        box-shadow: none !important;
    }

    .filter-modal .form-control:focus,
    .filter-modal .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 2px rgba(13,110,253,0.15);
    }

    /* Select2 fix */
    .select2-container {
        width: 100% !important;
    }

    /* Mobile fullscreen */
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 0;
        }

        .filter-modal {
            border-radius: 0;
            height: 100vh;
        }

        .filter-modal .modal-body {
            overflow-y: auto;
        }
    }

    .filter-section {
        padding: 15px;
        background: #f8f9fb;
        border-radius: 14px;
    }

    .section-label {
        font-size: 12px;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 6px;
        display: block;
    }

    .quick-date-wrapper {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .quick-date-btn {
        border: 1px solid #dee2e6;
        background: #fff;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        cursor: pointer;
        transition: all .2s;
    }

    .quick-date-btn:hover {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
        <div class="modal-content filter-modal">

            <div class="modal-header">
                <h5 class="modal-title">Filter Mutasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="modal-body">
                    <!-- DATE RANGE + QUICK -->
                    <div class="filter-section mb-4">
                        <input type="text"
                            class="form-control mb-2"
                            id="filterDateRange"
                            placeholder="Pilih rentang tanggal">

                        <div class="quick-date-wrapper">
                            <button type="button" class="quick-date-btn" data-range="today">Today</button>
                            <button type="button" class="quick-date-btn" data-range="yesterday">Yesterday</button>
                            <button type="button" class="quick-date-btn" data-range="7">Last 7 Days</button>
                            <button type="button" class="quick-date-btn" data-range="30">Last 30 Days</button>
                        </div>
                    </div>

                    <!-- GRID FILTER -->
                    <div class="row g-3">
                        {{-- KODE --}}
                        <div class="col-md-6">
                            <div class="filter-card">
                                <input type="text"
                                    class="form-control"
                                    id="filterKode" placeholder="Cari Kode Mutasi">
                            </div>
                        </div>

                        {{-- TYPE (SHOWROOM ONLY) --}}
                        <div class="col-md-6 showroom-only d-none">
                            <div class="filter-card">
                                <select class="form-select js-select2" id="filterType">
                                    <option value="">Semua Type</option>
                                    <option value="1">SHOWROOM</option>
                                    <option value="2">FREE PRODUCT</option>
                                    <option value="3">KLAIM</option>
                                    <option value="4">BUNDLING</option>
                                    <option value="5">PROMOSI</option>
                                </select>
                            </div>
                        </div>

                        {{-- CUSTOMER FULL WIDTH --}}
                        <div class="col-12 showroom-only d-none">
                            <div class="filter-card">
                                <select class="form-select js-select2" id="filterCustomer">
                                    <option value="">Semua Customer</option>
                                    @foreach($customers ?? [] as $cust)
                                        <option value="{{ $cust->id }}">
                                            {{ $cust->name }} {{ $cust->text_kota ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- GUDANG --}}
                        <div class="col-md-6 gudang-only d-none">
                            <div class="filter-card">
                                <select class="form-select js-select2" id="filterGudangTujuan">
                                    <option value="">Semua Gudang</option>
                                    @foreach($warehouses ?? [] as $wh)
                                        <option value="{{ $wh->id }}">
                                            {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-outline-secondary"
                        id="resetFilter">
                    Reset
                </button>

                <button type="button"
                        class="btn btn-primary"
                        id="applyFilter">
                    Terapkan
                </button>
            </div>

        </div>
    </div>
</div>

<!-- MODAL BUKTI GAMBAR -->
 <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Bukti Barang Diambil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <img id="previewImage"
                     src=""
                     class="img-fluid rounded shadow-sm"
                     style="max-height:70vh;">
            </div>

        </div>
    </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')

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
    // Inisialisasi modal
    filterModal = new bootstrap.Modal(
        document.getElementById('modalFilterSelesai')
    );

    // Inisialisasi select2 saat modal shown
    $('#modalFilterSelesai').on('shown.bs.modal', function () {
        $('.js-select2').select2({
            width: '100%',
            dropdownParent: $('#modalFilterSelesai'),
            allowClear: true
        });
    });

    // Inisialisasi flatpickr
    if (!dateRangePicker) {
        dateRangePicker = flatpickr("#filterDateRange", {
            mode: "range",
            dateFormat: "Y-m-d", 
            allowInput: false,
            altInput: true,      
            altFormat: "d-m-Y",  
            // Tambahkan ini agar saat clear benar-benar bersih
            onClose: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 1) {
                    instance.setDate([selectedDates[0], selectedDates[0]], true);
                }
            }
        });
    }

    // Load mutasi awal
    const activeBtn = $('.mutasi-type-btn.active').data('type');
    if (activeBtn) {
        CURRENT_MUTASI_TYPE = activeBtn;
        loadMutasiTable(activeBtn); // 🔥 penting supaya langsung load
    }
});

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

// Fungsi format tanggal ke dd-mm-yyyy
function formatDMY(date) {
    const d = date.getDate().toString().padStart(2,'0');
    const m = (date.getMonth()+1).toString().padStart(2,'0');
    const y = date.getFullYear();
    return `${d}-${m}-${y}`;
}

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
    let imageFile = $('#image')[0].files[0];

    if (!status) {
        Swal.fire({
            icon: 'warning',
            title: 'Status belum dipilih',
            text: 'Silakan pilih status barang terlebih dahulu.'
        });
        return;
    }

    // 🔴 VALIDASI WAJIB UPLOAD JIKA DIAMBIL
    if (status == '2' && !imageFile) {
        Swal.fire({
            icon: 'warning',
            title: 'Upload wajib',
            text: 'Jika status DIAMBIL, wajib upload bukti gambar.'
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

        let formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('mutasi_id', $('input[name=mutasi_id]').val());
        formData.append('status_barang', status);
        formData.append('type', CURRENT_MUTASI_TYPE);

        if (imageFile) {
            formData.append('image', imageFile);
        }

        $.ajax({
            url: '{{ route("superuser.gudang.sj_mutasi_internal.step3Update") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function (res) {

                Swal.close();

                if(!res.success) return;

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Status mutasi berhasil diperbarui.'
                });

                refreshMutasiTabs();

                if (res.to_selesai) {

                    hotReloadTab('selesai');

                    $('.tab-btn').removeClass('active');
                    $('.tab-btn[data-tab="selesai"]').addClass('active');

                    $('.mutasi-tab-content').addClass('d-none');
                    $('#tab-selesai').removeClass('d-none');

                    resetFrameB();

                } else {

                    hotReloadTab(CURRENT_TAB);
                    resetFrameB();
                }
            },

            error: function(xhr){

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
            }
        });

    });
});

$(document).on('change', '#status_barang', function () {

    if ($(this).val() == '2') {
        $('#uploadWrapper').removeClass('d-none');
    } else {
        $('#uploadWrapper').addClass('d-none');
        $('#image').val('');
    }

});

$(document).ready(function () {

    function toggleUpload() {
        if ($('#status_barang').val() == '2') {
            $('#uploadWrapper').removeClass('d-none');
        } else {
            $('#uploadWrapper').addClass('d-none');
            $('#image').val('');
        }
    }

    // saat change
    $(document).on('change', '#status_barang', function () {
        toggleUpload();
    });

    // saat pertama kali load
    toggleUpload();

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

/* =========================================================
   APPLY FILTER
========================================================= */
$('#applyFilter').on('click', function () {
    // Ambil semua value
    let data = {
        type: CURRENT_MUTASI_TYPE,
        kode: $('#filterKode').val() || null,
        customer_id: $('#filterCustomer').val() || null,
        type_mutasi: $('#filterType').val() || null,
        warehouse_to: $('#filterGudangTujuan').val() || null,
        date_from: null,
        date_to: null
    };

    // Ambil tanggal hanya jika ada yang dipilih di Flatpickr
    if (dateRangePicker && dateRangePicker.selectedDates.length > 0) {
        data.date_from = dateRangePicker.formatDate(dateRangePicker.selectedDates[0], "Y-m-d");
        data.date_to = dateRangePicker.selectedDates[1] 
            ? dateRangePicker.formatDate(dateRangePicker.selectedDates[1], "Y-m-d") 
            : data.date_from;
    }

    $.ajax({
        url: "{{ route('superuser.gudang.sj_mutasi_internal.filterSelesai') }}",
        type: "GET",
        data: data, // Kirim objek data yang fleksibel
        success: function (response) {
            $('#tab-selesai').html(response);
            filterModal.hide();
        }
    });
});

$('#resetFilter').on('click', function () {
    $('#filterKode').val('');
    $('#filterDateRange').val('');
    $('#filterCustomer').val('');
    $('#filterType').val('');
    $('#filterGudangTujuan').val('');

    if (dateRangePicker) {
        dateRangePicker.clear();
    }
});

$(document).on('click', '#btnAdvanceSearch', function () {
    updateFilterFields();   // 🔥 tambahkan ini
    filterModal.show();
});

function updateFilterFields() {

    $('.showroom-only').addClass('d-none');
    $('.gudang-only').addClass('d-none');

    if (CURRENT_MUTASI_TYPE === 'showroom') {
        $('.showroom-only').removeClass('d-none');
    }

    if (CURRENT_MUTASI_TYPE === 'gudang') {
        $('.gudang-only').removeClass('d-none');
    }
}

$(document).on('click', '.quick-date-btn', function (e) {
    e.preventDefault(); // Mencegah modal tertutup atau reload jika tombol bertipe submit
    
    if (!dateRangePicker) return;

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let start = new Date(today);
    let end = new Date(today);

    const range = $(this).data('range').toString();

    switch(range) {
        case 'today':
            break;
        case 'yesterday':
            start.setDate(today.getDate() - 1);
            end.setDate(today.getDate() - 1);
            break;
        case '7':
            start.setDate(today.getDate() - 6);
            break;
        case '30':
            start.setDate(today.getDate() - 29);
            break;
        default:
            return;
    }

    // 1. Set tanggal langsung ke instance Flatpickr
    // true di argumen kedua memerintahkan Flatpickr untuk mentrigger event 'Change'
    dateRangePicker.setDate([start, end], true);

    // 2. FORCE REFRESH (Penting jika altInput tidak mau update)
    // Kita ambil string yang sudah diformat dari instance dan masukkan ke input manual jika perlu
    const formattedDate = dateRangePicker.altInput.value;
    if (formattedDate) {
        $(dateRangePicker.altInput).val(formattedDate);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const imageModal = document.getElementById('imageModal');

    imageModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const imageUrl = button.getAttribute('data-image');
        const img = document.getElementById('previewImage');
        img.src = imageUrl;
    });
});
</script>
@endpush