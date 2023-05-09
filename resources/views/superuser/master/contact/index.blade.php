@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <span class="breadcrumb-item active">Contact</span>
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
<div class="block">
  <div class="block-content">
    <a href="{{ route('superuser.master.contact.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>

    <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="contact_list" class="table table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Created at</th>
          <th>Name</th>
          <th>Phone</th>
          <th>Position</th>
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
  'import_template_url' => route('superuser.master.contact.import_template'),
  'import_url' => route('superuser.master.contact.import'),
  'export_url' => route('superuser.master.contact.export'),
  'import_custom_message' => '
    DOB format : d-m-Y <br>
    example: 7 May 1999
    <div class="row">
      <div class="col-md-6">
        <i class="si si-check text-success"></i> 07-05-1999 <br>
        <i class="si si-close text-danger"></i> 7-5-1999
      </div>
      <div class="col-md-6">
        <i class="si si-close text-danger"></i> 7-5-99 <br>
        <i class="si si-close text-danger"></i> 7 May 1999
      </div>
    </div>
  '
])

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.master.contact.json') }}';

  $('#contact_list').DataTable({
    processing: true,
    serverSide: false,
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
      {data: 'name'},
      {data: 'phone'},
      {data: 'position'},
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
  });
});
</script>
@endpush
