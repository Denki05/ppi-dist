<style>
    .btn-page {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        background: #fff;
    }
    .btn-page:hover:not(:disabled) {
        background-color: #f8f9fa;
    }
    .btn-page:disabled {
        color: #dee2e6;
        cursor: not-allowed;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="mutasi-header-left">
        <h5 class="mb-0">Daftar Mutasi Selesai</h5>
    </div>
    <button id="btnRefreshDone" class="btn btn-sm btn-outline-secondary" title="Update Harga">
        <i class="fa fa-sync"></i>
    </button>
</div>

{{-- TABLE WRAPPER --}}
<div class="table-responsive">
    <table class="table table-hover align-middle" style="font-size: 13px;">
        <thead class="bg-light">
            <tr>
                <th class="text-secondary">#</th>
                <th class="text-secondary">TANGGAL</th>
                <th class="text-secondary text-center">TOTAL MUTASI</th>
                <th class="text-secondary text-end">AKSI</th>
            </tr>
        </thead>
        <tbody id="mutasi-list">
            {{-- diisi via AJAX --}}
        </tbody>
    </table>
</div>

{{-- PAGINATION TENGAH --}}
<div class="mt-4 d-flex justify-content-center align-items-center" id="mutasi-pagination">
    {{-- Akan diisi oleh format custom melalui JS --}}
</div>


<script>
$(function () {
    loadMutasi(1);
});

function loadMutasi(page = 1) {
    $.get('{{ route("superuser.gudang.mutasi_showroom.done.data") }}', {
        page: page,
        start_date: window.rangeValue?.start,
        end_date: window.rangeValue?.end
    }, function (res) {

        let html = '';

        if (res.data.length === 0) {
            html = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        Data mutasi showroom belum tersedia
                    </td>
                </tr>
            `;
        } else {
            res.data.forEach((row) => {
                html += `
                    <tr>
                        <td class="text-muted">${row.no}</td>
                        <td>
                            <div class="fw-bold text-dark">${row.tanggal}</div>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold">${row.total_mutasi}</span>
                        </td>
                        <td class="text-end">
                            ${row.action}
                        </td>
                    </tr>
                `;
            });
        }

        $('#mutasi-list').html(html);

        let currentPage = res.current_page;
        let lastPage = res.last_page;

        let paginationHtml = `
            <div class="d-flex justify-content-center align-items-center mt-3 gap-2 pagination">
                <button class="btn btn-sm btn-outline-secondary btn-page" data-page="1" ${currentPage == 1 ? 'disabled' : ''}>
                    &laquo;
                </button>

                <button class="btn btn-sm btn-outline-secondary btn-page" data-page="${currentPage - 1}" ${currentPage == 1 ? 'disabled' : ''}>
                    &lsaquo;
                </button>

                <span class="text-muted mx-2">
                    ${currentPage} / ${lastPage}
                </span>

                <button class="btn btn-sm btn-outline-secondary btn-page" data-page="${currentPage + 1}" ${currentPage == lastPage ? 'disabled' : ''}>
                    &rsaquo;
                </button>

                <button class="btn btn-sm btn-outline-secondary btn-page" data-page="${lastPage}" ${currentPage == lastPage ? 'disabled' : ''}>
                    &raquo;
                </button>
            </div>
        `;

        $('#mutasi-pagination').html(paginationHtml);
    });
}

/* PAGINATION CLICK - SUDAH DIPERBAIKI */
$(document).on('click', '.btn-page', function (e) {
    e.preventDefault();
    
    // Ambil angka halaman dari attribute data-page
    const page = $(this).data('page');
    
    // Validasi: pastikan page ada dan tombol tidak sedang disabled
    if (page && !$(this).prop('disabled')) {
        loadMutasi(page);
    }
});

// ROW ACTION CLICK
$(document).on('click', '.btnPrintInvoice', function (e) {
    e.stopPropagation(); // agar tidak trigger klik row
    const id = $(this).data('id');
    const url = '{{ route("superuser.gudang.mutasi_showroom.print_invoice", ":id") }}'
        .replace(':id', id);
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
</script>