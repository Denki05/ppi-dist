@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item active">SPK</span>
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

<div id="alert-block"></div>

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

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<div class="block">
  <div class="block-content">
      <!-- <div class="row mb-30">
        <div class="col-12">
          <a href="{{route('superuser.gudang.purchase_order.create')}}" class="btn btn-primary btn-add"><i class="fa fa-plus"></i> Add PO</a>
        </div>
      </div> -->
      <a href="{{route('superuser.gudang.purchase_order_spk.create')}}">
        <button type="button" class="btn btn-outline-primary min-width-125">New</button>
      </a>

      <a href="{{route('superuser.gudang.purchase_order_spk.summary')}}">
        <button type="button" class="btn btn-outline-info min-width-125">Summary</button>
      </a>

      <hr class="my-20">

      <div class="row mb-30">
        <div class="col-12">
        <table id="datatables" class="table table-bordred table-striped" style="width:100%">
          <thead>
            <tr>
              <td class="text-center">#</td>
              <td class="text-center">Created at</td>
              <td class="text-center">PO Code</td>
              <td class="text-center">Latest Update</td>
              <td class="text-center">Status</td>
              <td class="text-center">Action</td>
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
        "url": '{{route('superuser.gudang.purchase_order_spk.json')}}',
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
        {data: 'status'},
        {data: 'action', orderable: false, searcable: false}
      ],
      scrollCollapse: true,
      scrollX: true,
      scrollY: 300,
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