<h5 class="mb-3">Daftar Mutasi Showroom - DONE</h5>

{{-- INFO --}}
<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="text-muted" style="font-size:13px;" id="mutasi-info">
        Memuat data...
    </div>
</div>

{{-- LIST --}}
<div class="list-group list-group-flush" id="mutasi-list">
    {{-- diisi via AJAX --}}
</div>

{{-- PAGINATION --}}
<div class="mt-3 d-flex justify-content-end" id="mutasi-pagination"></div>


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
                <div class="text-center text-muted py-4">
                    Data mutasi showroom belum tersedia
                </div>
            `;
        } else {
            res.data.forEach((row, index) => {
                html += `
                    <div class="list-group-item mutasi-row mb-2"
                         style="border:1px solid #dee2e6;border-radius:8px;">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-light text-dark border me-2"
                                          style="font-weight:normal;font-size:11px;">
                                        ${row.no}
                                    </span>

                                    <span style="font-size:14px;">
                                        ${row.tanggal}
                                    </span>
                                </div>

                                <div class="text-muted" style="font-size:12px;">
                                    ${row.total_mutasi} Mutasi
                                </div>
                            </div>

                            ${row.action}

                        </div>
                    </div>
                `;
            });
        }

        $('#mutasi-list').html(html);

        $('#mutasi-info').html(
            `Menampilkan ${res.from} – ${res.to} dari ${res.total} data`
        );

        $('#mutasi-pagination').html(res.pagination);
    });
}

/* PAGINATION CLICK */
$(document).on('click', '#mutasi-pagination a', function (e) {
    e.preventDefault();
    const page = $(this).attr('href').split('page=')[1];
    loadMutasi(page);
});

/* ROW CLICK */
$(document).on('click', '.btnPrintInvoice', function (e) {
    e.stopPropagation(); // ⬅️ penting agar tidak trigger klik row

    const id = $(this).data('id');

    const url = '{{ route("superuser.gudang.mutasi_showroom.print_invoice", ":id") }}'
        .replace(':id', id);

    window.open(url, '_blank');
});
</script>