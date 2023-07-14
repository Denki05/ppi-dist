@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <span class="breadcrumb-item active">Store</span>
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

<!-- <nav class="breadcrumb bg-white push">
  <a href="{{route('superuser.master.customer.create')}}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true" style="margin-left: 10px !important;">Create</a>
  <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>
</nav> -->

<div class="block">
  <div class="block-content">
    <div id="Wrapper1">
      <table id="Table1" class="table table-striped table-bordered display compact hover stripe dataTable no-footer" style="width:100%!important">
        <thead>
            <tr class="bir-selection-color1 text-center">
                <th>Expand</th>
                <th>Store</th>
                <th>Category</th>
                <th>Kota</th>
                <th>Provinsi</th>
                <th>Tempo</th>
                <th>Action</th>
            </tr>
        </thead>
      </table>
    </div>
    <div id="Wrapper2" style="display:none;">
        <table id="Table2" class="table table-striped table-bordered display compact hover stripe dataTable no-footer" style="padding-left: 15px!important">
            <thead>
                <tr class="bir-selection-color1 text-center">
                    <th>Id</th>
                    <th>HeaderId</th>
                    <th>ProductId</th>
                </tr>
            </thead>
        </table>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@section('modal')

@include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.master.customer.import_template'),
  'import_url' => route('superuser.master.customer.import'),
  'export_url' => route('superuser.master.customer.export')
])

@endsection

@push('scripts')
<script>
    // var dataSet1 = [{ "HeaderId": "1", "Group": "Group 1" }
    //                     ];

    // var dataSet2 = [{ "LineNumber": "1", "HeaderId": "1", "ProductId": "1011" },
    //                 { "LineNumber": "2", "HeaderId": "1", "ProductId": "1012" }
    //                 ];

    //dataTables default values
    var dataTablesdefaultPageLength = 10;
    var dataTablesPageLengthDisplay = [10, 50, 100, 250, 500];

    var dataTablesserverSide = false;
    var dataTablesautoWidth = true;
    var dataTablesresponsive = true;
    var dataTablesprocessing = true;        // for show progress bar 
    var dataTablesfilter = false;            // this is for disable filter (search box)
    var dataTablesorderMulti = false;       // for disable multiple column at once  

    var dataTablesscrollX = false;

    $(document).ready(function () {

        // Get the Detail Table as Template for Every Row
        detailsTableHtml = $("#Table2").html();

        var table1 = $("#Table1").DataTable({
            async: true,
                serverSide: false, // for process server side
                deferRender: true, // deferred rendering
                scrollX: dataTablesscrollX,
                deferLoading: 0, //Disable Initial Load
                processing: dataTablesprocessing, // for show progress bar
                filter: dataTablesfilter,
                orderMulti: true,
                autoWidth: dataTablesautoWidth,
                responsive: dataTablesresponsive,
                pageLength: dataTablesdefaultPageLength,
                lengthMenu: [dataTablesPageLengthDisplay, dataTablesPageLengthDisplay],
                ajax : {
                    "url": '{{route('superuser.master.customer.json')}}',
                    "dataType": "json",
                    "type": "GET",
                    "data":{ _token: "{{csrf_token()}}"}
                },
                order: [[1, 'asc']],
                columnDefs: [],
                columns: [
                {
                    data: null, name: "Group", title: "", className: 'details-control', orderable: false, width: 5,
                    render: function (data, type, row, meta) {
                        return '<i class="fa fa-plus"></i>';
                    }
                },
                { data: "store_name", name: "master_customer.name", title: "Store" },
                { data: "category_name", name: "master_customer_categories.name", title: "Category", orderable: false },
                { data: "text_kota", name: "master_customer.text_kota", title: "Kota", orderable: false },
                { data: "text_provinsi", name: "master_customer.text_provinsi", title: "Profinsi", orderable: false },
                { data: "tempo_limit", name: "master_customer.tempo_limit", title: "Tempo", orderable: false },
                { data: "action", title: "Action", orderable: false },
            ]
            , rowId: 'customer_id'
        });

        $('#Table1 tbody').on('click', 'td i', function () {

            var tr = $(this).closest('tr');
            var row = table1.row(tr);

            if (row.child.isShown()) {
                // This row is already open - close it
                destroyChild(row);
                tr.removeClass('shown');
            }
            else {
                // Open this row
                createChild(row);
                tr.addClass('shown');
            }

        });
    });

    function createChild(row) {
        // This is the table we'll convert into a DataTable
        var table2 = $('<table class="display" width="100%"/>');

        // Display it the child row
        row.child(table2).show();

        // Initialise as a DataTable
        var usersTable = table2.DataTable({
            async: true,
          serverSide: false, // for process server side
          scrollX: false, //Enable Horizonal Scroll
          deferRender: true, // deferred rendering
          processing: dataTablesprocessing, // for show progress bar
          filter: false,
          orderMulti: true,
          info: false,
          paging: false,
          select: false,
          autoWidth: dataTablesautoWidth,
          responsive: dataTablesresponsive,
          ajax : {
                    "url": '{{route('superuser.master.customer.json')}}',
                    "dataType": "json",
                    "type": "GET",
                    "data":{ _token: "{{csrf_token()}}"}
          },
            order: [[0, 'asc']],
            columnDefs: [],
            columns: [
                { data: "member_name", name: "master_customer_other_addresses.name", title: "Member", autoWidth: true },
                // { data: "HeaderId", name: "HeaderId", title: "Header ID", autoWidth: true },
                // { data: "ProductId", name: "ProductId", title: "Product ID", autoWidth: true }
            ]
        });
    }

    function destroyChild(row) {
        var table2 = $("table", row.child());
        table2.detach();
        table2.DataTable().destroy();

        // And then hide the row
        row.child.hide();
    }
</script>
@endpush