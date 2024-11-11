@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item active">Omset Penjualan</span>
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
                  View <i class="fa fa-search ml-10"></i>
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
                  <th>Date</th>
                  <th>SO</th>
                  <th>Invoice</th>
                  <th>Customer</th>
                  <th>Cash</th>
                  <th>Tempo</th>
              </tr>
          </thead>
          <tbody>
          </tbody>
          <tfoot>
              <tr>
                  <th colspan="5" style="text-align:right"></th>
                  <th id="totalInvoiceCash" style="text-align: center;"></th>
                  <th id="totalInvoiceTempo" style="text-align: center;"></th>
              </tr>
              <tr>
                  <th colspan="6" style="text-align:right"></th>
                  <th id="subTotal" style="text-align: center;"></th>
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

    let datatableUrl = '{{ route('superuser.report.sales.json') }}';
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
          {data: 'DT_RowIndex', name: 'id'},
          {data: 'so_date'},
          {data: 'so_code'},
          {data: 'invoice_code'},
          {data: 'combined_column'},
          {data: 'invoice_cash'},
          {data: 'invoice_tempo'},
        ],
        order: [
            [2, 'asc'] // Sorting by combined column
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
            title: 'Report - Omset Penjualan',
            footer: false,
            exportOptions: {
              modifier: {
                page: 'all'
              },
              columns: ':visible'
            },
            customize: function (xlsx) {
              // Access the sheet object
              var sheet = xlsx.xl.worksheets['sheet1.xml'];

              // Add totals to the sheet footer for Excel
              var totalCash = $('#totalInvoiceCash').text();
              var totalTempo = $('#totalInvoiceTempo').text();
              var subTotal = $('#subTotal').text();

              // Define where to place the total row (adjusting the row number as needed)
              var rowNum = $('row', sheet).length + 2;

              // Add Total Cash, Total Tempo, and SubTotal in the Excel sheet
              $('sheetData', sheet).append(
                `<row r="${rowNum}">
                  <c t="inlineStr" r="A${rowNum}" s="2">
                    <is><t></t></is>
                  </c>
                  <c t="inlineStr" r="F${rowNum}" s="2">
                    <is><t>${totalCash}</t></is>
                  </c>
                  <c t="inlineStr" r="G${rowNum}" s="2">
                    <is><t>${totalTempo}</t></is>
                  </c>
                </row>
                <row r="${rowNum + 1}">
                  <c t="inlineStr" r="A${rowNum + 1}" s="2">
                    <is><t></t></is>
                  </c>
                  <c t="inlineStr" r="G${rowNum + 1}" s="2">
                    <is><t>${subTotal}</t></is>
                  </c>
                </row>`
              );
            }
          },
          {
            extend: 'pdfHtml5',
            text: '<i class="fa fa-file-pdf-o"></i>',
            titleAttr: 'PDF',
            title: 'Report - Omset Penjualan',
            orientation: 'landscape', // Set landscape orientation
            footer: false,
            exportOptions: {
              modifier: {
                page: 'all'
              },
              columns: ':visible'
            },
            customize: function (doc) {
              var totalCash = $('#totalInvoiceCash').text();
              var totalTempo = $('#totalInvoiceTempo').text();
              var subTotal = $('#subTotal').text();

              // Add totals to the PDF footer
              doc.content[1].table.body.push(
                [
                  { text: '', alignment: 'right', colSpan: 5 }, {}, {}, {}, {},
                  { text: totalCash, alignment: 'center', bold: true },
                  { text: totalTempo, alignment: 'center', bold: true  }
                ],
                [
                  { text: '', alignment: 'right', colSpan: 6 }, {}, {}, {}, {}, {},
                  { text: subTotal, alignment: 'center', bold: true  }
                ]
              );
            }
          }
        ],
        footerCallback: function (row, data, start, end, display) {
          var api = this.api();
          
          // Helper function to remove formatting and return a float value
          var intVal = function (i) {
              return typeof i === 'string' ? i.replace(/[\Rp.,]/g, '') * 1 : typeof i === 'number' ? i : 0;
          };

          // Calculate total for `invoice_cash` column (index 3)
          var totalInvoiceCash = api
              .column(5)
              .data()
              .reduce(function (a, b) {
                  return intVal(a) + intVal(b);
              }, 0);

          // Calculate total for `invoice_tempo` column (index 4)
          var totalInvoiceTempo = api
              .column(6)
              .data()
              .reduce(function (a, b) {
                  return intVal(a) + intVal(b);
              }, 0);

          // Calculate subtotal (you can define how you want to calculate this)
          var subTotal = totalInvoiceCash + totalInvoiceTempo;

          // Format totals using toLocaleString
          var formattedTotalCash = totalInvoiceCash.toLocaleString('id-ID', {
              style: 'currency',
              currency: 'IDR',
              minimumFractionDigits: 0,
              maximumFractionDigits: 0
          });

          var formattedTotalTempo = totalInvoiceTempo.toLocaleString('id-ID', {
              style: 'currency',
              currency: 'IDR',
              minimumFractionDigits: 0,
              maximumFractionDigits: 0
          });

          var formattedSubTotal = subTotal.toLocaleString('id-ID', {
              style: 'currency',
              currency: 'IDR',
              minimumFractionDigits: 0,
              maximumFractionDigits: 0
          });
          
          $('#totalInvoiceCash').html('Cash: ' + formattedTotalCash); // Update subtotal in the footer
          $('#totalInvoiceTempo').html('Tempo: ' + formattedTotalTempo); // Update subtotal in the footer
          $('#subTotal').html('Subtotal: ' + formattedSubTotal); // Update subtotal in the footer
        }
      });

      $('#btn-filter').on('click', function(e) {
        e.preventDefault();
        var customer = $('#customer').val();
        let periode_from = $("#periode_from").val();
        let periode_to = $("#periode_to").val();
        
        let newDatatableUrl = datatableUrl + '?start_date=' + periode_from + '&end_date=' + periode_to +
          '&customer=' + customer;
        datatable.ajax.url(newDatatableUrl).load();
      });

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
