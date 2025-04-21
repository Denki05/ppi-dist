@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Laporan</span>
  <span class="breadcrumb-item">Management</span>
  <span class="breadcrumb-item active">Register Customer UV</span>
</nav>

@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session('success') }}</p>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible" role="alert">
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
    <strong>{{ session('error') ? 'Error!' : 'Berhasil!' }}</strong> {!! session('error') ?? session('success') !!}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<form id="reportForm" method="POST">
    @csrf
    <input type="hidden" name="action" id="action" value="export">
    <div class="block">
        <hr class="my-20">
        <div class="block-content block-content-full">
            <div class="btn-toolbar" role="toolbar">
                <div class="btn-group mr-2">
                    <a class="btn btn-primary" href="{{ route('superuser.accounting.finance_simulation.generate_last_year') }}">
                        <i class="fa fa-sync"></i> Sync Data UV
                    </a>
                </div>
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-danger" onclick="saveConfirmation('{{ route('superuser.accounting.finance_simulation.delete_data') }}')">
                        <i class="fa fa-trash"></i> Remove Data UV
                    </button>
                </div>
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-success" onclick="submitForm('print')" targe="_blank">
                        <i class="fa fa-file-pdf-o" aria-hidden="true"></i> Export PDF
                    </button>
                </div>
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-info" onclick="submitForm('export')">
                        <i class="fa fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>
            <br>
            <div class="mb-3 row">
                <div class="col-3 col-form-label required"><h5>Type Report:</h5></div>
                <div class="col">
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" id="type_customer" value="1" checked>
                        <h6>Customer Type Brand UV</h6>
                    </label>
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" id="zone_customer" value="2">
                        <h6>Customer by Zone UV(transaksi)</h6>
                    </label>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-3 col-form-label required"><h5>Tampilkan Nominal:</h5></div>
                <div class="col">
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="nominal" id="nominal_yes" value="1" checked>
                        <h6>Yes</h6>
                    </label>
                    <!-- <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="nominal" id="nominal_no" value="2">
                        <h6>No</h6>
                    </label> -->
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Set Period From</label>
                        <input type="date" name="start" id="period_from" class="form-control" value="{{ old('start', date('Y-m-01')) }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Set Period To</label>
                        <input type="date" name="end" id="period_to" class="form-control" value="{{ old('end', date('Y-m-d')) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script>
  $(function(){
    $('.js-select2').select2();
  });

  function submitForm(actionType) {
      let form = $('#reportForm');
      if (actionType === 'print') {
          form.attr('action', "{{ route('superuser.report.customer_type_brand_uv.print_report_2') }}");
      } else {
          form.attr('action', "{{ route('superuser.report.customer_type_brand_uv.exportReport') }}");
      }
      form.submit();
  }

  function saveConfirmation(url) {
      Swal.fire({
          title: 'Are you sure?',
          text: 'This action cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete it!',
      }).then((result) => {
          if (result.isConfirmed) {
              window.location.href = url;
          }
      });
  }
</script>
@endpush