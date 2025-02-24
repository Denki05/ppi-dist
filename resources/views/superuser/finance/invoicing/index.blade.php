@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Invoicing</span>
</nav>

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
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
    <strong>{{ session('error') ? 'Error!' : 'Berhasil!' }}</strong> {!! session('error') ?? session('success') !!}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<div class="row">
  <div class="col-lg-9 col-md-8 col-12">
    <div class="block">
      <div class="block-content">
        <div class="form-group row">
          <label class="col-12 col-md-4 col-lg-2 col-form-label" for="start_date">Periode From :</label>
          <div class="col-12 col-md-8 col-lg-4">
            <input type="date" class="form-control" name="start_date" id="periode_from">
          </div>
          <label class="col-12 col-md-4 col-lg-2 col-form-label" for="end_date">Periode To :</label>
          <div class="col-12 col-md-8 col-lg-4">
            <input type="date" class="form-control" name="end_date" id="periode_to">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-12 col-md-4 col-lg-2 col-form-label" for="kategori">Customer :</label>
          <div class="col-12 col-md-8 col-lg-4">
            <select class="form-control js-select2" name="customer" id="customer" data-placeholder="Select Customer">
              <option value="all">All</option>
              @foreach($customer as $row)
              <option value="{{ $row->id }}">{{ $row->name }} {{ $row->text_kota }}</option>
              @endforeach
            </select>
          </div>
          @role('Developer')
          <div class="col-12 col-md-8 col-lg-4 mt-2 mt-md-0">
            <a class="btn btn-primary" href="{{ route('superuser.finance.invoicing.updateInvoice') }}" role="button">Update</a>
          </div>
          @endrole
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-4 col-12">
    <div class="block">
      <div class="block-content text-center">
        <a href="#" id="btn-filter" class="btn bg-gd-corporate border-0 text-white px-4 py-2">
          Filter <i class="fa fa-search ml-2"></i>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="block">
  <div class="block-content block-content-full">
    <div class="table-responsive">
      <table class="table table-striped" id="datatables">
        <thead>
          <tr>
            <th>Created At</th>
            <th>Reff SO</th>
            <th>Invoice Code</th>
            <th>Type</th>
            <th>Account</th>
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
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2();
    let datatableUrl = '{{ route('superuser.finance.invoicing.json') }}';

    var datatable = $('#datatables').DataTable({
        responsive: true,
        language: { processing: "<i class='fa fa-spinner fa-spin'></i>" },
        processing: true,
        serverSide: false,
        ajax: {
            url: datatableUrl,
            type: "GET",
            data: { _token: "{{ csrf_token() }}" }
        },
        columns: [
            { data: 'created_at', render: { _: 'display', sort: 'timestamp' } },
            { data: 'so_code', name: 'penjualan_so.so_code' },
            { data: 'invoice_code', name: 'finance_invoicing.code' },
            { data: 'transaksi', name: 'penjualan_do.type_transaction' },
            { data: 'account_customer' },
            { data: 'grand_total_idr', render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '), searchable: false },
            { data: 'action' },
        ],
        order: [[0, 'desc']],
        pageLength: 10,
    });

    $('#btn-filter').on('click', function(e) {
        e.preventDefault();
        let newDatatableUrl = `${datatableUrl}?start_date=${$('#periode_from').val()}&end_date=${$('#periode_to').val()}&customer=${$('#customer').val()}`;
        datatable.ajax.url(newDatatableUrl).load();
    });
  });
</script>
@endpush