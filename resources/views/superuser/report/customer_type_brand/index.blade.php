@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item">Management</span>
  <span class="breadcrumb-item active">Register Customer</span>
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

<form action="{{ route('superuser.report.customer_type_brand.print_report') }}" method="POST">
    @csrf
    <div class="block">
        <hr class="my-20">
        <div class="block-content block-content-full">
            <div class="row">
                <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
                    <div class="btn-group mr-2" role="group" aria-label="First group">
                        <a class="btn btn-primary" href="{{ route('superuser.report.customer_type_brand.postData') }}" role="button">
                            <i class="fa fa-sync"></i> Sync Data
                        </a>
                    </div>
                    <div class="btn-group mr-2" role="group" aria-label="Second group">
                        <a class="btn btn-danger" href="javascript:saveConfirmation('{{ route('superuser.report.customer_type_brand.removeDt') }}')" role="button">
                            <i class="fa fa-trash"></i> Remove Data
                        </a>
                    </div>
                </div>
            </div>
            <br>
            <div class="mb-3 row">
                <div class="col-3 col-form-label required"><h5>Type Report:</h5></div>
                <div class="col">
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" id="type_customer" value="1">
                        <h6>Customer Type Brand</h6>
                    </label>
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" id="zone_customer" value="2">
                        <h6>Customer by Zone (transaksi)</h6>
                    </label>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-3 col-form-label required"><h5>Tampilkan Nominal:</h5></div>
                <div class="col">
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="nominal" id="nominal_yes" value="1">
                        <h6>Yes</h6>
                    </label>
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="nominal" id="nominal_no" value="2">
                        <h6>No</h6>
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Set Period From</label>
                        <input type="date" name="start" id="period_from" class="form-control">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Set Period To</label>
                        <input type="date" name="end" id="period_to" class="form-control">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <br>
                        <button type="submit" class="btn btn-success"><i class="fa fa-print"></i> Print</button>
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
  <script type="text/javascript">
    
    $(function(){
      $('.js-select2').select2();

      // $('#printReport').on('click', function(e) {
      //   e.preventDefault(); // prevent the form submit
      //   let start = $('#period_from').val();
      //   let end = $('#period_to').val();
      //   let typeReport = $("input:radio[name=radios-inline]:checked").val();
      //   let showNominal = $("input:radio[name=radios-inline-nominal]:checked").val();
        
      //   $.ajax({
      //      type:'POST',
      //      url:"{{ route('superuser.report.customer_type_brand.print_report') }}",
      //      data:{"_token": "{{ csrf_token() }}", "start":start, "end":end, "type":typeReport, "nominal":showNominal},
      //      success:function(response){
      //       // console.log(response);
      //      }
      //   });
      // })
    });
  </script>
@endpush
