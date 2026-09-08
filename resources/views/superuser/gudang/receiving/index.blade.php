@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item active">Receiving</span>
</nav>

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif

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

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<div class="block">
  <div class="block-content">
    @php
      use App\Entities\Gudang\Receiving as RI;
      $role = $superuser->division;           // singkat
    @endphp

    <div class="d-flex flex-wrap" style="gap:8px;">
    @if(in_array($role, ['Admin','Developer', 'Management']))
    <button type="button" class="btn btn-primary min-width-125" data-toggle="modal" data-target="#modalCreateReceiving">
      <i class="fa fa-plus mr-5"></i> New
    </button>
    @endif

    <button type="button" class="btn btn-success min-width-125" data-toggle="modal" data-target="#modalExportReceiving" title="Download rekap receiving sebagai Excel (bisa filter tanggal)">
      <i class="fa fa-file-excel-o mr-5"></i> Export
    </button>
    </div>

    {{-- <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button> --}}
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-bordred table-striped" style="width:100%">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Tanggal Terima</th>
          <th class="text-center">kode</th>
          <th class="text-center">Gudang</th>
          <th class="text-center">Status</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('styles')
<style>
  #modalCreateReceiving .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
  #modalCreateReceiving .modal-header { background: #f2f5fa; border-bottom: 1px solid #e6eaf1; }
  #modalCreateReceiving .modal-title { font-weight: 800; font-size: 16px; color: #1c2733; }
  #modalCreateReceiving .field-label { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #7a8494; margin-bottom: 4px; text-align: left; }
  #modalCreateReceiving .field-label .req { color: #d9534f; margin-left: 2px; }
  #modalCreateReceiving .form-control { border-radius: 8px; border-color: #d8dfec; font-size: 13px; }
  #modalCreateReceiving .form-control:focus { border-color: #3d6fd1; box-shadow: 0 0 0 3px rgba(61,111,209,.14); }
  #modalCreateReceiving .select2-container .select2-selection--single { height: 38px !important; border-radius: 8px !important; border-color: #d8dfec !important; display: flex !important; align-items: center !important; }
  #modalCreateReceiving .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: normal !important; font-size: 13px; padding-left: 12px !important; }
  #modalCreateReceiving .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
</style>
@endpush

@section('modal')

<!-- Modal Create Receiving -->
<div class="modal fade" id="modalCreateReceiving" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-plus mr-5"></i>Receiving Baru</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmCreateReceiving">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label class="field-label">Code<span class="req">*</span></label>
            <input type="text" class="form-control" name="code" onkeyup="nospaces(this)" placeholder="Kode receiving..." required>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label class="field-label">Warehouse<span class="req">*</span></label>
              <select class="form-control js-select2" name="warehouse" data-placeholder="— Pilih warehouse —" required style="width:100%;">
                <option></option>
                @foreach($warehouses as $wh)
                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6">
              <label class="field-label">PBM Date</label>
              <input type="date" class="form-control" id="pbm_date" name="pbm_date">
            </div>
          </div>
          <div class="form-group mb-0">
            <label class="field-label">Note</label>
            <textarea class="form-control" name="note" rows="2" placeholder="Catatan..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary btn-submit" id="btnSubmitReceiving">Buat &amp; Isi Detail <i class="fa fa-arrow-right ml-5"></i></button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- @include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.master.warehouse.import_template'),
  'import_url' => route('superuser.master.warehouse.import'),
  'export_url' => route('superuser.master.warehouse.export')
]) --}}

<!-- Modal Export Receiving -->
<div class="modal fade" id="modalExportReceiving" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-file-excel-o mr-5"></i>Export Receiving</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Dari tanggal <small class="text-muted">(Tanggal Terima)</small></label>
          <input type="date" class="form-control" id="exp-start">
        </div>
        <div class="form-group mb-0">
          <label>Sampai tanggal</label>
          <input type="date" class="form-control" id="exp-end">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" id="btnDoExport">
          <i class="fa fa-download mr-5"></i> Download
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  $('.js-select2').select2({ dropdownParent: $('#modalCreateReceiving') });

  $('#modalCreateReceiving').on('hidden.bs.modal', function() {
    $('#frmCreateReceiving')[0].reset();
    $('#frmCreateReceiving .js-select2').val('').trigger('change');
  });

  // Klik tombol submit eksplisit (tidak bergantung asosiasi form-button browser)
  $(document).on('click', '#btnSubmitReceiving', function(e) {
    e.preventDefault();
    var form = $('#frmCreateReceiving')[0];
    if (!form) return;
    if (form.checkValidity()) {
      $(form).trigger('submit');
    } else if (typeof form.reportValidity === 'function') {
      form.reportValidity();
    }
  });

  // Paksa kalender tanggal terbuka saat field diklik
  $(document).on('click', '#pbm_date', function() {
    try { this.showPicker(); } catch (err) { /* browser lama: abaikan */ }
  });

  $(document).on('submit', '#frmCreateReceiving', function(e) {
    e.preventDefault();
    var $btn = $(this).find('.btn-submit');
    var btnHtml = $btn.html();
    $.ajax({
      url: '{{ route("superuser.gudang.receiving.store") }}',
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
          var msg = notif.content || 'Terjadi kesalahan';
          showToast('danger', Array.isArray(msg) ? msg.join('<br>') : msg);
        } else {
          $('#modalCreateReceiving').modal('hide');
          window.location.href = data.redirect_to;
        }
      },
      error: function(xhr) {
        var resp = xhr.responseJSON || {};
        var notif = (resp.data || {}).notification || {};
        var msg = notif.content || 'Terjadi kesalahan';
        showToast('danger', Array.isArray(msg) ? msg.join('<br>') : msg);
      },
      complete: function() {
        $btn.prop('disabled', false).html(btnHtml);
      }
    });
  });

  let datatableUrl = '{{ route('superuser.gudang.receiving.json') }}';

  $('#datatable').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'DT_RowIndex', name: 'id'},
      {
        data: 'pbm_date',
        render: {
          _: 'display',
          sort: 'timestamp'
        }
      },
      {data: 'code'},
      {data: 'warehouse'},
      {data: 'status'},
      {data: 'action', orderable: false, searcable: false}
    ],
    order: [
      [1, 'desc']
    ],
    pageLength: 5,
    lengthMenu: [
      [5, 15, 20],
      [5, 15, 20]
    ],
    "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>> <"row"<"col-sm-12 col-md-12"p>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
  });

  $(document).on('click', '#btnDoExport', function() {
    var start = $('#exp-start').val();
    var end = $('#exp-end').val();
    if ((start && !end) || (!start && end)) {
      alert('Isi kedua tanggal, atau kosongkan keduanya untuk semua data.');
      return;
    }
    if (start && end && start > end) {
      alert('Tanggal awal tidak boleh lebih dari tanggal akhir.');
      return;
    }
    var url = '{{ route("superuser.gudang.receiving.export") }}';
    var params = [];
    if (start) params.push('start_date=' + encodeURIComponent(start));
    if (end) params.push('end_date=' + encodeURIComponent(end));
    if (params.length > 0) url += '?' + params.join('&');
    $('#modalExportReceiving').modal('hide');
    window.location.href = url;
  });
});
</script>
@endpush