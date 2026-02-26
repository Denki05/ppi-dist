{{-- CDN DataTables & Bootstrap 5 --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

<div id="modal-content-wrapper">
    <div class="row g-3 align-items-center mb-3">
        <div class="col-md-6 d-flex align-items-center gap-3">
            <label for="month_filter" class="form-label mb-0 fw-bold text-secondary">Periode:</label>
            <div class="input-group input-group-sm" style="max-width: 200px;">
                <span class="input-group-text bg-white"><i class="fas fa-calendar-alt"></i></span>
                <input type="month" class="form-control" id="month_filter" value="{{ $month }}">
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('superuser.gudang.stock.print', [$warehouse->id, base64_encode($product->id)]) }}" 
               target="_blank"
               id="btnPrintPdf"
               class="btn btn-primary btn-sm">
               <i class="fas fa-print"></i> PDF
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm h-100 d-flex flex-column">
        <div class="table-responsive ks-scroll-wrapper flex-grow-1">
            <table class="table table-hover align-middle mb-0" id="ksDetailTable">
                <thead class="bg-primary text-white sticky-top">
                    <tr>
                        <th class="ps-3 text-center" style="width: 150px;">Tanggal</th>
                        <th class="text-center" style="width: 100px;">Transaksi</th>
                        <th class="text-end" style="width: 80px;">Masuk</th>
                        <th class="text-end" style="width: 80px;">Keluar</th>
                        <th class="text-end" style="width: 80px;">Saldo</th>
                        <th class="pe-3 text-center" style="width: 220px;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($openingBalance))
                    <tr class="bg-light fw-bold">
                        <td class="text-center">-</td>
                        <td>Opening Balance</td>
                        <td class="text-end">-</td>
                        <td class="text-end">-</td>
                        <td class="text-end text-primary">
                            {{ $openingBalance }}
                        </td>
                        <td></td>
                    </tr>
                    @endif
                    @forelse($collects as $c)
                        <tr>
                            <td class="ps-3 text-muted text-center">{{ $c['created_at'] }}</td>
                            <td class="text-center transaksi-cell 
                                {{ $c['in'] ? 'transaksi-masuk' : ($c['out'] ? 'transaksi-keluar' : 'transaksi-netral') }}">
                                {{ $c['transaction'] }}
                            </td>
                            <td class="text-end text-success fw-semibold">{{ $c['in'] ?: '-' }}</td>
                            <td class="text-end text-danger fw-semibold">{{ $c['out'] ?: '-' }}</td>
                            <td class="text-end fw-bold">{{ $c['balance'] }}</td>
                            <td class="pe-3 small text-secondary">{{ $c['description'] ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted small">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <div>Tidak ada data transaksi pada periode ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){

    function loadKsDetail(month){
        let warehouseId = '{{ $warehouse->id }}';
        let productEncoded = encodeURIComponent('{{ base64_encode($product->id) }}');

        $('#ksDetailTable tbody').html('<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Memproses...</td></tr>');

        $.ajax({
            url: '/superuser/gudang/stock/' + warehouseId + '/detail/' + productEncoded,
            type: 'GET',
            data: { month: month },
            dataType: 'html',
            success: function(response){
                let wrapped = $('<div>').append(response);
                let newContent = wrapped.find('#modal-content-wrapper').html();
                $('#modal-content-wrapper').html(newContent);
                attachChangeEvent();
                initDataTable();
            },
            error: function(xhr){
                $('#ksDetailTable tbody').html('<tr><td colspan="6" class="text-center text-danger py-4 small">Terjadi kesalahan sistem</td></tr>');
                console.error(xhr);
            }
        });
    }

    function attachChangeEvent(){

        function updatePrintLink(month){
            let baseUrl = '{{ route("superuser.gudang.stock.print", [$warehouse->id, base64_encode($product->id)]) }}';
            $('#btnPrintPdf').attr('href', baseUrl + '?month=' + month);
        }
    
        let initialMonth = $('#month_filter').val();
        updatePrintLink(initialMonth);
    
        $('#month_filter').off('change').on('change', function(){
            let selectedMonth = $(this).val();
            updatePrintLink(selectedMonth);
            loadKsDetail(selectedMonth);
        });
    }

    function initDataTable(){
        let table = $('#ksDetailTable');
    
        if( table.find('tbody tr').length === 0 || table.find('tbody tr td').length === 1 ){
            return;
        }
    
        table.DataTable({
            destroy: true,
            paging: false,     // ❗ matikan paging
            searching: false,
            ordering: false,
            info: false,
            lengthChange: false,
            columnDefs: [
                { targets: [2,3,4], className: 'text-end' }
            ]
        });
    }

    attachChangeEvent();
    initDataTable();
});
</script>

<style>
    .badge { font-size: 0.75rem; padding: 0.25em 0.4em; }
    #ksDetailTable thead th {
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
        padding-top: 12px;
        padding-bottom: 12px;
    }
    #ksDetailTable tbody tr { transition: none; cursor: default; } /* hilangkan hover scale */
    #ksDetailTable tbody tr:hover { background-color: transparent !important; transform: none; } /* hover netral */
    .table-responsive::-webkit-scrollbar { width: 6px; height: 6px; }
    .table-responsive::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
    .table-responsive::-webkit-scrollbar-track { background: #f1f1f1; }
    .sticky-top { z-index: 1020; background-color: #0d6efd !important; }
    .ks-scroll-wrapper{
        max-height: calc(75vh - 160px);
        overflow-y: auto;
    }
    #ksDetailTable tbody td{
        font-size: 0.9rem;
    }
    #ksDetailTable thead th {
        font-weight: 600;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
        padding-top: 14px;
        padding-bottom: 14px;
    }
    
    #ksDetailTable tbody td{
        font-size: 0.95rem;
        padding-top: 6px !important;
        padding-bottom: 6px !important;
        vertical-align: middle;
    }
    
    /* Transaksi diperbesar lagi agar dominan */
    .transaksi-cell{
        font-size: 1rem;
        font-weight: 600;
        letter-spacing: 0.2px;
    }
    
    /* Warna tanpa badge */
    .transaksi-masuk{
        color: #198754;
    }
    
    .transaksi-keluar{
        color: #dc3545;
    }
    
    .transaksi-netral{
        color: #495057;
    }
    
    #ksDetailTable tbody tr:hover {
        background-color: transparent !important;
    }
    
    .table-responsive::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .sticky-top {
        z-index: 1020;
        background-color: #0d6efd !important;
    }
    
    .ks-scroll-wrapper{
        height: 100%;
        overflow-y: auto;
    }
    
    .dataTables_wrapper .dataTables_paginate{
        display:flex !important;
        justify-content:center !important;
        margin-top:12px;
    }
    
    .ks-pagination{
        display:flex;
        align-items:center;
        gap:8px;
        font-size:13px;
        font-weight:600;
    }
    
    .ks-pagination button{
        background:#f4f6f9;
        border:1px solid #dfe3e8;
        padding:3px 8px;
        border-radius:4px;
        cursor:pointer;
        font-size:13px;
    }
    
    .ks-pagination button:hover{
        background:#e9eef5;
    }
    
    .ks-pagination .page-info{
        min-width:50px;
        text-align:center;
    }
</style>