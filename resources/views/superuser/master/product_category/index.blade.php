@extends('superuser.app')

@section('content')
<!-- <nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <span class="breadcrumb-item active">Product Category</span>
</nav> -->
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
  <a href="{{route('superuser.master.brand_lokal.create')}}" class="btn btn-primary btn-lg active" role="button" target="_blank" aria-pressed="true" style="margin-left: 10px !important;">Add Brand</a>
  <a href="{{route('superuser.master.product_category.create')}}" class="btn btn-primary btn-lg active" role="button" target="_blank" aria-pressed="true" style="margin-left: 10px !important;">Add Category</a>
</nav>

<div class="block">
  <div class="block-content">
    <div class="form-group row">
        <div class="col-md-9">
          <div class="block">
            <div class="block-content">
              <div class="form-group row">
                
                <label class="col-md-2 col-form-label text-left" for="filter_brand">Brand :</label>
                <div class="col-md-4">
                  <select class="form-control js-select2" id="filter_brand" name="filter_brand" data-placeholder="Select Brand">
                    <option value="">All</option>
                    @foreach($brand_lokal as $brand)
                    <option value="{{$brand->brand_name}}">{{$brand->brand_name}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="block">
            <div class="block-content">
              <div class="form-group row">
                <div class="col-md-12 text-center">
                  <a href="#" id="filter" name="filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                    Filter <i class="fa fa-search ml-10"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <hr class="my-20">
    <div class="block-content block-content-full">
      <table id="datatable" class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Created at</th>
            <th>Code</th>
            <th>Brand</th>
            <th>Category</th>
            <th>Type</th>
            <th>Packaging</th>
            <th>Status</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@section('modal')

@include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.master.product_category.import_template'),
  'import_url' => route('superuser.master.product_category.import'),
  'export_url' => route('superuser.master.product_category.export')
])

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  
  let datatableUrl = '{{ route('superuser.master.product_category.json') }}';
  let firstDatatableUrl = datatableUrl +
        '?filter_brand=all';

  var datatable = $('#datatable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'DT_RowIndex', name: 'id'},
      {
        data: 'category_date',
        render: {
          _: 'display',
          sort: 'timestamp'
        }, name: 'master_product_category.created_at'
      },
      {data: 'code', name: 'master_product_category.code'},
      {data: 'brandName', name: 'master_product_category.brand_name'},
      {data: 'name', name: 'master_product_category.name'},
      {data: 'type', name: 'master_product_category.type'},
      {data: 'packaging', name: 'master_product_category.packaging'},
      {data: 'status', name: 'master_product_category.status'}
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
  $('#filter').on('click', function(e) {
        e.preventDefault();
        var filter_brand = $('#filter_brand').val();
        let newDatatableUrl = datatableUrl + '?filter_brand=' + filter_brand;
        datatable.ajax.url(newDatatableUrl).load();
  })
});
</script>
@endpush
