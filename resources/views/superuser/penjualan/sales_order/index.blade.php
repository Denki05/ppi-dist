@extends('superuser.app')

@section('content')
<!-- <nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Sales Order {{ $step_txt }}</span>
</nav> -->
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
                                  <th width="15%">Member</th>
                                  <th width="25%">Address</th>
                                  <th width="15%">Region</th>
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
                                                {{$index->address}}
                                              </td>
                                              <td>
                                                {{$index->text_provinsi}}
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

@if($step == 1)
<!-- List SO Awal -->
<div class="block">
  <div class="block-content block-content-full">
  <div class="block-header block-header-default">
    <h3 class="block-title">Sales Order {{ $step_txt }}</h3>
  </div>
    <div class="row mb-30">
        <div class="col-12">
          <table class="table table-striped" id="datatables">
            <thead>
              <tr>
                <th>#</th>

                @if($step == 1 )
                <th>Code</th>
                @endif

                @if($step == 1)
                <th>Customer</th>
                @elseif($step == 9)
                <th>Warehouse</th>
                @endif

                @if($step == 1)
                <th>Sales</th>
                @endif
                
                @if($step == 1)
                <th>Transaksi Type</th>
                @endif

                

                @if($step == 1 )
                <th>Tanggal Dibuat</th>
                @endif

                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($table as $index => $row)
                <tr>
                  <td>{{$index+1}}</td>

                  @if($step == 1 )
                  <td><a href="{{route('superuser.penjualan.sales_order.detail',$row->id)}}">{{$row->code}}</a></td>
                  @endif

                  @if($step == 1)
                  <td>
                    {{$row->member->name}}
                  </td>
                  @endif

                  @if($step == 1)
                  <td>
                    {{$row->sales_senior->name ?? ''}} | {{ $row->sales->name ?? '' }} <br>
                  </td>
                  @endif

                  @if($step == 1)
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

                  <!-- @php
                    $soQty = 0;
                    foreach($row->so_detail as $index => $so_detail) {
                      $soQty = $soQty + $so_detail->qty;
                    }
                  @endphp -->

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
@elseif($step == 2)
<!-- List SO Lanjutan -->
<div class="card text-center">
        <div class="card-header">
          <h4 align="left">#TRANSAKSI</h4>
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#so_lanjutan">SO {{ $step_txt }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#so_packed">SO PACKED</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#proses">PROSES</a>
                </li>
            </ul>
        </div>
        <div class="tab-content card-body">

            <!-- SO Lanjutan -->
            <div id="so_lanjutan" class="tab-pane active">
              <table class="table table-striped" id="datatables">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Customer</th>
                    <th>Transaksi Type</th>
                    <th>Tanggal Buat</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($table as $index => $row)
                  <tr>
                    <td>{{ $index+1 }}</td>
                    <td>{{$row->code}}</td>
                    <td>{{ $row->member->name }}</td>
                    <td>{{$row->so_type_transaction()->scalar ?? ''}}</td>
                    <td><?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?></td>
                    <td>
                      @if ($step == 2 && $row->status === 2)
                        <a href="{{route('superuser.penjualan.sales_order.edit',['id'=>$row->id, 'step'=>2])}}" class="btn btn-success btn-sm btn-flat"><i class="fa fa-check"></i> Kerjakan</a>
                        <a href="#" class="btn btn-danger btn-sm btn-flat btn-kembali-ke-awal" data-id="{{$row->id}}"><i class="fa fa-times"></i> Kembali ke SO</a>
                      @endif
                      @if ($row->status === 4)
                        <a href="{{route('superuser.penjualan.sales_order.detail',$row->id)}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-eye"></i> Detail</a>
                      @endif
                     
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <!-- SO Packed -->
            <div id="so_packed" class="tab-pane">
              <div class="alert alert-warning" role="alert" align="left">
                Revisi hanya transaksi <strong>Tempo</strong>
              </div>
              <table class="table table-striped" id="datatables">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Customer</th>
                    <th>Tanggal Buat</th>
                    <th>Refrensi SO</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($packing_order as $index => $row)
                  <tr>
                    @if($row->so->payment_status == 1 || $row->type_transaction == 2)
                      <td>{{ $index+1 }}</td>
                      <td>{{$row->code}}</td>
                      <td>{{ $row->member->name }}</td>
                      <td><?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?></td>
                      <td>{{$row->so->code}} / {{$row->so->so_type_transaction()->scalar}}</td>
                      <td>
                        <!-- <span class="badge badge-{{ $row->do_status()->class }}"><b>{{ $row->do_status()->msg }}</b></span> -->
                        @if($row->status == 2)
                          <span class="badge badge-{{ $row->do_status()->class }}"><b>{{ $row->do_status()->msg }}</b></span>
                        @endif
                        @if($row->status == 3)
                          <span class="badge badge-success"><b>Success</b></span>
                        @endif
                      </td>
                      <td>
                        @if($row->status == 2)
                            <a href="#" class="btn btn-success btn-sm btn-flat btn-ready" data-id="{{$row->id}}"><i class="fa fa-send"></i> Naik Ke DO</a>
                            @if($row->type_transaction == 2)
                            <a href="#" class="btn btn-danger btn-sm btn-flat btn-revisi" data-id="{{$row->id}}"><i class="fa fa-edit"></i> Revisi</a>
                            @endif
                        @endif
                      </td>
                    @endif
                  </tr>
                  
                  @endforeach
                </tbody>
              </table>
            </div>

            <!-- Proses -->
            <div id="proses" class="tab-pane">
              <table class="table table-striped" id="datatables">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>DO Code</th>
                    <th>Refrensi SO</th>
                    <th>Tanggal Buat</th>
                    <th>Transaction Type</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($packing_order as $index => $row)
                  <tr>
                      <td>{{ $index+1 }}</td>
                      <td>{{$row->do_code}}</td>
                      <td>{{$row->so->code}}</td>
                      <td><?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?></td>
                      <td>{{$row->so->so_type_transaction()->scalar}}</td>
                      <td>
                        @if($row->status == 3)
                          <span class="badge badge-success"><b>SUBMIT DO</b></span>
                        @endif
                        @if($row->status == 4)
                          <span class="badge badge-primary"><b>ON PROSES</b></span>
                        @endif
                        @if($row->status == 5)
                          <span class="badge badge-warning"><b>CETAK SJ</b></span>
                        @endif
                      </td>
                      <td>
                        @if($row->type_transaction == 2)
                          <!-- <a href="#" class="btn btn-danger btn-sm btn-flat btn-revisi" data-id="{{$row->id}}"><i class="fa fa-edit"></i> Revisi</a> -->
                        @endif
                      </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
        </div>
  </div>
@endif

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
<form method="post" action="{{route('superuser.penjualan.packing_order.ready')}}" id="frmReady">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.penjualan.packing_order.revisi')}}" id="frmRevisi">
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

    $(document).on('click','.btn-ready',function(){
      if(confirm("Apakah anda yakin ingin mengubah status SO Validasi ke Ready?")){
        let id = $(this).data('id');
        $('#frmReady').find('input[name="id"]').val(id);
        $('#frmReady').submit();
      }
    })

    $(document).on('click','.btn-revisi',function(){
      if(confirm("Apakah anda yakin me revisi SO Validasi?")){
        let id = $(this).data('id');
        $('#frmRevisi').find('input[name="id"]').val(id);
        $('#frmRevisi').submit();
      }
    })

    

    });
  </script>
@endpush
