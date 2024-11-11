@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item ">Management</span>
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

<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
      <div class="row">
        <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
          <div class="btn-group mr-2" role="group" aria-label="First group">
            <a class="btn btn-primary" href="{{ route('superuser.report.customer_type_brand.postData') }}" role="button"><i class="fa fa-sync"></i> Sync Data</a>
          </div>
          <div class="btn-group mr-2" role="group" aria-label="Second group">
            <a class="btn btn-danger" href="javascript:saveConfirmation('{{ route('superuser.report.customer_type_brand.removeDt') }}')" role="button"><i class="fa fa-trash"></i> Remove Data</a>
          </div>
        </div>  
      </div>
      <br>

        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <label>Set Period From</label>
              <input type="date" name="period_from" id="period_from" class="form-control">
            </div>   
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <label>Set Period To</label>
              <input type="date" name="period_to" id="period_to" class="form-control">
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
          <div class="mb-3 row">
            <div class="col-3 col-form-label required"><b>Type Report</b></div>
              <div class="col">
                <label class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="radios-inline" id="type_customer" value="1">
                  <span class="form-check-label">Type Customer</span>
                </label>
                <label class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="radios-inline" id="zone_customer" value="2">
                  <span class="form-check-label">Zone Customer</span>
                </label>
            </div>
          </div>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')

  <script type="text/javascript">
    
    $(function(){
      

      $('.js-select2').select2();

      $(document).on('click','.btn-print',function(){
          let period_to = '<?= $_GET["period_to"] ?? null ?>';
          let period_from = '<?= $_GET["period_from"] ?? null ?>';
          let customer_id = '<?= $_GET["customer_id"] ?? null ?>';
          let sales_senior_id = '<?= $_GET["sales_senior_id"] ?? null ?>';
          let sales_id = '<?= $_GET["sales_id"] ?? null ?>';

          window.open('{{route('superuser.report.revenue.print')}}'+'?period_from='+period_from+'&period_to='+period_to+'&customer_id='+customer_id+'&sales_senior_id='+sales_senior_id+'&sales_id='+sales_id,'_blank');
      })

      $('#printReport').on('click', function(e) {
        e.preventDefault(); // prevent the form submit
        let start = $('#period_from').val();
        let end = $('#period_to').val();
        let typeReport = $("input:radio[name=radios-inline]:checked").val();
        
        $.ajax({
           type:'POST',
           url:"{{ route('superuser.report.customer_type_brand.print_report') }}",
           data:{"_token": "{{ csrf_token() }}", "start":start, "end":end, "type":typeReport},
           success:function(response){
            // console.log(response);
           }
        });
      })
    });
  </script>
@endpush
