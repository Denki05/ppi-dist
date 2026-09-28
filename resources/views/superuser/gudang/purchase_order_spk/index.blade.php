@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item active">SPK</span>
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
      <!-- <div class="row mb-30">
        <div class="col-12">
          <a href="{{route('superuser.gudang.purchase_order.create')}}" class="btn btn-primary btn-add"><i class="fa fa-plus"></i> Add PO</a>
        </div>
      </div> -->
      <button type="button" class="btn btn-primary min-width-125" data-toggle="modal" data-target="#modalCreateSPK">
        <i class="fa fa-plus mr-5"></i> New SPK
      </button>

      <a href="{{route('superuser.gudang.purchase_order_spk.summary')}}">
        <button type="button" class="btn btn-outline-info min-width-125">Summary</button>
      </a>

      <hr class="my-20">

      <div class="row mb-30">
        <div class="col-12">
        <table id="datatables" class="table table-bordred table-striped" style="width:100%">
          <thead>
            <tr>
              <td class="text-center">#</td>
              <td class="text-center">Created at</td>
              <td class="text-center">PO Code</td>
              <td class="text-center">Latest Update</td>
              <td class="text-center">Status</td>
              <td class="text-center">Action</td>
            </tr>
          </thead>
          
        </table>
        </div>
      </div>
    </div>
</div>

<!-- Modal Create SPK (sama seperti PO biasa) -->
<div class="modal fade" id="modalCreateSPK" tabindex="-1" role="dialog" aria-labelledby="modalCreateSPKLabel" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content po-create">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="modalCreateSPKLabel"><i class="fa fa-file-text-o mr-5"></i>Buat SPK Baru</h5>
          <small class="text-muted">1 SPK = 1 Brand — setelah dibuat, isi produk per halaman kemasan.</small>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmCreateSPK">
        @csrf
        <div class="modal-body">
          <div class="form-group mb-10">
            <label class="field-label">SPK Code <span class="text-muted">(otomatis)</span></label>
            <div class="input-group">
              <input type="text" class="form-control" name="code" readonly value="{{ App\Repositories\CodeRepo::generatePurchaseOrderSPK() }}">
              <div class="input-group-append"><span class="input-group-text"><i class="fa fa-lock"></i></span></div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6 mb-10">
              <label class="field-label">Warehouse<span class="req">*</span></label>
              <select class="form-control js-select2" name="warehouse" data-placeholder="— Pilih warehouse —" required>
                <option></option>
                @foreach($warehouse as $wh)
                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6 mb-10">
              <label class="field-label">Brand<span class="req">*</span></label>
              <select class="form-control js-select2" name="brand_lokal_id" data-placeholder="— Pilih brand —" required>
                <option></option>
                @foreach($brands as $br)
                <option value="{{ $br->id }}">{{ $br->brand_name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6 mb-10">
              <label class="field-label">ETD<span class="req">*</span></label>
              <input type="date" class="form-control" name="etd" required>
            </div>
            <div class="form-group col-md-6 mb-10">
              <label class="field-label">Note <span class="text-muted" style="font-weight:400;">(opsional)</span></label>
              <textarea class="form-control" name="note" rows="1" placeholder="Catatan SPK..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-submit">
            Buat SPK &amp; Isi Produk <i class="fa fa-arrow-right ml-5"></i>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('styles')
<style>
  .po-create { border: 0; border-radius: 14px; overflow: hidden; }
  .po-create .modal-header { background: #f2f5fa; border-bottom: 1px solid #e6eaf1; padding: 14px 18px; }
  .po-create .modal-title { font-weight: 800; font-size: 16px; color: #1c2733; }
  .po-create .modal-body { padding: 16px 18px; }
  .po-create .field-label { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #7a8494; margin-bottom: 4px; text-align: left; }
  .po-create .field-label .req { color: #d9534f; margin-left: 2px; }
  .po-create .form-control { border-radius: 8px; border-color: #d8dfec; font-size: 13px; }
  .po-create .form-control:focus { border-color: #3d6fd1; box-shadow: 0 0 0 3px rgba(61,111,209,.14); }
  .po-create .form-control[readonly] { background: #eef1f6; color: #7a8494; }
  .po-create .modal-footer { border-top: 1px solid #e6eaf1; padding: 12px 18px; }
  .po-create .modal-footer .btn { border-radius: 8px; }
  .po-create .btn-submit { box-shadow: 0 3px 10px rgba(61,111,209,.35); }
  #modalCreateSPK .select2-container .select2-selection--single { height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important; border-color: #d8dfec !important; display: flex !important; align-items: center !important; padding: 0 !important; }
  #modalCreateSPK .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: normal !important; font-size: 13px; padding-left: 12px !important; }
  #modalCreateSPK .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
</style>
@endpush

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2({
      dropdownParent: $('#modalCreateSPK')
    });

    $('#modalCreateSPK').on('hidden.bs.modal', function() {
      $('#frmCreateSPK')[0].reset();
      $('#frmCreateSPK .js-select2').val('').trigger('change');
    });

    $('#datatables').DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        "url": '{{route('superuser.gudang.purchase_order_spk.json')}}',
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
        {data: 'updated_by'},
        {data: 'status'},
        {data: 'action', orderable: false, searcable: false}
      ],
      scrollCollapse: true,
      scrollX: true,
      scrollY: 300,
      order: [
        [1, 'desc']
      ],
      pageLength: 5,
      lengthMenu: [
        [5, 15, 20],
        [5, 15, 20]
      ],
    });

    $(document).on('submit', '#frmCreateSPK', function(e) {
      e.preventDefault();
      var $btn = $('.btn-submit');
      $.ajax({
        url: '{{ route("superuser.gudang.purchase_order_spk.store") }}',
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
            $('#modalCreateSPK').modal('hide');
            Swal.fire('Success!', notif.content || 'SPK created successfully', 'success')
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
          $btn.prop('disabled', false).html('Buat SPK &amp; Isi Produk <i class="fa fa-arrow-right ml-5"></i>');
        }
      });
    });
  });
</script>
@endpush