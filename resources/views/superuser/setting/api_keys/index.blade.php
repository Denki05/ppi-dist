@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Setting</span>
  <span class="breadcrumb-item active">Api Keys</span>
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
  <div class="block-content block-content-full">
      <div class="row">
        <div class="col-12">
          <a href="{{ route('superuser.setting.api_keys.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> ADD API KEYS</a>
        </div>
      </div>
  </div>
</div>
<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
      <div class="row mt-20">
        <div class="col-12">
          <div class="table-responsive">
            <table class="table table-striped" id="api_table">
              <thead>
                <th>#</th>
                <th>Name</th>
                <th>Key Code</th>
                <th>Status</th>
                <th>Action</th>
              </thead>
              <tbody>
                @foreach($keys as $key)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $key->name ?? '-' }}</td>
                  <td>{{ $key->key }}</td>
                  <td>
                    @if($key->is_active == 1)
                    <span class="badge badge-success">Active</span>
                    @else
                    <span class="badge badge-danger">Inactive</span>
                    @endif
                  </td>
                  <td>
                    <form action="{{ route('superuser.setting.api_keys.destroy', $key->id) }}" method="POST" style="display:inline;">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this API key?')">
                        <i class="fa fa-trash"></i> Delete
                      </button>
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
  </div>
@endsection

@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
      var datatable = $('#api_table').DataTable({});
  });
</script>
@endpush