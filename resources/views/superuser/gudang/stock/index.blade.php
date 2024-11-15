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
          <th class="text-center">kode</th>
          <th class="text-center">Nama</th>
          <th class="text-center">Merek</th>
          <th class="text-center">Kemasan</th>
          <th class="text-center">In</th>
          <th class="text-center">Out</th>
          <th class="text-center">Stock</th>
          <th class="text-center">Sell Forecast</th>
          <th class="text-center">Effective</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th colspan="3"></th>
          <th class="text-right">Total :</th>
          <th class="text-center" id="total-in"></th>
          <th class="text-center" id="total-out"></th>
          <th class="text-center" id="total-stock"></th>
          <th class="text-center" id="total-sell"></th>
          <th colspan="1"></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.datatables-button')
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
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"},
      "dataSrc": function(json) {
        // Format the total stock with 2 decimal places
        let formattedTotalStock = parseFloat(json.total_stock || 0).toFixed(2);
        $('#total-stock').html(formattedTotalStock); // Set the formatted total stock in the footer
        return json.data;
      }
    },
    order: [
      [1, 'asc']
    ],
    pageLength: 10,
    lengthMenu: [
      [10, 25, 50, 100],
      [10, 25, 50, 100]
    ],
    "dom": '<"row"<"col-sm-2"l><"col-sm-7 text-left"B><"col-sm-3"f>> <"row"<"col-sm-12 col-md-12"p>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    buttons: [
      {
        extend: 'excelHtml5',
        text: '<i class="fa fa-file-excel-o"></i>',
        titleAttr: 'Excel',
        title: 'Report-Stock',
        footer: true, // This ensures that the footer is included in the export
        customize: function(xlsx) {
          let sheet = xlsx.xl.worksheets['sheet1.xml'];
          
          // Insert total values into the footer cells
          $('row c[r^="A"]', sheet).each(function() {
            if ($(this).text() === 'Total Stock') {
              let totalStockCell = $(this).attr('r').replace('A', 'B');
              $('row c[r="'+totalStockCell+'"]', sheet).text($('#total-stock').text());
            } else if ($(this).text() === 'Total In') {
              let totalInCell = $(this).attr('r').replace('A', 'B');
              $('row c[r="'+totalInCell+'"]', sheet).text($('#total-in').text());
            } else if ($(this).text() === 'Total Out') {
              let totalOutCell = $(this).attr('r').replace('A', 'B');
              $('row c[r="'+totalOutCell+'"]', sheet).text($('#total-out').text());
            } else if ($(this).text() === 'Total Sell') {
              let totalSellCell = $(this).attr('r').replace('A', 'B');
              $('row c[r="'+totalSellCell+'"]', sheet).text($('#total-sell').text());
            }
          });
        }
      }
    ],
    footerCallback: function(row, data, start, end, display) {
      // Use this callback to dynamically update the footer with totals
      let api = this.api();
      let totalStock = api.column(6).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
      let totalIn = api.column(4).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
      let totalOut = api.column(5).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
      let totalSell = api.column(7).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0);

      $(api.column(6).footer()).html(totalStock.toFixed(2));
      $(api.column(4).footer()).html(totalIn.toFixed(2));
      $(api.column(5).footer()).html(totalOut.toFixed(2));
      $(api.column(7).footer()).html(totalSell.toFixed(2));
    }
  });
});
</script>
@endpush
