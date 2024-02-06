@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <span class="breadcrumb-item active">Mitra</span>
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
    <a href="{{ route('superuser.master.mitra.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>

    {{--<button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>--}}
    
  </div>
  <div class="block-content block-content-full">
    <table id="mitra" class="table table-striped table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Created at</th>
          <th>Code</th>
          <th>Name</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($mitra as $row)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $row->created_at }}</td>
            <td>{{ $row->code }}</td>
            <td>{{ $row->name }}</td>
            <td>{{ $row->status() }}</td>
            <td>
              @if($row->status == 1)
              <a href="{{ route('superuser.master.mitra.edit', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="View">
                  <i class="mdi mdi-lead-pencil"></i>
                </button>
              </a>
              <a href="javascript:deleteConfirmation('{{ route('superuser.master.mitra.destroy', $row->id) }}')">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                  <i class="mdi mdi-delete"></i>
                </button>
              </a>
              @endif
            </td>
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
    let table = $('#mitra').DataTable({})
</script>
@endpush