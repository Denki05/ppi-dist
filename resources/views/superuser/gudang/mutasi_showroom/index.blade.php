@extends('superuser.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    body { background-color: #1f242a; }

    .crm-wrapper {
        max-width: 992px;
        margin: 0 auto;
        height: calc(100vh - 90px);
    }

    .crm-wrapper .card { height: 100%; border-radius: 12px; }

    .crm-row { display: flex; gap: 8px; height: 100%; }
    .frame-a, .frame-b { height: 100%; transition: all .35s ease; }

    .frame-a { flex: 0 0 240px; display: flex; flex-direction: column; }
    .frame-b { flex: 1; }

    .frame-a.collapsed { flex: 0 0 110px; }

    .menu-btn { width: 100%; text-align: left; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; cursor: pointer; background-color: #fff; border-radius:12px; padding:10px 12px; font-size:14px; }
    .menu-btn i { font-size:18px; }
    .menu-btn.active { background: linear-gradient(135deg,#0d6efd,#0b5ed7); color:#fff; border:none; }

    .collapse-btn { margin-top:auto; width:100%; display:flex; justify-content:left; }

    .frame-b .card { border-radius:18px; }
    .frame-b .card-body { padding-bottom: 80px; overflow-y:auto; }

    @media (max-width:768px) {
        .crm-wrapper { max-width:100%; }
        .frame-a { flex:0 0 90px; }
        .frame-a.collapsed { flex:0 0 70px; }
        .modal.show { display:flex !important; align-items:center; }
        .modal-dialog { margin:auto; }
        #frameBContent { position:relative; }
    }
</style>

<div class="container-fluid px-2">
    <div class="crm-wrapper">

        <div class="crm-row">

            {{-- ================= FRAME A ================= --}}
            <div class="frame-a" id="frameA">
                <div class="card h-100">
                    <div class="card-body p-2 d-flex flex-column">

                        {{-- MENU --}}
                        <button class="btn btn-outline-primary menu-btn active" data-menu="list">
                            <!-- <i class="bi bi-list-ul"></i> -->
                            <span>LIST</span>
                        </button>

                        <button class="btn btn-outline-primary menu-btn" data-menu="process">
                            <!-- <i class="bi bi-arrow-repeat"></i> -->
                            <span>PROSES</span>
                        </button>

                        {{-- COLLAPSE --}}
                        <!-- <div class="collapse-btn">
                            <button class="btn" id="toggleFrame">
                                <i class="bi bi-layout-sidebar-inset"></i>
                            </button>
                        </div> -->

                    </div>
                </div>
            </div>

            {{-- ================= FRAME B ================= --}}
            <div class="frame-b">
                <div class="card h-100">
                    <div class="card-body p-3" id="frameBContent">
                        <div class="text-center py-5">
                            <div class="spinner-border"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
/* ===== GLOBAL AJAX CONTROLLER ===== */
window.activeRequest = null;

window.mutasiFilterStore = {
    key: 'mutasi_showroom_filter',

    save(state) {
        sessionStorage.setItem(this.key, JSON.stringify(state));
    },

    load() {
        const raw = sessionStorage.getItem(this.key);
        return raw ? JSON.parse(raw) : null;
    },

    clear() {
        sessionStorage.removeItem(this.key);
    }
};

function loadFrameB(url, callback = null) {
    if (window.activeRequest) {
        window.activeRequest.abort();
        window.activeRequest = null;
    }

    $('#frameBContent').html(`<div class="text-center py-5"><div class="spinner-border"></div></div>`);

    window.activeRequest = $.ajax({
        url: url,
        method: 'GET',
        success: function (html) {
            $('#frameBContent').html(html);
            if (callback) callback();
        },
        error: function (xhr, status) {
            if (status !== 'abort') {
                $('#frameBContent').html('<div class="text-danger text-center py-5">Gagal load data</div>');
            }
        },
        complete: function () {
            window.activeRequest = null;
        }
    });
}

/* ===== UI STATE ===== */
window.uiState = { mode: 'list', collapsed: false };

function collapseFrameA() {
    uiState.collapsed = true;
    $('#frameA').addClass('collapsed');
    $('#toggleFrame').html('<i class="bi bi-layout-sidebar"></i>');
}

function expandFrameA() {
    uiState.collapsed = false;
    $('#frameA').removeClass('collapsed');
    $('#toggleFrame').html('<i class="bi bi-layout-sidebar-inset"></i>');
}

$(function() {
    /* ===== LOAD DEFAULT LIST ===== */
    loadFrameB('{{ route("superuser.gudang.mutasi_showroom.list_partial") }}');

    /* ===== COLLAPSE SIDEBAR ===== */
    $('#toggleFrame').on('click', function(){
        if(uiState.collapsed) expandFrameA(); else collapseFrameA();
    });

    /* ===== MENU NAV ===== */
    $('.menu-btn').on('click', function(){
        $('.menu-btn').removeClass('active');
        $(this).addClass('active');

        const menu = $(this).data('menu');
        uiState.mode = menu;

        if(menu === 'list'){
            expandFrameA();
            
            // AMBIL STATE TERAKHIR
            let stored = window.mutasiFilterStore.load();
            let url = '{{ route("superuser.gudang.mutasi_showroom.list_partial") }}';
            
            // JIKA ADA FILTER TERSET, KIRIM KE URL
            if(stored) {
                let params = $.param({
                    start_date: stored.start_date,
                    end_date: stored.end_date,
                    status: stored.status
                });
                url += '?' + params;
            }

            loadFrameB(url, function () {
                // PASTIKAN range picker hidup ulang
                if (typeof initRangePicker === 'function') {
                    initRangePicker();
                }
            });
        }

        if(menu === 'process'){
            // collapseFrameA();
            loadFrameB('{{ route("superuser.gudang.mutasi_showroom.done_index") }}');
        }
    });
});
</script>
@endpush