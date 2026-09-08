@extends('superuser.app')

@push('styles')
<style>
  @include('superuser.gudang.purchase_order._po_styles')
</style>
@endpush

@section('content')
@php
  $poBrandName = optional($purchase_order->brandLokal)->brand_name ?? '';
  $isDraft = $purchase_order->status == $purchase_order::STATUS['DRAFT'];
@endphp

<nav class="breadcrumb bg-white py-10" style="margin-bottom:8px;">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item">Purchase Order (PO)</span>
  <span class="breadcrumb-item active" id="crumb-code">{{ $purchase_order->code }}</span>
</nav>

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)<p class="mb-0">{{ $error }}</p>@endforeach
</div>
@endif
<div id="alert-block"></div>

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<div class="po-wrap">
  {{-- ===== KIRI: info PO, sticky ===== --}}
  <div class="po-col-left">
    <div class="po-info-card">
      <button type="button" class="btn btn-sm btn-circle btn-alt-secondary btn-collapse-info" id="btnHideInfo" title="Liput panel info (tabel jadi lebih lebar)"><i class="fa fa-chevron-left"></i></button>
      <div class="po-view-mode">
      <p class="po-info-code"><span id="card-code">{{ $purchase_order->code }}</span>
        <button type="button" class="btn btn-sm btn-circle btn-alt-warning ml-5" id="btnEditHeader" title="Edit info PO langsung di sini"><i class="fa fa-pencil"></i></button>
      </p>
      <span class="po-status-pill {{ $isDraft ? 'is-draft' : 'is-live' }}"><span class="dot"></span>{{ $purchase_order->status() }}</span>

      <ul class="po-info-list">
        <li>
          <span class="po-info-icon"><i class="fa fa-warehouse"></i></span>
          <span class="po-info-body"><span class="po-info-label">Warehouse</span><span class="po-info-val" id="view-warehouse" title="{{ optional($purchase_order->warehouse)->name ?? '-' }}">{{ optional($purchase_order->warehouse)->name ?? '-' }}</span></span>
        </li>
        <li>
          <span class="po-info-icon"><i class="fa fa-tag"></i></span>
          <span class="po-info-body"><span class="po-info-label">Brand</span><span class="po-info-val" id="view-brand" title="{{ $poBrandName ?: '-' }}">{{ $poBrandName ?: '-' }}</span></span>
        </li>
        <li>
          <span class="po-info-icon"><i class="fa fa-calendar"></i></span>
          <span class="po-info-body"><span class="po-info-label">ETD</span><span class="po-info-val" id="view-etd">{{ $purchase_order->etd ? \Carbon\Carbon::parse($purchase_order->etd)->format('d-m-Y') : '-' }}</span></span>
        </li>
        <li>
          <span class="po-info-icon"><i class="fa fa-sticky-note"></i></span>
          <span class="po-info-body"><span class="po-info-label">Note</span><span class="po-info-val" id="view-note" title="{{ $purchase_order->note ?: '-' }}">{{ $purchase_order->note ?: '-' }}</span></span>
        </li>
      </ul>
      </div>

      <div class="po-edit-mode" style="display:none;">
        <div class="form-row align-items-center hdr-field">
          <div class="col-4"><label class="field-label mb-0">PO Code<span class="req">*</span></label></div>
          <div class="col-8"><input type="text" class="form-control form-control-sm" id="hdr-code" value="{{ $purchase_order->code }}"></div>
        </div>
        <div class="form-row align-items-center hdr-field">
          <div class="col-4"><label class="field-label mb-0">Warehouse<span class="req">*</span></label></div>
          <div class="col-8">
            <select class="form-control form-control-sm" id="hdr-warehouse">
              @foreach($warehouses as $wh)
              <option value="{{ $wh->id }}" {{ $purchase_order->warehouse_id == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-row align-items-center hdr-field">
          <div class="col-4"><label class="field-label mb-0">Brand<span class="req">*</span></label></div>
          <div class="col-8">
            <select class="form-control form-control-sm" id="hdr-brand" title="Ganti brand memuat ulang produk & membatalkan baris baru">
              @foreach($merek as $br)
              <option value="{{ $br->id }}" data-name="{{ $br->brand_name }}" {{ $purchase_order->brand_lokal_id == $br->id ? 'selected' : '' }}>{{ $br->brand_name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-row align-items-center hdr-field">
          <div class="col-4"><label class="field-label mb-0">ETD<span class="req">*</span></label></div>
          <div class="col-8"><input type="date" class="form-control form-control-sm" id="hdr-etd" value="{{ $purchase_order->etd ? date('Y-m-d', strtotime($purchase_order->etd)) : '' }}"></div>
        </div>
        <div class="form-row align-items-center hdr-field">
          <div class="col-4"><label class="field-label mb-0">Note</label></div>
          <div class="col-8"><textarea class="form-control form-control-sm" id="hdr-note" rows="2">{{ $purchase_order->note }}</textarea></div>
        </div>
        <div class="row">
          <div class="col-6">
            <button type="button" class="btn btn-sm btn-primary btn-block" id="btnSaveHeader"><i class="fa fa-check mr-5"></i>Simpan</button>
          </div>
          <div class="col-6">
            <button type="button" class="btn btn-sm btn-secondary btn-block" id="btnCancelHeader">Batal</button>
          </div>
        </div>
      </div>

      <div class="po-info-actions">
        @if($isDraft)
          <div class="row">
            <div class="col-6">
              <a href="{{ route('superuser.gudang.purchase_order.index') }}" class="btn btn-sm btn-block btn-warning" title="Kembali ke daftar PO"><i class="fa fa-arrow-left"></i>Kembali</a>
            </div>
            <div class="col-6">
              <button type="button" class="btn btn-sm btn-block btn-edit btn-edit-header"><i class="fa fa-pencil"></i>Edit PO</button>
            </div>
          </div>
          <a href="javascript:saveConfirmation('{{ route('superuser.gudang.purchase_order.publish', $purchase_order->id) }}')" class="btn btn-sm btn-block btn-publish"><i class="fa fa-check"></i>Publish PO</a>
        @else
          <a href="{{ route('superuser.gudang.purchase_order.index') }}" class="btn btn-sm btn-back"><i class="fa fa-arrow-left"></i>Kembali ke daftar PO</a>
          <button type="button" class="btn btn-sm btn-edit btn-edit-header"><i class="fa fa-pencil"></i>Edit</button>
          <a href="javascript:saveConfirmation('{{ route('superuser.gudang.purchase_order.save_modify', [$purchase_order->id, 'save']) }}')" class="btn btn-sm btn-publish"><i class="fa fa-check"></i>Save</a>
          @role('Developer|SuperAdmin', 'superuser')
            <a href="javascript:saveConfirmation('{{ route('superuser.gudang.purchase_order.unpublish', $purchase_order->id) }}')" class="btn btn-sm btn-danger-ghost">Unpublish</a>
            <a href="javascript:saveConfirmation2('{{ route('superuser.gudang.purchase_order.save_modify', [$purchase_order->id, 'save-acc']) }}')" class="btn btn-sm btn-success-ghost">ACC</a>
          @endrole
        @endif
      </div>
    </div>
  </div>

  {{-- ===== KANAN: tab kemasan + daftar produk ===== --}}
  <div class="po-col-right">
    <div class="po-main-card">
      <div class="po-main-inner">
        <button type="button" class="btn btn-sm btn-secondary mb-5" id="btnShowInfo" style="display:none;"><i class="fa fa-info-circle mr-5"></i>Tampilkan Info PO</button>

        <div class="po-tabs-wrap">
          <div id="orphanBar" class="alert alert-warning py-5 px-10 mb-10" style="display:none; font-size:12px;">
            <i class="fa fa-exclamation-triangle mr-5"></i><strong>Kemasan lain terdeteksi</strong> — produk di bawah ini kemasannya di luar 4 halaman fix:
            <div id="orphanList" class="mt-5"></div>
          </div>

          <ul class="nav nav-tabs" role="tablist">
            @foreach($pack_tabs as $i => $tab)
            <li class="nav-item">
              <a class="nav-link {{ $i == 0 ? 'active' : '' }}" data-toggle="tab" href="#tab-{{ $tab['key'] }}" role="tab" title="{{ implode(', ', $tab['pack_names']) }}">{{ implode(' / ', $tab['pack_names']) }} <span class="badge badge-primary" id="badge-{{ $tab['key'] }}">0</span></a>
            </li>
            @endforeach
          </ul>

          <div class="tab-content">
            @foreach($pack_tabs as $i => $tab)
            <div class="tab-pane {{ $i == 0 ? 'active' : '' }}" id="tab-{{ $tab['key'] }}" role="tabpanel" data-tabkey="{{ $tab['key'] }}">
              <div class="po-panel">
              @if($isDraft)
              <div class="po-inputbar">
              <div class="po-input-caption">Input produk ke {{ implode(' / ', $tab['pack_names']) }}</div>
              <div class="form-row align-items-center">
                <div class="form-group col-xl-5 col-lg-5 col-md-12">
                  <select class="form-control form-control-sm js-select2-tab product-sel" data-tabkey="{{ $tab['key'] }}" style="width:100%;" title="Produk ({{ $poBrandName ?: 'brand?' }}) — wajib">
                    <option value="">— Pilih produk ({{ $poBrandName ?: 'brand?' }}) —</option>
                  </select>
                </div>
                <div class="form-group col-xl-1 col-lg-1 col-md-4">
                  <input type="number" class="form-control form-control-sm text-center qty-inp" data-tabkey="{{ $tab['key'] }}" placeholder="Qty" title="Qty (KG) — wajib, lebih dari 0" step="any" min="0">
                </div>
                <div class="form-group col-xl-6 col-lg-6 col-md-8">
                  <div class="po-toolbar-btns">
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-note-tab" data-tabkey="{{ $tab['key'] }}" title="Isi catatan (opsional)"><i class="fa fa-sticky-note-o"></i>Note <i class="fa fa-check-circle text-success note-flag" data-tabkey="{{ $tab['key'] }}" style="display:none;"></i></button>
                    <button type="button" class="btn btn-sm btn-success btn-add-tab" data-tabkey="{{ $tab['key'] }}" title="Tambahkan sebagai baris baru"><i class="fa fa-plus"></i>Tambah</button>
                    <button type="button" class="btn btn-sm btn-primary btn-save-tab" data-tabkey="{{ $tab['key'] }}" disabled><i class="fa fa-save"></i>Simpan</button>
                  </div>
                </div>
                </div>
              </div>
              @endif
              <div class="table-responsive po-table-scroll">
                <table class="table table-sm table-bordered table-striped mb-0 table-fit">
                  <thead class="thead-light">
                      <tr>
                        <th class="text-center" style="width:60px;">#</th>
                        <th class="text-center" style="width:13%;">Kemasan</th>
                        <th class="text-center">Produk</th>
                        <th class="text-center" style="width:80px;">Qty</th>
                        <th class="text-center" style="width:22%;">Note</th>
                        <th class="text-center" style="width:22%;">Customer</th>
                        @if($isDraft)<th class="text-center" style="width:80px;">Aksi</th>@endif
                      </tr>
                    </thead>
                    <tbody class="details-body" data-tabkey="{{ $tab['key'] }}">
                      <tr><td colspan="{{ $isDraft ? 7 : 6 }}" class="text-center text-muted"><i class="fa fa-spinner fa-spin mr-5"></i>Memuat...</td></tr>
                  </tbody>
                </table>
              </div>
              </div>
            </div>
            @endforeach

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal catatan produk: Note = status produksi (urgent, dll), Customer = nama customer (default STOCK) -->
<div class="modal fade" id="modalNote" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Note &amp; Customer</h5>
        <button type="button" class="close" id="btnNoteX" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="note-tabkey" value="">
        <div class="form-group mb-5">
          <textarea class="form-control form-control-sm" id="note-n1" rows="2" placeholder="Note status produksi, mis. URGENT (opsional)..."></textarea>
        </div>
        <div class="form-group mb-0">
          <textarea class="form-control form-control-sm" id="note-n2" rows="2" placeholder="Nama customer, mis. STOCK (opsional)..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" id="btnNoteCancel" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-sm btn-primary" id="btnNoteOk">OK</button>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  var IS_DRAFT = @json($isDraft);
  var PO_BRAND = @json($poBrandName);
  var CSRF = '{{ csrf_token() }}';
  var STORE_URL = '{{ route("superuser.gudang.purchase_order.detail.store", $purchase_order->id) }}';
  var LIST_URL = '{{ route("superuser.gudang.purchase_order.detail.detail_json", $purchase_order->id) }}';
  var GET_PRODUCT_URL = '{{ route("superuser.gudang.purchase_order.detail.get_product") }}';
  var PACK_TABS = @json($pack_tabs);
  var FIXED_IDS = @json($fixed_pack_ids).map(Number);
  var TAB_PACK = {};
  var TAB_PACK_NAME = {};
  PACK_TABS.forEach(function(t) {
    TAB_PACK[t.key] = (t.pack_ids || []).map(Number);
    TAB_PACK_NAME[t.key] = {};
    (t.pack_ids || []).forEach(function(id, idx) {
      TAB_PACK_NAME[t.key][Number(id)] = t.pack_names[idx] || id;
    });
  });
  var TAB_KEYS = PACK_TABS.map(function(t) { return t.key; });

  // Kemasan otomatis mengikuti produk yang dipilih (dropdown kemasan dihapus).
  // Tab 1 kemasan: selalu pakai itu. Tab 2 kemasan: pakai abjad pertama + info.
  var SELECTED_PACK = {};
  function defaultPack(tabkey) {
    var ids = TAB_PACK[tabkey] || [];
    if (ids.length === 1) {
      return { id: ids[0], name: (TAB_PACK_NAME[tabkey] || {})[ids[0]] || '' };
    }
    return null;
  }
  TAB_KEYS.forEach(function(k) { SELECTED_PACK[k] = defaultPack(k); });

  var SAVED = [];
  var STAGED = {};
  TAB_KEYS.forEach(function(k) { STAGED[k] = []; });
  var PENDING_NOTE = {}; // catatan per tab untuk baris yang sedang diinput: {n1, n2}
  TAB_KEYS.forEach(function(k) { PENDING_NOTE[k] = { n1: '', n2: '' }; });
  var EDITING = null; // {id, tabkey} — baris yang sedang diedit inline

  $('.js-select2-tab').select2({ width: '100%' });

  // Select2 di tab tersembunyi salah hitung lebar (caption meluber) — betulkan saat tab dibuka
  $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
    var $pane = $($(e.target).attr('href'));
    $pane.find('.js-select2-tab').each(function() {
      $(this).next('.select2-container').css('width', '100%');
    });
  });

  function esc(s) {
    return String(s === null || s === undefined ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function inTab(row, tabkey) {
    return (TAB_PACK[tabkey] || []).indexOf(Number(row.packaging_id)) !== -1;
  }

  function getOrphans() {
    return SAVED.filter(function(r) {
      return TAB_KEYS.every(function(k) { return !inTab(r, k); });
    });
  }

  function renderOrphans() {
    var orphans = getOrphans();
    var $bar = $('#orphanBar');
    if (orphans.length === 0) { $bar.hide(); return; }
    var html = '';
    orphans.forEach(function(r) {
      html += '<div class="orphan-item"><span><strong>' + esc(r.code) + '</strong> — ' + esc(r.name)
        + ' · Qty ' + esc(r.quantity) + ' · ' + esc(r.pack_name) + '</span>'
        + '<button type="button" class="btn btn-sm btn-danger btn-del-saved" data-url="' + r.destroy_url + '" title="Hapus"><i class="fa fa-times"></i></button></div>';
    });
    $('#orphanList').html(html);
    $bar.show();
  }

  function rowHtmlSaved(row, n, tabkey) {
    var prodLabel = esc(row.code) + ' - ' + esc(row.name);
    if (IS_DRAFT && EDITING && EDITING.id === row.id) {
      return '<tr class="row-editing">'
        + '<td class="text-center">' + n + '</td>'
        + '<td class="text-center">' + esc(row.pack_name) + '</td>'
        + '<td title="' + prodLabel + '">' + prodLabel + '</td>'
        + '<td class="text-center"><input type="number" class="cell-inp edit-qty" value="' + esc(row.quantity) + '" step="any" min="0" style="width:75px;"></td>'
        + '<td><input type="text" class="cell-inp edit-prod" value="' + esc(row.note_produksi || '') + '" placeholder="Note..."></td>'
        + '<td><input type="text" class="cell-inp edit-repack" value="' + esc(row.note_repack || '') + '" placeholder="Customer..."></td>'
        + '<td class="text-center" style="white-space:nowrap;">'
        + '<button type="button" class="btn btn-sm btn-circle btn-alt-success btn-save-edit" data-url="' + row.update_url + '" data-ppid="' + esc(row.product_packaging_id || '') + '" data-packid="' + esc(row.packaging_id || '') + '" title="Simpan perubahan"><i class="fa fa-check"></i></button> '
        + '<button type="button" class="btn btn-sm btn-circle btn-alt-secondary btn-cancel-edit" data-tabkey="' + tabkey + '" title="Batalkan"><i class="fa fa-times"></i></button>'
        + '</td></tr>';
    }
    var h = '<tr>'
      + '<td class="text-center">' + n + '</td>'
      + '<td class="text-center">' + esc(row.pack_name) + '</td>'
      + '<td>' + prodLabel + '</td>'
      + '<td class="text-center">' + esc(row.quantity) + '</td>'
      + '<td class="text-center cell-nowrap" title="' + esc(row.note_produksi || '') + '">' + esc(row.note_produksi || '-') + '</td>'
      + '<td class="text-center cell-nowrap" title="' + esc(row.note_repack || '') + '">' + esc(row.note_repack || '-') + '</td>';
    if (IS_DRAFT) {
      h += '<td class="text-center" style="white-space:nowrap;">'
        + '<button type="button" class="btn btn-sm btn-circle btn-alt-warning btn-edit-row" data-id="' + row.id + '" data-tabkey="' + tabkey + '" title="Edit langsung di sini"><i class="fa fa-pencil"></i></button> '
        + '<button type="button" class="btn btn-sm btn-circle btn-alt-danger btn-del-saved" data-url="' + row.destroy_url + '" title="Hapus"><i class="fa fa-times"></i></button>'
        + '</td>';
    }
    return h + '</tr>';
  }

  function rowHtmlStaged(s, n, justAdded) {
    var info = ((PRODUCT_INFO[s.tabkey] || {})[s.productId]) || {};
    var prodLabel = info.code ? (info.code + ' - ' + info.name) : s.productText.replace(/\s*\[.*\]$/, '');
    return '<tr class="table-warning' + (justAdded ? ' row-just-added' : '') + '">'
      + '<td class="text-center">' + n + ' <span class="badge badge-warning">baru</span></td>'
      + '<td class="text-center">' + esc(s.packText) + '</td>'
      + '<td title="' + esc(prodLabel) + '">' + esc(prodLabel) + '</td>'
      + '<td class="text-center">' + esc(s.qty) + '</td>'
      + '<td class="text-center cell-nowrap" title="' + esc(s.prod || '') + '">' + esc(s.prod || '-') + '</td>'
      + '<td class="text-center cell-nowrap" title="' + esc(s.repack || '') + '">' + esc(s.repack || '-') + '</td>'
      + (IS_DRAFT ? '<td class="text-center"><button type="button" class="btn btn-sm btn-circle btn-alt-danger btn-remove-staged" data-tabkey="' + s.tabkey + '" data-idx="' + s.idx + '" title="Batalkan baris ini"><i class="fa fa-times"></i></button></td>' : '')
      + '</tr>';
  }

  function renderTab(tabkey, justAddedIdx) {
    var $tb = $('.details-body[data-tabkey="' + tabkey + '"]');
    var saved = SAVED.filter(function(r) { return inTab(r, tabkey); });
    saved.sort(function(a, b) {
      var kp = String(a.pack_name || '').localeCompare(String(b.pack_name || ''));
      return kp !== 0 ? kp : String(a.name || '').localeCompare(String(b.name || ''));
    });
    var html = '', n = 0;
    saved.forEach(function(r) { n++; html += rowHtmlSaved(r, n, tabkey); });
    STAGED[tabkey].forEach(function(s) { n++; html += rowHtmlStaged(s, n, s.idx === justAddedIdx); });
    if (n === 0) {
      html = '<tr class="empty-state"><td colspan="' + (IS_DRAFT ? 7 : 6) + '" class="text-center">'
        + '<div class="empty-title">Belum ada produk di halaman ini</div>'
        + '</td></tr>';
    }
    $tb.html(html);
    $('#badge-' + tabkey).text(saved.length);
    var sc = STAGED[tabkey].length;
    var $btnSave = $('.btn-save-tab[data-tabkey="' + tabkey + '"]');
    $btnSave.prop('disabled', sc === 0);
    $btnSave.html('<i class="fa fa-save"></i> Simpan' + (sc > 0 ? ' (' + sc + ' baru)' : ''));
  }

  function renderAll() { TAB_KEYS.forEach(function(k) { renderTab(k); }); renderOrphans(); }

  function refreshAll() {
    $.ajax({
      url: LIST_URL, method: 'GET', dataType: 'JSON',
      success: function(resp) {
        SAVED = (resp && resp.Data) ? resp.Data : [];
        EDITING = null;
        renderAll();
      },
      error: function() { showToast('danger', 'Gagal memuat daftar produk.'); }
    });
  }

  var PRODUCT_PACKS = {}; // tabkey -> {productId: [{id, name}]}
  var PRODUCT_INFO = {}; // tabkey -> {productId: {code, name}}

  function loadTabProducts(tabkey, packIds) {
    PRODUCT_PACKS[tabkey] = {};
    PRODUCT_INFO[tabkey] = {};
    $.ajax({
      url: GET_PRODUCT_URL, method: 'GET',
      data: { brand_name: PO_BRAND, packaging_id: packIds || [] },
      dataType: 'JSON',
      success: function(resp) {
        var opt = '<option value="">— Pilih produk —</option>';
        $.each(resp.Data || [], function(i, e) {
          var packs = e.packagings || [];
          PRODUCT_PACKS[tabkey][e.id] = packs;
          PRODUCT_INFO[tabkey][e.id] = { code: e.productCode, name: e.productName };
          var packLabel = packs.length > 0
            ? ' [' + packs.map(function(p) { return p.name; }).join(', ') + ']'
            : '';
          opt += '<option value="' + e.id + '">' + esc(e.productCode + ' - ' + e.productName + packLabel) + '</option>';
        });
        $('.product-sel[data-tabkey="' + tabkey + '"]').html(opt);
      }
    });
  }

  // init
  @foreach($pack_tabs as $tab)
    loadTabProducts('{{ $tab["key"] }}', @json($tab['pack_ids']));
  @endforeach
  refreshAll();

  // Kemasan otomatis mengikuti relasi produk yang dipilih.
  $(document).on('change', '.product-sel', function() {
    var tabkey = $(this).data('tabkey');
    var productId = $(this).val();
    if (!productId) { SELECTED_PACK[tabkey] = defaultPack(tabkey); return; }
    var packs = ((PRODUCT_PACKS[tabkey] || {})[productId]) || [];
    var allowed = TAB_PACK[tabkey] || [];
    var valid = packs.filter(function(p) { return allowed.indexOf(Number(p.id)) !== -1; });
    if (valid.length === 0) {
      SELECTED_PACK[tabkey] = null;
      showToast('warning', 'Produk ini tidak punya kemasan tab ini. Pilih produk lain.');
      return;
    }
    valid.sort(function(a, b) { return String(a.name).localeCompare(String(b.name)); });
    SELECTED_PACK[tabkey] = { id: valid[0].id, name: valid[0].name };
    if (valid.length > 1) {
      showToast('info', 'Produk ini punya ' + valid.length + ' kemasan, dipakai: ' + valid[0].name + '. Lihat di baris tabel.');
    }
  });

  // Bersihkan tanda error begitu user mulai mengisi
  $(document).on('input change', '.qty-inp, .packaging-sel', function() {
    $(this).removeClass('is-invalid');
  });
  $(document).on('change', '.js-select2-tab', function() {
    $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
  });

  function updateNoteFlag(tabkey) {
    var p = PENDING_NOTE[tabkey] || { n1: '', n2: '' };
    $('.note-flag[data-tabkey="' + tabkey + '"]').toggle(!!(p.n1 || p.n2));
  }

  $(document).on('click', '.btn-note-tab', function() {
    var tabkey = $(this).data('tabkey');
    var p = PENDING_NOTE[tabkey] || { n1: '', n2: '' };
    $('#note-tabkey').val(tabkey);
    $('#note-n1').val(p.n1);
    $('#note-n2').val(p.n2);
    $('#modalNote').modal('show');
  });

  $(document).on('click', '#btnNoteOk', function() {
    var tabkey = $('#note-tabkey').val();
    if (!tabkey) { $('#modalNote').modal('hide'); return; }
    PENDING_NOTE[tabkey] = { n1: $('#note-n1').val() || '', n2: $('#note-n2').val() || '' };
    updateNoteFlag(tabkey);
    $('#modalNote').modal('hide');
  });

  // Penutup eksplisit (tidak hanya mengandalkan data-dismiss)
  $(document).on('click', '#btnNoteCancel, #btnNoteX', function() {
    $('#modalNote').modal('hide');
  });
  $('#modalNote').on('shown.bs.modal', function() {
    $('#note-n1').trigger('focus');
  });

  $(document).on('click', '.btn-add-tab', function() {
    var tabkey = $(this).data('tabkey');
    var $pane = $('#tab-' + tabkey);
    var productId = $pane.find('.product-sel').val();
    var productText = $pane.find('.product-sel option:selected').text();
    var qty = $pane.find('.qty-inp').val();
    var pending = PENDING_NOTE[tabkey] || { n1: '', n2: '' };
    var prod = pending.n1 || '';
    var repack = pending.n2 || '';
    var sel = SELECTED_PACK[tabkey];
    var packId = sel ? sel.id : '';
    var packText = sel ? sel.name : '';

    var invalid = false;
    if (!productId) { $pane.find('.js-select2-tab').next('.select2-container').find('.select2-selection').addClass('is-invalid'); invalid = true; }
    if (!qty || Number(qty) <= 0) { $pane.find('.qty-inp').addClass('is-invalid'); invalid = true; }
    if (!packId) { invalid = true; }
    if (invalid) {
      showToast('danger', 'Lengkapi dulu: produk, qty (>0), dan kemasan otomatis (ganti produk bila kosong).');
      return;
    }

    var newIdx = Date.now() + Math.floor(Math.random() * 1000);
    STAGED[tabkey].push({ idx: newIdx, tabkey: tabkey, productId: productId, productText: productText, qty: qty, packId: packId, packText: packText, prod: prod, repack: repack });
    renderTab(tabkey, newIdx);
    $pane.find('.product-sel').val('').trigger('change');
    $pane.find('.qty-inp').val('');
    PENDING_NOTE[tabkey] = { n1: '', n2: '' };
    updateNoteFlag(tabkey);
    SELECTED_PACK[tabkey] = defaultPack(tabkey);
  });

  // Edit inline: ubah baris jadi mode edit (qty + catatan), produk & kemasan terkunci
  $(document).on('click', '.btn-edit-row', function() {
    EDITING = { id: $(this).data('id'), tabkey: $(this).data('tabkey') };
    renderTab(EDITING.tabkey);
  });

  $(document).on('click', '.btn-cancel-edit', function() {
    EDITING = null;
    renderTab($(this).data('tabkey'));
  });

  $(document).on('click', '.btn-save-edit', function() {
    var $btn = $(this);
    var $tr = $btn.closest('tr');
    var qty = $tr.find('.edit-qty').val();
    if (qty === '' || qty === null || Number(qty) < 0) {
      Swal.fire('Belum lengkap', 'Qty wajib diisi (minimal 0).', 'warning');
      return;
    }
    $.ajax({
      url: $btn.data('url'), method: 'POST',
      data: {
        _token: CSRF, _method: 'PUT',
        product_packaging_id: $btn.data('ppid'),
        packaging_id: $btn.data('packid'),
        quantity: qty,
        note_produksi: $tr.find('.edit-prod').val(),
        note_repack: $tr.find('.edit-repack').val()
      },
      dataType: 'JSON',
      beforeSend: function() { $btn.prop('disabled', true); },
      success: function(resp) {
        var notif = (resp && resp.data && resp.data.notification) || {};
        EDITING = null;
        showToast('success', notif.content || 'Perubahan disimpan.');
        refreshAll();
      },
      error: function(xhr) {
        var resp = xhr.responseJSON || {};
        var notif = (resp.data && resp.data.notification) || {};
        var msg = notif.content || 'Gagal menyimpan.';
        showToast('danger', Array.isArray(msg) ? msg.join('<br>') : msg);
        $btn.prop('disabled', false);
      }
    });
  });

  $(document).on('click', '.btn-remove-staged', function() {
    var tabkey = $(this).data('tabkey');
    var idx = $(this).data('idx');
    STAGED[tabkey] = STAGED[tabkey].filter(function(s) { return s.idx != idx; });
    renderTab(tabkey);
  });

  $(document).on('click', '.btn-save-tab', function() {
    var tabkey = $(this).data('tabkey');
    var rows = STAGED[tabkey];
    if (rows.length === 0) return;
    var $btn = $(this);
    Swal.fire({
      title: 'Simpan ' + rows.length + ' produk ke PO?',
      text: 'Baris yang sudah disimpan hanya bisa diedit atau dihapus satu per satu, bukan dibatalkan dari sini.',
      type: 'question', showCancelButton: true,
      confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal'
    }).then(function(res) {
      if (!res || (!res.isConfirmed && !res.value)) return;
      var payload = { _token: CSRF, merek: PO_BRAND, product_packaging_id: [], qty: [], packaging_id: [], note_produksi: [], note_repack: [] };
      rows.forEach(function(s) {
        payload.product_packaging_id.push(s.productId);
        payload.qty.push(s.qty);
        payload.packaging_id.push(s.packId);
        payload.note_produksi.push(s.prod);
        payload.note_repack.push(s.repack);
      });
      $.ajax({
        url: STORE_URL, method: 'POST', data: payload, dataType: 'JSON',
        beforeSend: function() { $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...'); },
        success: function(resp) {
          if (resp.IsError) {
            showToast('danger', resp.Message);
          } else {
            STAGED[tabkey] = [];
            showToast('success', resp.Message);
            refreshAll();
          }
        },
        error: function() { showToast('danger', 'Cek Koneksi Internet'); },
        complete: function() { renderTab(tabkey); }
      });
    });
  });

  // ===== Panel info lipat (tabel jadi full-width saat produk banyak) =====
  $(document).on('click', '#btnHideInfo', function() {
    $('.po-col-left').hide();
    $('#btnShowInfo').show();
  });
  $(document).on('click', '#btnShowInfo', function() {
    $('.po-col-left').show();
    $('#btnShowInfo').hide();
  });

  // ===== Edit header PO inline (tanpa redirect) =====
  var UPDATE_URL = '{{ route("superuser.gudang.purchase_order.update", $purchase_order->id) }}';
  var HDR = {
    code: @json($purchase_order->code),
    warehouseId: @json($purchase_order->warehouse_id),
    brandId: @json($purchase_order->brand_lokal_id),
    etd: @json($purchase_order->etd ? date('Y-m-d', strtotime($purchase_order->etd)) : ''),
    note: @json($purchase_order->note ?: '')
  };

  function headerEditMode(on) {
    if (on) { $('.po-view-mode, .po-info-actions').hide(); $('.po-edit-mode').show(); }
    else { $('.po-edit-mode').hide(); $('.po-view-mode, .po-info-actions').show(); }
  }

  function fillHeaderForm() {
    $('#hdr-code').val(HDR.code);
    $('#hdr-warehouse').val(HDR.warehouseId);
    $('#hdr-brand').val(HDR.brandId);
    $('#hdr-etd').val(HDR.etd);
    $('#hdr-note').val(HDR.note);
  }

  function fmtDateID(ymd) {
    if (!ymd) return '-';
    var p = ymd.split('-');
    return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : ymd;
  }

  $(document).on('click', '#btnEditHeader, .btn-edit-header', function() {
    fillHeaderForm();
    headerEditMode(true);
  });

  $(document).on('click', '#btnCancelHeader', function() {
    fillHeaderForm();
    headerEditMode(false);
  });

  $(document).on('click', '#btnSaveHeader', function() {
    var $btn = $(this);
    var payload = {
      _token: CSRF, _method: 'PUT',
      code: $('#hdr-code').val(),
      warehouse: $('#hdr-warehouse').val(),
      brand_lokal_id: $('#hdr-brand').val(),
      etd: $('#hdr-etd').val(),
      note: $('#hdr-note').val()
    };
    if (!payload.code || !payload.warehouse || !payload.brand_lokal_id || !payload.etd) {
      Swal.fire('Belum lengkap', 'Kode, warehouse, brand, dan ETD wajib diisi.', 'warning');
      return;
    }
    var brandChanged = String(payload.brand_lokal_id) !== String(HDR.brandId);
    $.ajax({
      url: UPDATE_URL, method: 'POST', data: payload, dataType: 'JSON',
      beforeSend: function() { $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>...'); },
      success: function(resp) {
        var data = resp.data || {};
        var notif = data.notification || {};
        if (resp.status && resp.status.code !== 200) {
          var msg = notif.content || 'Gagal menyimpan.';
          showToast('danger', Array.isArray(msg) ? msg.join('<br>') : msg);
          return;
        }
        var whName = $('#hdr-warehouse option:selected').text();
        var brName = $('#hdr-brand option:selected').text();
        HDR = { code: payload.code, warehouseId: payload.warehouse, brandId: payload.brand_lokal_id, etd: payload.etd, note: payload.note };
        $('#card-code').text(HDR.code);
        $('#crumb-code').text(HDR.code);
        $('#view-warehouse').text(whName).attr('title', whName);
        $('#view-brand').text(brName).attr('title', brName);
        $('#view-etd').text(fmtDateID(HDR.etd));
        $('#view-note').text(HDR.note || '-').attr('title', HDR.note || '-');
        headerEditMode(false);
        showToast('success', 'Info PO disimpan.');
        if (brandChanged) {
          PO_BRAND = brName;
          TAB_KEYS.forEach(function(k) { STAGED[k] = []; PENDING_NOTE[k] = { n1: '', n2: '' }; updateNoteFlag(k); SELECTED_PACK[k] = defaultPack(k); renderTab(k); loadTabProducts(k, TAB_PACK[k]); });
          showToast('warning', 'Brand berubah — daftar produk dimuat ulang & baris baru dibatalkan.');
        }
      },
      error: function(xhr) {
        var resp = xhr.responseJSON || {};
        var notif = ((resp.data || {}).notification) || {};
        var msg = notif.content || 'Gagal menyimpan.';
        showToast('danger', Array.isArray(msg) ? msg.join('<br>') : msg);
      },
      complete: function() { $btn.prop('disabled', false).html('<i class="fa fa-check mr-5"></i>Simpan'); }
    });
  });

  $(document).on('click', '.btn-del-saved', function() {
    var url = $(this).data('url');
    Swal.fire({
      title: 'Hapus produk ini dari PO?',
      text: 'Tindakan ini langsung tersimpan dan tidak bisa dibatalkan.',
      type: 'warning', showCancelButton: true,
      confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal'
    }).then(function(res) {
      if (!res || (!res.isConfirmed && !res.value)) return;
      $.ajax({
        url: url, method: 'POST', data: { _token: CSRF, _method: 'DELETE' }, dataType: 'JSON',
        success: function() { showToast('success', 'Produk dihapus.'); refreshAll(); },
        error: function() { showToast('danger', 'Gagal menghapus.'); }
      });
    });
  });
});
</script>
@endpush