@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
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
                  <input type="date" class="form-control form-control" name="periode_from" id="periode_from" >
                </div>
              </div>
              <label class="col-md-2 col-form-label text-left" for="product">Period To :</label>
              <div class="col-md-4">
                <div class="input-group">
                  <input type="date" class="form-control form-control" name="periode_to" id="periode_to">
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
            serverSide: true,
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
                    // Safely calculate totals for each group
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

                    // Create the group header row with totals
                    return $('<tr/>')
                        .append('<td style="font-weight:bold; background-color: #bfbfbf;">' + group + '</td>') // Group header in `account_customer` column
                        .append('<td colspan="2" style="background-color: #bfbfbf;"></td>') // Empty cells for the remaining columns
                        .append('<td style="font-weight:bold; background-color: #bfbfbf;">' + totalNilaiFaktur.toFixed(2) + '</td>') // Total for `nilai_faktur`
                        .append('<td style="font-weight:bold; background-color: #bfbfbf;">' + totalHutangAsing.toFixed(2) + '</td>') // Total for `hutang_asing`
                        .append('<td colspan="3" style="background-color: #bfbfbf;"></td>'); // Empty cells for the remaining columns
                }
            },
            columnDefs: [
                {
                    targets: 0, // The `account_customer` column (index 0)
                    visible: false // Hide the column
                }
            ]
        });


        $('#btn-filter').on('click', function(e) {
          e.preventDefault();

          // Update startDate and endDate values
          startDate = $('#periode_from').val();
          endDate = $('#periode_to').val();
          var customer = $('#customer').val();

          // Update the DataTable URL with new filter values
          let newDatatableUrl = datatableUrl + '?startDate=' + startDate + '&endDate=' + endDate +
            '&customer=' + customer;

          // Reload the DataTable with the new URL
          datatable.ajax.url(newDatatableUrl).load();
        });
    })
</script>
@endpush