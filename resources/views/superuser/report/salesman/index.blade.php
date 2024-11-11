@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item active">Sales</span>
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
              <label class="col-md-2 col-form-label text-left" for="period">Period :</label>
              <div class="col-md-4">
                <div class="input-group">
                  <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"
                        aria-hidden="true"></i></span></div><input type="text" class="form-control pull-right"
                    id="datesearch" name="datesearch" placeholder="Select period"
                    value="{{ \Carbon\Carbon::now()->format('d/m/Y') }} - {{ \Carbon\Carbon::now()->format('d/m/Y') }}">
                </div>
              </div>
              <label class="col-md-2 col-form-label text-left" for="sales">Sales :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="salesman" name="salesman" data-placeholder="Select Marketplace">
                  <option value="all">All</option>
                  @foreach(\App\Entities\Penjualan\SalesOrder::SALES_REPORT AS $row => $key)
                  <option value="{{ $key }}">{{ $row }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <!-- <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="status">Status :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="status" name="status" data-placeholder="Select Status">
                  <option value="all">All</option>
                  <option value="paid">Paid</option>
                  <option value="debt">Unpaid</option>
                </select>
              </div>
            </div> -->
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
  </form>

  <div class="block">
    <div class="block-content block-content-full">
      <table class="datatable table" id="datatable">
        <thead class="thead-dark">
          <tr>
            <th>#</th>
            <th>Salesman</th>
            <th>Invoice</th>
            <th>Customer</th>
            <th>qty</th>
            <th>omset</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot style="background-color: #E7E7E7;">
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th style="text-align: right;">TOTAL :</th>
                <th id="totalQty" style="text-align: center; color: green;"></th>
                <th id="totalOmset" style="text-align: center; color: blue;"></th>
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

@push('scripts')
<script type="text/javascript">
  var start_date = {{ \Carbon\Carbon::now()->format('Y-m-d') }};
  var end_date = {{ \Carbon\Carbon::now()->format('Y-m-d') }};
  var print_date = "SR-{{ \Carbon\Carbon::now()->format('dmy') }}-{{ \Carbon\Carbon::now()->format('dmy') }}";

  $(document).ready(function() {
    $('.js-select2').select2()
    $('#datesearch').daterangepicker({
      autoUpdateInput: false
    });
    $('#datesearch').data('daterangepicker').setStartDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');
    $('#datesearch').data('daterangepicker').setEndDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');
    $('#datesearch').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
      start_date = picker.startDate.format('YYYY-MM-DD');
      end_date = picker.endDate.format('YYYY-MM-DD');
      print_date = "SR-"+picker.startDate.format('DDMMYY')+"-"+picker.endDate.format('DDMMYY');
    });

    let datatableUrl = '{{ route('superuser.report.salesman.json') }}';
    let firstDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
      '&marketplace=all';

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
          {data: 'DT_RowIndex', name: 'id'},
          {data: 'salesman'},
          {data: 'invoice_code'},
          {data: 'combined_column'},
          {data: 'total_qty'},
          {
            data: 'total_omset',
            render: $.fn.dataTable.render.number('.', ',', 2),
            searchable: false
          }
        ],
        order: [
          [2, 'asc']
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
            title: 'Salesman-Report',
            footer: true,
          },
          {
            extend: 'pdfHtml5',
            orientation: 'portrait',
            pageSize: 'A4',
            text: '<i class="fa fa-file-pdf-o"></i>',
            titleAttr: 'PDF',
            title: 'Salesman-Report',
            footer: true,
          }
        ],
        footerCallback: function (row, data, start, end, display) {
          let api = this.api();
  
          // Remove the formatting to get integer data for summation
          let intVal = function (i) {
              return typeof i === 'string'
                  ? i.replace(/[\$,]/g, '') * 1
                  : typeof i === 'number'
                  ? i
                  : 0;
          };
  
          // Total over all pages
          totalQty = api
              .column(4)
              .data()
              .reduce((a, b) => intVal(a) + intVal(b), 0);
  
          // Total over this page
          pageTotalQty = api
              .column(4, { page: 'current' })
              .data()
              .reduce((a, b) => intVal(a) + intVal(b), 0);

          totalOmset = api
              .column(5)
              .data()
              .reduce((a, b) => intVal(a) + intVal(b), 0);
  
          // Total over this page
          pageTotalOmset = api
              .column(5, { page: 'current' })
              .data()
              .reduce((a, b) => intVal(a) + intVal(b), 0);
  
          // Update footer
          api.column(4).footer().innerHTML =
              // '$' + pageTotalQty + ' ( $' + totalQty + ' total)';
              toCommas(totalQty) + ' KG'

          api.column(5).footer().innerHTML =
              // '$' + pageTotalOmset + ' ( $' + totalOmset + ' total)';
              'Rp. ' + toCommas(totalOmset)
        }
      });

      $('#btn-filter').on('click', function(e) {
        e.preventDefault();
        var salesman = $('#salesman').val();
        let newDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
          '&salesman=' + salesman;
        datatable.ajax.url(newDatatableUrl).load();
      });

      function toCommas(value) {
        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      }
  })
</script>
@endpush
