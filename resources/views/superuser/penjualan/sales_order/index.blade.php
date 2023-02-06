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



<!-- add SO via search -->
@if($step == 1)
  <div class="block">
    <div class="block-content block-content-full">
      <table id="customer-table" class="table ">
        <thead class="thead-dark">
          <tr>
            <th></th>
            <th>Store</th>
            <th>Address</th>
            <th>Category</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($customers as $row)
            <tr class="clickable js-tabularinfo-toggle" data-toggle="collapse" id="row2" data-target=".a{{ $row->id }}">
                <td>
                  <div class="col-sm-6">
                    <div class="row mb-2">
                      <a href="#" class="link">
                        <button type="button" name='edit' id='{{ $row->id }}'>#</button>
                      </a>
                    </div>
                  </div>
                </td>
                <td>{{ $row->name }}</td>
                <td>{{ $row->address }}</td>
                <td>{{ $row->category->name ?? '-' }}</td>
                <td>
                  @if($row->status == $row::STATUS['ACTIVE'])
                    <span class="badge badge-success">ACTIVE</span>
                  @elseif($row->status == $row::STATUS['DELETED'])
                    <span class="badge badge-danger">IN ACTIVE</span>
                  @endif
                </td>
            </tr>

            <tr class="tabularinfo__subblock collapse a{{ $row->id }}">
                    <td colspan="8">
                      <table class="table-active table table-bordered">
                              <tr>
                                  <th width="30%">Member</th>
                                  <th width="10%">Invoice Brand</th>
                                  <th width="10%">TL</th>
                                  <th width="10%">Sales</th>
                                  <th width="10%"></th>
                              </tr>

                              <tbody>
                                  @foreach ($other_address as $index)
                                      @if ($row->id == $index->customer_id)
                                        <form method="GET" id="frmcrt" action="{{route('superuser.penjualan.sales_order.create', ['store' => $row->id, 'step' => $step, 'member' => $index->id])}}">
                                          @csrf
                                          <tr>
                                              <td>
                                                {{$index->name}}
                                              </td>
                                              <td>
                                                <select class="form-control js-select2" name="brand_type">
                                                  <option value="">Pilih Brand Invoice</option>
                                                  @foreach ($brand as $key => $i)
                                                    <option value="{{ $i->id }}">{{ $i->brand_name }}</option>
                                                  @endforeach
                                                </select>
                                              </td>
                                              <td>
                                                <select class="form-control js-select2" name="sales_senior_id">
                                                  <option value="">Pilih Team Leader</option>
                                                  @foreach($sales as $tl => $ss)
                                                    <option value="{{ $ss->id }}">{{ $ss->name }}</option>
                                                  @endforeach
                                                </select>
                                              </td>
                                              <td>
                                                <select class="form-control js-select2" name="sales_id">
                                                  <option value="">Pilih Salesman</option>
                                                  @foreach($sales as $salesman => $s)
                                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                                  @endforeach
                                                </select>
                                              </td>
                                              <td>
                                                <input class="btn btn-primary" type="submit" value="Add Sales Order {{ $step_txt }} (SO)">
                                              </td>
                                          </tr>   
                                        </form>
                                      @endif
                                  @endforeach
                              </tbody>
                      </table>
                  </td>
              </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif

<!-- List SO Awal -->
<div class="block">
  <div class="block-content block-content-full">
  <div class="block-header block-header-default">
    <h3 class="block-title">Sales Order List</h3>
  </div>
    <div class="row mb-30">
        <div class="col-12">
          <table class="table table-striped" id="datatables">
            <thead>
              <tr>
                <th>#</th>

                @if($step == 1 || $step == 2 || $step == 9)
                <th>Code</th>
                @endif

                @if($step == 1 || $step == 2)
                <th>Store / Member</th>
                @elseif($step == 9)
                <th>Warehouse</th>
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
                    {{$row->sales_senior->name ?? ''}} / <br>
                    {{$row->sales->name ?? ''}}
                  </td>
                  @endif

                  @if($step == 2)
                  <td>{{$row->so_type_transaction()->scalar ?? ''}}</td>
                  @endif

                  @if($step == 2)
                  <td>{{$row->ekspedisi->name ?? null}}</td>
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

      $('#table-Customer').DataTable( {
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
