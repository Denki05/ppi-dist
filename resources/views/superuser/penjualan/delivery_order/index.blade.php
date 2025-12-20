@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item active">Delivery Order (DO)</span>
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

@if(session()->has('collect_success') || session()->has('collect_error'))
<div class="container">
  <div class="row">
    <div class="col pl-0">
      <div class="alert alert-success alert-dismissable" role="alert" style="max-height: 300px; overflow-y: auto;">
        <h3 class="alert-heading font-size-h4 font-w400">Successful Import</h3>
        //@foreach (session()->get('collect_success') as $msg)
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

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

  <form id="form" target="_blank" action="#"
    enctype="multipart/form-data" method="POST">
    @csrf
    <div class="block">
      <div class="block-content">
        <div class="form-group row">
          <label class="col-md-1 col-form-label text-left" for="period">Period :</label>
          <div class="col-md-3">
            <div class="input-group">
              <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"
                    aria-hidden="true"></i></span></div><input type="text" class="form-control pull-right" id="datesearch"
                name="datesearch" placeholder="Select period"
                value="{{ \Carbon\Carbon::now()->format('d/m/Y') }} - {{ \Carbon\Carbon::now()->format('d/m/Y') }}">
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

<div class="block">
  <div class="block-content">
    @if($superuser->can('sales order-create'))
    {{-- <a href="{{ route('superuser.sale.sales_order.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a> --}}
    @endif
    <div class="pull-right">
      <label class="css-control css-control-primary css-radio">
        <input type="radio" class="css-control-input" name="show-control" value="default" checked>
        <span class="css-control-indicator"></span> Do Proses (Packing)
      </label>
      <label class="css-control css-control-success css-radio">
        <input type="radio" class="css-control-input" name="show-control" value="acc">
        <span class="css-control-indicator"></span> DO Siap Kirim
      </label>
      <label class="css-control css-control-warning css-radio">
        <input type="radio" class="css-control-input" name="show-control" value="all">
        <span class="css-control-indicator"></span> DO Resi
      </label>
      <label class="css-control css-control-warning css-radio">
        <input type="radio" class="css-control-input" name="show-control" value="update">
        <span class="css-control-indicator"></span> Update Resi
      </label>
    </div>
  </div>
  <br>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Created at</th>
          <th class="text-center">DO Code</th>
          <th class="text-center">Customer</th>
          <th class="text-center">Status</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')



@push('scripts')
<script src="{{ url('https://cdn.datatables.net/select/1.3.1/js/dataTables.select.min.js') }}"></script>
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
@include('superuser.asset.plugin.daterangepicker')
<script type="text/javascript">
$(document).ready(function() {
  // setTimeout(function () { 
  //     location.reload();
  //   }, 60 * 1000);

  let datatableUrl = '{{ route('superuser.penjualan.delivery_order.json') }}';
  
  let showControl = $('input[type=radio][name=show-control]');
  let valShow = "default";
  showControl.change(function() {
    let newDatatableUrl = datatableUrl+'?show='+this.value;
    valShow = this.value;
    $('#datatable').DataTable().ajax.url(newDatatableUrl).load();
  });

  $('#datesearch').daterangepicker({
    autoUpdateInput: false
  });

  $('#datesearch').data('daterangepicker').setStartDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');
  $('#datesearch').data('daterangepicker').setEndDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');

  $('#datesearch').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    start_date = picker.startDate.format('YYYY-MM-DD');
    end_date = picker.endDate.format('YYYY-MM-DD');

    if (start_date && end_date) {
      let newDatatableUrl = datatableUrl + '?from=' + start_date + '&to=' + end_date +'&show='+valShow;
      $('#datatable').DataTable().ajax.url(newDatatableUrl).load();
    // alert('aa')
    }
  });
  
  var table = $('#datatable').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'id', width: '3%'},
      {
        data: 'created_at',
        render: {
          _: 'display',
          sort: 'timestamp'
        }
      },
      {data: 'do_code'},
      {data: 'customer_other_address_id'},
      {data: 'status'},
      {data: 'action', orderable: false, searcable: false}
    ],
    order: [
      [1, 'desc']
    ],
    pageLength: 10,
    lengthMenu: [
      [10, 30, 100, -1],
      [10, 30, 100, 'All']
    ],
    "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>> <"row"<"col-sm-12 col-md-12"p>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
  });
});
</script>
@endpush
