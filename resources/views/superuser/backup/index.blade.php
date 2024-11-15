@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item active">Backup</span>
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
    <table class="table table-striped" id="datatables">
      <thead>
        <tr>
          <th>#</th>
          <th>Backup file</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($backupFiles as $file)
        <tr>
          <td>
            {{$loop->iteration}}
          </td>
          <td>
            {{ basename($file) }}
          </td>
          <td>
            <a href="{{ route('superuser.backup.download', ['fileName' => basename($file)]) }}">
              <button type="button" class="btn btn-primary"><i class="fa fa-download"></i></button>
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    var datatable = $('#datatables').DataTable({

    })
  })
</script>
@endpush