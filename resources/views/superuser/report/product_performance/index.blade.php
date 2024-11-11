@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item">Operasional</span>
  <span class="breadcrumb-item active">Produk - Customer</span>
</nav>
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

<form action="{{ route('superuser.report.product_performance.print_report') }}" method="POST">
  @csrf
  <div class="form-group row">
    <div class="col-md-9">
      <div class="block">
        <div class="block-content">
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-left" for="periode_from">Period From :</label>
            <div class="col-md-4">
              <input type="date" class="form-control" name="periode_from" id="periode_from" required>
            </div>
            <label class="col-md-2 col-form-label text-left" for="periode_to">Period To :</label>
            <div class="col-md-4">
              <input type="date" class="form-control" name="periode_to" id="periode_to" required>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-left" for="brand">Brand :</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="brand" name="brand[]" data-placeholder="Pilih Brand" multiple required>
                <option value="all">All</option>
                @foreach ($brand as $value)
                  <option value="{{ $value->brand_name }}">{{ $value->brand_name }}</option>
                @endforeach
              </select>
            </div>

            <label class="col-md-2 col-form-label text-left" for="product">Product :</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="product" name="product[]" data-placeholder="Pilih Variant" multiple required>
                <option value="all">All</option>
                @foreach ($product as $value)
                  <option value="{{ $value->id }}">{{ $value->code }} - {{ $value->name }} / {{ $value->packaging->pack_name }}</option>
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
              <button type="submit" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                Print <i class="fa fa-print ml-10"></i>
              </button>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-12 text-center">
                <button type="button" id="btn-filter" class="btn bg-gd-sea border-0 text-white pl-50 pr-50">
                  Preview <i class="fa fa-search ml-10"></i>
                </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="form-group row">
    <div class="block">
      <div class="block-content">
        <table class="datatable table" id="datatable">
          <thead class="thead-dark">
            <tr>
              <th>Brand</th>
              <th>Variant</th>
              <th>Customer</th>
              <th>Qty</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.daterangepicker')
@include('superuser.asset.plugin.datatables-button')

@push('scripts')
<script type="text/javascript">
  var start_date = $('#periode_from').val();
  var end_date = $('#periode_to').val();

  $(document).ready(function() {
    $('.js-select2').select2()

    let datatableUrl = '{{ route('superuser.report.product_performance.json') }}';
    let firstDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
      '&product=all&brand=all';

    var datatable = $('#datatable').DataTable({
      language: {
              processing: "<span class='fa-stack fa-lg'>\n\
                                    <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                              </span>",
      },
      processing: true,
      serverSide: true,
      ajax: {
        "url": firstDatatableUrl,
        "dataType": "json",
        "type": "GET",
        "data":{ _token: "{{csrf_token()}}"}
      },
      columns: [
        {data: 'brand'},
        {data: 'product'},
        {data: 'customer'},
        {data: 'qty'},
      ],
      order: [
        [0, 'asc']
      ],
      dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
      buttons: [
        {
          extend: 'excelHtml5',
          text: '<i class="fa fa-file-excel-o"></i>',
          titleAttr: 'Excel',
          title: 'Product-Order Report',
          footer: true,
        },
        {
          extend: 'pdfHtml5',
          orientation: 'landscape',
          pageSize: 'A4',
          text: '<i class="fa fa-file-pdf-o"></i>',
          titleAttr: 'PDF',
          title: 'Product-Order Report',
          footer: true,
        }
      ],
    });

    $('#btn-filter').on('click', function(e) {
        e.preventDefault();
        var brand = $('#brand').val();
        var product = $('#product').val();
        var start_date = $('#periode_from').val();
        var end_date = $('#periode_to').val();
        
        let newDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
          '&brand=' + brand + '&product=' + product;
        datatable.ajax.url(newDatatableUrl).load();
    });
  })
</script>
@endpush
