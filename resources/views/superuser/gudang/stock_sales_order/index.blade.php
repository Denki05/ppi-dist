@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item active">Stock Sales(Awal)</span>
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
<div class="block">
  <div class="block-content">
    <a href="{{ route('superuser.gudang.stock_sales_order.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>

    {{-- <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button> --}}
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th>Warehouse</th>
          <th>Product</th>
          <th>Quantity</th>
          <th>Created at</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@section('modal')

{{-- @include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.master.warehouse.import_template'),
  'import_url' => route('superuser.master.warehouse.import'),
  'export_url' => route('superuser.master.warehouse.export')
]) --}}

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.gudang.stock_sales_order.json') }}';

  $('#datatable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'warehouse', name: 'master_warehouses.name'},
      {data: 'productName', name: 'master_products.name'},
      {data: 'qty', name: 'stock_sales_order.quantity'},
      {data: 'date', name: 'stock_sales_order.created_at'},
    ],
    order: [
      [1, 'desc']
    ],
    pageLength: 5,
    lengthMenu: [
      [5, 15, 20],
      [5, 15, 20]
    ],
  });
});
</script>
@endpush
