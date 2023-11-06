@extends('superuser.app')

@section('content')
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

@if($step == 1)
<div class="block">
    <div class="block-content block-content-full">
      <div class="block-header block-header-default">
        <h3 class="block-title">Search Customer</h3>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <div class="form-group row">
          <div class="col-md-9">
            <div class="block">
              <div class="block-content">
                <div class="form-group row">
                  <label class="col-md-2 col-form-label text-left" for="member_name">Member</label>
                  <div class="col-md-4">
                    <select class="form-control js-select2" id="member_name" name="member_name" data-placeholder="Cari Member">
                      <option value="">All</option>
                      @foreach($other_address as $row)
                      <option value="{{$row->id}}">{{$row->name}}  {{$row->text_kota}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="block">
              <div class="block-content">
                <div class="form-group row">
                  <div class="col-md-12 text-center">
                    <a href="#" id="filter" name="filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                      Filter <i class="fa fa-search ml-10"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12">
          <table class="table table-striped" id="member_list" style="display:none;" width="100%">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Kota</th>
                <th>Kategori</th>
                <th>Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="block">
  <div class="block-content block-content-full">
    <div class="block-header block-header-default">
      <h3 class="block-title">List SO {{ $step_txt }}</h3>
    </div>
    <div class="row mb-30">
        <div class="col-12">
          <table class="table table-striped" id="so_awal">
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
                    {{$row->member->name}} {{$row->member->text_kota}}
                  </td>
                  @endif

                  @if($step == 1)
                  <td>
                    {{$row->so_sales_senior() ?? ''}} | {{ $row->so_sales() ?? '' }} <br>
                  </td>
                  @endif


                  @if($step == 1 || $step == 9)
                  <td>
                    <?= date('d-m-Y',strtotime($row->so_date)); ?>
                  </td>
                  @endif
                  @if($step == 2)
                  <td>
                    <?= date('d-m-Y h:i:s',strtotime($row->updated_at)); ?>
                  </td>
                  @endif

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
    </div>
</div>
@elseif($step == 2)
<h4 style="font-weight: bold;">#SALES ORDER LANJUTAN</h4>
<main style="background:#fff">
  
  <input style="display: none;" id="tab1" type="radio" name="tabs" checked>
  <label style="padding: 15px 25px;" for="tab1">SO {{ $step_txt }}</label>
    
  <input style="display: none;" id="tab2" type="radio" name="tabs">
  <label style="padding: 15px 25px;" for="tab2">Packing Order</label>
    
  <input style="display: none;" id="tab3" type="radio" name="tabs">
  <label style="padding: 15px 25px;" for="tab3">SO Progress</label>

  @if($superuser->canAny(['superuser-manage', 'salesperson-manage']))
    @role('Developer|SuperAdmin', 'superuser')
      <input style="display: none;" id="tab4" type="radio" name="tabs">
      <label style="padding: 15px 25px;" for="tab4">DO CANCEL</label>
    @endrole
  @endif

    
  <!-- DO Proses -->
  <section id="content1">
    <div class="row mb-30">
      <div class="col-12">
        <table class="table table-hover" id="so_lanjutan">
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
              @if($row->so_indent == 0)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{$row->code}}</td>
                <td>{{ $row->member->name }} {{ $row->member->text_kota }}</td>
                <td>{{$row->type_transaction}}</td>
                <td><?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?></td>
                <td>
                  @if ($step == 2 && $row->status === 2)
                    <a href="{{route('superuser.penjualan.sales_order.edit',['id'=>$row->id, 'step'=>2])}}" class="btn btn-success btn-sm btn-flat"><i class="fa fa-check"></i> Kerjakan</a>
                    <a href="#" class="btn btn-warning btn-sm btn-flat btn-kembali-ke-awal" data-id="{{$row->id}}"><i class="fa fa-times"></i> Kembali ke SO</a>
                    <a href="javascript:saveConfirmation2('{{ route('superuser.penjualan.sales_order.indent', ['id' => $row->id]) }}')" class="btn btn-info btn-sm btn-flat btn-indent"><i class="fa fa-clipboard"></i> Indent</a>
                    <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.sales_order.delete_lanjutan', ['id' => $row->id]) }}')" class="btn btn-danger btn-sm btn-flat btn-delete-lanjutan"><i class="fa fa-times"></i> Delete</a>
                  @endif
                  @if ($row->status === 4)
                    <a href="{{route('superuser.penjualan.sales_order.detail',$row->id)}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-eye"></i> Detail</a>
                  @endif
                     
                </td>
              </tr>
              @endif
            @endforeach
          </tbody>
        </table>
      </div>
      
    </div>
  </section>
    
  <section id="content2">
    <div class="alert alert-warning" role="alert" align="left">
      Revisi hanya transaksi <strong>Tempo</strong>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <table class="table table-hover" id="packing_order">
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
              @if($row->status == 2)
                <tr>
                  @if($row->so->payment_status == 1 || $row->type_transaction == "TEMPO")
                    <td>{{ $index+1 }}</td>
                    <td>{{$row->code}}</td>
                    <td>{{ $row->member->name }} {{$row->member->text_kota}}</td>
                    <td><?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?></td>
                    <td>{{$row->so->code}} / {{$row->so->type_transaction}}</td>
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
                        <a href="{{route('superuser.penjualan.delivery_order.print_manifest', $row->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$row->id}}" target="_blank">
                          <i class="fas fa-clipboard-list"></i> Print Manifest
                        </a>
                      @if($row->type_transaction == 'TEMPO')
                        <a href="#" class="btn btn-danger btn-sm btn-flat btn-frmedit" data-id="{{$row->id}}"><i class="fa fa-edit"></i> Revisi</a>
                      @endif
                    @endif
                      </td>
                  @endif
                </tr>
              @endif
            @endforeach
          </tbody>
        </table>
      </div>
      
    </div>
  </section>
    
  <section id="content3">
    <div class="row mb-30">
      <div class="col-12">
        <table class="table table-hover" id="so_progress">
          <thead>
            <tr>
              <th>#</th>
              <th>Refrensi SO</th>
              <th>DO Code</th>
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
                      <td>{{$row->so->code}}</td>
                      <td>{{$row->do_code}}</td>
                      <td><?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?></td>
                      <td>{{$row->type_transaction}}</td>
                      <td>
                      @if($row->status == 2)
                          <span class="badge badge-secondary"><b>SO Lanjutan</b></span>
                        @endif
                        @if($row->status == 3)
                          <span class="badge badge-success"><b>SUBMIT DO</b></span>
                        @endif
                        @if($row->status == 4)
                          <span class="badge badge-primary"><b>ON PROSES</b></span>
                        @endif
                        @if($row->status == 5)
                          <span class="badge badge-warning"><b>CETAK SJ</b></span>
                        @endif
                        @if($row->status == 6)
                          <span class="badge badge-info"><b>TERKIRIM</b></span>
                        @endif
                        @if($row->status == 7)
                          <span class="badge badge-danger"><b>DO REVISI</b></span>
                        @endif
                      </td>
                      <td>
                        @if($row->type_transaction == 2 && $row->status < 5)
                          <a href="#" class="btn btn-danger btn-sm btn-flat btn-frmedit" data-id="{{$row->id}}"><i class="fa fa-edit"></i> Revisi</a>
                        @endif
                      </td>
                  </tr>
                  @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </section>  

  @if($superuser->canAny(['superuser-manage', 'salesperson-manage']))
    @role('Developer|SuperAdmin', 'superuser')
    <section id="content4">
      <div class="row mb-30">
        <div class="col-12">
          <table class="table table-hover" id="do_cancel">
            <thead>
              <tr>
                <th>#</th>
                <th>DO Code</th>
                <th>Refrensi SO</th>
                <th>Customer</th>
                <th>Tanggal Buat</th>
                <th>Transaction Type</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            @foreach($packing_order as $index => $row)
                      @if($row->status == 5 OR $row->status == 7)
                        <tr>
                            <td>{{ $index+1 }}</td>
                            <td>{{$row->do_code}}</td>
                            <td>{{$row->so->code}}</td>
                            <td>{{$row->member->name}}</td>
                            <td><?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?></td>
                            <td>{{$row->so->type_transaction}}</td>
                            
                            <td>
                              @if($row->status == 5)
                                <a href="#" class="btn btn-danger btn-sm btn-flat btn-cancel" data-id="{{$row->id}}"><i class="fa fa-edit"></i> Cancel DO</a>
                              @endif
                              @if($row->status == 7)
                              <a href="#" class="btn btn-info btn-sm btn-flat btn-frmdoedit" data-id="{{$row->id}}"><i class="fa fa-edit"></i> Form Revisi</a>
                              @endif
                            </td>
                        </tr>
                      @endif
                    @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </section>
    @endrole
  @endif
</main>
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
<form method="post" action="{{route('superuser.penjualan.delivery_order.cancel_proses')}}" id="frmCancel">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.penjualan.delivery_order.do_edit')}}" id="frmDoEdit">
    @csrf
    <input type="hidden" name="id">
</form>
@endsection
@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  let datatableUrl = '{{ route('superuser.master.customer_other_address.json') }}';
  let firstDatatableUrl = datatableUrl +
        '?member_name=all';

  $(function(){
    $('#so_lanjutan').DataTable( {
        "paging":   false,
        "ordering": true,
        "info":     false,
        "searching" : false,
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
    });

    $('#packing_order').DataTable( {
        "paging":   false,
        "ordering": true,
        "info":     false,
        "searching" : false,
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
    });

    $('#so_progress').DataTable( {
        "paging":   false,
        "ordering": true,
        "info":     false,
        "searching" : false,
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
    });
       
    $('#do_cancel').DataTable( {
        "paging":   false,
        "ordering": true,
        "info":     false,
        "searching" : false,
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
    });

    var datatable = $('#member_list').DataTable({
        language: {
              processing: "<span class='fa-stack fa-lg'>\n\
                                    <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                              </span>",
        },
        processing: true,
        serverSide: false,
        searching: false,
        paging: false,
        info: false,
        ajax: {
          "url": datatableUrl,
          "dataType": "json",
          "type": "GET",
          "data":{ _token: "{{csrf_token()}}"}
        },
        columns: [
          {data: 'DT_RowIndex', name: 'id'},
          {data: 'member_name', name: 'master_customer_other_addresses.name'},
          {data: 'member_kota', name: 'master_customer_other_addresses.text_kota'},
          {data: 'category_name', name: 'master_customer_categories.name'},
          {data: 'action'}
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

      $('#filter').on('click', function(e) {
        e.preventDefault();
        var member_name = $('#member_name').val();
        let newDatatableUrl = datatableUrl + '?member_name=' + member_name;
        datatable.ajax.url(newDatatableUrl).load();
      })

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

    $(document).on('click','.btn-frmedit',function(){
      if(confirm("Apakah anda yakin melakukan Edit?")){
        let id = $(this).data('id');
        $('#frmRevisi').find('input[name="id"]').val(id);
        $('#frmRevisi').submit();
      }
    })

    $(document).on('click','.btn-cancel',function(){
      if(confirm("Apakah anda yakin melakukan Cancel DO?")){
        let id = $(this).data('id');
        $('#frmCancel').find('input[name="id"]').val(id);
        $('#frmCancel').submit();
      }
    })

    $(document).on('click','.btn-frmdoedit',function(){
      if(confirm("Apakah anda yakin melakukan Update DO?")){
        let id = $(this).data('id');
        $('#frmDoEdit').find('input[name="id"]').val(id);
        $('#frmDoEdit').submit();
      }
    })

    $("#filter").on("click", function(){
      $("#member_list").toggle();
    });
  })
</script>
@endpush