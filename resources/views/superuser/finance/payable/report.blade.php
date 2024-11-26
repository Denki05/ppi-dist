@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Laporan</span>
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item active">Laporan Pembayaran</span>
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
    <div class="form-group row">
      <div class="col-md-9">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="period">Period From :</label>
              <div class="col-md-4">
                <div class="input-group">
                  <input type="date" class="form-control form-control" name="periode_from" id="periode_from" value="{{ date('Y-m-01') }}">
                </div>
              </div>
              <label class="col-md-2 col-form-label text-left" for="product">Period To :</label>
              <div class="col-md-4">
                <div class="input-group">
                  <input type="date" class="form-control form-control" name="periode_to" id="periode_to" value="{{ date('Y-m-d') }}">
                </div>
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
                <a href="#" id="btn-filter" class="btn bg-gd-sea border-0 text-white pl-50 pr-50">
                  Filter <i class="fa fa-search ml-10"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="block">
        <div class="block-content block-content-full">
            <div class="row mb-30">
              <div class="col-12">
                <table class="table table-striped" id="datatables">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Pay Date</th>
                      <th>Code</th>
                      <th>Amount Pay</th>
                      <th>Invoice</th>
                      <th>Customer</th>
                      <th>Amount Invoice</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
        </div>
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
    $(document).ready(function() {
        $('.js-select2').select2();

        let startDate = $('#periode_from').val();
        let endDate = $('#periode_to').val();

        let datatableUrl = '{{ route('superuser.finance.payable.json2') }}';
        let firstDatatableUrl = datatableUrl + '?startDate=' + startDate + '&endDate=' + endDate;

        let datatable = $('#datatables').DataTable({
    "language": {
        "processing": "<span class='fa-stack fa-lg'>\n\
                        <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                    </span>",
    },
    processing: true,
    serverSide: false,
    ajax: {
        "url": firstDatatableUrl,
        "dataType": "json",
        "type": "GET",
        "data": {
            _token: "{{ csrf_token() }}"
        }
    },
    columns: [
        {data: 'DT_RowIndex', name: 'id'},
        {
            data: 'payable_date',
            render: {
                _: 'display',
                sort: 'timestamp'
            }
        },
        { data: 'payable_code' },
        { 
            data: 'payable_total',
            render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
            searchable: false
        },
        { data: 'invoice_code' },
        { data: 'account_customer' },
        { 
            data: 'invoice_total',
            render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
            searchable: false
        },
    ],
    order: [
        [1, 'asc']
    ],
    lengthMenu: [
        [10, 20, 50],
        [10, 20, 50]
    ],
    dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    buttons: [
        {
            extend: 'excel',
            text: '<i class="fa fa-file-excel-o"></i>',
            title: 'Laporan Pembayaran',
            exportOptions: {
                modifier: {
                    page: 'all' // Export all data, not just the visible page
                }
            }
        },
        {
            extend: 'pdf',
            text: '<i class="fa fa-file-pdf-o"></i>',
            orientation: 'landscape',  // Set orientation to landscape
            title: 'Laporan Pembayaran',
            exportOptions: {
                modifier: {
                    page: 'all' // Export all data, not just the visible page
                }
            },
            customize: function(doc) {
                // Adjust table layout for landscape orientation
                doc.content[1].table.widths = [
                    '5%',   // Set the first column width to 5%
                    '*',    // Automatically adjust next columns based on content
                    '*', 
                    '*',
                    '*',
                    '*',
                    '*'
                ];

                doc.pageMargins = [20, 20, 20, 20]; // Set margins [left, top, right, bottom]

                // Set table header styling: black background, white text
                doc.styles.tableHeader = {
                    bold: true,
                    fontSize: 12,
                    color: 'white',  // Text color: white
                    fillColor: 'black',  // Background color: black
                    alignment: 'center' // Center-align headers
                };

                // Center-align all table body rows
                var tableBody = doc.content[1].table.body;
                for (var i = 1; i < tableBody.length; i++) { // Skip the header row (index 0)
                    for (var j = 0; j < tableBody[i].length; j++) {
                        tableBody[i][j].alignment = 'center'; // Apply center alignment to data
                    }
                }

                // Optional: Adjust the font size for the content
                doc.styles.tableBody = {
                    fontSize: 10,
                    alignment: 'center' // Center-align body rows
                };

                // Optional: Modify table header row style further
                doc.content[1].table.headerRows = 1; // Make the first row the header
            }
        }
    ],
    columnDefs: [
        {
            targets: '_all', // Apply to all columns
            className: 'text-center' // Center-align text for table body
        }
    ]
});


        $('#btn-filter').on('click', function(e) {
          e.preventDefault();

          // Update startDate and endDate values
          startDate = $('#periode_from').val();
          endDate = $('#periode_to').val();

          // Update the DataTable URL with new filter values
          let newDatatableUrl = datatableUrl + '?startDate=' + startDate + '&endDate=' + endDate;

          // Reload the DataTable with the new URL
          datatable.ajax.url(newDatatableUrl).load();
        });

        // $('#btn-print').on('click', function(e){
        //     e.preventDefault();

        //     var startDate = $('#periode_from').val();
        //     var endDate = $('#periode_to').val();
        //     var customer = $('#customer').val();
        //     var status = $('#status_payment').val();

        //     $.ajax({
        //         type:'POST',
        //         url:"{{ route('superuser.finance.invoicing.printReportPage') }}",
        //         data:{"_token": "{{ csrf_token() }}", "start":startDate, "end":endDate, "customer":customer, "status":status},
        //         success:function(response){
        //             // console.log(response);
        //         }
        //     });
        // })
    })
</script>
@endpush