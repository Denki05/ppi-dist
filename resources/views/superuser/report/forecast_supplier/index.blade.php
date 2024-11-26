@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Laporan</span>
  <span class="breadcrumb-item">Management</span>
  <span class="breadcrumb-item active">Forecast Principal</span>
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
<div id="alert-container"></div>

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
    <form action="{{ route('superuser.report.forecast_supplier.printReport') }}" method="POST">
      @csrf
        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <label>Vendor</label>
              <!-- <input type="date" name="period_from" id="period_from" class="form-control"> -->
              <select class="form-control js-select2" name="vendor_name" id="vendor_name">
                <option value="all">All</option>
                @foreach($vendor AS $row)
                <option value="{{$row->name}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>   
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
              <!-- <button class="btn btn-success" type="submit"><i class="fa fa-print"></i> print</button> -->
              <button type="submit" class="btn btn-success"><i class="fa fa-print"></i> Print</button>
              <!-- <button type="submit" id="printReport" class="btn btn-success">Print</button> -->
            </div>   
          </div>
        </div>
  </div>
  </form>
</div>

@endsection

@include('superuser.asset.plugin.select2')

@push('scripts')

  <script type="text/javascript">
    
    $(function(){

      $('.js-select2').select2();

      function showAlert(message, type) {
          var alertClass = 'alert-info'; // Default alert class
          if (type === 'error') {
              alertClass = 'alert-danger';
          } else if (type === 'success') {
              alertClass = 'alert-success';
          }
          var alertHTML = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                              message +
                              '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                          '</div>';
          $('#alert-container').html(alertHTML);
      }

      $("#vendor_name").val("all").change();
    });
  </script>
@endpush
