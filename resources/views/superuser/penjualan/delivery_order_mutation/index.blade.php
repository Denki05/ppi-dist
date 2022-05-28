@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Delivery Order Mutation</span>
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
          <a href="{{route('superuser.penjualan.delivery_order_mutation.create')}}" class="btn btn-primary"><i class="fa fa-plus"></i> Add Delivery Order Mutation(DOM)</a>
        </div>
      </div>
  </div>
</div>
<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
      <form method="get" action="{{ route('superuser.penjualan.delivery_order_mutation.index') }}">
        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <select class="form-control js-select2" name="origin_warehouse_id">
                <option value="">==All Origin Warehouse==</option>
                @foreach($warehouse as $index => $row)
                  <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>          
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <select class="form-control js-select2" name="destination_warehouse_id">
                <option value="">==All Destination Warehouse==</option>
                @foreach($warehouse as $index => $row)
                  <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>          
          </div>
          <div class="col-lg-6">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Keyword" name="search">
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                </div>
              </div>
          </div>
        </div>
      </form>
      <div class="row mb-30">
        <div class="col-12">
          <table class="table table-striped table-vcenter table-responsive" id="datatables">
            <thead>
              <tr>
                <th>#</th>
                <th>Code</th>
                <th>Origin Warehouse</th>
                <th>Destination Warehouse</th>
                <th>Address</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($table as $index => $row)
                <tr>
                  <td>{{$table->firstItem() + $index}}</td>
                  <td>
                    <a href="{{route('superuser.penjualan.delivery_order_mutation.detail',$row->id)}}">{{$row->code}}</a>
                  </td>
                  <td>{{$row->origin_warehouse->name ?? ''}}</td>
                  <td>{{$row->destination_warehouse->name ?? ''}}</td>
                  <td>{{$row->address}}</td>
                  <td>
                    <span
                        class="badge badge-{{ $row->do_mutation_status()->class }}">{{ $row->do_mutation_status()->msg }}</span>
                  </td>
                  <td>
                    <?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?>
                  </td>
                  <td>
                    @if($row->status ==1)
                      <a href="{{route('superuser.penjualan.delivery_order_mutation.edit',$row->id)}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                      <a href="#" class="btn btn-danger btn-sm btn-flat btn-delete" data-id="{{$row->id}}"><i class="fa fa-trash"></i> Delete</a>
                    @else
                      <a href="{{route('superuser.penjualan.delivery_order_mutation.detail',$row->id)}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-eye"></i> Detail</a>
                      <a href="{{route('superuser.penjualan.delivery_order_mutation.print',$row->id)}}" target="_blank" class="btn btn-info btn-sm btn-flat"><i class="fa fa-print"></i> Print</a>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="row mb-30">
        <div class="col-12">
          {{$table->links()}}
        </div>
      </div>
  </div>
</div>
<form method="post" action="{{route('superuser.penjualan.delivery_order_mutation.destroy')}}" id="frmDestroyItem">
    @csrf
    <input type="hidden" name="id">
</form>
@endsection

<!-- Modal -->


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')

  <script type="text/javascript">
    $(function(){
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


        $('.js-select2').select2();

        $(document).on('click','.btn-delete',function(){
          if(confirm("Yakin ? ")){
            let id = $(this).data('id');
            $('#frmDestroyItem').find('input[name="id"]').val(id);
            $('#frmDestroyItem').submit();
          }
        })
      });
    })
  </script>
@endpush
