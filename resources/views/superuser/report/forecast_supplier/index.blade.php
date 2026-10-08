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
  <form id="formForecast" action="{{ route('superuser.report.forecast_supplier.printReport') }}" method="POST">
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
              <label>Jumlah Semester</label>
              <input type="number" name="semester_count" id="semester_count" class="form-control" value="2" min="1" max="12">
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
  var alertClass = (type === 'error') ? 'alert-danger' : 'alert-success';
  var alertHTML = '<div class="alert ' + alertClass + ' alert-dismissable" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    message +
                  '</div>';
  $('#alert-container').html(alertHTML);
}

$("#vendor_name").val("all").change();

$('#formForecast').on('submit', function(e){
  e.preventDefault();
  var $btn = $(this).find('button[type=submit]');
  $btn.prop('disabled', true);

  // buka tab dulu supaya tidak diblokir popup blocker
  var w = window.open('', '_blank');

  $.ajax({
    url: $(this).attr('action'),
    method: 'POST',
    data: $(this).serialize(),
    success: function(res){
      if (res.success && res.pdf_url) {
        w.location = res.pdf_url;
      } else {
        w.close();
        showAlert(res.error || 'Gagal membuat laporan.', 'error');
      }
    },
    error: function(xhr){
      w.close();
      var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Terjadi kesalahan saat membuat laporan.';
      showAlert(msg, 'error');
    },
    complete: function(){ $btn.prop('disabled', false); }
  });
});

});
  </script>
@endpush
