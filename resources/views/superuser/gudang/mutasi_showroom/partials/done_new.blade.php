<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="mutasi-header-left">
        <h5 class="mb-0">Daftar Mutasi Selesai (Done)</h5>
    </div>
    <!-- BUTTON UNTUK DI LEMBAR PADA UI UPDATE HARGA -->
    <button id="btnRefreshDone"
            class="btn btn-sm btn-outline-secondary"
            title="Update Harga">
        <i class="fa fa-sync"></i>
    </button>
</div>

<div class="table-responsive">
    <table class="table table-hover table-sm align-middle mb-0" id="table-done-mutasi">
        <thead>
            <tr>
                <th width="10">#</th>
                <th>Tanggal - Kode Mutasi</th>
                <th>Brand</th>
                <th>Type</th>
                <th>Status</th>>
            </tr>
        </thead>
        <tbody id="mutasi-list">
            </tbody>
    </table>
</div>

{{-- PAGINATION --}}
<div id="mutasi-pagination" class="d-flex justify-content-center align-items-center mt-3 gap-2"></div>

<!-- MODAL DETAIL MUTASI -->
<div class="modal fade" id="modalMutasiDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Mutasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- HEADER INFO -->
                <div class="mb-3">
                    <strong>Tanggal / Kode:</strong> <span id="mutasiDetailKode"></span><br>
                    <strong>Type:</strong> <span id="mutasiDetailType"></span><br>
                    <strong>Brand:</strong> <span id="mutasiDetailBrand"></span><br>
                    <strong>Customer:</strong> <span id="mutasiDetailCustomer"></span>
                </div>

                <!-- BODY -->
                <div class="d-flex gap-3">
                    <!-- PREVIOUS -->
                    <div class="col-2 d-flex flex-column justify-content-center align-items-center">
                        <button id="btnPrevMutasi" class="btn btn-sm btn-outline-secondary mb-2">&laquo; Prev</button>
                    </div>

                    <!-- TABLE DETAIL -->
                    <div class="col-8 table-responsive">
                        <table class="table table-sm table-bordered mb-0" id="mutasiDetailTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Packaging</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <!-- NEXT -->
                    <div class="col-2 d-flex flex-column justify-content-center align-items-center">
                        <button id="btnNextMutasi" class="btn btn-sm btn-outline-secondary mb-2">Next &raquo;</button>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <!-- <button id="btnPrintRequest" class="btn btn-sm btn-primary">Print PDF Permintaan</button> -->
                <button id="btnPrintInvoice" class="btn btn-sm btn-success">Print Invoice</button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    loadMutasi(1);

    $(document).on('click', '#btnRefreshDone', function() {
        loadMutasi(1);
    });
});

function loadMutasi(page = 1) {
    const listContainer = $('#mutasi-list');
    listContainer.html('<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div> Memuat...</td></tr>');

    $.get('{{ route("superuser.gudang.mutasi_showroom.done.data") }}', {
        page: page,
        start_date: window.rangeValue?.start,
        end_date: window.rangeValue?.end
    }, function (res) {
        let html = '';

        if (res.data.length === 0) {
            html = `<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data mutasi selesai ditemukan</td></tr>`;
        } else {
            res.data.forEach((row) => {
                html += `
                    <tr class="mutasi-row-done" data-id="${row.id}" style="cursor:pointer;">
                        <td>${row.no}</td>
                        <td><strong>${row.tanggal}</strong> - ${row.kode}</td>
                        <td>${row.brand}</td>
                        <td>${row.type}</td>
                        <td>${row.status}</td>
                    </tr>
                `;
            });
        }

        listContainer.html(html);
        renderPagination(res);
    });
}

function renderPagination(res) {
    let paginationHtml = '';
    
    if (res.total > 0) {
        // Tombol First & Prev
        paginationHtml += `<button class="btn btn-sm btn-outline-secondary page-link-done ${res.current_page === 1 ? 'disabled' : ''}" data-page="1">&laquo;</button>`;
        paginationHtml += `<button class="btn btn-sm btn-outline-secondary page-link-done ${res.current_page === 1 ? 'disabled' : ''}" data-page="${res.current_page - 1}">&lsaquo;</button>`;
        
        // Info Page
        paginationHtml += `<span class="text-muted small mx-2">${res.current_page} / ${res.last_page}</span>`;
        
        // Tombol Next & Last
        paginationHtml += `<button class="btn btn-sm btn-outline-secondary page-link-done ${res.current_page === res.last_page ? 'disabled' : ''}" data-page="${res.current_page + 1}">&rsaquo;</button>`;
        paginationHtml += `<button class="btn btn-sm btn-outline-secondary page-link-done ${res.current_page === res.last_page ? 'disabled' : ''}" data-page="${res.last_page}">&raquo;</button>`;
    }

    $('#mutasi-pagination').html(paginationHtml);
}

/* PAGINATION CLICK */
$(document).on('click', '.page-link-done', function (e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return;
    const page = $(this).data('page');
    loadMutasi(page);
});

/* PRINT ACTION */
$(document).on('click', '.btnPrintInvoice', function (e) {
    e.stopPropagation();
    const id = $(this).data('id');
    const url = '{{ route("superuser.gudang.mutasi_showroom.print_invoice", ":id") }}'.replace(':id', id);
    window.open(url, '_blank');
});

$(document).on('click', '#btnRefreshDone', function () {
    // Aktifkan menu PROSES
    $('.menu-btn').removeClass('active');
    $('.menu-btn[data-menu="process"]').addClass('active');

    // Optional: collapse Frame A (recommended untuk fokus kerja)
    if (typeof collapseFrameA === 'function') {
        collapseFrameA();
    }

    // Load halaman Update Harga ke Frame B
    loadFrameB('{{ route("superuser.gudang.mutasi_showroom.update_list_partial") }}');
});

let mutasiListCache = []; // Menyimpan data mutasi untuk navigasi previous/next
let currentMutasiIndex = 0;

$(document).on('click', '.mutasi-row-done', function () {
    const id = $(this).data('id');

    // Ambil data mutasi dari server
    $.get('{{ route("superuser.gudang.mutasi_showroom.detail") }}', { id: id }, function(res) {
        mutasiListCache = res.dataList; // array mutasi untuk navigasi
        currentMutasiIndex = mutasiListCache.findIndex(m => m.id == id);

        renderMutasiDetail(currentMutasiIndex);
        $('#modalMutasiDetail').modal('show');
    });
});

function renderMutasiDetail(index) {
    if (index < 0 || index >= mutasiListCache.length) return;

    const mutasi = mutasiListCache[index];

    $('#mutasiDetailKode').text(mutasi.kode + ' (' + mutasi.tanggal + ')');
    $('#mutasiDetailType').text(mutasi.type);
    $('#mutasiDetailBrand').text(mutasi.brand);
    $('#mutasiDetailCustomer').text(mutasi.customer);

    const tbody = $('#mutasiDetailTable tbody');
    tbody.html('');
    mutasi.details.forEach((item, i) => {
        tbody.append(`
            <tr>
                <td>${i+1}</td>
                <td>${item.product_name}</td>
                <td>${item.packaging_name}</td>
                <td>${item.qty}</td>
            </tr>
        `);
    });
}

// NAVIGASI PREV / NEXT
$('#btnPrevMutasi').click(function () {
    if (currentMutasiIndex > 0) {
        currentMutasiIndex--;
        renderMutasiDetail(currentMutasiIndex);
    }
});
$('#btnNextMutasi').click(function () {
    if (currentMutasiIndex < mutasiListCache.length - 1) {
        currentMutasiIndex++;
        renderMutasiDetail(currentMutasiIndex);
    }
});

// PRINT PDF / INVOICE
$('#btnPrintRequest').click(function () {
    const mutasi = mutasiListCache[currentMutasiIndex];
    window.open('{{ route("superuser.gudang.mutasi_showroom.print_request", ":id") }}'.replace(':id', mutasi.id), '_blank');
});

$('#btnPrintInvoice').click(function () {
    const mutasi = mutasiListCache[currentMutasiIndex];
    window.open('{{ route("superuser.gudang.mutasi_showroom.print_invoice", ":id") }}'.replace(':id', mutasi.id), '_blank');
});

</script>