@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Inventory</span>
  <span class="breadcrumb-item active">Stock</span>
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
    <div class="form-group row">
      <label class="col-md-1 col-form-label text-left" for="code">Product Code</label>
      <div class="col-md-3">
        <div class="form-control-plaintext">{{ $product->code }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-1 col-form-label text-left" for="name">Product Name</label>
      <div class="col-md-4">
        <div class="form-control-plaintext">{{ $product->name }}</div>
      </div>
      <div class="col-md-7 text-right">
        <div class="form-control-plaintext">Warehouse : {{ $warehouse->name }}</div>
      </div>
    </div>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">Date</th>
          <th class="text-center">Transaction</th>
          <th class="text-center">In</th>
          <th class="text-center">Out</th>
          <th class="text-center">Balance</th>
          <th class="text-center">Description</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($collects as $item)
            <tr>
              <td>{{ $item['created_at'] }}</td>
              <td>{{ $item['transaction'] }}</td>
              <td>{{ $item['in'] }}</td>
              <td>{{ $item['out'] }}</td>
              <td>{{ $item['balance'] }}</td>
              <td>{{ $item['description'] }}</td>
            </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {

  $('#datatable').DataTable({
    // paging: false,
    searching: false,
    sorting: false,
    dom: '<"row"<"col-sm-2"l><"col-sm-10 text-left"B>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    // order: [
    //   [0, 'desc']
    // ],
  });
});
</script>
@endpush
