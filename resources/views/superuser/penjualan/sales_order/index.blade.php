@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Sales Order</span>
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
@if($step == 1 || $step == 9)
<div class="block">
  <div class="block-content block-content-full">
      <div class="row">
        <div class="col-12">
          <a href="{{route('superuser.penjualan.sales_order.create', $step)}}" class="btn btn-primary"><i class="fa fa-plus"></i> Add Sales Order {{ $step_txt }} (SO)</a>
        </div>
      </div>
  </div>
</div>
@endif
<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
      <form method="get" action="{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}">
        <div class="row">
          
          @if($step == 1 || $step == 2)
          <div class="col-lg-3">
            <div class="form-group">
              <select class="form-control js-select2" name="field">
                <option value="">==Field==</option>
                <option value="customer" {{isset($_GET['field']) && $_GET['field'] == 'customer' ? 'selected="selected"' : ''}}>Customer</option>
                <option value="sales" {{isset($_GET['field']) && $_GET['field'] == 'sales' ? 'selected="selected"' : ''}}>Sales</option>
                <option value="code" {{isset($_GET['field']) && $_GET['field'] == 'code' ? 'selected="selected"' : ''}}>Code</option>
                <option value="transaksi" {{isset($_GET['field']) && $_GET['field'] == 'transaksi' ? 'selected="selected"' : ''}}>Transaksi</option>
              </select>
            </div>          
          </div>
          @endif
          <div class="col-lg-3">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Keyword" name="search" value="{{isset($_GET['search']) && $_GET['search'] ? $_GET['search'] : ''}}">
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                </div>
              </div>
          </div>
        </div>
      </form>
      <div class="row mb-30">
        <div class="col-12">
          <table class="table table-hover" id="datatables">
            <thead>
              <tr>
                <th>#</th>

                @if($step == 1 || $step == 2 || $step == 9)
                <th>Code</th>
                @endif

                @if($step == 1 || $step == 2)
                <th>Customer</th>
                @elseif($step == 9)
                <th>Warehouse</th>
                @endif

                @if($step == 1 || $step == 2)
                <th>Invoice Brand</th>
                @endif

                @if($step == 1 || $step == 2)
                <th>Sales Senior / Sales</th>
                @endif
                
                @if($step == 2)
                <th>Type Transaction</th>
                @endif

                @if($step == 1 || $step == 2 || $step == 9)
                <th>Tanggal Dibuat</th>
                @endif

                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($table as $index => $row)
                <tr>
                  <td>{{$index+1}}</td>

                  @if($step == 1 || $step == 2 || $step == 9)
                  <td><a href="{{route('superuser.penjualan.sales_order.detail',$row->id)}}">{{$row->code}}</a></td>
                  @endif

                  <td>
                    @if($row->so_for == 1)
                      {{$row->customer->name ?? ''}}
                    @elseif($row->so_for == 2)
                      {{$row->customer_gudang->name ?? ''}}
                    @endif
                  </td>

                  @if($step == 1 || $step == 2)
                  <td>
                    {{$row->so_brand_type()->scalar ?? ''}}
                  </td>
                  @endif
                  
                  @if($step == 1 || $step == 2)
                  <td>
                    {{$row->sales_senior->name ?? ''}} / <br>
                    {{$row->sales->name ?? ''}}
                  </td>
                  @endif

                  @if($step == 2)
                  <td>{{$row->so_type_transaction()->scalar ?? ''}}</td>
                  @endif

                  @if($step == 1 || $step == 9)
                  <td>
                    <?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?>
                  </td>
                  @endif
                  @if($step == 2)
                  <td>
                    <?= date('d-m-Y h:i:s',strtotime($row->updated_at)); ?>
                  </td>
                  @endif

                  @php
                    $soQty = 0;
                    foreach($row->so_detail as $index => $so_detail) {
                      $soQty = $soQty + $so_detail->qty;
                    }
                  @endphp

                  @if(($step == 1 || $step == 2 || $step == 9) && ($row->so_for == 1))
                  <td>
                    @if ($row->status === 4)
                    <a href="{{route('superuser.penjualan.sales_order.detail',$row->id)}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-eye"></i> Detail</a>
                    @endif
                    @if ($row->status === 4 && $soQty > 0)
                    <a href="{{route('superuser.penjualan.sales_order.print_rejected_so',$row->id)}}" class="btn btn-info btn-sm btn-flat" target="_blank"><i class="fa fa-print"></i> Print Rejected Item</a>
                    @endif
                    @if ($row->status === 1 || $row->status === 3)
                    <a href="{{route('superuser.penjualan.sales_order.edit',['id'=>$row->id, 'step'=>1])}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-edit"></i> {{ $row->status === 1 ? 'Edit' : 'Revisi' }}</a>
                    @endif
                    @if ($row->status === 1)
                    <a href="#" class="btn btn-success btn-sm btn-flat btn-lanjutan" data-id="{{$row->id}}"><i class="fa fa-check"></i> Lanjutan</a>
                    @endif
                    @if ($step == 1 && $row->status === 2)
                    <a href="{{route('superuser.penjualan.sales_order.detail',$row->id)}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-eye"></i> View</a>
                    @endif
                    @if ($step == 2 && $row->status === 2)
                    <a href="{{route('superuser.penjualan.sales_order.edit',['id'=>$row->id, 'step'=>2])}}" class="btn btn-success btn-sm btn-flat"><i class="fa fa-check"></i> Kerjakan</a>
                    <a href="#" class="btn btn-danger btn-sm btn-flat btn-kembali-ke-awal" data-id="{{$row->id}}"><i class="fa fa-times"></i> Kembali ke SO</a>
                    @endif
                    @if ($row->status === 1 || $row->status === 3)
                    <a href="#" class="btn btn-danger btn-sm btn-flat btn-delete" data-id="{{$row->id}}"><i class="fa fa-trash"></i> Delete</a>
                    @endif
                  </td>
                  @endif
                  @if($step == 9 && $row->so_for == 2)
                  <td>
                    <a href="{{route('superuser.penjualan.sales_order.edit',['id'=>$row->id, 'step'=>9])}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                    <a href="#" class="btn btn-danger btn-sm btn-flat btn-delete" data-id="{{$row->id}}"><i class="fa fa-trash"></i> Delete</a>
                  </td>
                  @endif
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      {{--<a href="{{ route('superuser.penjualan.sales_order.index_lanjutan') }}" class="btn btn-danger btn-sm btn-flat btn-delete" ><i class="mdi mdi-skip-forward"></i> SO Lanjutan</a>--}}
      
      <div class="row mb-30">
        <div class="col-12">
          {{$table->links()}}
        </div>
      </div>
  </div>
</div>
<form method="post" action="{{route('superuser.penjualan.sales_order.destroy')}}" id="frmDestroyItem">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.penjualan.sales_order.lanjutkan')}}" id="frmLanjutkan">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.penjualan.sales_order.kembali')}}" id="frmKembali">
    @csrf
    <input type="hidden" name="id">
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')

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

      $('.js-select2').select2();

      $(document).on('click','.btn-delete',function(){
        if(confirm("Apakah anda yakin ingin menghapus SO ini ? ")){
          let id = $(this).data('id');
          $('#frmDestroyItem').find('input[name="id"]').val(id);
          $('#frmDestroyItem').submit();
        }
      })

      $(document).on('click','.btn-lanjutan',function(){
        if(confirm("Apakah anda yakin ingin mengajukan sales order ke Lanjutan?")){
          let id = $(this).data('id');
          $('#frmLanjutkan').find('input[name="id"]').val(id);
          $('#frmLanjutkan').submit();
        }
      })

      $(document).on('click','.btn-kembali-ke-awal',function(){
        if(confirm("Apakah anda yakin ingin mengembalikan sales order ini?")){
          let id = $(this).data('id');
          $('#frmKembali').find('input[name="id"]').val(id);
          $('#frmKembali').submit();
        }
      })
    });
  </script>
@endpush
