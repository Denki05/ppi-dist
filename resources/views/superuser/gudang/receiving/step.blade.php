@extends('superuser.app')

@push('styles')
<style>
  @include('superuser.gudang.shared._doc_styles')
</style>
@endpush

@section('content')
@php
  use App\Entities\Gudang\Receiving as RI;
  $role = $superuser->division;
  $isDraft = $receiving->status == RI::STATUS['ACTIVE'];
  $isRetur = $receiving->type == 1;
  $canDeleteDetail = in_array($role, ['Admin', 'Developer', 'Management']);
  $canApproveQc = in_array($role, ['Admin', 'Developer']);
  $canDelQc = in_array($role, ['Warehouse', 'Developer']);
  $steps = ['ACTIVE', 'QC', 'READY', 'ACC'];
  $stepOrder = array_flip([RI::STATUS['ACTIVE'], RI::STATUS['QC'], RI::STATUS['READY'], RI::STATUS['ACC']]);
  $curOrder = isset($stepOrder[$receiving->status]) ? $stepOrder[$receiving->status] : -1;
@endphp

<nav class="breadcrumb bg-white py-10" style="margin-bottom:8px;">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item">Receiving</span>
  <span class="breadcrumb-item active">{{ $receiving->code }}</span>
</nav>

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

@if(session()->has('error'))
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <p class="mb-0">{{ session()->get('error') }}</p>
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

<div class="po-wrap">
  {{-- ===== KIRI: info receiving ===== --}}
  <div class="po-col-left">
    <div class="po-info-card">
      <div class="d-flex align-items-center justify-content-between">
        <p class="po-info-code mb-0">{{ $receiving->code }}</p>
        <span class="po-status-pill {{ $isDraft ? 'is-draft' : 'is-live' }}"><span class="dot"></span>{{ $receiving->status() }}</span>
      </div>

      <ul class="po-info-list">
        <li>
          <span class="po-info-icon"><i class="fa fa-warehouse"></i></span>
          <span class="po-info-body"><span class="po-info-label">Warehouse</span><span class="po-info-val" title="{{ optional($receiving->warehouse)->name ?? '-' }}">{{ optional($receiving->warehouse)->name ?? '-' }}</span></span>
        </li>
        <li>
          <span class="po-info-icon"><i class="fa fa-exchange"></i></span>
          <span class="po-info-body"><span class="po-info-label">Tipe</span><span class="po-info-val">{{ $receiving->type() }}</span></span>
        </li>
        <li>
          <span class="po-info-icon"><i class="fa fa-calendar"></i></span>
          <span class="po-info-body"><span class="po-info-label">PBM Date</span><span class="po-info-val">{{ $receiving->pbm_date ? date('d-m-Y', strtotime($receiving->pbm_date)) : '-' }}</span></span>
        </li>
        <li>
          <span class="po-info-icon"><i class="fa fa-sticky-note"></i></span>
          <span class="po-info-body"><span class="po-info-label">Note</span><span class="po-info-val" title="{{ $receiving->note ?: '-' }}">{{ $receiving->note ?: '-' }}</span></span>
        </li>
      </ul>

      <div class="po-info-actions">
        {{-- 1. Draft (ACTIVE) : 2 tombol sebaris --}}
        @if($receiving->status == RI::STATUS['ACTIVE'] && in_array($role, ['Admin','Developer']))
          <div class="row">
            <div class="col-6">
              <a href="{{ route('superuser.gudang.receiving.index') }}" class="btn btn-sm btn-back" title="Kembali ke daftar"><i class="fa fa-arrow-left"></i>Kembali</a>
            </div>
            <div class="col-6">
              <a href="{{ route('superuser.gudang.receiving.edit', $receiving->id) }}" class="btn btn-sm btn-edit"><i class="fa fa-pencil"></i>Edit</a>
            </div>
          </div>
          <div class="row">
            <div class="col-6">
              <a href="{{ route('superuser.gudang.receiving.publish', $receiving->id) }}" class="btn btn-sm btn-publish"><i class="fa fa-check"></i>Publish QC</a>
            </div>
            <div class="col-6">
              <a href="javascript:deleteConfirmation('{{ route('superuser.gudang.receiving.destroy', $receiving->id) }}', true)" class="btn btn-sm btn-danger-ghost"><i class="fa fa-trash"></i>Delete</a>
            </div>
          </div>

        {{-- 2. Tahap QC --}}
        @elseif($receiving->status == RI::STATUS['QC'] && in_array($role, ['Warehouse','Developer']))
          <a href="{{ route('superuser.gudang.receiving.index') }}" class="btn btn-sm btn-back"><i class="fa fa-arrow-left"></i>Kembali ke daftar</a>
          <a href="javascript:saveConfirmation('{{ route('superuser.gudang.receiving.cancel', $receiving->id) }}')" class="btn btn-sm btn-danger-ghost" title="Kembalikan ke Active (ditolak bila sudah ada log QC)"><i class="fa fa-undo"></i>Kembalikan ke Active</a>
          <a href="{{ route('superuser.gudang.receiving.publish', $receiving->id) }}" class="btn btn-sm btn-publish"><i class="fa fa-check"></i>Publish to Ready</a>
        {{-- 3. Tahap ACC --}}
        @elseif($receiving->status == RI::STATUS['READY'] && in_array($role, ['Admin','Developer']))
          <a href="javascript:saveConfirmation('{{ route('superuser.gudang.receiving.rollback', $receiving->id) }}')" class="btn btn-sm btn-danger-ghost" title="Kembalikan ke QC untuk revisi"><i class="fa fa-undo"></i>Kembalikan ke QC</a>
          <a href="javascript:saveConfirmation2('{{ route('superuser.gudang.receiving.acc_ri', $receiving->id) }}')" class="btn btn-sm btn-publish" title="ACC"><i class="fa fa-check"></i>ACC</a>
        @endif
      </div>
    </div>
  </div>

  {{-- ===== KANAN: workflow + detail ===== --}}
  <div class="po-col-right">
    <div class="po-main-card">
      <div class="po-main-inner">
        <div class="ri-stepper">
          @foreach($steps as $i => $s)
            @php
              if ($curOrder < 0) $cls = '';
              elseif ($i < $curOrder) $cls = 'is-done';
              elseif ($i == $curOrder) $cls = ($s == 'ACC') ? 'is-done' : 'is-current';
              else $cls = '';
            @endphp
            <div class="ri-step {{ $cls }}">
              <span class="ri-dot">@if($cls == 'is-done')<i class="fa fa-check"></i>@else{{ $i + 1 }}@endif</span>
              <span class="ri-lbl">{{ $s }}</span>
            </div>
          @endforeach
        </div>

        @if(in_array($role, ['Admin','Developer', 'Warehouse', 'Management']) && in_array($receiving->status, [RI::STATUS['ACTIVE'], RI::STATUS['READY'], RI::STATUS['ACC']]))
        <div class="po-panel">
          @if($receiving->status == RI::STATUS['ACTIVE'] && in_array($role, ['Admin','Developer', 'Management']))
          <div class="po-inputbar">
            <div class="po-input-caption">Input produk</div>
            <div class="form-row align-items-center">
                <div class="form-group col-xl-4 col-lg-4 col-md-12">
                  <select class="form-control form-control-sm js-select2-ri" id="ri-product" style="width:100%;" title="Produk — wajib">
                    <option value="">— Pilih produk —</option>
                  </select>
                </div>
                <div class="form-group col-xl-1 col-lg-1 col-md-4">
                  <input type="number" class="form-control form-control-sm text-center qty-compact" id="ri-qty" placeholder="Qty" title="Qty — wajib, lebih dari 0" step="any" min="0">
                </div>
              <div class="form-group col-xl-2 col-lg-2 col-md-4">
                <input type="text" class="form-control form-control-sm text-center" id="ri-batch" placeholder="No Batch" title="No Batch (opsional)">
              </div>
              <div class="form-group col-xl-2 col-lg-2 col-md-4">
                <input type="text" class="form-control form-control-sm" id="ri-note" placeholder="Note" title="Note (opsional)">
              </div>
                <div class="form-group col-xl-3 col-lg-3 col-md-12">
                  <div class="po-toolbar-btns">
                    <button type="button" class="btn btn-sm btn-success" id="btnRiAdd" title="Tambahkan sebagai baris baru"><i class="fa fa-plus"></i>Tambah</button>
                    <button type="button" class="btn btn-sm btn-primary" id="btnRiSave" disabled><i class="fa fa-save"></i>Simpan</button>
                  </div>
                </div>
            </div>
          </div>
          @endif
          <div class="table-responsive po-table-scroll">
            <table class="table table-sm table-bordered table-striped mb-0">
              <thead class="thead-light">
                <tr>
                  <th class="text-center" style="width:45px;">#</th>
                  <th class="text-center">Produk</th>
                  <th class="text-center" style="width:80px;">Qty RI</th>
                  <th class="text-center" style="width:80px;">Qty QC</th>
                  <th class="text-center" style="width:90px;">Kurang Kirim</th>
                  <th class="text-center">No Batch</th>
                  <th class="text-center">Note</th>
                  @if(in_array($role, ['Admin','Developer', 'Management']))<th class="text-center" style="width:80px;">Aksi</th>@endif
                </tr>
              </thead>
              <tbody class="details-body" data-cols="{{ in_array($role, ['Admin','Developer', 'Management']) ? 8 : 7 }}">
                <tr><td colspan="{{ in_array($role, ['Admin','Developer', 'Management']) ? 8 : 7 }}" class="text-center text-muted"><i class="fa fa-spinner fa-spin mr-5"></i>Memuat...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        @elseif($receiving->status == RI::STATUS['QC'])
        <div class="po-panel">
          @if(in_array($role, ['Warehouse','Developer']))
          <div class="po-inputbar">
            <div class="po-input-caption">Input QC</div>
            <div class="form-row align-items-center">
              <div class="form-group col-xl-5 col-lg-5 col-md-12">
                <select class="form-control form-control-sm js-select2-qc" id="qc-product" style="width:100%;" title="Produk — wajib">
                  <option value="">Pilih produk</option>
                </select>
              </div>
              <div class="form-group col-xl-1 col-lg-1 col-md-4">
                <input type="number" class="form-control form-control-sm text-center qty-compact" id="qc-qty" placeholder="Qty" title="Jumlah QC — wajib" step="any" min="0">
              </div>
              <div class="form-group col-xl-2 col-lg-2 col-md-4">
                <select class="form-control form-control-sm text-center" id="qc-status" title="Status QC — wajib">
                  <option value="">Status</option>
                  <option value="OK">OK</option>
                  <option value="NOT OK">NOT OK</option>
                </select>
              </div>
              @if($receiving->type == 0)
              <div class="form-group col-xl-1 col-lg-1 col-md-4">
                <label class="po-spk-toggle mini" for="qc-sellable" title="Langsung bisa dijual (Saleable)">
                  <input type="checkbox" id="qc-sellable">
                  <span class="po-spk-box"><i class="fa fa-check"></i></span>
                  <span class="po-spk-text"><strong>Saleable</strong></span>
                </label>
              </div>
              @endif
              <div class="form-group col-xl-3 col-lg-3 col-md-12">
                <div class="po-toolbar-btns">
                  <button type="button" class="btn btn-sm btn-success" id="btnQcAdd" title="Tambahkan sebagai baris baru"><i class="fa fa-plus"></i>Tambah</button>
                  <button type="button" class="btn btn-sm btn-primary" id="btnQcSave" disabled><i class="fa fa-save"></i>Simpan</button>
                  <button type="button" class="btn btn-sm btn-danger" id="btnQcBulkDel" style="display:none;" title="Hapus yang dipilih sekaligus"><i class="fa fa-trash"></i>Hapus (<span id="bulkDelCount">0</span>)</button>
                </div>
              </div>
            </div>
          </div>
          @endif
          <div class="table-responsive po-table-scroll">
            <table class="table table-sm table-bordered table-striped mb-0">
              <!-- qc-bulk-v2 -->
              <thead class="thead-light">
                <tr>
                  @if(in_array($role, ['Warehouse','Developer']))
                  <th class="text-center" style="width:36px;"><input type="checkbox" id="qc-check-all" title="Pilih semua"></th>
                  @endif
                  <th class="text-center" style="width:45px;">#</th>
                  <th class="text-center">Produk</th>
                  <th class="text-center" style="width:100px;">Quantity</th>
                  <th class="text-center" style="width:120px;">Status QC</th>
                  <th class="text-center" style="width:90px;">Aksi</th>
                </tr>
              </thead>
              <tbody id="qc-body">
                <tr><td colspan="{{ in_array($role, ['Warehouse','Developer']) ? 6 : 5 }}" class="text-center text-muted"><i class="fa fa-spinner fa-spin mr-5"></i>Memuat...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@section('modal')
  @include('superuser.component.modal-manage-receiving-detail', [
    'import_template_url' => route('superuser.gudang.receiving.import_template'),
    'import_url' => route('superuser.gudang.receiving.import', $receiving->id),
    // 'export_url' => route('superuser.gudang.receiving.export')
  ])
@endsection

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
$(document).ready(function () {
  var CSRF = '{{ csrf_token() }}';
  var CAN_DELETE = @json($canDeleteDetail);
  var CAN_APPROVE = @json($canApproveQc);
  var CAN_DEL_QC = @json($canDelQc);
  var DETAIL_STORE_URL = '{{ route("superuser.gudang.receiving.detail.store", $receiving->id) }}';
  var DETAIL_LIST_URL = '{{ route("superuser.gudang.receiving.detail.detail_json", $receiving->id) }}';
  var PRODUCT_LIST_URL = '{{ route("superuser.gudang.receiving.detail.product_list", $receiving->id) }}';
  var QC_OPTIONS_URL = '{{ route("superuser.gudang.receiving.detail.qc_options", $receiving->id) }}';
  var QC_LIST_URL = '{{ route("superuser.gudang.receiving.detail.qc_json", $receiving->id) }}';
  var QC_STORE_TPL = '{{ route("superuser.gudang.receiving.detail.qty_qc", ":detail") }}';


  function esc(s) {
    return String(s === null || s === undefined ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function toastNotif(resp, okMsg) {
    var notif = (((resp || {}).data || {}).notification) || ((resp || {}).notification) || {};
    var msg = notif.content || okMsg || 'Berhasil';
    showToast(notif.type && notif.type.indexOf('danger') !== -1 ? 'danger' : 'success', Array.isArray(msg) ? msg.join('<br>') : msg);
  }

  function prodLabel(code, name, pack) {
    var t = (code || '-') + ' - ' + (name || '-');
    if (pack) t += ' - ' + pack;
    return t;
  }

  /* ================= FASE ACTIVE: daftar produk ================= */
  var D_SAVED = [], D_STAGED = [];
  var DETAILS_COLS = parseInt($('.details-body').data('cols') || '8', 10);

  function rowSavedDetail(r, n) {
    // Kurang Kirim = Qty RI - Qty QC, dihitung live agar benar di semua status.
    // Data tersimpan tidak diubah; publish menghitung ulang seperti biasa.
    var qpoNum = parseFloat(r.quantity_po) || 0;
    var qcNum = parseFloat(r.qty_qc) || 0;
    var kurangNum = Math.round((qpoNum - qcNum) * 100) / 100;
    var h = '<tr>'
      + '<td class="text-center">' + n + '</td>'
      + '<td title="' + esc(r.code + ' - ' + r.name) + '">' + esc(prodLabel(r.code, r.name, r.pack_name !== '-' ? r.pack_name : null)) + '</td>'
      + '<td class="text-center">' + esc(r.quantity_po) + '</td>'
      + '<td class="text-center">' + esc(qcNum) + '</td>'
      + '<td class="text-center">' + esc(kurangNum) + '</td>'
      + '<td class="text-center">' + esc(r.no_batch || '-') + '</td>'
      + '<td class="text-center">' + esc(r.note || '-') + '</td>';
    if (CAN_DELETE) {
      h += '<td class="text-center"><button type="button" class="btn btn-sm btn-circle btn-alt-danger btn-ri-del" data-url="' + r.destroy_url + '" title="Hapus"><i class="fa fa-times"></i></button></td>';
    }
    return h + '</tr>';
  }

  function rowStagedDetail(s, n) {
    var h = '<tr class="table-warning">'
      + '<td class="text-center">' + n + ' <span class="badge badge-warning">baru</span></td>'
      + '<td title="' + esc(s.productText) + '">' + esc(s.productText) + '</td>'
      + '<td class="text-center">' + esc(s.qty) + '</td>'
      + '<td class="text-center">0</td>'
      + '<td class="text-center">' + esc(s.qty) + '</td>'
      + '<td class="text-center">' + esc(s.batch || '-') + '</td>'
      + '<td class="text-center">' + esc(s.note || '-') + '</td>';
    h += '<td class="text-center"><button type="button" class="btn btn-sm btn-circle btn-alt-danger btn-ri-remove-staged" data-idx="' + s.idx + '" title="Batalkan baris ini"><i class="fa fa-times"></i></button></td></tr>';
    return h;
  }

  function renderDetails() {
    var html = '', n = 0;
    D_SAVED.forEach(function(r) { n++; html += rowSavedDetail(r, n); });
    D_STAGED.forEach(function(s) { n++; html += rowStagedDetail(s, n); });
    if (n === 0) {
      html = '<tr class="empty-state"><td colspan="' + DETAILS_COLS + '" class="text-center">'
        + '<div class="empty-title">Belum ada produk di receiving ini</div></td></tr>';
    }
    $('.details-body').html(html);
    var sc = D_STAGED.length;
    var $b = $('#btnRiSave');
    $b.prop('disabled', sc === 0);
    $b.html('<i class="fa fa-save"></i>Simpan' + (sc > 0 ? ' (' + sc + ' baru)' : ''));
  }

  function refreshDetails() {
    $.ajax({
      url: DETAIL_LIST_URL, method: 'GET', dataType: 'JSON',
      success: function(resp) {
        D_SAVED = (resp && resp.Data) ? resp.Data : [];
        renderDetails();
      },
      error: function() { showToast('danger', 'Gagal memuat daftar produk.'); }
    });
  }

  var RI_PROD_TEXT = {}; // product_pack_id -> "KODE - Nama [kemasan]" bersih (tanpa sisa)
  var RI_OPTS_ALL = []; // cache opsi server

  function round2(x) { return Math.round((parseFloat(x) || 0) * 100) / 100; }

  function stagedQtyRi(packId) {
    var t = 0;
    D_STAGED.forEach(function(s) { if (String(s.productId) === String(packId)) t += Number(s.qty) || 0; });
    return round2(t);
  }

  function riEffective(packId) {
    var base = null;
    RI_OPTS_ALL.forEach(function(e) { if (String(e.product_pack_id) === String(packId)) base = parseFloat(e.qty_available) || 0; });
    if (base === null) return 0;
    return round2(base - stagedQtyRi(packId));
  }

  // Dropdown dikurangi baris staging: habis diambil → hilang, sebagian → sisa berkurang
  function renderRiOptions() {
    var keep = $('#ri-product').val();
    var opt = '<option value="">— Pilih produk —</option>';
    RI_PROD_TEXT = {};
    RI_OPTS_ALL.forEach(function(e) {
      var eff = round2((parseFloat(e.qty_available) || 0) - stagedQtyRi(e.product_pack_id));
      if (eff < 0.01) return;
      var packTxt = e.pack_name ? ' [' + e.pack_name + ']' : '';
      RI_PROD_TEXT[e.product_pack_id] = e.code + ' - ' + e.name + packTxt;
      var extra = (e.retur_code ? ' (retur ' + e.retur_code + ')' : '') + ' (sisa: ' + eff + ')';
      opt += '<option value="' + e.product_pack_id + '">' + esc(RI_PROD_TEXT[e.product_pack_id] + extra) + '</option>';
    });
    $('#ri-product').html(opt);
    if (keep) $('#ri-product').val(keep);
  }

  function loadRiProducts() {
    $.ajax({
      url: PRODUCT_LIST_URL, method: 'GET', dataType: 'JSON',
      success: function(resp) {
        RI_OPTS_ALL = resp.Data || [];
        renderRiOptions();
      }
    });
  }

  if ($('.details-body').length) { refreshDetails(); }
  if ($('#ri-product').length) { $('.js-select2-ri').select2({ width: '100%' }); loadRiProducts(); }

  $(document).on('click', '#btnRiAdd', function() {
    var productId = $('#ri-product').val();
    var productText = RI_PROD_TEXT[productId] || $('#ri-product option:selected').text().replace(/\s*\(sisa:[^)]*\)\s*$/, '');
    var qty = $('#ri-qty').val();
    var batch = $('#ri-batch').length ? ($('#ri-batch').val() || '') : '';
    var note = $('#ri-note').val() || '';
    if (!productId || !qty || Number(qty) <= 0) {
      Swal.fire('Belum lengkap', 'Pilih produk dan isi qty (>0).', 'warning');
      return;
    }
    var effRi = riEffective(productId);
    if (Number(qty) > effRi) {
      Swal.fire('Melebihi sisa', 'Sisa tersedia (termasuk baris baru) tinggal ' + effRi + '.', 'warning');
      return;
    }
    D_STAGED.push({ idx: Date.now() + Math.floor(Math.random() * 1000), productId: productId, productText: productText, qty: qty, batch: batch, note: note });
    renderDetails();
    renderRiOptions();
    $('#ri-product').val('').trigger('change');
    $('#ri-qty').val('');
    if ($('#ri-batch').length) $('#ri-batch').val('');
    $('#ri-note').val('');
  });

  $(document).on('click', '.btn-ri-remove-staged', function() {
    var idx = $(this).data('idx');
    D_STAGED = D_STAGED.filter(function(s) { return s.idx != idx; });
    renderDetails();
    renderRiOptions();
  });

  $(document).on('click', '#btnRiSave', function() {
    if (D_STAGED.length === 0) {
      Swal.fire('Kosong', 'Belum ada baris baru. Klik Tambah dulu.', 'info');
      return;
    }
    var $btn = $(this);
    Swal.fire({
      title: 'Simpan ' + D_STAGED.length + ' produk ke receiving?',
      type: 'question', showCancelButton: true,
      confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal'
    }).then(function(res) {
      if (!res || (!res.isConfirmed && !res.value)) return;
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
      var queue = D_STAGED.slice();
      function doneFail(msg) {
        showToast('danger', msg);
        refreshDetails();
        loadRiProducts();
        renderDetails();
      }
      function next() {
        if (queue.length === 0) {
          D_STAGED = [];
          showToast('success', 'Produk berhasil ditambahkan.');
          refreshDetails();
          loadRiProducts();
          renderDetails();
          return;
        }
        var s = queue[0];
        $.ajax({
          url: DETAIL_STORE_URL, method: 'POST',
          data: { _token: CSRF, product_pack_id: s.productId, quantity: s.qty, no_batch: s.batch, description: s.note },
          dataType: 'JSON',
          success: function(resp) {
            if (resp.IsError) {
              doneFail(resp.Message || 'Gagal menyimpan.');
              return;
            }
            queue.shift();
            next();
          },
          error: function(xhr) {
            var r = xhr.responseJSON || {};
            var nt = ((r.data || {}).notification) || {};
            var msg = nt.content || r.Message || 'Cek koneksi internet.';
            doneFail(Array.isArray(msg) ? msg.join('<br>') : msg);
          }
        });
      }
      next();
    });
  });

  $(document).on('click', '.btn-ri-del', function() {
    var url = $(this).data('url');
    Swal.fire({
      title: 'Hapus produk ini?', type: 'warning',
      showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal'
    }).then(function(res) {
      if (!res || (!res.isConfirmed && !res.value)) return;
      $.ajax({
        url: url, method: 'POST', data: { _token: CSRF, _method: 'DELETE' }, dataType: 'JSON',
        success: function() { showToast('success', 'Produk dihapus.'); refreshDetails(); loadRiProducts(); },
        error: function() { showToast('danger', 'Gagal menghapus.'); }
      });
    });
  });

  /* ================= FASE QC: staging log QC ================= */
  var Q_SAVED = [], Q_STAGED = [];

  var QC_SELECTED = {}; // qcId -> true (pilihan hapus massal, bertahan antar refresh)

  function qcCols() {
    var ths = $('#qc-body').closest('table').find('thead th').length;
    return ths > 0 ? ths : 6;
  }

  function renderQc() {
    var html = '', n = 0;
    Q_SAVED.forEach(function(r) {
      n++;
      var checked = QC_SELECTED[r.id] ? ' checked' : '';
      html += '<tr>'
        + (CAN_DEL_QC ? '<td class="text-center"><input type="checkbox" class="qc-row-check" data-id="' + r.id + '" data-url="' + r.destroy_url + '"' + checked + '></td>' : '')
        + '<td class="text-center">' + n + '</td>'
        + '<td title="' + esc(r.code + ' - ' + r.name) + '">' + esc(prodLabel(r.code, r.name, r.pack)) + '</td>'
        + '<td class="text-center">' + esc(r.qty_qc) + '</td>'
        + '<td class="text-center">' + esc(r.status_qc) + '</td>'
        + '<td class="text-center" style="white-space:nowrap;">'
        + ((r.is_sellable && !r.is_approved && CAN_APPROVE) ? '<button type="button" class="btn btn-sm btn-circle btn-alt-warning btn-qc-approve" data-url="' + r.approve_url + '" title="Approve QC Saleable"><i class="fa fa-check"></i></button> ' : '')
        + (CAN_DEL_QC ? '<button type="button" class="btn btn-sm btn-circle btn-alt-danger btn-qc-del" data-url="' + r.destroy_url + '" title="Hapus Log QC"><i class="fa fa-trash"></i></button>' : '')
        + '</td></tr>';
    });
    Q_STAGED.forEach(function(s) {
      n++;
      html += '<tr class="table-warning">'
        + (CAN_DEL_QC ? '<td></td>' : '')
        + '<td class="text-center">' + n + ' <span class="badge badge-warning">baru</span></td>'
        + '<td title="' + esc(s.productText) + '">' + esc(s.productText) + '</td>'
        + '<td class="text-center">' + esc(s.qty) + '</td>'
        + '<td class="text-center">' + esc(s.status) + (s.sellable ? ' <span class="badge badge-info">saleable</span>' : '') + '</td>'
        + '<td class="text-center"><button type="button" class="btn btn-sm btn-circle btn-alt-danger btn-qc-remove-staged" data-idx="' + s.idx + '" title="Batalkan baris ini"><i class="fa fa-times"></i></button></td></tr>';
    });
    if (n === 0) {
      html = '<tr class="empty-state"><td colspan="' + qcCols() + '" class="text-center">'
        + '<div class="empty-title">Belum ada log QC</div></td></tr>';
    }
    $('#qc-body').html(html);
    // Bersihkan pilihan yang sudah tidak ada + sinkron check-all & tombol hapus massal
    Object.keys(QC_SELECTED).forEach(function(id) {
      if (!Q_SAVED.some(function(r) { return String(r.id) === String(id); })) delete QC_SELECTED[id];
    });
    var selCount = Object.keys(QC_SELECTED).length;
    QC_ALL_ON = Q_SAVED.length > 0 && selCount === Q_SAVED.length;
    var $master = $('#qc-check-all');
    if ($master.length) {
      $master.prop('checked', QC_ALL_ON);
      $master.prop('indeterminate', selCount > 0 && !QC_ALL_ON);
    }
    var $bulk = $('#btnQcBulkDel');
    $bulk.toggle(selCount > 0);
    $('#bulkDelCount').text(selCount);
    var sc = Q_STAGED.length;
    var $b = $('#btnQcSave');
    $b.prop('disabled', sc === 0);
    $b.html('<i class="fa fa-save"></i>Simpan' + (sc > 0 ? ' (' + sc + ' baru)' : ''));
  }

  function refreshQc() {
    $.ajax({
      url: QC_LIST_URL, method: 'GET', dataType: 'JSON',
      success: function(resp) {
        Q_SAVED = (resp && resp.Data) ? resp.Data : [];
        renderQc();
      },
      error: function() { showToast('danger', 'Gagal memuat log QC.'); }
    });
  }

  var QC_PROD_TEXT = {}; // detailId -> "KODE - Nama / kemasan" bersih (tanpa sisa)
  var QC_OPTS_ALL = []; // cache opsi server

  function stagedQtyQc(detailId) {
    var t = 0;
    Q_STAGED.forEach(function(s) { if (String(s.detailId) === String(detailId)) t += Number(s.qty) || 0; });
    return round2(t);
  }

  function qcEffective(detailId) {
    var base = null;
    QC_OPTS_ALL.forEach(function(p) { if (String(p.id) === String(detailId)) base = parseFloat(p.sisa) || 0; });
    if (base === null) return 0;
    return round2(base - stagedQtyQc(detailId));
  }

  // Dropdown dikurangi baris staging: habis diambil → hilang, sebagian → sisa berkurang
  function renderQcOptions() {
    var keep = $('#qc-product').val();
    var opt = '<option value="">— Pilih produk —</option>';
    QC_PROD_TEXT = {};
    QC_OPTS_ALL.forEach(function(p) {
      var eff = round2((parseFloat(p.sisa) || 0) - stagedQtyQc(p.id));
      if (eff < 0.01) return;
      QC_PROD_TEXT[p.id] = p.code + ' - ' + p.name + (p.pack ? ' / ' + p.pack : '');
      opt += '<option value="' + p.id + '">' + esc(QC_PROD_TEXT[p.id] + ' (sisa: ' + eff + ' kg)') + '</option>';
    });
    $('#qc-product').html(opt);
    if (keep) $('#qc-product').val(keep);
  }

  function loadQcOptions() {
    $.ajax({
      url: QC_OPTIONS_URL, method: 'GET', dataType: 'JSON',
      success: function(resp) {
        QC_OPTS_ALL = resp.Data || [];
        renderQcOptions();
      }
    });
  }

  if ($('.js-select2-ri').length) { $('.js-select2-ri').select2({ width: '100%' }); }
  if ($('.js-select2-qc').length) { $('.js-select2-qc').select2({ width: '100%' }); }
  if ($('#qc-body').length) { refreshQc(); }
  if ($('#qc-product').length) { loadQcOptions(); }

  $(document).on('click', '#btnQcAdd', function() {
    var detailId = $('#qc-product').val();
    var productText = QC_PROD_TEXT[detailId] || $('#qc-product option:selected').text().replace(/\s*\(sisa:[^)]*\)\s*$/, '');
    var qty = $('#qc-qty').val();
    var status = $('#qc-status').val();
    var sellable = $('#qc-sellable').length ? ($('#qc-sellable').is(':checked') ? 1 : 0) : 0;
    if (!detailId || !qty || Number(qty) <= 0 || !status) {
      Swal.fire('Belum lengkap', 'Pilih produk, isi qty (>0), dan status QC.', 'warning');
      return;
    }
    var effQc = qcEffective(detailId);
    if (Number(qty) > effQc) {
      Swal.fire('Melebihi sisa', 'Sisa QC (termasuk baris baru) tinggal ' + effQc + ' kg.', 'warning');
      return;
    }
    Q_STAGED.push({ idx: Date.now() + Math.floor(Math.random() * 1000), detailId: detailId, productText: productText, qty: qty, status: status, sellable: sellable });
    renderQc();
    renderQcOptions();
    $('#qc-product').val('').trigger('change');
    $('#qc-qty').val('');
    $('#qc-status').val('');
    if ($('#qc-sellable').length) $('#qc-sellable').prop('checked', false);
  });

  $(document).on('click', '.btn-qc-remove-staged', function() {
    var idx = $(this).data('idx');
    Q_STAGED = Q_STAGED.filter(function(s) { return s.idx != idx; });
    renderQc();
    renderQcOptions();
  });

  $(document).on('click', '#btnQcSave', function() {
    if (Q_STAGED.length === 0) {
      Swal.fire('Kosong', 'Belum ada baris baru. Klik Tambah dulu.', 'info');
      return;
    }
    var $btn = $(this);
    Swal.fire({
      title: 'Simpan ' + Q_STAGED.length + ' entri QC?',
      type: 'question', showCancelButton: true,
      confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal'
    }).then(function(res) {
      if (!res || (!res.isConfirmed && !res.value)) return;
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
      var queue = Q_STAGED.slice();
      function fail(msg) {
        showToast('danger', msg);
        refreshQc();
        loadQcOptions();
        renderQc();
      }
      function next() {
        if (queue.length === 0) {
          Q_STAGED = [];
          showToast('success', 'Entri QC tersimpan.');
          refreshQc();
          loadQcOptions();
          renderQc();
          return;
        }
        var s = queue[0];
        $.ajax({
          url: QC_STORE_TPL.replace(':detail', s.detailId), method: 'POST',
          data: { _token: CSRF, qty_qc: s.qty, status_qc: s.status, is_sellable: s.sellable },
          dataType: 'JSON',
          success: function(resp) {
            var bad = (resp && resp.IsError) || (resp && resp.status && resp.status.code && resp.status.code !== 200);
            if (bad) {
              var nt = (((resp || {}).data || {}).notification) || {};
              var msg = nt.content || resp.Message || 'Gagal menyimpan.';
              fail(Array.isArray(msg) ? msg.join('<br>') : msg);
              return;
            }
            queue.shift();
            next();
          },
          error: function(xhr) {
            var r = xhr.responseJSON || {};
            var nt2 = ((r.data || {}).notification) || {};
            var msg2 = nt2.content || r.Message || r.message || 'Cek koneksi internet.';
            fail(Array.isArray(msg2) ? msg2.join('<br>') : msg2);
          }
        });
      }
      next();
    });
  });

  $(document).on('click', '.btn-qc-approve', function() {
    var url = $(this).data('url');
    Swal.fire({
      title: 'Approve QC saleable ini?', type: 'question',
      showCancelButton: true, confirmButtonText: 'Ya, approve', cancelButtonText: 'Batal'
    }).then(function(res) {
      if (!res || (!res.isConfirmed && !res.value)) return;
      $.ajax({
        url: url, method: 'GET', dataType: 'JSON',
        success: function(resp) { toastNotif(resp, 'QC di-approve.'); refreshQc(); },
        error: function(xhr) {
          var r = xhr.responseJSON || {};
          var nt = ((r.data || {}).notification) || {};
          var msg = nt.content || r.message || 'Gagal approve.';
          showToast('danger', Array.isArray(msg) ? msg.join('<br>') : msg);
        }
      });
    });
  });

  $(document).on('click', '.btn-qc-del', function() {
    var url = $(this).data('url');
    Swal.fire({
      title: 'Hapus log QC ini?', type: 'warning',
      showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal'
    }).then(function(res) {
      if (!res || (!res.isConfirmed && !res.value)) return;
      $.ajax({
        url: url, method: 'GET', dataType: 'JSON',
        success: function(resp) {
          if (resp && resp.status === 'success') {
            showToast('success', resp.message || 'Log QC dihapus.');
            refreshQc();
            loadQcOptions();
          } else {
            showToast('danger', (resp && resp.message) || 'Gagal menghapus.');
          }
        },
        error: function(xhr) {
          var r = xhr.responseJSON || {};
          showToast('danger', r.message || 'Gagal menghapus.');
        }
      });
    });
  });

  // Pilih massal: state eksplisit (tidak tergantung perilaku indeterminate browser)
  var QC_ALL_ON = false;
  window.__qcDbg = function() {
    return 'saved=' + Q_SAVED.length + ' selected=' + Object.keys(QC_SELECTED).length + ' allOn=' + QC_ALL_ON
      + ' masterChecked=' + $('#qc-check-all').is(':checked')
      + ' rowChecked=' + $('.qc-row-check:checked').length + '/' + $('.qc-row-check').length;
  };

  $(document).on('click', '#qc-check-all', function(e) {
    e.stopPropagation();
    QC_ALL_ON = !QC_ALL_ON;
    QC_SELECTED = {};
    if (QC_ALL_ON) {
      Q_SAVED.forEach(function(r) { QC_SELECTED[r.id] = true; });
    }
    renderQc();
  });

  $(document).on('click', '.qc-row-check', function(e) {
    e.stopPropagation();
    var id = $(this).data('id');
    var on = $(this).is(':checked');
    // Kembalikan visual dulu bila render gagal — state dihitung ulang di bawah
    if (on) QC_SELECTED[id] = true;
    else delete QC_SELECTED[id];
    renderQc();
  });

  // Hapus massal yang dipilih (berurutan, berhenti saat gagal)
  $(document).on('click', '#btnQcBulkDel', function() {
    var ids = Object.keys(QC_SELECTED);
    if (ids.length === 0) return;
    var $btn = $(this);
    var btnHtml = $btn.html();
    Swal.fire({
      title: 'Hapus ' + ids.length + ' log QC terpilih?', type: 'warning',
      showCancelButton: true, confirmButtonText: 'Ya, hapus semua', cancelButtonText: 'Batal'
    }).then(function(res) {
      if (!res || (!res.isConfirmed && !res.value)) return;
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menghapus...');
      var urlById = {};
      Q_SAVED.forEach(function(r) { urlById[String(r.id)] = r.destroy_url; });
      var queue = ids.slice();
      var okCount = 0;
      function resetBulkBtn() {
        $btn.prop('disabled', false).html(btnHtml);
        renderQc();
      }
      function next() {
        if (queue.length === 0) {
          QC_SELECTED = {};
          showToast('success', okCount + ' log QC dihapus.');
          refreshQc();
          loadQcOptions();
          resetBulkBtn();
          return;
        }
        var id = queue[0];
        $.ajax({
          url: urlById[String(id)], method: 'GET', dataType: 'JSON',
          success: function(resp) {
            if (resp && resp.status === 'success') {
              okCount++;
              delete QC_SELECTED[id];
              queue.shift();
              next();
            } else {
              showToast('danger', (resp && resp.message) || 'Gagal menghapus. Berhenti.');
              refreshQc();
              loadQcOptions();
              resetBulkBtn();
            }
          },
          error: function(xhr) {
            var r = xhr.responseJSON || {};
            showToast('danger', r.message || 'Gagal menghapus. Berhenti.');
            refreshQc();
            loadQcOptions();
            resetBulkBtn();
          }
        });
      }
      next();
    });
  });
});
</script>
@endpush
