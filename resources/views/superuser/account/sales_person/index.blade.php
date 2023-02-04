@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <span class="breadcrumb-item active">Sales Person</span>
</nav>
<div class="block">
  <div class="block-content">
    <a href="{{ route('superuser.account.sales_person.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Username</th>
          <th>Email</th>
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

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  $('#datatable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      "url": '{{ route('superuser.account.sales_person.json') }}',
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'DT_RowIndex', name: 'id'},
      {data: 'username'},
      {data: 'email'},
      {data: 'is_active'},
      {data: 'action', orderable: false, searcable: false}
    ],
    order: [
      [0, 'asc']
    ],
    pageLength: 5,
    lengthMenu: [
      [5, 15, 20],
      [5, 15, 20]
    ]
  });
});
</script>
@endpush