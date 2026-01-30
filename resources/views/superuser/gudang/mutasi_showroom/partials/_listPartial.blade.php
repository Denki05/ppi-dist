<style>
/* ===== MUTASI MODAL ===== */
.mutasi-modal {
    border-radius: 14px;
    overflow: hidden;
    max-height: 70vh;          /* modal tidak pernah terlalu tinggi */
    display: flex;
    flex-direction: column;`
}

.mutasi-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
    background: #fff;
}

.mutasi-modal-header,
.mutasi-modal-footer {
    flex-shrink: 0;
}

.mutasi-title {
    font-weight: 600;
    font-size: 15px;
}

.mutasi-modal-body {
    display: flex;
    align-items: stretch;
    background: #fff;
    display: flex;
    background: #fff;
    flex: 1;                   /* isi sisa ruang */
    overflow: hidden;
}

.nav-slice {
    width: 45px;
    display: flex;
    align-items: center;       /* ⬅️ INI yang bikin tombol di tengah */
    justify-content: center;
    background: #f8f9fa;
    border-left: 1px solid #dee2e6;
    border-right: 1px solid #dee2e6;
}

.mutasi-viewer {
    flex: 1;
    padding: 16px 20px;
    overflow-y: auto;
    max-height: 45vh;    
}

/* ===== NAV ARROW ===== */
.nav-arrow {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: #ffffff;
    box-shadow: 0 2px 6px rgba(0,0,0,.15);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.nav-arrow:hover {
    background: #e9ecef;
}

.nav-arrow.left {
    border-right: 1px solid #dee2e6;
}

.nav-arrow.right {
    border-left: 1px solid #dee2e6;
}

/* ===== FOOTER ===== */
.mutasi-modal-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-top: 1px solid #e5e7eb;
    background: #fff;
}

/* ===== MOBILE ===== */
@media (max-width: 768px) {
    .nav-slice {
        width: 44px;
        align-self: stretch;
    }
}

.mutasi-dialog {
    max-width: 600px;
}

.mutasi-modal-footer {
    padding: 10px 16px;
    border-top: 1px solid #e5e7eb;
    background: #fafafa;
}

/* =====================================================
   FIX KHUSUS MODAL KECIL (TIDAK MENGGANGGU MODAL LAIN)
===================================================== */

.modal-fix-center {
    margin: auto !important;
}

.modal.show .modal-fix-center {
    display: flex;
    align-items: center;
    min-height: calc(100vh - 1rem);
}

/* Class tambahan untuk mode PDF */
.modal-pdf-mode {
    max-width: 65% !important; /* Modal melebar hampir penuh */
    transition: all 0.3s ease-in-out;
}

.modal-pdf-mode .mutasi-modal {
    max-height: 95vh !important; /* Modal meninggi */
}

.modal-pdf-mode .mutasi-viewer {
    max-height: 75vh !important; /* Area iframe diperluas */
    padding: 0; /* Hilangkan padding agar PDF mentok ke pinggir */
    overflow: hidden; /* Iframe sudah punya scroll sendiri */
}

/* Memastikan iframe memenuhi area */
.pdf-iframe {
    width: 100%;
    height: 80vh;
    border: none;
    display: block;
}

/* Styling untuk Header Tabel */
.table thead th {
    font-size: 14px;
    font-weight: bold;
    color: #333; /* Opsional: warna teks sedikit lebih tegas */
}

/* Styling untuk Isi Tabel */
.table tbody td {
    font-size: 14px;
}

/* Khusus untuk Badge status agar font-nya tidak terlalu kecil/besar */
.table tbody td .badge {
    font-size: 11px; /* Badge biasanya sedikit lebih kecil dari teks biasa */
}

/* Merapikan Select2 di dalam Modal */
.select2-container--default .select2-selection--single {
    height: 38px !important; /* Sesuaikan dengan tinggi form-control-sm/default */
    padding: 5px;
    border: 1px solid #dee2e6;
}

.select2-container {
    display: block;
}

.select2-dropdown {
    z-index: 1061 !important;
}

/* ================= RANGE MONTH FIX ================= */

/* Container header kiri (judul + periode) */
.mutasi-header-left {
    display: flex;
    align-items: center;
    gap: 12px; /* jarak judul ke periode */
}

/* Label periode kecil & kalem */
.mutasi-period-label {
    font-size: 13px;
    color: #6c757d;
    white-space: nowrap;
}

/* Wrapper input agar tidak melebar */
.range-month-wrapper {
    width: 80px; /* pas untuk "Jan 2026" */
}

/* Input flatpickr */
.range-month-wrapper .flatpickr-input {
    width: 100% !important;
    text-align: center;
    padding: 4px 6px;
    font-size: 12px;
    cursor: pointer;
}

/* ================= FIX SCROLL HORIZONTAL MODAL ================= */

/* Pastikan tidak ada overflow horizontal */
.modal,
.modal-dialog,
.modal-content,
.modal-body {
    overflow-x: hidden !important;
}

/* Netralisir efek negatif margin bootstrap row di modal */
.modal-body .row {
    margin-left: 0;
    margin-right: 0;
}

/* Pastikan kolom tidak melebar */
.modal-body [class^="col-"],
.modal-body [class*=" col-"] {
    padding-left: 8px;
    padding-right: 8px;
}

/* Select2 jangan pernah lebih lebar dari parent */
.select2-container {
    max-width: 100% !important;
    box-sizing: border-box;
}

/* ================= SELECT2 BORDER FIX ================= */

.select2-container--bootstrap-5 .select2-selection {
    border: 1px solid #dee2e6 !important;
    border-radius: 6px;
    min-height: 38px;
    padding: 4px 8px;
    background-color: #fff;
}

/* Hover */
.select2-container--bootstrap-5 .select2-selection:hover {
    border-color: #adb5bd;
}

/* Focus */
.select2-container--bootstrap-5.select2-container--focus .select2-selection {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.15rem rgba(13,110,253,.25);
}

/* Clear icon agar sejajar */
.select2-container--bootstrap-5 .select2-selection__clear {
    margin-right: 6px;
}

/* Pastikan dropdown Select2 selalu di atas modal */
.select2-container--open {
    z-index: 9999999 !important;
}

.select2-container--bootstrap-5 .select2-dropdown {
    border: 1px solid #dee2e6;
    z-index: 9999999;
}

/* Memperbaiki tampilan box select2 agar lebih rapi di modal */
.select2-container--bootstrap-5 .select2-selection {
    min-height: 38px !important;
    display: flex;
    align-items: center;
}

/* =====================================================
   COMPACT MODE – CREATE MUTASI MODAL
===================================================== */

#modalCreateMutasi .modal-dialog {
    max-width: 480px;
}

#modalCreateMutasi .modal-content {
    border-radius: 12px;
}

/* Header lebih tipis */
#modalCreateMutasi .modal-header {
    padding: 10px 14px;
}

/* Body dipadatkan */
#modalCreateMutasi .modal-body {
    padding: 12px 14px;
    overflow: visible !important; 
    min-height: 250px;
}

/* Footer tipis */
#modalCreateMutasi .modal-footer {
    padding: 10px 14px;
}

/* Row jadi rapat */
#modalCreateMutasi .row {
    margin-bottom: 8px !important;
}

/* Label kiri lebih kecil & sejajar */
#modalCreateMutasi .col-4 {
    font-size: 13px;
    color: #6c757d;
    padding-top: 6px;
}

/* Select2 lebih compact */
#modalCreateMutasi .select2-container--bootstrap-5 .select2-selection {
    min-height: 34px !important;
    padding: 4px 8px;
    font-size: 14px;
}

/* Hilangkan scroll vertikal kosong */
#modalCreateMutasi .modal-dialog-scrollable .modal-body {
    overflow-y: visible;
}

#modalCreateMutasi
.select2-container--bootstrap-5 .select2-selection {
    min-height: 34px;
    padding: 4px 8px;
    font-size: 14px;
}
</style>

@php
    $canCreate = in_array(auth()->id(), [1, 33]);
@endphp

{{-- ================= HEADER ================= --}}
<div class="d-flex justify-content-between align-items-center mb-3">

    {{-- KIRI: Judul + Periode --}}
    <div class="mutasi-header-left">
        <h5 class="mb-0">Daftar Mutasi Showroom</h5>

        <!-- <span class="mutasi-period-label">Periode :</span> -->

        <div class="range-month-wrapper">
            <input type="text"
                   id="rangePickerPartial"
                   class="form-control form-control-sm bg-white"
                   placeholder="Jan 2026">
        </div>
    </div>

    {{-- KANAN: Tombol Create --}}
    @if($canCreate)
        <button id="btnCreateMutasi"
                class="btn btn-sm btn-primary"
                data-bs-toggle="tooltip"
                title="Create Mutasi">
            <i class="bi bi-plus-lg"></i>
        </button>
    @endif
</div>

{{-- ================= TABLE ================= --}}
<div class="table-responsive">
    <table class="table table-hover table-sm align-middle mb-0">
        <thead>
        <tr>
            <th width="10">#</th>
            <th>Tanggal - Kode Mutasi</th>
            <th>Brand</th>
            <th>Type</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @forelse($mutasi_showrooms as $index => $row)
            <tr class="mutasi-row"
                data-id="{{ $row->id }}"
                data-kode="{{ $row->kode }}"
                style="cursor:pointer;">
                <td>{{ $mutasi_showrooms->firstItem() + $index }}</td>
                <td>
                    {{ \Carbon\Carbon::parse($row->tanggal)->format('d M Y') }} - {{ $row->kode }}
                </td>
                <td>{{ $row->brand_name }}</td>
                <td>{{ $row->type() }}</td>
                <td>
                    {{ $row->status() }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                    Tidak ada data mutasi ditemukan
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{-- ================= PAGINATION ================= --}}
    @if($mutasi_showrooms->hasPages())
        <div class="d-flex justify-content-center align-items-center mt-3 gap-2 pagination">
            <a href="{{ $mutasi_showrooms->url(1) }}"
               class="btn btn-sm btn-outline-secondary {{ $mutasi_showrooms->onFirstPage() ? 'disabled' : '' }}">
                &laquo;
            </a>

            <a href="{{ $mutasi_showrooms->previousPageUrl() }}"
               class="btn btn-sm btn-outline-secondary {{ $mutasi_showrooms->onFirstPage() ? 'disabled' : '' }}">
                &lsaquo;
            </a>

            <span class="text-muted">
                {{ $mutasi_showrooms->currentPage() }} / {{ $mutasi_showrooms->lastPage() }}
            </span>

            <a href="{{ $mutasi_showrooms->nextPageUrl() }}"
               class="btn btn-sm btn-outline-secondary {{ !$mutasi_showrooms->hasMorePages() ? 'disabled' : '' }}">
                &rsaquo;
            </a>

            <a href="{{ $mutasi_showrooms->url($mutasi_showrooms->lastPage()) }}"
               class="btn btn-sm btn-outline-secondary {{ !$mutasi_showrooms->hasMorePages() ? 'disabled' : '' }}">
                &raquo;
            </a>
        </div>
    @endif
</div>

{{-- ================= MODAL VIEWER ================= --}}
<div class="modal fade" id="mutasiViewer" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mutasi-dialog">
        <div class="modal-content mutasi-modal">

            {{-- HEADER --}}
            <div class="mutasi-modal-header">
                <div class="mutasi-title">
                    <span>Detail Mutasi</span>
                    <span class="text-muted ms-2" id="mutasiKode" style="font-size:13px;"></span>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- BODY --}}
            <div class="mutasi-modal-body">

                {{-- NAV LEFT --}}
                <div class="nav-slice">
                    <button class="nav-arrow" id="prevMutasi">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                </div>

                {{-- CONTENT --}}
                <div class="mutasi-viewer" id="viewerContent">
                    <div class="text-center py-5">
                        <div class="spinner-border"></div>
                    </div>
                </div>

                {{-- NAV RIGHT --}}
                <div class="nav-slice">
                    <button class="nav-arrow" id="nextMutasi">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>

            </div>

            {{-- FOOTER --}}
            <div class="mutasi-modal-footer d-flex justify-content-end gap-2">
                @if(in_array(auth()->id(), [1, 31, 36]))
                    <button type="button"
                            class="btn btn-primary"
                            id="btnAction"
                            style="display:none;"
                            data-step="process">
                        Cetak
                    </button>
                @endif
                <button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCreateMutasi" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mutasi-dialog">
    <div class="modal-content">
      {{-- HEADER --}}
      <div class="modal-header">
        <h6 class="mb-0">Create Mutasi</h6>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      {{-- BODY --}}
      <div class="modal-body">

        {{-- BRAND --}}
        <div class="row align-items-center mb-2">
          <div class="col-4 text-muted small">Brand</div>
          <div class="col-8">
            <select id="selectBrand" class="form-control select2">
              <option value="">Pilih Brand</option>
              @foreach($brands as $b)
                <option value="{{ $b->brand_name }}">{{ $b->brand_name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- TYPE --}}
        <div class="row align-items-center mb-2">
          <div class="col-4 text-muted small">Type</div>
          <div class="col-8">
            <select id="selectType" class="form-control select2">
              <option value="">Pilih Type</option>
              @foreach($types as $key => $val)
                <option value="{{ $val }}">{{ $key }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- CUSTOMER (HIDDEN DEFAULT) --}}
        <div class="row align-items-center mb-2 d-none" id="customerWrapper">
          <div class="col-4 text-muted small">Customer</div>
          <div class="col-8">
            <select id="selectCustomer" class="form-control select2">
              <option value="">Pilih Customer</option>
              @foreach($customerAddresses as $cust)
                <option value="{{ $cust->id }}">
                  {{ $cust->name }} - {{ $cust->text_kota }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

      </div>

      {{-- FOOTER --}}
      <div class="modal-footer justify-content-end">
        <button class="btn btn-primary" id="btnSubmitMutasi">
          Lanjut
        </button>
      </div>

    </div>
  </div>
</div>

<script>
    /* ================= GLOBAL STATE ================= */
    window.mutasiIds = @json($mutasi_showrooms->pluck('id'));
    let currentIndex = null;
    window.rangePickerInstance = null;

    /* ================= HELPER DATE (WAJIB GLOBAL) ================= */
    function isValidYmd(dateStr) {
        if (!dateStr) return false;
        return /^\d{4}-\d{2}-\d{2}$/.test(dateStr);
    }

    function parseYmdToLocal(dateStr) {
        if (!dateStr) return null;
        const parts = dateStr.split('-');
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function formatDateLocal(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    /* ================= FILTER STATE ================= */
    let storedFilter = window.mutasiFilterStore.load();

    if (
        storedFilter &&
        (!isValidYmd(storedFilter.start_date) ||
         !isValidYmd(storedFilter.end_date))
    ) {
        window.mutasiFilterStore.clear();
        storedFilter = null;
    }

    let filterState = {
        start_date: storedFilter ? storedFilter.start_date : "{{ $rangeStart }}",
        end_date: storedFilter ? storedFilter.end_date : "{{ $rangeEnd }}",
        status: storedFilter ? storedFilter.status : "{{ $statusSelected ?? '' }}"
    };

    /* ================= RANGE PICKER (FIX UTAMA) ================= */
    window.initRangePicker = function () {
        const pickerElem = document.querySelector("#rangePickerPartial");
        if (!pickerElem) return;

        if (window.rangePickerInstance) {
            window.rangePickerInstance.destroy();
            window.rangePickerInstance = null;
        }

        const startDate = parseYmdToLocal(filterState.start_date);
        const endDate   = parseYmdToLocal(filterState.end_date);

        window.rangePickerInstance = flatpickr(pickerElem, {
            mode: "range",

            // === FORMAT INTERNAL (UNTUK FILTER & BACKEND) ===
            dateFormat: "Y-m-d",

            // === FORMAT TAMPILAN (UNTUK USER) ===
            altInput: true,
            altFormat: "M Y",        // Jan 2026
            altInputClass: "form-control",

            defaultDate: [startDate, endDate],
            allowInput: false,
            static: true,

            onReady(selectedDates, dateStr, instance) {
                // Saat load awal → tampilkan "Jan 2026"
                if (selectedDates.length) {
                    instance.altInput.value = instance.formatDate(
                        selectedDates[0],
                        "M Y"
                    );
                }
            },

            onChange(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    filterState.start_date = formatDateLocal(selectedDates[0]);
                    filterState.end_date   = formatDateLocal(selectedDates[1]);

                    // Simpan ke storage
                    window.mutasiFilterStore.save(filterState);

                    // Set tampilan ringkas lagi
                    instance.altInput.value =
                        instance.formatDate(selectedDates[0], "M Y");

                    reloadPartialList();
                }
            }
        });
    };

    /* ================= AJAX RELOAD ================= */
    window.reloadPartialList = function (extraUrl = null) {
        let params = {
            start_date: filterState.start_date,
            end_date: filterState.end_date,
            status: filterState.status
        };

        let url = extraUrl
            ? extraUrl
            : '{{ route("superuser.gudang.mutasi_showroom.list_partial") }}?' + $.param(params);

        if (typeof loadFrameB === "function") {
            loadFrameB(url, afterReload);
        } else {
            $('#containerList').load(url, afterReload);
        }
    };

    window.afterReload = function () {
        // 1. Update list ID untuk navigasi modal
        window.mutasiIds = $('.mutasi-row').map(function () {
            return $(this).data('id');
        }).get();

        // 2. RE-INITIALIZE Range Picker
        initRangePicker();
    };


    /* ================= DOCUMENT READY ================= */
    $(function () {

        initRangePicker();

        /* ================= FILTER BUTTON ================= */
        $(document).off('click', '#filterInProses').on('click', '#filterInProses', function () {
            filterState.status = filterState.status === 'process' ? '' : 'process';
            window.mutasiFilterStore.save(filterState);
            reloadPartialList();
        });

        $(document).off('click', '#filterSettle').on('click', '#filterSettle', function () {
            filterState.status = filterState.status === 'settle' ? '' : 'settle';
            window.mutasiFilterStore.save(filterState);
            reloadPartialList();
        });

        /* ================= ROW CLICK (VIEWER) ================= */
        $(document).off('click', '.mutasi-row').on('click', '.mutasi-row', function () {
            const id   = $(this).data('id');
            const kode = $(this).data('kode');

            currentIndex = window.mutasiIds.indexOf(id);

            if (currentIndex !== -1) {
                $('#mutasiKode').text(`#${kode}`);
                openViewer();
            }
        });

        function updateHeader() {
            const row = $('.mutasi-row').eq(currentIndex);
            $('#mutasiKode').text('#' + row.data('kode'));
        }

        function updateNav() {
            $('#prevMutasi').prop('disabled', currentIndex <= 0);
            $('#nextMutasi').prop('disabled', currentIndex >= window.mutasiIds.length - 1);
        }

        function spinnerHtml() {
            return `
                <div class="text-center py-5">
                    <div class="spinner-border"></div>
                </div>
            `;
        }

        /* ====================== OPEN VIEWER ====================== */
        function openViewer() {
            const id = window.mutasiIds[currentIndex];
            const modalElem = $('#mutasiViewer');
            const modal = bootstrap.Modal.getOrCreateInstance(modalElem);

            $('.mutasi-dialog').removeClass('modal-pdf-mode');

            modal.show();
            $('#viewerContent').html(spinnerHtml());

            $.get(
                '{{ route("superuser.gudang.mutasi_showroom.show_partial", ":id") }}'.replace(':id', id),
                function (res) {
                    $('#viewerContent').html(res);
                    updateHeader();
                    updateNav();

                    // === SET BUTTON ACTION ===
                    const status = $('#status_viewer').val();
                    const btn    = $('#btnAction');

                    if (status === 'ACTIVE') {
                        // NORMAL (BELUM CETAK)
                        btn
                            .show()
                            .text('Cetak')
                            .removeClass('btn-success')
                            .addClass('btn-primary')
                            .attr('data-step', 'process')
                            .attr('data-printed', '0')
                            .attr('data-id', id);

                        // Hapus tombol Proses jika ada sebelumnya
                        $('#btnProses').remove();
                    } else {
                        btn.hide();
                        $('#btnProses').remove();
                    }
                }
            );
        }

        /* ====================== BTN ACTION CETAK ====================== */
        $('#mutasiViewer').on('click', '#btnAction', function(e){
            e.preventDefault();
            e.stopPropagation();

            const btn  = $(this);
            const id   = btn.attr('data-id'); // pakai attr() supaya dinamis terbaca
            const step = btn.attr('data-step');

            if (!id) return false;

            if (step === 'process') {
                // STEP 1: CETAK PDF
                $('.mutasi-dialog').addClass('modal-pdf-mode');

                const pdfUrl = '{{ route("superuser.gudang.mutasi_showroom.print_pdf", ":id") }}'.replace(':id', id);

                // Hide tombol cetak dulu
                btn.hide();

                // Tambahkan tombol Proses jika belum ada
                let prosesBtn = $('#btnProses');
                if (prosesBtn.length === 0) {
                    prosesBtn = $('<button>', {
                        id: 'btnProses',
                        class: 'btn btn-success',
                        text: 'Proses',
                        'data-id': id
                    }).appendTo('.mutasi-modal-footer');
                } else {
                    prosesBtn.attr('data-id', id); // update id
                }

                prosesBtn.hide(); // sembunyikan sampai iframe load

                // Load PDF iframe
                const iframe = $('<iframe>', {
                    src: pdfUrl,
                    class: 'pdf-iframe'
                });

                // Attach load event setelah iframe ada di DOM
                iframe.on('load', function(){
                    // PDF sudah siap → tampilkan tombol Proses
                    prosesBtn.show();
                });

                // Append ke container
                $('#viewerContent').html(iframe);

                return false;
            }
        });

        /* ====================== BTN ACTION PUBLISH ====================== */
        $('#mutasiViewer').on('click', '#btnProses', function(e){
            e.preventDefault();
            e.stopPropagation();

            const btn = $(this);
            const id  = btn.attr('data-id'); // gunakan attr() agar selalu terbaca

            if (!id) return false;

            Swal.fire({
                title: 'Publish Mutasi?',
                text: 'Pastikan dokumen sudah dicetak dan diperiksa.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Publish',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                btn.prop('disabled', true);

                $.ajax({
                    url: '{{ route("superuser.gudang.mutasi_showroom.publish", ":id") }}'.replace(':id', id),
                    type: 'POST',
                    data: {_token: '{{ csrf_token() }}'},
                    success(res) {
                        Swal.fire('Sukses', res.message, 'success').then(() => {
                            // sembunyikan tombol
                            btn.hide();
                            $('#btnAction').hide();

                            // tutup modal Bootstrap
                            const modalElem = $('#mutasiViewer');
                            const modal = bootstrap.Modal.getInstance(modalElem);
                            if(modal) modal.hide();

                            // reload list
                            reloadPartialList();
                        });
                    },
                    error(xhr) {
                        btn.prop('disabled', false);
                        let msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan';
                        Swal.fire('Gagal', msg, 'error');
                    }
                });
            });
        });
        
        /* ================= NAV VIEWER ================= */
        $(document).on('click', '#prevMutasi', function () {
            if (currentIndex > 0) {
                currentIndex--;
                openViewer();
            }
        });

        $(document).on('click', '#nextMutasi', function () {
            if (currentIndex < window.mutasiIds.length - 1) {
                currentIndex++;
                openViewer();
            }
        });

        /* ================= PAGINATION ================= */
        $(document).off('click', '.pagination a').on('click', '.pagination a', function (e) {
            e.preventDefault();
            reloadPartialList($(this).attr('href'));
        });

        /* ================= CREATE MUTASI (TIDAK DIUBAH) ================= */
        let createContext = {
            brand: null,
            type: null,
            customer_id: null
        };

        /* === OPEN MODAL === */
        $(document).on('click', '#btnCreateMutasi', function () {
            $('#modalCreateMutasi').modal('show');
            resetCreateModal();
            initSelect2('#modalCreateMutasi');
        });

        /* === TYPE CHANGE → TOGGLE CUSTOMER === */
        $(document).on('change', '#selectType', function () {
            const type = parseInt($(this).val());
            if ([2, 3].includes(type)) {
                $('#customerWrapper').removeClass('d-none');
                
                // RE-INIT dengan dropdownParent yang benar
                $('#selectCustomer').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $('#modalCreateMutasi'), 
                    placeholder: 'Pilih Customer',
                    allowClear: true
                });
            } else {
                $('#customerWrapper').addClass('d-none');
                $('#selectCustomer').val('').trigger('change');
            }
        });

         /* === SUBMIT === */
        $(document).on('click', '#btnSubmitMutasi', function () {
            const brand    = $('#selectBrand').val();
            const type     = parseInt($('#selectType').val());
            const customer = $('#selectCustomer').val();

            if (!brand || !type) {
                Swal.fire('Info', 'Brand dan Type wajib dipilih', 'info');
                return;
            }

            if ([2, 3].includes(type) && !customer) {
                Swal.fire('Info', 'Customer wajib dipilih', 'info');
                return;
            }

            createContext = {
                brand: brand,
                type: type,
                customer_id: customer
            };

            $('#modalCreateMutasi').modal('hide');

            setTimeout(loadCreatePartial, 300);
        });

        /* === LOAD CREATE PARTIAL === */
        function loadCreatePartial() {
            loadFrameB(
                '{{ route("superuser.gudang.mutasi_showroom.create_partial") }}',
                function () {
                    $('input[name="type"]').val(createContext.type);
                    $('input[name="brand_name"]').val(createContext.brand);
                    $('input[name="gudang_id"]').val(2);
                    $('input[name="vendor_id"]').val(53);

                    if (createContext.customer_id) {
                        $('input[name="customer_id"]').val(createContext.customer_id);
                    }
                }
            );
        }

        function resetCreateModal() {
            $('#selectBrand, #selectType, #selectCustomer')
                .val('')
                .trigger('change');
            $('#customerWrapper').addClass('d-none');
        }

        function initSelect2(modalId) {
            $(modalId + ' .select2').each(function() {
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $(modalId), // Mengunci dropdown di dalam modal
                    placeholder: 'Pilih',
                    allowClear: true,
                    // Opsional: Jika ingin memaksa selalu ke bawah, tapi pastikan ada ruang
                    // selectOnClose: false 
                });
            });
        }
    });
</script>
