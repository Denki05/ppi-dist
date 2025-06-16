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

<div class="block">
  
  <div class="block-content block-content-full">
    <div class="form-group row align-items-center">
      <label class="col-md-2 col-form-label" for="warehouse">Warehouse <span class="text-danger">*</span></label>
      <div class="col-md-4">
          <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Warehouse">
              <option></option>
              @foreach($warehouses as $warehouse)
              <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
              @endforeach
          </select>
      </div>
    </div>
</div>
  </div>
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
        </tr>
      </thead>
      
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.datatables-button')
@include('superuser.asset.plugin.select2')

@section('modal')

@include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.gudang.stock.import_template'),
  'import_url' => route('superuser.gudang.stock.import'),
  'export_url' => route('superuser.gudang.stock.export_stock_db')
])

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  $('.js-select2').select2()
  let datatableUrl = '{{ route('superuser.gudang.stock.json') }}';

  $('#warehouse').on('select2:select', function (e) {
    var data = e.params.data;

    // alert(data.id);
    
    let newDatatableUrl = datatableUrl+'?warehouse_id='+data.id;
    $('#datatable').DataTable().ajax.url(newDatatableUrl).load();
      
  });

  $('#datatable').DataTable({
    processing: true,
    ajax: {
      url: datatableUrl,
      type: 'GET',
      data:{ _token: "{{csrf_token()}}" },
      dataSrc: function (json) {
        $('#total-stock').html(json.total_stock);
        $('#total-in').html(json.total_in);
        $('#total-out').html(json.total_out);
        return json.data;
      }
    },
    columns: [
      { data: 0 },
      { data: 1 },
      { data: 2 },
      { data: 3 },
      { data: 4, className:'text-center' },
      { data: 5, className:'text-center' },
      { data: 6, className:'text-center' }
    ],
    order: [[1,'asc']],
    pageLength: 10,
    dom: '<"row"<"col-sm-2"l><"col-sm-7"B><"col-sm-3"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>',
    buttons: [{
        extend: 'excelHtml5',
        text: '<i class="fa fa-file-excel-o"></i>',
        titleAttr: 'Excel',
        title: 'Report-Stock',
        footer: true
    }],
    footerCallback: function (row, data) {
      let api = this.api();
      let totalIn  = api.column(4).data().reduce((a,b)=>(+a)+(+b.replace(/,/g,'')),0);
      let totalOut = api.column(5).data().reduce((a,b)=>(+a)+(+b.replace(/,/g,'')),0);
      let totalStk = api.column(6).data().reduce((a,b)=>(+a)+(+b.replace(/,/g,'')),0);

      $(api.column(4).footer()).html(totalIn.toFixed(2));
      $(api.column(5).footer()).html(totalOut.toFixed(2));
      $(api.column(6).footer()).html(totalStk.toFixed(2));
    }
  });

  $('#warehouse').on('select2:select', function(e) {
    var warehouseId = e.params.data.id;

    if (warehouseId) {
      $('#export-container').show();

      // Update export link saat warehouse dipilih
      updateExportLink(warehouseId);
    } else {
      $('#export-container').hide();
    }
  });

  // Event listener untuk perubahan date range
  $('#start_date, #end_date').on('change', function() {
      var warehouseId = $('#warehouse').val();
      if (warehouseId) {
        updateExportLink(warehouseId);
      }
  });

  function updateExportLink(warehouseId) {
    var startDate = $('#start_date').val();
    var endDate = $('#end_date').val();

    if (startDate && endDate && startDate > endDate) {
        alert('Start Date tidak boleh lebih besar dari End Date!');
        return;
    }

    // Pastikan warehouseId ada dan bukan null atau kosong
    if (!warehouseId) {
        alert('Warehouse belum dipilih!');
        return;
    }

    // Gantikan :warehouse, :startDate, :endDate dengan nilai yang sesuai
    var url = '{{ route("superuser.gudang.stock.exportTransactions", [":warehouse", ":startDate", ":endDate"]) }}';
    url = url.replace(':warehouse', warehouseId);
    url = url.replace(':startDate', startDate);
    url = url.replace(':endDate', endDate);

    $('#export-link').attr('href', url);
  }

    document.getElementById('backfillBalancesButton').addEventListener('click', function () {
        if (confirm('Are you sure you want to recalculate month-end balances?')) {
            fetch('{{ route("superuser.gudang.stock.backfillMonthEndBalances") }}', {
                method: 'GET',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                } else {
                    alert('Failed to initiate month-end balance calculation.');
                }
            })
            .catch(error => {
                alert('An error occurred: ' + error.message);
            });
        }
    });
});
</script>
@endpush
