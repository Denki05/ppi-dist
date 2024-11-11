@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <a href="{{ route('superuser.master.product.create') }}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true" style="margin-left: 10px !important;">Create</a>
  <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>

  <button type="button" class="btn btn-outline-warning ml-10" data-toggle="modal" data-target="#ModalLoginForm">
    Print
  </button>

  @role('Developer')
    <a class="btn btn-outline-success ml-10" href="javascript:saveConfirmation('{{ route('superuser.master.product.update_category_type_pack') }}')" role="button">Fee</a>
  @endrole
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

<div class="form-group row">
      <div class="col-md-9">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="product_name">Product :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="product_name" name="product_name" data-placeholder="Select Product">
                  <option value="all">All</option>
                  @foreach ($product as $value)
                    <option value="{{ $value->name }}">{{ $value->code }} - {{ $value->name }}</option>
                  @endforeach
                </select>
              </div>
              <label class="col-md-2 col-form-label text-left" for="brand_lokal">Brand :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="brand_lokal" name="brand_lokal" data-placeholder="Select Brand">
                  <option value="all">All</option>
                  @foreach ($brand_lokal as $value)
                    <option value="{{ $value->brand_name }}">{{ $value->brand_name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="kategori">Kategori :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="kategori" name="kategori" data-placeholder="Select Kategori">
                  <option value="all">All</option>
                  @foreach ($kategori as $value)
                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                  @endforeach
                </select>
              </div>

              <label class="col-md-2 col-form-label text-left" for="status">Status :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="status" name="status" data-placeholder="Select Status">
                  <option value="all">All</option>
                  @foreach(App\Entities\Master\Product::STATUS as $row => $value)
                    <option value="{{$value}}">{{ $row }}</option>
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
                <a href="#" id="btn-filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                  Filter <i class="fa fa-search ml-10"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

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
  <div class="block-content block-content-full">
  <table class="table table-striped" id="datatables">
      <thead>
        <tr>
          <th>Code</th>
          <th>Brand</th>
          <th>Category</th>
          <th>Name</th>
          <th>Status</th>
          <th>Action</th>
          <th>Action 2</th>
        </tr>
      </thead>
      <tbody>
        
      </tbody>
    </table>
  </div>
</div>

<!-- Modal HTML Markup -->
<div id="ModalLoginForm" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title">Print Product</h1>
            </div>
            <div class="modal-body">
                <form role="form" method="get" action="{{ route('superuser.master.product.print_product') }}">
                @csrf
                    <div class="form-group">
                        <label class="control-label">Merek</label>
                        <div>
                            <select class="form-control js-select2" name="brand_name" style="width:100%;">
                              <option value="">Pilih Merek</option>
                              @foreach($brand_lokal as $row)
                              <option value="{{$row->brand_name}}">{{$row->brand_name}}</option>
                              @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Type Print</label>
                        <div>
                            <select class="form-control js-select2" name="type_print" style="width:100%;">
                              <option value="">Pilih Category</option>
                              <option value="price_list">Price List</option>
                              <option value="product_list">Product List</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div>
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Print</button>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

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
  let firstDatatableUrl = datatableUrl + '?product_name=all' + '&brand_lokal=all' + '&kategori=all';

  var datatable = $('#datatables').DataTable({
    language: {
      processing: "<span class='fa-stack fa-lg'>\n\
                    <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                  </span>",
    },
    processing: true,
    serverSide: false,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'code', name: 'master_products.code', width: "100px"},
      {data: 'brand_name', name: 'master_products.brand_name', width: "150px"},
      {data: 'category_name', name: 'master_product_categories.category_name', width: "200px"},
      {data: 'name', name: 'master_products.name', width: "250px"},
      {data: 'status', width: "150px"},
      {data: 'action', width: "100px"},
      {data: 'action2', width: "100px"},
    ],
    autoWidth: false
  })

  $('#btn-filter').on('click', function(e) {
    e.preventDefault();

    var kategori_name = $('#kategori').val();
    var product = $('#product_name').val();
    var brand = $('#brand_lokal').val();
    var status = $('#status').val();

    let newDatatableUrl = datatableUrl + '?product_name=' + product + '&brand_lokal=' + brand + '&kategori=' + kategori_name + '&status=' + status;
    datatable.ajax.url(newDatatableUrl).load();
  });

  $('.js-select2').select2()
});
</script>
@endpush
