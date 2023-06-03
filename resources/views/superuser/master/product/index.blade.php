@extends('superuser.app')

@section('content')
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
<nav class="breadcrumb bg-white push">
  <a href="{{ route('superuser.master.product.create') }}" class="btn btn-primary btn-lg active" role="button" target="_blank" aria-pressed="true" style="margin-left: 10px !important;">Add Product</a>
  {{--<a href="{{ route('superuser.master.product.cetak') }}" class="btn btn-primary btn-lg active" role="button" target="_blank" aria-pressed="true" style="margin-left: 10px !important;">Print Product</a>
  <button type="button" class="btn btn-outline-danger ml-10" onclick="deleteMultiple()">Delete Checked</button>--}}
</nav>
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
      [1, 'desc']
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
