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
      <label class="col-md-2 col-form-label text-left" for="warehouse">Warehouse <span class="text-danger">*</span></label>
      <div class="col-md-3">
        <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Warehouse">
          <option></option>
          @foreach($warehouses as $warehouse)
          <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">SKU</th>
          <th class="text-center">Item</th>
          <th class="text-center">In</th>
          <th class="text-center">Out</th>
          <th class="text-center">Stock</th>
          <th class="text-center">Sell Forecast</th>
          <th class="text-center">Effective</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@section('modal')

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  $('.js-select2').select2()
  let datatableUrl = '{{ route('superuser.gudang.stock.json') }}';

  $('#warehouse').on('select2:select', function (e) {
    var data = e.params.data;
    
    let newDatatableUrl = datatableUrl+'?warehouse_id='+data.id;
    $('#datatable').DataTable().ajax.url(newDatatableUrl).load();
      
  });

  $('#datatable').DataTable({
    processing: true,
    // serverSide: true,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    // columns: [
    //   {data: 'DT_RowIndex', name: 'id'},
    //   {
    //     data: 'created_at',
    //     render: {
    //       _: 'display',
    //       sort: 'timestamp'
    //     }
    //   },
    //   {data: 'code'},
    //   {data: 'warehouse_id'},
    //   {data: 'status'},
    //   {data: 'action', orderable: false, searcable: false}
    // ],
    order: [
      [0, 'asc']
    ],
    pageLength: 10,
    lengthMenu: [
      [10, 25, 50, 100],
      [10, 25, 50, 100]
    ],
    "dom": '<"row"<"col-sm-2"l><"col-sm-7 text-left"B><"col-sm-3"f>> <"row"<"col-sm-12 col-md-12"p>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
  });
});
</script>
@endpush
