@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Invoicing</span>
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

<div id="alert-block"></div>

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

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<div class="form-group row">
      <div class="col-md-9">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="start_date">Periode From :</label>
              <div class="col-md-4">
                <input type="date" class="form-control" name="start_date" id="periode_from">
              </div>
              <label class="col-md-2 col-form-label text-left" for="end_date">peridoe To :</label>
              <div class="col-md-4">
                <input type="date" class="form-control" name="end_date" id="periode_to">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="kategori">Customer :</label>
              <div class="col-md-4">
               <select class="form-control js-select2" name="customer" id="customer" data-placeholder="Select Customer">
                <option value="all">All</option>
                @foreach($customer as $row)
                <option value="{{ $row->id }}">{{ $row->name }} {{ $row->text_kota }}</option>
                @endforeach
               </select>
              </div>

              <div class="col-md-4">
                <a class="btn btn-primary" href="{{ route('superuser.finance.invoicing.updateInvoice') }}" role="button">Report</a>
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
                    <th>Created At</th>
                    <th>Reff SO</th>
                    <th>Invoice Code</th>
                    <th>Type</th>
                    <th>Acccount</th>
                    <th>Total</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
      </div>
    </div>


@endsection


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')

  <script type="text/javascript">
    $(document).ready(function() {
      $('.js-select2').select2();
       // Function to get the current values of date filters
      function getDateValues() {
          return {
              start_date: $('#periode_from').val(),
              end_date: $('#periode_to').val()
          };
      }

      // Get initial date values
      var dateValues = getDateValues();

      let datatableUrl = '{{ route('superuser.finance.invoicing.json') }}';
      let firstDatatableUrl = `${datatableUrl}?start_date=${dateValues.start_date}&end_date=${dateValues.end_date}&customer=all`;

      // Initialize DataTable
      var datatable = $('#datatables').DataTable({
          "language": {
              "processing": "<span class='fa-stack fa-lg'>\n\
                                <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                            </span>",
          },
          processing: true,
          serverSide: false,
          ajax: {
              "url": datatableUrl,
              "dataType": "json",
              "type": "GET",
              "data": {
                  _token: "{{ csrf_token() }}"
              }
          },
          columns: [
              {
                data: 'created_at',
                render: {
                  _: 'display',
                  sort: 'timestamp'
                }
              },
              { data: 'so_code', mame: 'penjualan_so.so_code' },
              { data: 'invoice_code', name: 'finance_invoicing.code' },
              { data: 'transaksi', name: 'penjualan_do.type_transaction' },
              { data: 'account_customer' },
              {
                data: 'grand_total_idr',
                render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
                searchable: false
              },
              { data: 'action' },
          ],
          order: [
              [2, 'desc']
          ],
          pageLength: 10,
          lengthMenu: [
              [10, 25, 50, 100],
              [10, 25, 50, 100]
          ],
          dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
              "<'row'<'col-sm-12'tr>>" +
              "<'row'<'col-sm-5'i><'col-sm-7'p>>",
      });

      // Filter button click event
      $('#btn-filter').on('click', function(e) {
          e.preventDefault();

          // Update date values
          dateValues = getDateValues();
          var customer = $('#customer').val();

          let newDatatableUrl = `${datatableUrl}?start_date=${dateValues.start_date}&end_date=${dateValues.end_date}&customer=${customer}`;
          
          datatable.ajax.url(newDatatableUrl).load();
      });
    })
  </script>
@endpush
