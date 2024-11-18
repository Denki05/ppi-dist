@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Laporan</span>
  <span class="breadcrumb-item">Accounting</span>
  <span class="breadcrumb-item active">Laporan Pendapatan</span>
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
<form id="form" target="_blank" action="{{ route('superuser.report.sales.export') }}"
    enctype="multipart/form-data" method="POST">
    @csrf
    <input type="hidden" name="download_type" id="download_type" value="">
    <div class="form-group row">
      <div class="col-md-9">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="period">Period From :</label>
              <div class="col-md-4">
                <div class="input-group">
                  <input type="date" class="form-control form-control" name="start_date" id="periode_from">
                </div>
              </div>
              <label class="col-md-2 col-form-label text-left" for="product">Period To :</label>
              <div class="col-md-4">
                <div class="input-group">
                  <input type="date" class="form-control form-control" name="end_date" id="periode_to">
                </div>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="customer">Customer :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="customer" name="customer[]" data-placeholder="Select Customer" multiple="multiple">
                  <option value="all">All</option>
                  @foreach ($customer as $value)
                    <option value="{{ $value->id }}">{{ $value->name }} {{ $value->text_kota }}</option>
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

            {{--<div class="form-group row">
              <div class="col-md-12 text-center">
                <a href="#" id="btn-print" class="btn bg-gd-sea border-0 text-white pl-50 pr-50">
                  Print <i class="fa fa-print ml-10"></i>
                </a>
              </div>
            </div>--}}
          </div>
        </div>
      </div>
    </div>
  </form>

  <div class="block">
    <div class="block-content block-content-full">
      <table class="datatable table table-striped" id="datatable">
          <thead>
              <tr>
                  <th>#</th>
                  <th>Customer</th>
                  <th>Total</th>
              </tr>
          </thead>
          <tbody>
          </tbody>
          <tfoot>
              <tr>
                  <th colspan="2" style="text-align:right"></th>
                  <th id="totalInvoiceCash" style="text-align: center;"></th>
              </tr>
          </tfoot>
      </table>
    </div>
  </div>
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
  var print_date = "SR-{{ \Carbon\Carbon::now()->format('dmy') }}-{{ \Carbon\Carbon::now()->format('dmy') }}";

  $(document).ready(function() {
    $('.js-select2').select2()

    let datatableUrl = '{{ route('superuser.report.revenue.json') }}';
    let firstDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
      '&marketplace=all';

    var datatable = $('.datatable').DataTable({
      language: {
          processing: "<span class='fa-stack fa-lg'>\n\
                            <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                      </span>",
      },
      processing: true,
      serverSide: false,
      ajax: {
          "url": firstDatatableUrl, // Set this to your actual URL
          "dataType": "json",
          "type": "GET",
          "data": { _token: "{{csrf_token()}}" } // Token if needed for Laravel CSRF
      },
      columns: [
          {
              "class": "details-control",
              "orderable": false,
              "data": null,
              "defaultContent": "<i class='fa fa-plus'></i>",
              searchable: false
          },
          {data: 'combined_column'}, // Assuming your server returns this field
          {data: 'total_purchase'},
          {
              data: 'detail', // The detail data will be hidden but used for expanding rows
              "visible": false,
              searchable: false
          },
      ],
      order: [
          [1, 'asc'] // Sorting by combined column
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
          title: 'Report - Laporan Pendapatan',
          footer: true,
          exportOptions: {
              columns: [1, 2], // Specify the column indices to include (exclude index 0)
              format: {
                  body: function (data, row, column, node) {
                      if (column === 3) { // Detail column
                          return data;
                      }
                      return data;
                  }
              }
          },
          customize: function (xlsx) {
              var sheet = xlsx.xl.worksheets['sheet1.xml'];
              var totalRow = '<row>';
              totalRow += '<c r="A' + (datatable.data().count() + 2) + '" t="s"><v>Total</v></c>';
              totalRow += '<c r="B' + (datatable.data().count() + 2) + '" t="n"><v>' + total + '</v></c>';
              totalRow += '</row>';
              $(sheet).find('row:last').after(totalRow); // Insert the total row
          }
        },
        {
          extend: 'pdfHtml5',
          orientation: 'landscape', // Set to landscape
          pageSize: 'A4',
          text: '<i class="fa fa-file-pdf-o"></i>',
          titleAttr: 'PDF',
          title: 'Report - Laporan Pendapatan',
          footer: true,
          exportOptions: {
              columns: [1, 2], // Specify the column indices to include (exclude index 0)
              format: {
                  body: function (data, row, column) {
                      // Process the data before exporting
                      if (column === 2) { // Example: Assume column 2 is numeric
                          return data.replace(/[Rp.,]/g, ''); // Strip formatting
                      }
                      return data; // Return unaltered for other columns
                  }
              }
          },
          customize: function (doc) {
              // Adjust table layout for landscape orientation
              doc.content[1].table.widths = Array(doc.content[1].table.body[0].length).fill('*'); // Auto-fit columns
              doc.pageMargins = [20, 20, 20, 20]; // Set margins [left, top, right, bottom]

              // Set table header styling
              doc.styles.tableHeader = {
                  bold: true,
                  fontSize: 12,
                  color: 'black',
                  alignment: 'center' // Center-align headers
              };

              // Center-align all table body rows
              var tableBody = doc.content[1].table.body;
              for (var i = 1; i < tableBody.length; i++) { // Skip the header row (index 0)
                  for (var j = 0; j < tableBody[i].length; j++) {
                      tableBody[i][j].alignment = 'center'; // Apply center alignment
                  }
              }
          }
        }
      ],
      footerCallback: function (row, data, start, end, display) {
        var api = this.api();

        // Helper function to remove formatting and return a float value
        var intVal = function (i) {
            return typeof i === 'string' ? i.replace(/[\Rp.,]/g, '') * 1 : typeof i === 'number' ? i : 0;
        };

        // Calculate total for `total_purchase` column (index 2)
        var totalInvoiceCash = api
            .column(2)
            .data()
            .reduce(function (a, b) {
                return intVal(a) + intVal(b);
            }, 0);

        // Format totals using toLocaleString
        var formattedTotalCash = totalInvoiceCash.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });

        // Update the footer
        $(api.column(2).footer()).html('Total : ' + formattedTotalCash);

        // Assign total value for export buttons
        total = totalInvoiceCash;
      }
    });


      function format(data) {
            // Customize this HTML to display the desired detail view
            return '<table class="table table-dark" style="margin-top: -5px !important;margin-bottom: 0px;">' +
                   '<tr>' +
                       '<td>' + data.detail + '</td>' +
                   '</tr>' +
                   '</table>';
        }

        // Add event listener for opening and closing details
        $('.datatable tbody').on('click', 'td.details-control', function() {
            var tr = $(this).closest('tr');
            var row = datatable.row(tr);
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                row.child(format(row.data())).show();
                tr.addClass('shown');
            }
        });

      $('#btn-filter').on('click', function(e) {
        e.preventDefault();
        var customer = $('#customer').val();
        let periode_from = $("#periode_from").val();
        let periode_to = $("#periode_to").val();
        // alert(periode_from);
        let newDatatableUrl = datatableUrl + '?start_date=' + periode_from + '&end_date=' + periode_to +
          '&customer=' + customer;
        datatable.ajax.url(newDatatableUrl).load();
      });

      // $('.datatable tbody').on('click', 'td.details-control', function() {
      //   var tr = $(this).closest('tr');
      //   var row = datatable.row(tr);
      //   if (row.child.isShown()) {
      //     // This row is already open - close it
      //     row.child.hide();
      //     tr.removeClass('shown');
      //   } else {
      //     // Open this row
      //     row.child(format(row.data())).show();
      //     tr.addClass('shown');
      //   }
      // });

      function toCommas(value) {
        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      }

      $('#btn-print').on('click', function(e) {
        e.preventDefault();

        let start_date = $('#periode_from').val();
        let end_date = $('#periode_to').val();
        let customer = $('#customer').val();

        $.ajax({
          type: 'POST',
          url:"{{ route('superuser.report.sales.print_report') }}",
          data: {
            "_token": "{{ csrf_token() }}", 
            "start": start_date, 
            "end": end_date,
            "customer": customer
          },
          success: function(response){
            
          }
        });
      })
  })
</script>
@endpush