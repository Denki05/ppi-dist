@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sales Order</span>
  <span class="breadcrumb-item active">Proforma</span>
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

<div id="alert-block"></div>

<div class="block">
  <div class="block-content">
    <a href="{{ route('superuser.penjualan.so_proforma.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-bordred table-striped" style="width:100%">
      <thead>
        <tr>
          <th>#</th>
          <th>Created at</th>
          <th>Code</th>
          <th>Brand</th>
          <th>Customer</th>
          <th>Created By</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($results AS $row)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $row->created_at }}</td>
          <td>{{ $row->code }}</td>
          <td>{{ $row->so_brand_name }}</td>
          <td>
            @if($row->exsisting_customer == 0)
              {{$row->customer_name}}
            @elseif($row->exsisting_customer === 1)
            {{$row->member->name}} {{ $row->member->text_kota }}
            @endif
          </td>
          <td>{{ $row->createdBySuperuser() }}</td>
          <td>{{ $row->status }}</td>
          <td>
            @if($row->status == "DELETED")
              <a href="{{ route('superuser.penjualan.so_proforma.show', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Show">
                  <i class="fa fa-eye"></i>
                </button>
              </a>
            @endif
            @if($row->status == "ACTIVE")
              @if(!empty($row->details_cost->grand_total_idr))
              <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.so_proforma.acc', $row->id) }}')">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Acc">
                    <i class="fa fa-check"></i>
                </button>
              </a>
              @endif
              <a href="{{ route('superuser.penjualan.so_proforma.edit', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Edit">
                  <i class="fa fa-pencil"></i>
                </button>
              </a>
              @if(!empty($row->details_cost->grand_total_idr))
              <a href="{{ route('superuser.penjualan.so_proforma.print_so_proforma', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Print">
                  <i class="fa fa-print" aria-hidden="true"></i>
                </button>
              </a>
              @endif
              <a href="javascript:deleteConfirmation('{{ route('superuser.penjualan.so_proforma.destroy', $row->id) }}')">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                  <i class="fa fa-trash"></i>
                </button>
              </a>
            @endif
            @if($row->status == "ACC")
              <a href="#">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Show">
                  <i class="fa fa-eye"></i>
                </button>
              </a>
             
              <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.so_proforma.approval_so', $row->id) }}')">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Lanjutkan">
                  <i class="fa fa-arrow-right"></i>
                </button>
              </a>
            @endif
            @if($row->status == "LANJUTAN")
              <a href="{{ route('superuser.penjualan.so_proforma.show', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Show">
                  <i class="fa fa-eye"></i>
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
    var table = $('#datatable').DataTable({});
</script>
@endpush