@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Product</span>
  <span class="breadcrumb-item active">Catalog</span>
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
<div class="block">
  <div class="block-content">
    <a href="{{ route('superuser.master.catalog.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>

    <!-- <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button> -->
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Created at</th>
          <th>Code</th>
          <th>Note</th>
          <th>Print Count</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($result as $row)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $row->created_at }}</td>
          <td>{{ $row->code }}</td>
          <td>{{ $row->note }}</td>
          <td>{{ $row->print_count }}</td>
          <td>{{ $row->status }}</td>
          <td>-</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
$(document).ready(function () {
    $('#datatable').DataTable();
});
</script>
@endpush