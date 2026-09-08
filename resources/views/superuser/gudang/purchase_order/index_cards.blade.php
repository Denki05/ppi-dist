@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item active">Purchase Order (PO)</span>
</nav>

<div id="alert-block"></div>

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

<div class="row">
  {{-- KIRI: form PO baru --}}
  <div class="col-lg-4 col-md-12">
    <div class="block po-create">
      <div class="block-header block-header-default">
        <h3 class="block-title"><i class="fa fa-file-text-o mr-5"></i>Buat PO Baru</h3>
      </div>
      <div class="block-content">
        <p class="text-muted mb-15" style="font-size:12px;">1 PO = 1 Brand — setelah dibuat, isi produk per halaman kemasan.</p>
        <form id="frmCreatePO">
          @csrf
          <div class="form-group mb-10">
            <label class="field-label">PO Code <span class="text-muted">(otomatis)</span></label>
            <div class="input-group">
              <input type="text" class="form-control" name="code" readonly value="{{ App\Repositories\CodeRepo::generatePurchaseOrder() }}">
              <div class="input-group-append"><span class="input-group-text"><i class="fa fa-lock"></i></span></div>
            </div>
          </div>
          <div class="form-group mb-10">
            <label class="field-label">Warehouse<span class="req">*</span></label>
            <select class="form-control js-select2" name="warehouse" data-placeholder="— Pilih warehouse —" required style="width:100%;">
              <option></option>
              @foreach($warehouse as $wh)
              <option value="{{ $wh->id }}">{{ $wh->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group mb-10">
            <label class="field-label">Brand<span class="req">*</span></label>
            <select class="form-control js-select2" name="brand_lokal_id" data-placeholder="— Pilih brand —" required style="width:100%;">
              <option></option>
              @foreach($brands as $br)
              <option value="{{ $br->id }}">{{ $br->brand_name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group mb-10">
            <label class="field-label">ETD<span class="req">*</span></label>
            <input type="date" class="form-control" name="etd" required>
          </div>
          <div class="form-group mb-10">
            <label class="field-label">Note <span class="text-muted" style="font-weight:400;">(opsional)</span></label>
            <textarea class="form-control" name="note" rows="2" placeholder="Catatan PO..."></textarea>
          </div>
          <div class="form-group mb-15">
            <label class="field-label">Auto SPK</label>
            <input type="hidden" name="sub_type" value="0">
            <label class="po-spk-toggle" for="swAutoSpk">
              <input type="checkbox" id="swAutoSpk" name="sub_type" value="1">
              <span class="po-spk-box"><i class="fa fa-check"></i></span>
              <span class="po-spk-text"><strong>Buatkan SPK</strong><small>Otomatis saat PO di-ACC</small></span>
            </label>
          </div>
          <button type="submit" class="btn btn-primary btn-block btn-submit">
            Buat PO &amp; Isi Produk <i class="fa fa-arrow-right ml-5"></i>
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- KANAN: daftar PO --}}
  <div class="col-lg-8 col-md-12">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title"><i class="fa fa-list mr-5"></i>Daftar PO</h3>
        <div class="block-options">
          <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalSummary">
            <i class="fa fa-bar-chart mr-5"></i> Summary
          </button>
        </div>
      </div>
      <div class="block-content">
        <div class="row">
          <div class="col-12">
          <table id="datatables" class="table table-bordred table-striped" style="width:100%">
            <thead>
              <tr>
                <td class="text-center">#</td>
                <td class="text-center">Created at</td>
                <td class="text-center">PO Code</td>
                <td class="text-center">Warehouse</td>
                <td class="text-center">Brand</td>
                <td class="text-center">ETD</td>
                <td class="text-center">Status</td>
                <td class="text-center">Action</td>
              </tr>
            </thead>
          </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Summary Outstanding -->
<div class="modal fade" id="modalSummary" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content po-create">
      <div class="modal-header">
        <div>
          <h5 class="modal-title"><i class="fa fa-bar-chart mr-5"></i>Ringkasan Outstanding PO</h5>
          <small class="text-muted">Produk terkirim yang belum selesai (per kemasan).</small>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="summaryLoading" class="text-center text-muted py-20">
          <i class="fa fa-spinner fa-spin mr-5"></i>Memuat ringkasan...
        </div>
        <div class="table-responsive" id="summaryWrap" style="display:none;">
          <table class="table table-sm table-bordered table-striped mb-0">
            <thead class="thead-light">
              <tr>
                <th class="text-center" style="width:40px;">#</th>
                <th class="text-center">Produk</th>
                <th class="text-center">Kemasan</th>
                <th class="text-center" style="width:100px;">Total Qty</th>
                <th class="text-center">Kode PO</th>
              </tr>
            </thead>
            <tbody id="summaryBody"></tbody>
            <tfoot>
              <tr>
                <td colspan="3" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-center"><strong id="summaryTotal">0</strong></td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <a href="{{ route('superuser.gudang.purchase_order.summary') }}" class="btn btn-sm btn-info"><i class="fa fa-external-link mr-5"></i>Halaman penuh</a>
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('styles')
<style>
  .po-create { border: 0; }
  .po-create .field-label { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #7a8494; margin-bottom: 4px; text-align: left; }
  .po-create .field-label .req { color: #d9534f; margin-left: 2px; }
  .po-create .form-control { border-radius: 8px; border-color: #d8dfec; font-size: 13px; }
  .po-create .form-control:focus { border-color: #3d6fd1; box-shadow: 0 0 0 3px rgba(61,111,209,.14); }
  .po-create .form-control[readonly] { background: #eef1f6; color: #7a8494; }
  .po-create .btn-submit { box-shadow: 0 3px 10px rgba(61,111,209,.35); border-radius: 8px; }
  .po-create .select2-container .select2-selection--single { height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important; border-color: #d8dfec !important; display: flex !important; align-items: center !important; padding: 0 !important; }
  .po-create .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: normal !important; font-size: 13px; padding-left: 12px !important; }
  .po-create .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
  .po-spk-toggle { display: flex; align-items: center; gap: 10px; border: 1px solid #d8dfec; border-radius: 8px; padding: 6px 10px; cursor: pointer; margin: 0; min-height: 38px; }
  .po-spk-toggle input { display: none; }
  .po-spk-box { flex: 0 0 22px; height: 22px; border-radius: 6px; border: 2px solid #c3cad6; color: transparent; display: flex; align-items: center; justify-content: center; font-size: 12px; }
  .po-spk-toggle input:checked + .po-spk-box { background: #2fa85a; border-color: #2fa85a; color: #fff; }
  .po-spk-toggle input:checked ~ .po-spk-text strong { color: #1d7a41; }
  .po-spk-text { line-height: 1.25; }
  .po-spk-text strong { display: block; font-size: 12.5px; color: #1c2733; }
  .po-spk-text small { color: #7a8494; font-size: 10.5px; }
  #modalSummary .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
  #modalSummary .modal-header { background: #f2f5fa; border-bottom: 1px solid #e6eaf1; }
  #modalSummary .modal-title { font-weight: 800; font-size: 16px; color: #1c2733; }
</style>
@endpush

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2();

    $('#datatables').DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        "url": '{{route('superuser.gudang.purchase_order.json')}}',
        "dataType": "json",
        "type": "GET",
        "data":{ _token: "{{csrf_token()}}"}
      },
      columns: [
        {data: 'DT_RowIndex', name: 'id'},
        {
          data: 'created_at',
          render: {
            _: 'display',
            sort: 'timestamp'
          }
        },
        {data: 'code'},
        {data: 'warehouse'},
        {data: 'brand'},
        {data: 'etd'},
        {data: 'status'},
        {data: 'action', orderable: false, searcable: false}
      ],
      scrollCollapse: true,
      scrollX: true,
      scrollY: 350,
      order: [
        [1, 'desc']
      ],
      pageLength: 10,
      lengthMenu: [
        [5, 10, 15, 20],
        [5, 10, 15, 20]
      ],
    });

    function esc(s) {
      return String(s === null || s === undefined ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    $(document).on('submit', '#frmCreatePO', function(e) {
      e.preventDefault();
      var $btn = $('.btn-submit');
      var btnHtml = $btn.html();
      $.ajax({
        url: '{{ route("superuser.gudang.purchase_order.store") }}',
        method: 'POST',
        data: $(this).serializeArray(),
        dataType: 'JSON',
        beforeSend: function() {
          $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-5"></i> Processing...');
        },
        success: function(resp) {
          var data = resp.data || {};
          var notif = data.notification || {};
          if (resp.status && resp.status.code !== 200) {
            var msg = notif.content || resp.status.message || 'Terjadi kesalahan';
            if (Array.isArray(msg)) msg = msg.join('<br>');
            showToast('danger', msg);
          } else {
            Swal.fire('Success!', notif.content || 'PO created successfully', 'success')
              .then(() => {
                window.location.href = data.redirect_to;
              });
          }
        },
        error: function(xhr) {
          var resp = xhr.responseJSON || {};
          var data = resp.data || {};
          var notif = data.notification || {};
          var msg = notif.content || resp.message || 'Terjadi kesalahan';
          if (Array.isArray(msg)) msg = msg.join('<br>');
          showToast('danger', msg);
        },
        complete: function() {
          $btn.prop('disabled', false).html(btnHtml);
        }
      });
    });

    $('#modalSummary').on('show.bs.modal', function() {
      var $loading = $('#summaryLoading');
      var $wrap = $('#summaryWrap');
      $loading.show();
      $wrap.hide();
      $.ajax({
        url: '{{ route("superuser.gudang.purchase_order.summary_json") }}',
        method: 'GET',
        dataType: 'JSON',
        success: function(resp) {
          var rows = (resp && resp.Data) ? resp.Data : [];
          var html = '';
          var total = 0;
          if (rows.length === 0) {
            html = '<tr><td colspan="5" class="text-center text-muted py-15"><i class="fa fa-inbox mr-5"></i>Tidak ada outstanding.</td></tr>';
          } else {
            $.each(rows, function(i, r) {
              total += parseFloat(r.total_quantity) || 0;
              html += '<tr>'
                + '<td class="text-center">' + (i + 1) + '</td>'
                + '<td>' + esc(r.produk_code) + ' - ' + esc(r.produk_name) + '</td>'
                + '<td class="text-center">' + esc(r.kemasan) + '</td>'
                + '<td class="text-center">' + esc(r.total_quantity) + '</td>'
                + '<td class="text-center">' + esc(r.kode_po) + '</td>'
                + '</tr>';
            });
          }
          $('#summaryBody').html(html);
          $('#summaryTotal').text(total);
          $loading.hide();
          $wrap.show();
        },
        error: function() {
          $loading.html('<span class="text-danger">Gagal memuat ringkasan. Cek koneksi internet.</span>');
        }
      });
    });
  });
</script>
@endpush
