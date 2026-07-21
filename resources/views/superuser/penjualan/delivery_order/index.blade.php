@php
    $useTabletLayout = ($isChecker ?? false) && !($isSpvGudang ?? false) && Auth::user()->is_superuser == 0;
@endphp
@extends($useTabletLayout ? 'superuser.app_tablet' : 'superuser.app')

@section('content')
@unless($useTabletLayout ?? false)
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Manajemen Barang</span>
  <span class="breadcrumb-item active">Checker Transaksi</span>
</nav>
@endunless
@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
    @if (session('error'))
    <strong>Error!</strong> {!! session('error') !!}
    @elseif (session('success'))
    <strong>Berhasil!</strong> {!! session('success') !!}
    @endif
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div id="alert-block"></div>

@if(session()->has('collect_success') || session()->has('collect_error'))
<div class="container">
  <div class="row">
    <div class="col pl-0">
      <div class="alert alert-success alert-dismissable" role="alert" style="max-height: 300px; overflow-y: auto;">
        <h3 class="alert-heading font-size-h4 font-w400">Successful Import</h3>
        @foreach (session()->get('collect_success') as $msg)
        <p class="mb-0">{{ $msg }}</p>
        @endforeach
      </div>
    </div>
    <div class="col pr-0">
      <div class="alert alert-danger alert-dismissable" role="alert" style="max-height: 300px; overflow-y: auto;">
        <h3 class="alert-heading font-size-h4 font-w400">Failed Import</h3>
        @foreach (session()->get('collect_error') as $msg)
        <p class="mb-0">{{ $msg }}</p>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endif

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

@if($useTabletLayout)
{{-- =========================================================
     CHECKER / TABLET: list padat, bukan card gede-gede.
     ========================================================= --}}
<div class="checker-toolbar">
  <div class="checker-toolbar-date">
    <i class="fa fa-calendar"></i>
    <input type="text" id="datesearch"
      value="{{ \Carbon\Carbon::now()->format('d/m/Y') }} - {{ \Carbon\Carbon::now()->format('d/m/Y') }}">
  </div>
</div>

<div class="checker-list-head">
  <span class="col-code">Code</span>
  <span class="col-nota">Reff</span>
  <span class="col-customer">Customer</span>
  <span class="col-action">Aksi</span>
</div>

<div id="checker-list" class="checker-list">
  {{-- diisi via JS dari endpoint json() --}}
</div>

<div id="checker-empty" class="checker-empty d-none">
  <i class="fa fa-inbox"></i>
  <p>Tidak ada DO yang perlu diproses saat ini.</p>
</div>

<script>
  window.__checkerDetailUrlTpl = '{{ route("superuser.penjualan.delivery_order.detail", ["id" => "__ID__"]) }}';
  window.__checkerPrintUrlTpl = '{{ route("superuser.penjualan.delivery_order.print_manifest", ["id" => "__ID__"]) }}';
</script>
@else
{{-- =========================================================
     SPV GUDANG / SUPERUSER: canvas ala CRM/APM/AO, samain sama
     tampilan detail_new.blade.php (do-canvas / do-toolbar / do-footer).
     ========================================================= --}}
<div class="do-page-wrap">
<div class="do-canvas">

  <div class="do-canvas-header">
    <div class="do-canvas-title">
      <span class="do-canvas-icon"><i class="fas fa-truck-loading"></i></span>
      <div>
        <h4>Delivery Order</h4>
        <small>Daftar DO yang perlu ditindaklanjuti SPV Gudang</small>
      </div>
    </div>
  </div>

  <div class="do-toolbar">
    <div class="spv-tabs" role="tablist">
      <label class="spv-tab">
        <input type="radio" name="show-control" value="default" checked>
        <span><i class="fas fa-clipboard-list"></i> List SPK</span>
      </label>
      <label class="spv-tab">
        <input type="radio" name="show-control" value="acc">
        <span><i class="fas fa-shipping-timed"></i> Cetak SJ</span>
      </label>
      <label class="spv-tab">
        <input type="radio" name="show-control" value="all">
        <span><i class="fas fa-money-bill-wave"></i> Update Resi</span>
      </label>
      <label class="spv-tab">
        <input type="radio" name="show-control" value="history">
        <span><i class="fas fa-history"></i> History Resi</span>
      </label>
    </div>

    <div class="spv-date">
      <i class="fa fa-calendar"></i>
      <input type="text" id="datesearch"
        value="{{ \Carbon\Carbon::now()->format('d/m/Y') }} - {{ \Carbon\Carbon::now()->format('d/m/Y') }}">
    </div>
  </div>

  <div class="do-canvas-body" style="padding-top:0;">
    <div class="do-list-toolbar">
      <div class="do-list-search">
        <i class="fa fa-search"></i>
        <input type="text" id="doSearch" placeholder="Cari kode DO / customer...">
      </div>
    </div>

    <div id="doList" class="do-list"></div>

    <div id="doListEmpty" class="do-list-empty d-none">
      <i class="fa fa-inbox"></i>
      <p>Tidak ada data.</p>
    </div>

    <div id="doListLoading" class="do-list-loading d-none">
      <i class="fa fa-circle-notch fa-spin"></i> Memuat...
    </div>

    <div class="do-pager-wrap">
      <div class="spv-pager">
        <button type="button" class="spv-pager-btn" id="doPagerPrev" title="Sebelumnya">&lsaquo;</button>
        <span class="spv-pager-info" id="doPagerInfo">1/1</span>
        <button type="button" class="spv-pager-btn" id="doPagerNext" title="Selanjutnya">&rsaquo;</button>
      </div>
    </div>
  </div>

</div>
</div>
@endif
@endsection

@if($useTabletLayout)
@push('styles')
<style>
  .checker-toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
  }
  .checker-toolbar-date {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f8f9fb;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 6px 10px;
    width: 190px;
    max-width: 60%;
    cursor: pointer;
  }
  .checker-toolbar-date i { color: #adb5bd; font-size: 12px; flex-shrink: 0; }
  .checker-toolbar-date input {
    border: none;
    font-size: 12px;
    width: 100%;
    background: transparent;
    color: #495057;
    cursor: pointer;
  }

  .checker-list-head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #adb5bd;
    border-bottom: 2px solid #eef0f2;
  }

  .checker-list {
    display: flex;
    flex-direction: column;
  }

  .checker-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    min-height: 46px;
    background: #fff;
    border-bottom: 1px solid #f1f3f5;
    transition: background .1s ease;
  }
  .checker-row:nth-child(even) { background: #fafbfc; }
  .checker-row:active { background: #f1f3f5; }
  .checker-row:last-child { border-bottom: none; }

  .col-code { flex: 0 0 18%; }
  .col-nota { flex: 0 0 18%; }
  .col-customer { flex: 1; min-width: 0; }
  .col-action { flex: 0 0 30%; text-align: right; }

  .checker-list-head .col-action { text-align: right; }

  .checker-row-code {
    font-size: 13px;
    font-weight: 700;
    color: #212529;
    flex: 0 0 18%;
  }
  .checker-row-nota {
    font-size: 13px;
    font-weight: 700;
    color: #212529;
    flex: 0 0 18%;
  }
  .checker-row-customer {
    font-size: 12.5px;
    color: #495057;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
    min-width: 0;
    padding-right: 6px;
  }
  .checker-row-actions {
    display: flex;
    gap: 5px;
    justify-content: flex-end;
    flex: 0 0 30%;
    flex-shrink: 0;
  }
  .checker-row-actions .btn {
    font-size: 11.5px;
    padding: 5px 8px;
    border-radius: 6px;
    white-space: nowrap;
    line-height: 1.3;
  }
  .checker-empty {
    text-align: center;
    padding: 32px 20px;
    color: #ced4da;
  }
  .checker-empty i {
    font-size: 30px;
    margin-bottom: 8px;
    display: block;
  }
  .checker-empty p {
    font-size: 12.5px;
  }
  .badge { font-size: 10.5px; padding: .35em .5em; }

  @media (max-width: 480px) {
    .col-code, .checker-row-code { flex: 0 0 26%; }
  }
</style>
@endpush
@endif

@unless($useTabletLayout)
@push('styles')
<style>
  /* ===========================================================
     Sama persis bahasa desain dengan detail_new.blade.php:
     .do-canvas / .do-canvas-header / .do-toolbar / .do-canvas-body
     Lebar dikunci setara tablet landscape biar konsisten & nggak
     jadi lautan putih kosong di layar lebar.
     =========================================================== */
  .do-page-wrap {
    max-width: 1180px;
    margin: 20px auto 32px;
  }
  .do-canvas {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    overflow: hidden;
  }
  .do-canvas-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 14px 20px;
    border-bottom: 1px solid #f1f3f5;
  }
  .do-canvas-title {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .do-canvas-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    background: #eef2ff;
    color: #4c6ef5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
  }
  .do-canvas-title h4 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: #212529;
  }
  .do-canvas-title small {
    display: block;
    font-size: 12px;
    color: #adb5bd;
    margin-top: 1px;
  }
  .do-canvas-body {
    padding: 18px 20px;
  }

  /* Status badge BAKU, sama definisinya dengan detail_new.blade.php */
  .do-status-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 4px 11px;
    border-radius: 20px;
    letter-spacing: .02em;
    white-space: nowrap;
    display: inline-block;
  }
  .do-status-ready      { background: #f1f3f5; color: #495057; }
  .do-status-packed     { background: #fff3bf; color: #995c00; }
  .do-status-delivering { background: #edf2ff; color: #3b5bdb; }
  .do-status-delivered  { background: #ebfbee; color: #2b8a3e; }
  .do-status-cancel     { background: #fff5f5; color: #c92a2a; }

  .do-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 12px 20px;
    background: #f8f9fb;
    border-top: 1px solid #f1f3f5;
    border-bottom: 1px solid #f1f3f5;
  }

  .spv-tabs {
    display: inline-flex;
    background: #eef0f2;
    border-radius: 8px;
    padding: 3px;
    gap: 2px;
  }
  .spv-tab {
    margin: 0;
    cursor: pointer;
  }
  .spv-tab input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
  }
  .spv-tab span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #868e96;
    padding: 7px 14px;
    border-radius: 6px;
    transition: all .15s ease;
    white-space: nowrap;
  }
  .spv-tab input:checked + span {
    background: #fff;
    color: #212529;
    box-shadow: 0 1px 3px rgba(0,0,0,.12);
  }

  .spv-date {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 7px 12px;
    width: 200px;
    max-width: 100%;
    cursor: pointer;
  }
  .spv-date i { color: #adb5bd; font-size: 12px; flex-shrink: 0; }
  .spv-date input {
    border: none;
    font-size: 12.5px;
    width: 100%;
    background: transparent;
    color: #495057;
    cursor: pointer;
  }

  /* ===========================================================
     Custom DO List (bukan DataTables) - kerasa kayak mobile app,
     tetap nyaman di laptop (field sejajar horizontal saat lebar cukup).
     =========================================================== */
  .do-list-toolbar {
    margin-bottom: 12px;
  }
  .do-list-search {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8f9fb;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 8px 12px;
    max-width: 320px;
  }
  .do-list-search i { color: #adb5bd; font-size: 13px; }
  .do-list-search input {
    border: none;
    background: transparent;
    font-size: 13.5px;
    width: 100%;
    color: #495057;
  }

  .do-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .do-card {
    background: #fff;
    border: 1px solid #eef0f2;
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: box-shadow .15s ease;
  }
  .do-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.06); }

  .do-card-code {
    font-size: 14px;
    font-weight: 700;
    color: #212529;
    flex: 0 0 100px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .do-card-date {
    font-size: 12px;
    color: #adb5bd;
    flex: 0 0 130px;
    white-space: nowrap;
  }
  .do-card-customer {
    font-size: 13px;
    color: #495057;
    flex: 1;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .do-card-status {
    flex: 0 0 100px;
  }
  .do-card-actions {
    flex: 0 0 auto;
    display: flex;
    gap: 6px;
    justify-content: flex-end;
  }
  .do-card-actions .btn,
  .do-card-actions a > .btn {
    padding: 6px 10px;
    font-size: 12.5px;
    border-radius: 7px;
    white-space: nowrap;
  }

  .do-list-empty, .do-list-loading {
    text-align: center;
    padding: 40px 20px;
    color: #ced4da;
    font-size: 13px;
  }
  .do-list-empty i { font-size: 34px; margin-bottom: 10px; display: block; }
  .do-list-loading i { margin-right: 6px; }

  .do-pager-wrap {
    display: flex;
    justify-content: center;
    margin-top: 14px;
  }

  .status-pill {
    font-size: 10.5px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 20px;
    letter-spacing: .02em;
    white-space: nowrap;
    display: inline-block;
  }
  .status-pill-ready      { background: #f1f3f5; color: #495057; }
  .status-pill-packed     { background: #fff3bf; color: #995c00; }
  .status-pill-delivering { background: #edf2ff; color: #3b5bdb; }
  .status-pill-delivered  { background: #ebfbee; color: #2b8a3e; }
  .status-pill-default    { background: #f1f3f5; color: #868e96; }

  .spv-pagination-custom,
  .spv-pager {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #f8f9fb;
    border: 1px solid #eef0f2;
    border-radius: 8px;
    padding: 4px;
  }
  .spv-pager-btn {
    border: none;
    background: transparent;
    width: 30px;
    height: 28px;
    border-radius: 6px;
    font-size: 14px;
    line-height: 1;
    color: #495057;
    cursor: pointer;
    transition: background .15s ease;
  }
  .spv-pager-btn:hover { background: #e9ecef; }
  .spv-pager-btn:disabled { color: #ced4da; cursor: not-allowed; }
  .spv-pager-info {
    font-size: 12.5px;
    font-weight: 600;
    color: #495057;
    padding: 0 8px;
    min-width: 44px;
    text-align: center;
  }

  /* MOBILE: field di-stack vertikal per card, bukan sejajar horizontal */
  @media (max-width: 768px) {
    .do-page-wrap { margin: 12px auto 20px; }
    .do-canvas-body { padding: 10px; }

    .do-card {
      flex-wrap: wrap;
      align-items: flex-start;
      padding: 10px 12px;
    }
    .do-card-code {
      flex: 1 1 auto;
      font-size: 14.5px;
      order: 1;
    }
    .do-card-status {
      flex: 0 0 auto;
      order: 2;
    }
    .do-card-date {
      flex: 1 1 100%;
      order: 3;
      margin-top: 2px;
    }
    .do-card-customer {
      flex: 1 1 100%;
      order: 4;
      white-space: normal;
      margin-top: 2px;
    }
    .do-card-actions {
      flex: 1 1 100%;
      order: 5;
      justify-content: flex-end;
      margin-top: 8px;
      padding-top: 8px;
      border-top: 1px solid #f4f5f6;
    }

    .do-list-search { max-width: 100%; }
  }

  @media (max-width: 768px) {
    .do-canvas-header, .do-toolbar { padding: 14px 16px; }
    .do-canvas-body { padding: 16px; }
  }

  @media (max-width: 640px) {
    .do-toolbar { flex-direction: column; align-items: stretch; }
    .spv-tabs { width: 100%; justify-content: space-between; }
    .spv-tab span { padding: 7px 8px; font-size: 12px; }
    .spv-tab span i { display: none; }
    .spv-date { width: 100%; }
  }
</style>
@endpush
@endunless

@include('superuser.asset.plugin.daterangepicker')

@unless($useTabletLayout)
{{-- DataTables plugin udah nggak dipakai lagi - list SPV sekarang custom render --}}
@endunless

@push('scripts')
@if($useTabletLayout)
<script>
$(document).ready(function() {
  let jsonUrl = '{{ route("superuser.penjualan.delivery_order.json") }}';

  function renderList(rows) {
    let $list = $('#checker-list');
    $list.empty();

    if (!rows || rows.length === 0) {
      $('#checker-empty').removeClass('d-none');
      return;
    }
    $('#checker-empty').addClass('d-none');

    rows.forEach(function(row) {
      let detailUrl = window.__checkerDetailUrlTpl.replace('__ID__', row.id);
      let printUrl = window.__checkerPrintUrlTpl.replace('__ID__', row.id);
      let alreadyPrinted = Number(row.print_count || 0) > 0;

      let rowHtml = '' +
        '<div class="checker-row" data-do-id="' + row.id + '">' +
        '  <span class="checker-row-code">' + (row.code || '-') + '</span>' +
        '  <span class="checker-row-nota">' + (row.do_code || '-') + '</span>' +
        '  <span class="checker-row-customer">' + (row.customer_other_address_id || '-') + '</span>' +
        '  <div class="checker-row-actions">' +
        '    <a href="' + printUrl + '" target="_blank" class="btn btn-outline-secondary btn-print-spk" title="Cetak SPK"><i class="fas fa-clipboard-list"></i></a>' +
        '    <a href="' + detailUrl + '" class="btn btn-primary btn-proses" style="' + (alreadyPrinted ? '' : 'display:none;') + '"><i class="fa fa-tasks"></i> Proses</a>' +
        '  </div>' +
        '</div>';
      $list.append(rowHtml);
    });
  }

  function loadList(from, to) {
    let params = { show: 'default' };
    if (from && to) {
      params.from = from;
      params.to = to;
    }
    $.getJSON(jsonUrl, params, function(res) {
      renderList(res.data || []);
    });
  }

  $('#datesearch').daterangepicker({
    autoUpdateInput: false
  });

  $('#datesearch').data('daterangepicker').setStartDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');
  $('#datesearch').data('daterangepicker').setEndDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');

  $('#datesearch').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    loadList(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'));
  });

  $(document).on('click', '.btn-print-spk', function() {
    $(this).closest('.checker-row').find('.btn-proses').show();
  });

  loadList();
});
</script>
@else
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.penjualan.delivery_order.json') }}';
  let valShow = "default";
  let currentPage = 0;   // 0-based, dikonversi ke 'start' pas fetch
  let pageLength = 10;
  let searchValue = '';
  let fromDate = null;
  let toDate = null;
  let searchDebounce = null;

  const statusMap = {
    'READY':      'ready',
    'PACKED':     'packed',
    'DELIVERING': 'delivering',
    'DELIVERED':  'delivered'
  };

  function statusBadge(text) {
    let cls = statusMap[text] || 'default';
    return '<span class="status-pill status-pill-' + cls + '">' + (text || '-') + '</span>';
  }

  function renderList(rows) {
    let $list = $('#doList');
    $list.empty();

    if (!rows || rows.length === 0) {
      $('#doListEmpty').removeClass('d-none');
      return;
    }
    $('#doListEmpty').addClass('d-none');

    rows.forEach(function (row) {
      let card = '' +
        '<div class="do-card">' +
        '  <div class="do-card-code">' + (row.do_code || row.code || '-') + '</div>' +
        '  <div class="do-card-date">' + (row.created_at ? row.created_at.display : '-') + '</div>' +
        '  <div class="do-card-customer">' + (row.customer_other_address_id || '-') + '</div>' +
        '  <div class="do-card-status">' + statusBadge(row.status) + '</div>' +
        '  <div class="do-card-actions">' + (row.action || '') + '</div>' +
        '</div>';
      $list.append(card);
    });
  }

  function updatePagerInfo(recordsFiltered) {
    let totalPages = Math.max(1, Math.ceil(recordsFiltered / pageLength));
    $('#doPagerInfo').text((currentPage + 1) + '/' + totalPages);
    $('#doPagerPrev').prop('disabled', currentPage <= 0);
    $('#doPagerNext').prop('disabled', currentPage + 1 >= totalPages);
  }

  function loadList() {
    $('#doListLoading').removeClass('d-none');
    $('#doListEmpty').addClass('d-none');

    // Bentuk request PERSIS seperti yang dikirim plugin DataTables,
    // karena endpoint json() di backend masih pakai Yajra/Table wrapper
    // yang mengharapkan format ini (draw, start, length, columns, order, search).
    let params = {
      show: valShow,
      draw: 1,
      start: currentPage * pageLength,
      length: pageLength,
      columns: [
        { data: 'id', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
        { data: 'created_at', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
        { data: 'do_code', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
        { data: 'customer_other_address_id', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
        { data: 'status', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
        { data: 'action', name: '', searchable: false, orderable: false, search: { value: '', regex: false } }
      ],
      order: [{ column: 1, dir: 'desc' }],
      search: { value: searchValue, regex: false }
    };

    if (fromDate && toDate) {
      params.from = fromDate;
      params.to = toDate;
    }

    $.ajax({
      url: datatableUrl,
      method: 'GET',
      data: params,
      dataType: 'json'
    }).done(function (res) {
      renderList(res.data || []);
      updatePagerInfo(res.recordsFiltered ?? (res.data ? res.data.length : 0));
    }).fail(function () {
      $('#doList').empty();
      $('#doListEmpty').removeClass('d-none');
    }).always(function () {
      $('#doListLoading').addClass('d-none');
    });
  }

  // ==== Tabs (show-control) ====
  $('input[type=radio][name=show-control]').on('change', function () {
    valShow = this.value;
    currentPage = 0;

    if (valShow === 'history') {
      let startOfMonth = moment().startOf('month');
      let today = moment();
      $('#datesearch').data('daterangepicker').setStartDate(startOfMonth);
      $('#datesearch').data('daterangepicker').setEndDate(today);
      $('#datesearch').val(startOfMonth.format('DD/MM/YYYY') + ' - ' + today.format('DD/MM/YYYY'));
      fromDate = startOfMonth.format('YYYY-MM-DD');
      toDate = today.format('YYYY-MM-DD');
    } else {
      let today = moment();
      $('#datesearch').data('daterangepicker').setStartDate(today);
      $('#datesearch').data('daterangepicker').setEndDate(today);
      $('#datesearch').val(today.format('DD/MM/YYYY') + ' - ' + today.format('DD/MM/YYYY'));
      fromDate = null;
      toDate = null;
    }

    loadList();
  });

  // ==== Date range ====
  $('#datesearch').daterangepicker({ autoUpdateInput: false });
  $('#datesearch').data('daterangepicker').setStartDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');
  $('#datesearch').data('daterangepicker').setEndDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');

  $('#datesearch').on('apply.daterangepicker', function (ev, picker) {
    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    fromDate = picker.startDate.format('YYYY-MM-DD');
    toDate = picker.endDate.format('YYYY-MM-DD');
    currentPage = 0;
    loadList();
  });

  // ==== Search (debounced) ====
  $('#doSearch').on('input', function () {
    let val = $(this).val();
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(function () {
      searchValue = val;
      currentPage = 0;
      loadList();
    }, 400);
  });

  // ==== Pager ====
  $('#doPagerPrev').on('click', function () {
    if (currentPage > 0) { currentPage--; loadList(); }
  });
  $('#doPagerNext').on('click', function () {
    currentPage++; loadList();
  });

  loadList();
});
</script>
@endif
@endpush