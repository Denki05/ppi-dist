@extends('superuser.gudang.mutasi_out.layouts._wrapper')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<style>
/* SweetAlert */
.swal2-popup { border-radius: 14px; padding: 1.6rem 1.8rem; }
.swal-inline-form { display: flex; flex-direction: column; gap: 12px; }
.swal-row { display: grid; grid-template-columns: 130px 1fr; align-items: center; gap: 10px; }
.swal-row label { font-size: 0.8rem; font-weight: 600; color: #6c757d; }
.swal-row select, .swal-row textarea { width: 100%; padding: 7px 10px; font-size: 0.85rem; border-radius: 8px; border:1px solid #ced4da; }
.swal-row textarea { resize: vertical; }
.swal2-popup .select2-container { width: 100% !important; }
.swal2-popup .select2-selection--single { height: 38px; padding: 6px 10px; border-radius: 8px; border: 1px solid #ced4da; display: flex; align-items: center; }
.swal2-popup .select2-selection__rendered { padding-left: 0; font-size: 0.85rem; color: #495057; line-height: normal; }
.swal2-popup .select2-selection__arrow { height: 100%; }

/* Detail table layout */
.detail-table { table-layout: fixed; }
.detail-table th, .detail-table td { vertical-align: middle; }
.detail-table td:first-child { padding-right: 12px; }
.detail-table input, .detail-table select { width: 100%; }

/* Table */
#mutationTable thead th { font-size: 14px; font-weight: 600; color: #333; border-bottom: 1px solid #e5e7eb; }
#mutationTable tbody td { font-size: 14px; padding: 10px 8px; }
#mutationTable tbody tr { cursor: pointer; }
#mutationTable tbody tr:hover { background-color: #f8f9fa; }

/* Custom pagination */
.page-btn { border: 1px solid #ddd; background: #fff; padding: 2px 8px; border-radius: 4px; cursor: pointer; font-size: 12px; }
.page-btn:hover:not(.disabled) { background-color: #f0f0f0; }
.page-btn.disabled { color: #ccc; cursor: not-allowed; }
.page-info { font-weight: bold; font-size: 13px; margin: 0 6px; }

.crm-wrapper {
    display: block; /* hapus flex */
}

.card {
    width: 100%;
    height: calc(100vh - 250px); /* 150px untuk header + tabs */
    overflow: hidden; /* hilangkan scroll internal */
}

.table-wrapper {
    max-height: 100%;
}

/* ===== MUTASI MODAL ===== */
.mutasi-dialog {
    max-width: 750px;
}

.mutasi-modal {
    display: flex;
    flex-direction: column;
    border-radius: 14px;
    overflow: hidden;
    max-height: 70vh; /* modal tidak terlalu tinggi */
    background: #fff;
}

/* HEADER */
.mutasi-modal-header {
    display: flex;
    align-items: center;          /* vertikal center */
    justify-content: space-between; /* judul kiri, tombol kanan */
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
    background: #fff;
    flex-shrink: 0;
}

.mutasi-title {
    font-weight: 600;
    font-size: 16px;
    margin: 0;
    color: #222;
    line-height: 1.2;
    white-space: nowrap;          /* judul tidak wrap */
    overflow: hidden;
    text-overflow: ellipsis;
}

.btn-close {
    width: 32px;
    height: 32px;
    padding: 0;
    background: transparent;
    border: none;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.btn-close:hover {
    opacity: 1;
}

/* BODY SLICED */
.mutasi-modal-body {
    display: flex;
    flex: 1;
    overflow: hidden; /* agar viewer scrollable */
    align-items: stretch;
    background: #fff;
}

/* NAV SLICE LEFT / RIGHT */
.nav-slice {
    width: 45px;
    display: flex;
    align-items: center;      /* tombol di tengah vertikal */
    justify-content: center;
    background: #f8f9fa;
    border-left: 1px solid #dee2e6;
    border-right: 1px solid #dee2e6;
    flex-shrink: 0;
}

/* NAV ARROW */
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
    font-size: 18px;
    transition: background 0.2s;
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

/* VIEWER CONTENT */
.mutasi-viewer {
    flex: 1;
    padding: 16px 20px;
    overflow-y: auto;
    max-height: 45vh;
}

/* FOOTER */
.mutasi-modal-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-top: 1px solid #e5e7eb;
    background: #fafafa;
    flex-shrink: 0;
}

/* ===== RESPONSIVE MOBILE ===== */
@media (max-width: 768px) {
    .mutasi-dialog {
        max-width: 95%;
    }
    .nav-slice {
        width: 44px;
        align-self: stretch;
    }
    .mutasi-viewer {
        padding: 12px 10px;
        max-height: 40vh;
    }
    .nav-arrow {
        width: 32px;
        height: 32px;
        font-size: 16px;
    }
}

/* ===== VIEWER INFO BOX ===== */
.viewer-info-box {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* ROW FLEX */
.viewer-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px; /* jarak antar item */
}

/* GROUP FLEX */
.viewer-group {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    width: 100%;
}

/* ITEM FLEX (2 per baris) */
.viewer-item {
    display: flex;
    flex: 1 1 calc(50% - 6px); /* 2 item per baris, sisakan gap */
    gap: 6px;
    margin-bottom: 4px;
    min-width: 180px; /* agar tetap rapi saat shrink */
}

/* LABEL & VALUE */
.viewer-label {
    font-weight: 600;
    color: #555;
    width: 100px; /* fixed label width */
    flex-shrink: 0;
}

.viewer-value {
    font-weight: 500;
    color: #222;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* BADGE STATUS */
.badge-type-custom {
    color: #0d6efd;
    font-weight: bold;
    text-transform: uppercase;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .viewer-item {
        flex: 1 1 100%; /* 1 item per baris di mobile */
    }
    .viewer-label {
        width: 90px;
    }
}

</style>
@endpush

@section('page-header')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-semibold">Mutasi Gudang Utama</h5>
    <button type="button" class="btn btn-primary btn-sm" id="btnCreateMutation">+ Buat Mutasi</button>
</div>

<div class="status-tabs mb-3">
    <button class="status-tab active" data-tab="aktif">Aktif (<span id="count-aktif">{{ $mutasiAktif->total() }}</span>)</button>
    <button class="status-tab" data-tab="proses">Proses Logistik (<span id="count-proses">{{ $mutasiProses->total() }}</span>)</button>
    <button class="status-tab" data-tab="selesai">Selesai (<span id="count-selesai">{{ $mutasiSelesai->total() }}</span>)</button>
</div>
@endsection

@section('page-content')
<div class="crm-wrapper">
    {{-- TABEL UTAMA --}}
    <div class="card p-2">
        {{-- Tab Aktif --}}
        <div id="tab-aktif" class="mutasi-tab-content">
            <div class="table-wrapper">
                <table class="table table-hover table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Gudang Tujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasiAktif as $i => $m)
                        <tr data-id="{{ $m->id }}" class="mutasi-row" data-tab="aktif">
                            <td>{{ $i + $mutasiAktif->firstItem() }}</td>
                            <td>{{ $m->code }}</td>
                            <td>{{ \Carbon\Carbon::parse($m->date)->format('d-m-Y') }}</td>
                            <td>{{ optional($m->warehouse_to_attribute)->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="compactPaginationAktif" class="mt-2 d-flex justify-content-center align-items-center"></div>
        </div>

        {{-- Tab Proses --}}
        <div id="tab-proses" class="mutasi-tab-content d-none">
            <div class="table-wrapper">
                <table class="table table-hover table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Gudang Tujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasiProses as $i => $m)
                        <tr data-id="{{ $m->id }}" class="mutasi-row" data-tab="proses">
                            <td>{{ $i + $mutasiProses->firstItem() }}</td>
                            <td>{{ $m->code }}</td>
                            <td>{{ \Carbon\Carbon::parse($m->date)->format('d-m-Y') }}</td>
                            <td>{{ optional($m->warehouse_to_attribute)->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="compactPaginationProses" class="mt-2 d-flex justify-content-center align-items-center"></div>
        </div>

        {{-- Tab Selesai --}}
        <div id="tab-selesai" class="mutasi-tab-content d-none">
            <div class="table-wrapper">
                <table class="table table-hover table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Gudang Tujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasiSelesai as $i => $m)
                        <tr data-id="{{ $m->id }}" class="mutasi-row text-muted" data-tab="selesai">
                            <td>{{ $i + $mutasiSelesai->firstItem() }}</td>
                            <td>{{ $m->code }}</td>
                            <td>{{ \Carbon\Carbon::parse($m->date)->format('d-m-Y') }}</td>
                            <td>{{ optional($m->warehouse_to_attribute)->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="compactPaginationSelesai" class="mt-2 d-flex justify-content-center align-items-center"></div>
        </div>
    </div>
</div>

<!-- MODAL MUTASI DETAIL (Slice Layout) -->
<div class="modal fade" id="mutasiDetailModal" tabindex="-1" aria-labelledby="mutasiDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog mutasi-dialog modal-dialog-scrollable">
    <div class="modal-content mutasi-modal">
      
      <!-- HEADER -->
      <div class="mutasi-modal-header">
        <h5 class="mutasi-title" id="mutasiDetailModalLabel">Detail Mutasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- BODY (SLICED) -->
      <div class="mutasi-modal-body">
        <!-- NAV LEFT -->
        <div class="nav-slice">
          <button id="btnMutasiPrev" class="nav-arrow left">&#8249;</button>
        </div>

        <!-- CONTENT -->
        <div class="mutasi-viewer" id="mutasiDetailContent">
          <!-- Content _detail_popup akan dimasukkan di sini via AJAX -->
        </div>

        <!-- NAV RIGHT -->
        <div class="nav-slice">
          <button id="btnMutasiNext" class="nav-arrow right">&#8250;</button>
        </div>
      </div>

      <!-- FOOTER -->
      <div class="mutasi-modal-footer">
        <div></div> <!-- bisa dikosongkan atau taruh info -->
        <div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="button" class="btn btn-primary" id="btnMutasiPublish">Publish</button>
        </div>
      </div>

    </div>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){

    // --- Helper untuk ambil query param ---
    function getQueryParam(param) {
        let params = new URLSearchParams(window.location.search);
        return params.get(param);
    }

    // --- Fungsi aktifkan tab ---
    function activateTab(tab){
        $('.status-tab').removeClass('active');
        $(`.status-tab[data-tab="${tab}"]`).addClass('active');
        $('.mutasi-tab-content').addClass('d-none');
        $('#tab-' + tab).removeClass('d-none');
    }

    // --- Saat load, ambil tab dari query string ---
    let activeTab = getQueryParam('tab') || 'aktif';
    activateTab(activeTab);

    // --- Tab click ---
    $('.status-tab').click(function(){
        let tab = $(this).data('tab');
        activateTab(tab);
        // Update query string tanpa reload
        let url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        history.replaceState(null, '', url.toString());
    });

    // --- Pagination click ---
    $(document).on('click', '.page-btn', function(){
        const page = $(this).data('page');
        const param = $(this).data('param'); // page_aktif / page_proses / page_selesai
        const tab = $('.status-tab.active').data('tab');

        let url = new URL(window.location.href);
        url.searchParams.set(param, page);
        url.searchParams.set('tab', tab); // pastikan tab tetap sama
        window.location.href = url.toString();
    });

    // Tombol publish di modal
    $(document).on('click', '#swalPublish', function(){
        let id = $(this).data('id');
        Swal.fire({
            title: 'Publish Mutasi?',
            text: "Data akan dikirim ke logistik",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Publish',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#4f46e5'
        }).then((res)=>{
            if(res.isConfirmed){
                $.post("{{ url('superuser/gudang/mutasi_out') }}/"+id+"/publish", {_token: "{{ csrf_token() }}"} )
                .done(function(r){
                    Swal.fire({icon:'success',title:'Berhasil',text:r.message,timer:1500,showConfirmButton:false});
                    $('.mutasi-row[data-id="'+id+'"]').remove();
                    refreshCounts();
                }).fail(function(err){
                    Swal.fire({icon:'error',title:'Gagal',text: err.responseJSON?.message || 'Terjadi kesalahan'});
                });
            }
        });
    });

    // Tombol tutup modal
    $(document).on('click', '#swalClose', function(){
        Swal.close();
    });

    function refreshCounts(){
        $.get("{{ route('superuser.gudang.mutasi_out.refreshCounts') }}", function(res){
            $('#count-aktif').text(res.aktif);
            $('#count-proses').text(res.proses);
            $('#count-selesai').text(res.selesai);
        });
    }

    // Pagination render
    function renderPagination(id, current, last, pageParam){
        let html = '';
        html += `<span class="page-btn" data-page="1" data-param="${pageParam}"><<</span>`;
        html += `<span class="page-btn" data-page="${Math.max(1,current-1)}" data-param="${pageParam}"><</span>`;
        html += `<span class="page-info">${current}/${last}</span>`;
        html += `<span class="page-btn" data-page="${Math.min(last,current+1)}" data-param="${pageParam}">></span>`;
        html += `<span class="page-btn" data-page="${last}" data-param="${pageParam}">>></span>`;
        $(id).html(html);
    }


    renderPagination('#compactPaginationAktif', {{ $mutasiAktif->currentPage() }}, {{ $mutasiAktif->lastPage() }}, 'page_aktif');
    renderPagination('#compactPaginationProses', {{ $mutasiProses->currentPage() }}, {{ $mutasiProses->lastPage() }}, 'page_proses');
    renderPagination('#compactPaginationSelesai', {{ $mutasiSelesai->currentPage() }}, {{ $mutasiSelesai->lastPage() }}, 'page_selesai');


    // Buat Mutasi
    $('#btnCreateMutation').on('click', function () {
        Swal.fire({
            title: 'Buat Mutasi Gudang',
            html: `
                <div class="swal-inline-form">
                    <div class="swal-row">
                        <label>Gudang Asal</label>
                        <select id="warehouse_from" disabled>
                            <option value="{{ $warehouse_araya->id }}" selected>
                                {{ $warehouse_araya->name }}
                            </option>
                        </select>

                        <input type="hidden" name="warehouse_from"
                            value="{{ $warehouse_araya->id }}">
                    </div>
                    <div class="swal-row">
                        <label>Gudang Tujuan</label>
                        <select id="warehouse_to">
                            <option value=""></option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="swal-row">
                        <label>Brand</label>
                        <select id="brand_name">
                            <option value=""></option>
                            @foreach($brands as $b)
                                <option value="{{ $b->brand_name }}">{{ $b->brand_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="swal-row">
                        <label>Catatan</label>
                        <textarea id="note" rows="3" placeholder="Opsional"></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Lanjut',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#4f46e5',
            didOpen: () => {
                if ($.fn.select2) {
                    $('#warehouse_from, #warehouse_to, #brand_name').select2({
                        dropdownParent: $('.swal2-popup'),
                        width: '100%',
                        allowClear: true,
                        placeholder: 'Pilih'
                    });
                }
            },
            preConfirm: () => {
                const from = $('#warehouse_from').val();
                const to = $('#warehouse_to').val();
                const brand = $('#brand_name').val();
                if(!from || !to || !brand){
                    Swal.showValidationMessage('Gudang asal, tujuan, dan brand wajib diisi');
                    return false;
                }
                if(from === to){
                    Swal.showValidationMessage('Gudang asal dan tujuan tidak boleh sama');
                    return false;
                }
                return { warehouse_from: from, warehouse_to: to, brand_name: brand, note: $('#note').val() };
            }
        }).then((res)=>{
            if(res.isConfirmed){
                const q = new URLSearchParams(res.value).toString();
                window.location.href = "{{ route('superuser.gudang.mutasi_out.create') }}?" + q;
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function() {
    const mutasiDetailModal = new bootstrap.Modal(document.getElementById('mutasiDetailModal'));
    const contentDiv = document.getElementById('mutasiDetailContent');
    const btnPublish = document.getElementById('btnMutasiPublish');

    // Ambil semua ID mutasi dari tabel aktif/proses/selesai
    let mutasiIds = Array.from(document.querySelectorAll('.mutasi-row')).map(r => parseInt(r.dataset.id));
    let currentIndex = -1;

    function getMutasiIdsByActiveTab() {
        const activeTab = document.querySelector('.status-tab.active')?.dataset.tab;
        if (!activeTab) return [];

        return Array.from(
            document.querySelectorAll(`#tab-${activeTab} .mutasi-row`)
        ).map(r => parseInt(r.dataset.id));
    }

    function loadMutasiDetailByIndex(index) {
        if(index < 0 || index >= mutasiIds.length) return;

        currentIndex = index;
        const id = mutasiIds[currentIndex];
        contentDiv.innerHTML = '<div class="text-center py-5">Loading...</div>';

        fetch("{{ route('superuser.gudang.mutasi_out.detail', ':id') }}".replace(':id', id))
            .then(res => res.text())
            .then(html => {
                contentDiv.innerHTML = html;
                updateNavButtons();
                updatePublishButton(); // cek status mutasi
            })
            .catch(err => {
                contentDiv.innerHTML = '<div class="text-danger text-center py-5">Gagal memuat data.</div>';
                console.error(err);
                btnPublish.style.display = 'none';
            });
    }

    function updateNavButtons() {
        document.getElementById('btnMutasiPrev').disabled = currentIndex <= 0;
        document.getElementById('btnMutasiNext').disabled = currentIndex >= mutasiIds.length - 1;
    }

    function updatePublishButton() {
        const statusEl = contentDiv.querySelector('#mutasiStatus');
        const btnPublish = document.getElementById('btnMutasiPublish');

        if(statusEl && statusEl.textContent.trim() === '1') {
            btnPublish.style.display = 'inline-block';
        } else {
            btnPublish.style.display = 'none';
        }
    }


    // Event klik row untuk buka modal
    document.addEventListener('click', function(e){
        const row = e.target.closest('.mutasi-row');
        if(!row) return;

        mutasiIds = getMutasiIdsByActiveTab(); // 🔑 reset sesuai tab
        currentIndex = mutasiIds.indexOf(parseInt(row.dataset.id));

        if(currentIndex === -1) return;

        loadMutasiDetailByIndex(currentIndex);
        mutasiDetailModal.show();
    });

    // Tombol Prev
    document.getElementById('btnMutasiPrev').addEventListener('click', function() {
        if(currentIndex > 0) loadMutasiDetailByIndex(currentIndex - 1);
    });

    // Tombol Next
    document.getElementById('btnMutasiNext').addEventListener('click', function() {
        if(currentIndex < mutasiIds.length - 1) loadMutasiDetailByIndex(currentIndex + 1);
    });

    // Tombol Publish
    btnPublish.addEventListener('click', function() {
        if(currentIndex < 0) return;
        const currentId = mutasiIds[currentIndex];

        Swal.fire({
            title: 'Publish Mutasi?',
            text: 'Data akan dikirim ke logistik',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Publish',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#4f46e5'
        }).then((result) => {
            if(result.isConfirmed){
                fetch(`/superuser/gudang/mutasi_out/${currentId}/publish`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success'){
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message,
                            timer: 1200,
                            showConfirmButton: false
                        });

                        // Hapus row dari tabel tab saat ini
                        const row = document.querySelector(`.mutasi-row[data-id="${currentId}"]`);
                        if(row) row.remove();
                        mutasiIds.splice(currentIndex, 1);

                        refreshCounts();

                        // --- HOT RELOAD ke tab target (Proses) ---
                        const prosesTab = document.querySelector('#tab-proses tbody');
                        if(prosesTab && prosesTab.querySelector('tr')) {
                            // Ambil semua ID mutasi baru di tab Proses
                            mutasiIds = Array.from(prosesTab.querySelectorAll('.mutasi-row')).map(r => parseInt(r.dataset.id));
                            currentIndex = 0;
                            loadMutasiDetailByIndex(currentIndex);
                        } else {
                            // Jika tab Proses kosong, coba lanjut di tab saat ini
                            if(mutasiIds.length === 0){
                                mutasiDetailModal.hide();
                            } else {
                                if(currentIndex >= mutasiIds.length) currentIndex = mutasiIds.length - 1;
                                loadMutasiDetailByIndex(currentIndex);
                            }
                        }

                    } else {
                        Swal.fire({ icon:'error', title:'Gagal', text: data.message || 'Terjadi kesalahan' });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({ icon:'error', title:'Gagal', text:'Terjadi kesalahan' });
                });
            }
        });
    });

    function refreshCounts(){
        $.get("{{ route('superuser.gudang.mutasi_out.refreshCounts') }}", function(res){
            $('#count-aktif').text(res.aktif);
            $('#count-proses').text(res.proses);
            $('#count-selesai').text(res.selesai);
        });
    }
});
</script>
@endpush

<!-- Notifikasi -->
@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: @json(session('success')),
        timer: 1500,
        showConfirmButton: false
    });
});
</script>
@endif

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: @json(session('error'))
    });
});
</script>
@endif
