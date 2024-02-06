@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting</span>
  <span class="breadcrumb-item active">Product Tax</span>
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

@if(session()->has('collect_success') || session()->has('collect_error'))
<div class="container">
  <div class="row">
    <div class="col pl-0">
      <div class="alert alert-success alert-dismissable" role="alert" style="max-height: 300px; overflow-y: auto;">
        <h3 class="alert-heading font-size-h4 font-w400">Successful Import</h3>
        @foreach (session()->get('collect_success') as $msg)
        <p class="mb-0">{{ $msg }}</p>
        @endforeach
      </div>
    </div>
    <div class="col pr-0">
      <div class="alert alert-danger alert-dismissable" role="alert" style="max-height: 300px; overflow-y: auto;">
        <h3 class="alert-heading font-size-h4 font-w400">Failed Import</h3>
        @foreach (session()->get('collect_error') as $msg)
        <p class="mb-0">{{ $msg }}</p>
        @endforeach
      </div>
    </div>
  </div>
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
  <div class="block-header block-header-default">
    <h2 class="block-title"><b>#Select a Mitra first to continue the process!</b></h2>
  </div>
  <div class="block-content block-content-full">
    <form id="myForm" method="POST" role="form" enctype="multipart/form-data" novalidate>
    @csrf
      <div class="row">
        <div class="col-lg-6">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Mitra</label>
              <div class="col-md-8">
                <select class="form-control js-select2 js-select2-mitra" name="mitra" id="mitra" data-placeholder="Select Mitra">
                </select>
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Submit</button>
              </div>
            </div>   
          </div>
      </div>
    </form>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@section('modal')

@include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.accounting.product_finance.import_template'),
  'import_url' => route('superuser.accounting.product_finance.import')
])

@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {
      $('.js-select2').select2();

      $(".js-select2-mitra").select2({
        ajax: {
          url: '{{ route('superuser.accounting.product_finance.search_mitra') }}',
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              q: params.term,
              _token: "{{csrf_token()}}"
            };
          },
          cache: true
        },
      });

      $('#myForm').on('submit', function (e) {
        e.preventDefault(); // prevent the form submit
        var id = $('#mitra').val();
        var url = "{{ route('superuser.accounting.product_finance.show', ":id") }}";
        url = url.replace(':id', id);
        var AlertMsg = $('div[role="alert"]');

        var formData = new FormData(this); 
        // build the ajax call
        $.ajax({
            url: url,
            type: 'GET',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) { 
              window.location.href = url;
            },
        });
      });
    })
</script>
@endpush
