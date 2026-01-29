@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item active">Mutasi Out</span>
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
    <a href="{{ route('superuser.gudang.mutasi_out.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Created at</th>
          <th class="text-center">Code</th>
          <th class="text-center">Gudang Asal</th>
          <th class="text-center">Gudang Tujuan</th>
          <th class="text-center">Status</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($mutasi_out as $row)
          <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $row->created_at }}</td>
            <td class="text-center">{{ $row->code }}</td>
            <td class="text-center">{{ $row->warehouse_from_attribute->name }}</td>
            <td class="text-center">{{ $row->warehouse_to_attribute->name }}</td>
            <td class="text-center">{{ $row->status() }}</td>
            <td class="text-center">
              @if($row->status() == 'ACTIVE')
                <a class="btn btn-sm btn-primary" href="{{ route('superuser.gudang.mutasi_out.show', $row->id) }}" role="button"><i class="fa fa-eye"></i></a>
                <form action="{{ route('superuser.gudang.mutasi_out.acc', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apa kamu yakin melakukan proses mutasi out ini?');">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-success">
                      <i class="fa fa-check"></i>
                  </button>
                </form>
                <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this sale return?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this sale return?');"><i class="fa fa-trash"></i></button>
                </form>
              @endif

              @if($row->status() == 'ACC')
                <a class="btn btn-sm btn-primary" href="{{ route('superuser.gudang.mutasi_out.show', $row->id) }}" role="button"><i class="fa fa-eye"></i></a>
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

@section('modal')

@endsection

@push('scripts')
@include('superuser.asset.plugin.daterangepicker')
<script type="text/javascript">
$(document).ready(function() {
  $('#datatable').DataTable({
    
  });
});
</script>
@endpush
