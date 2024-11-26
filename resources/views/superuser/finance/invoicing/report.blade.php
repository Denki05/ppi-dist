@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Laporan</span>
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item active">Piutang Faktur</span>
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
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="customer">Customer :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="customer" name="customer" data-placeholder="Select Customer" multiple="multiple">
                  <option value="all">All</option>
                  @foreach ($customer as $value)
                    <option value="{{ $value->id }}">{{ $value->name }} {{ $value->text_kota }}</option>
                  @endforeach
                </select>
              </div>

              <!-- <label class="col-md-2 col-form-label text-left" for="status_payment">Status :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="status_payment" name="status_payment" data-placeholder="Select Status">
                  <option value="all">All</option>
                  <option value="UNPAID">UNPAID</option>
                  <option value="PAID">PAID</option>
                </select>
              </div> -->
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

            <!-- <div class="form-group row">
              <div class="col-md-12 text-center">
                <a href="#" id="btn-print" class="btn bg-gd-sea border-0 text-white pl-50 pr-50">
                  Print <i class="fa fa-print ml-10"></i>
                </a>
              </div>
            </div> -->
          </div>
        </div>
      </div>

      <div class="block">
        <div class="block-content block-content-full">
            <div class="row mb-30">
              <div class="col-12">
                <table class="table" id="datatables">
                  <thead>
                    <tr>
                      <th>Customer</th>
                      <th>No. Faktur</th>
                      <th>Tanggal Faktur</th>
                      <th>Jatuh Tempo</th>
                      <th>Nilai faktur</th>
                      <th>Hutang(Asing)</th>
                      <th>Tempo</th>
                      <th>Status Faktur</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                    
                  </tfoot>
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

        let datatableUrl = '{{ route('superuser.finance.invoicing.json2') }}';
        let firstDatatableUrl = datatableUrl + '?startDate=' + startDate + '&endDate=' + endDate +
          '&customer=all';

        var datatable = $('#datatables').DataTable({
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
                { data: 'account_customer' },
                { data: 'no_faktur' },
                {
                    data: 'tanggal_faktur',
                    render: {
                        _: 'display',
                        sort: 'timestamp'
                    }
                },
                { data: 'jatuh_tempo' },
                { data: 'nilai_faktur' },
                { data: 'hutang_asing' },
                { data: 'diff_days' },
                { data: 'status_faktur' },
            ],
            order: [[0, 'asc'], [1, 'asc']],
            rowGroup: {
                dataSrc: 'account_customer',
                startRender: function(rows, group) {
                    var totalNilaiFaktur = rows
                        .data()
                        .pluck('nilai_faktur')
                        .reduce(function(a, b) { 
                            return a + (parseFloat(b) || 0); 
                        }, 0);
                        
                    var totalHutangAsing = rows
                        .data()
                        .pluck('hutang_asing')
                        .reduce(function(a, b) { 
                            return a + (parseFloat(b) || 0); 
                        }, 0);

                    return $('<tr/>')
                    .append('<td style="font-weight:bold; background-color: #bfbfbf;">' + group + '</td>')
                        .append('<td colspan="2" style="background-color: #bfbfbf;"></td>')
                        .append('<td style="font-weight:bold; background-color: #bfbfbf;">' + totalNilaiFaktur.toFixed(2) + '</td>')
                        .append('<td style="font-weight:bold; background-color: #bfbfbf;">' + totalHutangAsing.toFixed(2) + '</td>')
                        .append('<td colspan="3" style="background-color: #bfbfbf;"></td>');
                }
            },
            columnDefs: [
                {
                    targets: 0,
                    visible: false
                }
            ],
            dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel-o"></i>',
                    title: 'Piutang Faktur',
                    exportOptions: {
                        modifier: {
                            page: 'all' // Export all data
                        }
                    }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf-o"></i>',
                    orientation: 'landscape',  // Set landscape orientation
                    title: 'Piutang Faktur',
                    exportOptions: {
                        modifier: {
                            page: 'all' // Export all data
                        }
                    },
                    customize: function(doc) {
                        // Set table header style
                        doc.content[1].table.widths = [
                            '15%', '20%', '15%', '15%', '15%', '15%', '10%', '10%'
                        ];

                        doc.pageMargins = [20, 20, 20, 20]; // Set margins [left, top, right, bottom]

                        doc.styles.tableHeader = {
                            bold: true,
                            fontSize: 12,
                            color: 'white',
                            fillColor: 'black',
                            alignment: 'center'
                        };

                        // Center-align the body rows
                        var tableBody = doc.content[1].table.body;
                        for (var i = 1; i < tableBody.length; i++) {
                            for (var j = 0; j < tableBody[i].length; j++) {
                                tableBody[i][j].alignment = 'center';
                            }
                        }

                        doc.styles.tableBody = {
                            fontSize: 10,
                            alignment: 'center'
                        };

                        // Make the first row the header
                        doc.content[1].table.headerRows = 1;
                    }
                }
            ]
        });

        $('#btn-filter').on('click', function(e) {
          e.preventDefault();

          startDate = $('#periode_from').val();
          endDate = $('#periode_to').val();
          var customer = $('#customer').val();

          let newDatatableUrl = datatableUrl + '?startDate=' + startDate + '&endDate=' + endDate +
            '&customer=' + customer;

          datatable.ajax.url(newDatatableUrl).load();
        });
        
        $("#customer").val("all").change();
    })
</script>
@endpush