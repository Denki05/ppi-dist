@extends('superuser.app')

@section('content')
{{--<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <span class="breadcrumb-item active">Vendor</span>
</nav>--}}
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
<div class="block">
  <div class="block-content">
    <a href="{{ route('superuser.master.vendor.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>

    <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>
    
    {{--<a href="{{ route('superuser.master.vendor.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Add Contact</button>
    </a>--}}
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Created at</th>
          <th>Code</th>
          <th>Name</th>
          <th>Type</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@section('modal')

@include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.master.vendor.import_template'),
  'import_url' => route('superuser.master.vendor.import'),
  'export_url' => route('superuser.master.vendor.export')
])

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.master.vendor.json') }}';

  $('#datatable').DataTable({
    
    processing: true,
    serverSide: true,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'DT_RowIndex', name: 'id'},
      {
        data: 'created_at',
        render: {
          _: 'display',
          sort: 'timestamp'
        }
      },
      {data: 'code'},
      {data: 'name'},
      {data: 'type'},
      {data: 'status'},
      {data: 'action', orderable: false, searcable: false}
    ],
    order: [
      [1, 'desc']
    ],
    pageLength: 5,
    lengthMenu: [
      [5, 15, 20],
      [5, 15, 20]
    ],
    createdRow: function(row, data, index) {
      if (data.status == 'ACTIVE') {
        $('td:eq(5)', row).css('color', 'Green');
        $('td:eq(5)', row).css('font-weight', 'bold');
      }else if (data.status == 'DELETED') {
        $('td:eq(5)', row).css('color', 'Red');
        $('td:eq(5)', row).css('font-weight', 'bold');
      }
    },
  });
});
</script>
@endpush
