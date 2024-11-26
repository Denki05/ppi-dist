@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item">Oprasional</span>
  <span class="breadcrumb-item active">Customer Type Zone</span>
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
      <div class="row">
        <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
          <div class="btn-group mr-2" role="group" aria-label="First group">
            <a class="btn btn-primary" href="{{ route('superuser.report.customer_type_zone.postData') }}" role="button"><i class="fa fa-sync"></i> Sync Data</a>
          </div>
          <div class="btn-group mr-2" role="group" aria-label="Second group">
            <a class="btn btn-danger" href="{{ route('superuser.report.customer_type_zone.removeDt') }}" role="button"><i class="fa fa-trash"></i> Remove Data</a>
          </div>
        </div>  
      </div>
      <br>

      <form action="{{ route('superuser.report.customer_type_zone.print_report') }}" method="POST">
      @csrf
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
              <button type="submit" id="printReport" class="btn btn-success"><i class="fa fa-print"></i> Print</button>
              <!-- <button type="submit" id="printReport" class="btn btn-success">Print</button> -->
            </div>   
          </div>
        </div>
      </form>
  </div>
</div>

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
        
      //   $.ajax({
      //      type:'POST',
      //      url:"{{ route('superuser.report.customer_type_zone.print_report') }}",
      //      data:{"_token": "{{ csrf_token() }}", "start":start, "end":end},
      //      success:function(response){
      //       // console.log(response);
      //      }
      //   });
      // })
    });
  </script>
@endpush
