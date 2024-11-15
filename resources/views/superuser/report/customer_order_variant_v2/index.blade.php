@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item">Operasional</span>
  <span class="breadcrumb-item active">Customer - Produk</span>
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

<form action="{{ route('superuser.report.customer_order_variant_v2.print_report') }}" method="POST">
  @csrf
  <div class="row">
    <div class="col-10">
      <div class="block">
        <div class="block-content">
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-left" for="customer[]">Customer:</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="customer" name="customer[]" data-placeholder="Select Customer" multiple required>
                <option value="all">All</option>
                @foreach($customer as $row)
                  <option value="{{ $row->id }}">{{ $row->name }} {{ $row->text_kota }}</option>
                @endforeach
              </select>
            </div>

           
          </div>

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-left" for="brand_name">Brand:</label>
            <div class="col-md-4">
              <select class="js-select2 form-control js-select2-brand" id="brand_name" name="brand_name[]" data-placeholder="Select Brand/Merek" multiple>
                <option value="all">All</option>
                @foreach($brand as $row)
                  <option value="{{ $row->brand_name }}">{{ $row->brand_name }}</option>
                @endforeach
              </select>
            </div>

            <label class="col-md-2 col-form-label text-left" for="product">Product:</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="product" name="product[]" data-placeholder="Select Product" multiple>
                <option value="all">All</option>
                @foreach($product as $row)
                  <option value="{{ $row->id }}">{{ $row->code }} - {{ $row->name }} / {{ $row->packaging->pack_name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-left" for="start">Periode From:</label>
            <div class="col-md-4">
              <input type="date" class="form-control" id="start_date" name="start" required>
            </div>

            <label class="col-md-2 col-form-label text-left" for="end">Periode To:</label>
            <div class="col-md-4">
              <input type="date" class="form-control" id="end_date" name="end" required>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-2">
      <div class="block">
        <div class="block-content">
          <div class="form-group row">
            <div class="col-md-12 text-center">
              <button type="submit" class="btn bg-gd-corporate border-0 text-white" aria-label="Print Report" id="submit-btn">
                Download <i class="fa fa-print ml-10"></i>
              </button>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-12 text-center">
              <a href="#" id="btn-filter" class="btn bg-gd-corporate border-0 text-white" aria-label="Preview Report">
                Preview <i class="fa fa-search ml-10"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="block">
      <div class="block-content block-content-full">
        <table class="datatable table" id="datatable">
          <thead class="thead-dark">
            <tr>
              <th>Customer</th>
              <th>Brand</th>
              <th>Month</th>
              <th>Variant</th>
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
  var start_date = $('#start_date').val();
  var end_date = $('#end_date').val();

  $(document).ready(function() {
      $('.js-select2').select2();

      let datatableUrl = '{{ route('superuser.report.customer_order_variant.json') }}';
      let firstDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
      '&customer=all&brand=all';

      var datatable = $('#datatable').DataTable({
        language: {
              processing: "<span class='fa-stack fa-lg'>\n\
                                    <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                              </span>",
        },
        processing: true,
        serverSide: false,
        ajax: {
          "url": firstDatatableUrl,
          "dataType": "json",
          "type": "GET",
          "data":{ _token: "{{csrf_token()}}"}
        },
        columns: [
          {data: 'combined_column'},
          {data: 'invoice_brand', name: 'penjualan_so.brand_name'},
          {data: 'invoice_month'},
          {data: 'combined_column2'},
          {data: 'invoice_qty'},
        ],
        order: [
          [0, 'asc']
        ],
        pageLength: 10,
        lengthMenu: [
          [10, 30, 100, -1],
          [10, 30, 100, 'All']
        ], 
        dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="fa fa-file-excel-o"></i>',
            titleAttr: 'Excel',
            title: 'Customer-Order Variant',
            footer: true,
          },
          {
            extend: 'pdfHtml5',
            orientation: 'landscape',
            pageSize: 'A4',
            text: '<i class="fa fa-file-pdf-o"></i>',
            titleAttr: 'PDF',
            title: 'Customer-Order Variant',
            footer: true,
          }
        ],
      });

      $('#btn-filter').on('click', function(e) {
        e.preventDefault();
        var customer = $('#customer').val();
        var brand = $('#brand_name').val();
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        
        let newDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
          '&customer=' + customer + '&brand_name=' + brand;
        datatable.ajax.url(newDatatableUrl).load();
      });

      function handleClick(cb) {
        cb.value = cb.checked ? 0 : 1;
        console.log(cb.value);
      }

      function toCommas(value) {
        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      }
  })
</script>
@endpush