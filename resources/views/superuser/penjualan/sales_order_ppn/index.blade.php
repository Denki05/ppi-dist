@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sales</span>
  <span class="breadcrumb-item active">Sale Order PPN</span>
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
    <a href="{{ route('superuser.penjualan.sales_order_ppn.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatables" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Created at</th>
          <th class="text-center">Code</th>
          <th class="text-center">Customer</th>
          <th class="text-center">Sales</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($so_ppn as $key)
          <tr>
            <td>{{$loop->iteration}}</td>
            <td>{{$key->created_at}}</td>
            <td>{{$key->code}}</td>
            <td>{{$key->member->name}}</td>
            <td>{{$key->sales->name}}</td>
            <td>
              @if($key->status == 1)
                <a href="{{route('superuser.penjualan.sales_order_ppn.lanjutkan', $key->id)}}" class="btn btn-success btn-sm btn-flat"><i class="fa fa-check"></i> Lanjutan</a>
              @endif
              @if ($key->status == 2)
                <a href="#" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-eye"></i> Show</a>
              @endif
              @if ($key->status == 1)
                <a href="#" class="btn btn-warning btn-sm btn-flat"><i class="fa fa-pencil"></i> Edit</a>
              @endif
              @if ($key->status == 1)
                <a href="#" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-trash"></i> Delete</a>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>


@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(function(){
    $('#datatables').DataTable( {
        "paging":   false,
        "ordering": true,
        "info":     false,
        "searching" : false,
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
      });
  });
</script>
@endpush
