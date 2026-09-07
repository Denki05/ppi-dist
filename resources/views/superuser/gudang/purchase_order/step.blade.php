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

<nav class="breadcrumb bg-white push py-10 mb-10">
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
        <div class="form-group mb-5">
          <label class="field-label">PO Code<span class="req">*</span></label>
          <input type="text" class="form-control form-control-sm" id="hdr-code" value="{{ $purchase_order->code }}">
        </div>
        <div class="form-group mb-5">
          <label class="field-label">Warehouse<span class="req">*</span></label>
          <select class="form-control form-control-sm" id="hdr-warehouse">
            @foreach($warehouses as $wh)
            <option value="{{ $wh->id }}" {{ $purchase_order->warehouse_id == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group mb-5">
          <label class="field-label">Brand<span class="req">*</span></label>
          <select class="form-control form-control-sm" id="hdr-brand">
            @foreach($merek as $br)
            <option value="{{ $br->id }}" data-name="{{ $br->brand_name }}" {{ $purchase_order->brand_lokal_id == $br->id ? 'selected' : '' }}>{{ $br->brand_name }}</option>
            @endforeach
          </select>
          <small class="form-text text-muted" style="font-size:10px;">Ganti brand memuat ulang produk & membatalkan baris baru.</small>
        </div>
        <div class="form-group mb-5">
          <label class="field-label">ETD<span class="req">*</span></label>
          <input type="date" class="form-control form-control-sm" id="hdr-etd" value="{{ $purchase_order->etd ? date('Y-m-d', strtotime($purchase_order->etd)) : '' }}">
        </div>
        <div class="form-group mb-5">
          <label class="field-label">Note</label>
          <textarea class="form-control form-control-sm" id="hdr-note" rows="2">{{ $purchase_order->note }}</textarea>
        </div>
        <div class="d-flex" style="gap:6px;">
          <button type="button" class="btn btn-sm btn-primary flex-fill" id="btnSaveHeader"><i class="fa fa-check mr-5"></i>Simpan</button>
          <button type="button" class="btn btn-sm btn-secondary flex-fill" id="btnCancelHeader">Batal</button>
        </div>
      </div>

      <div class="po-info-actions">
        <a href="{{ route('superuser.gudang.purchase_order.index') }}" class="btn btn-sm btn-back"><i class="fa fa-arrow-left"></i>Kembali ke daftar PO</a>
        @if($isDraft)
          <button type="button" class="btn btn-sm btn-edit btn-edit-header"><i class="fa fa-pencil"></i>Edit PO</button>
          <a href="javascript:saveConfirmation('{{ route('superuser.gudang.purchase_order.publish', $purchase_order->id) }}')" class="btn btn-sm btn-publish"><i class="fa fa-check"></i>Publish PO</a>
        @else
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
              <div class="po-section-label">Tambah produk ke {{ implode(' / ', $tab['pack_names']) }}</div>
              <div class="form-row">
                <div class="form-group col-xl-2 col-lg-2 col-md-4 mb-5 {{ count($tab['pack_ids']) == 1 ? 'po-kemasan-fixed' : '' }}">
                  @if(count($tab['pack_ids']) == 1)
                    <label class="field-label">Kemasan <span title="Kemasan halaman ini tetap, tidak bisa diubah"></span></label>
                    <input type="text" class="form-control form-control-sm text-center" value="{{ $tab['pack_names'][0] ?? '' }}" readonly title="Kemasan halaman ini sudah ditentukan dan tidak bisa diubah">
                    <i class="fa fa-lock"></i>
                  @else
                    <label class="field-label">Kemasan<span class="req">*</span></label>
                    <select class="form-control form-control-sm packaging-sel" data-tabkey="{{ $tab['key'] }}" style="width:100%;">
                      <option value="">— Kemasan —</option>
                    </select>
                  @endif
                </div>
                <div class="form-group col-xl-3 col-lg-3 col-md-12 mb-5">
                  <label class="field-label">Produk<span class="req">*</span></label>
                  <select class="form-control form-control-sm js-select2-tab product-sel" data-tabkey="{{ $tab['key'] }}" style="width:100%;">
                    <option value="">— Pilih produk ({{ $poBrandName ?: 'brand?' }}) —</option>
                  </select>
                </div>
                <div class="form-group col-xl-1 col-lg-1 col-md-4 mb-5">
                  <label class="field-label">Qty (KG)<span class="req">*</span></label>
                  <input type="number" class="form-control form-control-sm text-center qty-inp" data-tabkey="{{ $tab['key'] }}" placeholder="0" step="any" min="0">
                </div>
                <div class="form-group col-xl-2 col-lg-2 col-md-4 mb-5">
                  <label class="field-label">Cat Produksi <span style="font-weight:400;color:#a7b0c0;">(opsional)</span></label>
                  <input type="text" class="form-control form-control-sm note-prod-inp" data-tabkey="{{ $tab['key'] }}">
                </div>
                <div class="form-group col-xl-2 col-lg-2 col-md-8 mb-5">
                  <label class="field-label">Cat Repack <span style="font-weight:400;color:#a7b0c0;">(opsional)</span></label>
                  <input type="text" class="form-control form-control-sm note-repack-inp" data-tabkey="{{ $tab['key'] }}">
                </div>
                <div class="form-group col-xl-2 col-lg-2 col-md-4 mb-5 d-flex flex-column">
                  <label class="field-label">&nbsp;</label>
                  <button type="button" class="btn btn-sm btn-success btn-block btn-add-tab" data-tabkey="{{ $tab['key'] }}" title="Tambahkan sebagai baris baru"><i class="fa fa-plus mr-5"></i>Tambah</button>
                </div>
              </div>
              </div>
              @endif
              <div class="po-section-label" style="margin: 10px 0 0 12px;">Daftar produk di halaman ini</div>
              <div class="table-responsive po-table-scroll">
                <table class="table table-sm table-bordered table-striped mb-0">
                  <thead class="thead-light">
                    <tr>
                      <th class="text-center" style="width:45px;">#</th>
                      <th class="text-center">Kode</th>
                      <th class="text-center">Nama Varian</th>
                      <th class="text-center" style="width:80px;">Qty</th>
                      <th class="text-center">Kemasan</th>
                      <th class="text-center">Catatan Produksi</th>
                      <th class="text-center">Catatan Repack</th>
                      @if($isDraft)<th class="text-center" style="width:80px;">Aksi</th>@endif
                    </tr>
                  </thead>
                  <tbody class="details-body" data-tabkey="{{ $tab['key'] }}">
                    <tr><td colspan="{{ $isDraft ? 8 : 7 }}" class="text-center text-muted"><i class="fa fa-spinner fa-spin mr-5"></i>Memuat...</td></tr>
                  </tbody>
                </table>
              </div>
              @if($isDraft)
              <div class="po-save-bar" data-tabkey="{{ $tab['key'] }}">
                <span class="staged-count" data-tabkey="{{ $tab['key'] }}"></span>
                <button type="button" class="btn btn-sm btn-primary btn-save-tab" data-tabkey="{{ $tab['key'] }}" disabled><i class="fa fa-save mr-5"></i>Simpan perubahan</button>
              </div>
              @endif
              </div>
            </div>
            @endforeach

          </div>
        </div>
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
  var GET_PACKAGING_URL = '{{ route("superuser.gudang.purchase_order.detail.get_packaging") }}';
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

  var SAVED = [];
  var STAGED = {};
  TAB_KEYS.forEach(function(k) { STAGED[k] = []; });
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
    if (IS_DRAFT && EDITING && EDITING.id === row.id) {
      return '<tr class="row-editing">'
        + '<td class="text-center">' + n + '</td>'
        + '<td class="text-center">' + esc(row.code) + '</td>'
        + '<td>' + esc(row.name) + '</td>'
        + '<td class="text-center"><input type="number" class="cell-inp edit-qty" value="' + esc(row.quantity) + '" step="any" min="0" style="width:75px;"></td>'
        + '<td class="text-center">' + esc(row.pack_name) + '</td>'
        + '<td><input type="text" class="cell-inp edit-prod" value="' + esc(row.note_produksi || '') + '" placeholder="Catatan..."></td>'
        + '<td><input type="text" class="cell-inp edit-repack" value="' + esc(row.note_repack || '') + '" placeholder="Customer..."></td>'
        + '<td class="text-center" style="white-space:nowrap;">'
        + '<button type="button" class="btn btn-sm btn-circle btn-alt-success btn-save-edit" data-url="' + row.update_url + '" data-ppid="' + esc(row.product_packaging_id || '') + '" data-packid="' + esc(row.packaging_id || '') + '" title="Simpan perubahan"><i class="fa fa-check"></i></button> '
        + '<button type="button" class="btn btn-sm btn-circle btn-alt-secondary btn-cancel-edit" data-tabkey="' + tabkey + '" title="Batalkan"><i class="fa fa-times"></i></button>'
        + '</td></tr>';
    }
    var h = '<tr>'
      + '<td class="text-center">' + n + '</td>'
      + '<td class="text-center">' + esc(row.code) + '</td>'
      + '<td>' + esc(row.name) + '</td>'
      + '<td class="text-center">' + esc(row.quantity) + '</td>'
      + '<td class="text-center">' + esc(row.pack_name) + '</td>'
      + '<td class="text-center">' + esc(row.note_produksi || '-') + '</td>'
      + '<td class="text-center">' + esc(row.note_repack || '-') + '</td>';
    if (IS_DRAFT) {
      h += '<td class="text-center" style="white-space:nowrap;">'
        + '<button type="button" class="btn btn-sm btn-circle btn-alt-warning btn-edit-row" data-id="' + row.id + '" data-tabkey="' + tabkey + '" title="Edit langsung di sini"><i class="fa fa-pencil"></i></button> '
        + '<button type="button" class="btn btn-sm btn-circle btn-alt-danger btn-del-saved" data-url="' + row.destroy_url + '" title="Hapus"><i class="fa fa-times"></i></button>'
        + '</td>';
    }
    return h + '</tr>';
  }

  function rowHtmlStaged(s, n, justAdded) {
    return '<tr class="table-warning' + (justAdded ? ' row-just-added' : '') + '">'
      + '<td class="text-center">' + n + ' <span class="badge badge-warning">baru</span></td>'
      + '<td class="text-center">' + esc(s.productText.split(' - ')[0]) + '</td>'
      + '<td>' + esc(s.productText) + '</td>'
      + '<td class="text-center">' + esc(s.qty) + '</td>'
      + '<td class="text-center">' + esc(s.packText) + '</td>'
      + '<td class="text-center">' + esc(s.prod || '-') + '</td>'
      + '<td class="text-center">' + esc(s.repack || '-') + '</td>'
      + (IS_DRAFT ? '<td class="text-center"><button type="button" class="btn btn-sm btn-circle btn-alt-danger btn-remove-staged" data-tabkey="' + s.tabkey + '" data-idx="' + s.idx + '" title="Batalkan baris ini"><i class="fa fa-times"></i></button></td>' : '')
      + '</tr>';
  }

  function renderTab(tabkey, justAddedIdx) {
    var $tb = $('.details-body[data-tabkey="' + tabkey + '"]');
    var saved = SAVED.filter(function(r) { return inTab(r, tabkey); });
    var html = '', n = 0;
    saved.forEach(function(r) { n++; html += rowHtmlSaved(r, n, tabkey); });
    STAGED[tabkey].forEach(function(s) { n++; html += rowHtmlStaged(s, n, s.idx === justAddedIdx); });
    if (n === 0) {
      html = '<tr class="empty-state"><td colspan="' + (IS_DRAFT ? 8 : 7) + '" class="text-center">'
        + '<i class="fa fa-inbox"></i>'
        + '<div class="empty-title">Belum ada produk di halaman ini</div>'
        + (IS_DRAFT ? '<div class="empty-hint">Pilih produk di atas, isi qty, lalu klik Tambah.</div>' : '')
        + '</td></tr>';
    }
    $tb.html(html);
    $('#badge-' + tabkey).text(saved.length);
    var sc = STAGED[tabkey].length;
    $('.staged-count[data-tabkey="' + tabkey + '"]').text(sc > 0 ? sc + ' baris baru belum disimpan' : '');
    var $bar = $('.po-save-bar[data-tabkey="' + tabkey + '"]');
    $bar.toggleClass('has-staged', sc > 0);
    $bar.find('.btn-save-tab').prop('disabled', sc === 0);
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

  function loadTabProducts(tabkey, packIds) {
    PRODUCT_PACKS[tabkey] = {};
    $.ajax({
      url: GET_PRODUCT_URL, method: 'GET',
      data: { brand_name: PO_BRAND, packaging_id: packIds || [] },
      dataType: 'JSON',
      success: function(resp) {
        var opt = '<option value="">— Pilih produk —</option>';
        $.each(resp.Data || [], function(i, e) {
          var packs = e.packagings || [];
          PRODUCT_PACKS[tabkey][e.id] = packs;
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

  // Kemasan langsung mengikuti relasi produk yang dipilih (tanpa request tambahan).
  // Produk umumnya hanya punya 1 kemasan dalam tab ini (mis. hanya Alu) — langsung terpilih otomatis.
  $(document).on('change', '.product-sel', function() {
    var tabkey = $(this).data('tabkey');
    var productId = $(this).val();
    var $packSel = $('.packaging-sel[data-tabkey="' + tabkey + '"]');
    if ($packSel.length === 0 || !productId) return;
    var packs = ((PRODUCT_PACKS[tabkey] || {})[productId]) || [];
    var allowed = TAB_PACK[tabkey] || [];
    var opt = '<option value="">— Kemasan —</option>';
    var validIds = [];
    packs.forEach(function(p) {
      if (allowed.indexOf(Number(p.id)) !== -1) {
        opt += '<option value="' + p.id + '">' + esc(p.name) + '</option>';
        validIds.push(p.id);
      }
    });
    $packSel.html(opt);
    if (validIds.length === 1) $packSel.val(validIds[0]);
    else if (validIds.length === 0) showToast('warning', 'Produk ini tidak punya kemasan tab ini. Pilih produk lain.');
  });

  // Bersihkan tanda error begitu user mulai mengisi
  $(document).on('input change', '.qty-inp, .packaging-sel', function() {
    $(this).removeClass('is-invalid');
  });
  $(document).on('change', '.js-select2-tab', function() {
    $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
  });

  $(document).on('click', '.btn-add-tab', function() {
    var tabkey = $(this).data('tabkey');
    var $pane = $('#tab-' + tabkey);
    var productId = $pane.find('.product-sel').val();
    var productText = $pane.find('.product-sel option:selected').text();
    var qty = $pane.find('.qty-inp').val();
    var prod = $pane.find('.note-prod-inp').val() || '';
    var repack = $pane.find('.note-repack-inp').val() || '';
    var packId, packText;
    var $packSel = $pane.find('.packaging-sel');
    if ($packSel.length > 0) {
      packId = $packSel.val();
      packText = $packSel.find('option:selected').text();
    } else {
      var ids = TAB_PACK[tabkey] || [];
      packId = ids.length === 1 ? ids[0] : '';
      packText = packId !== '' ? (TAB_PACK_NAME[tabkey][packId] || '') : '';
    }

    var invalid = false;
    if (!productId) { $pane.find('.js-select2-tab').next('.select2-container').find('.select2-selection').addClass('is-invalid'); invalid = true; }
    if (!qty || Number(qty) <= 0) { $pane.find('.qty-inp').addClass('is-invalid'); invalid = true; }
    if (!packId && $packSel.length > 0) { $packSel.addClass('is-invalid'); invalid = true; }
    if (invalid) {
      showToast('danger', 'Lengkapi dulu field yang ditandai merah: produk, kemasan, dan qty (harus lebih dari 0).');
      return;
    }

    var newIdx = Date.now() + Math.floor(Math.random() * 1000);
    STAGED[tabkey].push({ idx: newIdx, tabkey: tabkey, productId: productId, productText: productText, qty: qty, packId: packId, packText: packText, prod: prod, repack: repack });
    renderTab(tabkey, newIdx);
    $pane.find('.product-sel').val('').trigger('change');
    $pane.find('.qty-inp').val('');
    $pane.find('.note-prod-inp').val('');
    $pane.find('.note-repack-inp').val('');
    if ($packSel.length > 0) $packSel.html('<option value="">— Kemasan —</option>');
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
    var btnHtml = $btn.html();
    Swal.fire({
      title: 'Simpan ' + rows.length + ' produk ke PO?',
      text: 'Baris yang sudah disimpan hanya bisa diedit atau dihapus satu per satu, bukan dibatalkan dari sini.',
      icon: 'question', showCancelButton: true,
      confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal'
    }).then(function(res) {
      if (!res.isConfirmed) return;
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
        complete: function() { $btn.prop('disabled', false).html(btnHtml); renderTab(tabkey); }
      });
    });
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
    if (on) { $('.po-view-mode').hide(); $('.po-edit-mode').show(); }
    else { $('.po-edit-mode').hide(); $('.po-view-mode').show(); }
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
          TAB_KEYS.forEach(function(k) { STAGED[k] = []; renderTab(k); loadTabProducts(k, TAB_PACK[k]); });
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
      icon: 'warning', showCancelButton: true,
      confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal'
    }).then(function(res) {
      if (!res.isConfirmed) return;
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