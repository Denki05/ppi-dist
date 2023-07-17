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
    <table id="store_list" class="table">
      <thead class="thead-dark">
        <tr>
          <th>#</th>
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
    $(document).ready(function() {
      var table = $('#store_list').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('superuser.master.customer.json') }}',
        columns: [
            {
                "className":      'details-control',
                "orderable":      false,
                "data":           null,
                "defaultContent": "<i class='fa fa-angle-down' aria-hidden='true'></i>"
            },
            {data: 'store_name', name: 'master_customers.name'},
            {data: 'category_name', name: 'master_customer_categories.name'},
            {data: 'text_kota', name: 'master_customers.text_kota'},
            {data: 'text_provinsi', name: 'master_customers.text_provinsi'},
            {data: 'tempo_limit', name: 'master_customers.tempo_limit'},
            {data: 'action', orderable: false, searcable: false}
        ],
        order: [[1, 'asc']]
    });

    // Add event listener for opening and closing details
    $('#store_list tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
    });

    /* Formatting function for row details - modify as you need */
    function format ( d ) {
        // `d` is the original data object for the row
        // return '<table class="table table table-hover">'+
        //     '<tr>'+
        //         '<td>Member:</td>'+
        //         '<td>'+d.member_name+'</td>'+
        //         '</tr>'+
        //     '<tr>'+
        //         '<td>Email:</td>'+
        //         '<td>'+d.email+'</td>'+
        //         '</tr>'+
        //     '<tr>'+
        //         '<td>Extra info:</td>'+
        //         '<td>And any further details here (images etc)...</td>'+
        //         '</tr>'+
        //     '</table>';
        return '<table class="table">'+
                  '<thead>'+
                      '<tr class="table-active">'+
                        '<th>Member</th>'+
                        '<th>Kota</th>'+
                        '<th>Provinsi</th>'+
                        '<th>Default</th>'+
                      '</tr>'+
                  '</thead>'+
                  '<tbody>'+
                      '<tr>'+
                        '<td>'+d.member_name+'</td>'+
                        '<td>'+d.member_kota+'</td>'+
                        '<td>'+d.member_provinsi+'</td>'+
                        '<td>'+d.member_default+'</td>'+
                      '</tr>'+
                  '</tbody>'
                '</table>';
    }
    })
</script>
@endpush