@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item active">Purchase Order (PO)</span>
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
      <!-- <div class="row mb-30">
        <div class="col-12">
          <a href="{{route('superuser.gudang.purchase_order.create')}}" class="btn btn-primary btn-add"><i class="fa fa-plus"></i> Add PO</a>
        </div>
      </div> -->
      <a href="{{route('superuser.gudang.purchase_order.create')}}">
        <button type="button" class="btn btn-outline-primary min-width-125">New</button>
      </a>
      <hr class="my-20">

      <div class="row mb-30">
        <div class="col-12">
        <table id="datatables" class="table table-striped">
          <thead>
            <tr>
              <th class="text-center">#</th>
              <th class="text-center">Created at</th>
              <th class="text-center">PO Code</th>
              <th class="text-center">Latest Update</th>
              <th class="text-center">Edit Counter</th>
              <th class="text-center">Status</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          
        </table>
        </div>
      </div>
    </div>
</div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    

    $('#datatables').DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        "url": '{{route('superuser.gudang.purchase_order.json')}}',
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
        {data: 'updated_by'},
        {data: 'edit_counter'},
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