@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item">Management</span>
  <span class="breadcrumb-item">Penjualan Sales</span>
  <span class="breadcrumb-item active">Kinerja Sales</span>
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
  <hr class="my-20">
  <div class="block-content block-content-full">
    <form action="{{ route('superuser.report.employee_performance_product.print_report') }}" method="POST">
    @csrf
          <div class="mb-3 row">
            <div class="col-3 col-form-label required"><h5>Sales :</h5></div>
              <div class="col">
                <select class="js-select2 form-control" id="sales" name="sales[]" style="width: 50%;" data-placeholder="Select Sales" multiple required>
                  <option value="">Pilih Sales</option>
                  @foreach($sales as $sale)
                    <option value="{{ $sale->officer }}">{{ $sale->officer }}</option>
                  @endforeach
                </select>
              </div>
          </div>
        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <label>Set Period From</label>
              <input type="date" name="period_from" id="period_from" class="form-control" value="{{ date('Y-m-01') }}">
            </div>   
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <label>Set Period To</label>
              <input type="date" name="period_to" id="period_to" class="form-control" value="{{ date('Y-m-d') }}">
            </div>   
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <br>
              <button type="submit" class="btn bg-gd-corporate border-0 text-white" aria-label="Print Report" id="submit-btn">
                Download <i class="fa fa-print ml-10"></i>
              </button>
            </div>   
          </div>
        </div>
  </div>
</form>
</div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
  <script type="text/javascript">
    $(document).ready(function() {
        $('.js-select2').select2();


    });
  </script>
@endpush
