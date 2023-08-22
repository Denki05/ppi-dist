@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <a href="{{ route('superuser.master.product.create') }}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true" style="margin-left: 10px !important;">Create</a>
  <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>
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

@if(session()->has('collect_success'))
<div class="container">
  <div class="row"></div>
  <div class="alert alert-success alert-dismissable">
    <div class="alertwrapper clearfix">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <div class="alerticon successful">
        <span class="glyphicon glyphicon-ok-sign"></span>
      </div>
      <div class="alertcontent">
        <h4>Successful Import!</h4>
        @foreach (session()->get('collect_success') as $msg)
        <p class="mb-0">{{ $msg }}</p>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endif

@if(session()->has('collect_error'))
<div class="container">
  <div class="alert alert-danger alert-dismissable">
    <div class="alertwrapper clearfix">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <div class="alerticon successful">
        <span class="glyphicon glyphicon-ok-sign"></span>
      </div>
      <div class="alertcontent">
        <h4>Failed Import!</h4>
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
  <div class="block-content block-content-full">
  <table class="table table-striped" id="datatables">
      <thead>
        <tr>
          <th>#</th>
          <th>Code</th>
          <th>Brand</th>
          <th>Category</th>
          <th>Name</th>
          <th>Kemasan</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@section('modal')

@include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.master.product.import_template'),
  'import_url' => route('superuser.master.product.import'),
  'export_url' => route('superuser.master.product.export')
])

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.master.product.json') }}';

  $('#datatables').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'DT_RowIndex', name: 'master_products.id'},
      {data: 'code'},
      {data: 'brand_name'},
      {data: 'category_name', name: 'master_product_categories.category_name'},
      {data: 'name'},
      {data: 'pack_name', name: 'master_packaging.pack_name'},
      {data: 'status'},
      {data: 'action', orderable: false, searcable: false}
    ],
    order: [
      [0, 'asc']
    ],
    pageLength: 10,
    lengthMenu: [
      [15, 20, 50],
      [15, 20, 50]
    ],
  });
});
</script>
@endpush
