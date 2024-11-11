@extends('superuser.app')

@section('content')

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

@if($step == 1)
  <div class="row">
  <div class="col-12">
    <div class="block">
      <div class="block-content block-content-full">
        <form>
            <div class="row">
              <div class="col-lg-2 pt-2">
                <h5>#List SO {{ $step_txt }}</h5>
              </div>
              <div class="col-lg-3">
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-right">Customer</label>
                  <div class="col-md-9">
                    <select class="form-control js-select2" name="customer_other_address_id" data-placeholder="Cari Customer">
                      <option value="">All</option>
                      @foreach($other_address as $key)
                      <option value="{{ $key->id }}">{{ $key->name }} {{$key->text_kota}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>   
              </div>
              <div class="col-lg-3">
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-right">Status</label>
                  <div class="col-md-9">
                    <select class="form-control js-select2" name="status_so" data-placeholder="Cari Status">
                      <option value=""></option>
                      <option value="1">AWAL</option>
                      <option value="3">REVISI</option>
                      <option value="4">TUTUP</option>
                      <option value="5">HOLD</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="form-group row">
                  <div class="col-md-9">
                    <div class="input-group mb-3">
                        <div class="input-group-append">
                          <button type="submit" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50"><i class="fa fa-search ml-10"></i></button>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </form>

        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
          <i class="fa fa-plus mr-10"></i> Add SO
        </button>

        @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer")
          <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Export</button>
        @endif


        <!-- <button type="button" class="btn btn-outline-info"><i class="fa fa-print"></i> GET SO</button> -->

        <br>
        <br>
        <table class="table table-striped" id="so_awal">
          <thead>
            <th>#</th>
            <th>Code</th>
            <th>Nota</th>
            <th>Brand</th>
            <th>Customer</th>
            <th>Sales</th>
            <th>Created By</th>
            <th>Created At</th>
            <th>Status</th>
            <th>Action</th>
          </thead>
          <tbody>
            @foreach($table as $index => $row)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td><a href="{{route('superuser.penjualan.sales_order.detail',$row->id)}}">{{$row->so_code}}</a></td>
                  <td>{{ $row->code ?? '-' }}</td>
                  <td>{{ $row->brand_name ?? '-' }}</td>
                  <td>{{ $row->member->name }} {{ $row->member->text_kota }}</td>
                  <td>{{ $row->sales() }} | {{ $row->sales_senior() }}</td>
                  <td>{{ $row->createdBySuperuser() }}</td>
                  <td><?= date('d-m-Y',strtotime($row->so_date)); ?></td>
                  <td>{{ $row->so_status()->scalar }}</td>
                  <td>
                      @if($row->status == 1 OR $row->status == 3)
                        <a href="{{route('superuser.penjualan.sales_order.edit',['id'=>$row->id, 'step'=>1])}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-edit"></i> {{ $row->status === 1 ? 'Edit' : 'Revisi' }}</a>
                        <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.sales_order.lanjutkan', $row->id) }}')" class="btn btn-success btn-sm btn-flat btn-lanjutan" data-id="{{$row->id}}"><i class="fa fa-check"></i> Lanjutan</a>
                        <a href="#" class="btn btn-danger btn-sm btn-flat btn-delete" data-id="{{$row->id}}"><i class="fa fa-trash"></i> Delete</a>
                      @endif
                      @if($row->status == 2 OR $row->status == 4)
                        <button type="button" class="btn btn-primary btn-sm btn-flat" data-toggle="modal" data-target="#myModal{{$row->id}}"><i class="fa fa-eye"></i> View</button>
                      @endif
                      @if($row->status == 5)
                        <button type="button" class="btn btn-primary btn-sm btn-flat" data-toggle="modal" data-target="#myModal{{$row->id}}"><i class="fa fa-eye"></i> View</button>
                      @endif
                      <a href="{{route('superuser.penjualan.sales_order.print_so',$row->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$row->id}}" target="_blank"><i class="fa fa-print"></i> Print SO</a>
                  </td>
                </tr>
            @endforeach
          </tbody>
        </table>
    </div>
  </div>
</div>
@elseif($step == 2)
<h4 style="font-weight: bold;">#SALES ORDER LANJUTAN</h4>
@role('Developer', 'superuser')
  <a class="btn btn-primary" href="{{ route('superuser.penjualan.sales_order.updateBrandName') }}" role="button">Update</a>
@endrole
<br>
<br>
<main style="background:#fff">
  
  <input style="display: none;" id="tab1" type="radio" name="tabs" checked>
  <label style="padding: 15px 25px;" for="tab1">SO {{ $step_txt }}</label>
    
  <input style="display: none;" id="tab2" type="radio" name="tabs">
  <label style="padding: 15px 25px;" for="tab2">Packing Order</label>
    
  <input style="display: none;" id="tab3" type="radio" name="tabs">
  <label style="padding: 15px 25px;" for="tab3">SO Progress</label>

  @if($superuser->division == "Management" OR $superuser->division == "Admin" OR $superuser->division == "Developer")
  <input style="display: none;" id="tab4" type="radio" name="tabs">
  <label style="padding: 15px 25px;" for="tab4">DO CANCEL</label>
  @endif  

    
  <!-- Sales Order Lanjutan -->
  <section id="content1">
    <div class="row mb-30">
      <div class="col-12">
        <table class="table table-hover" id="so_lanjutan">
          <thead>
            <tr>
              <th>#</th>
              <th>Code</th>
              <th>Nota</th>
              <th>Brand</th>
              <th>Customer</th>
              <th>Created By</th>
              <th>Type</th>
              <th>Created At</th>
              <th>Action</th>
              </tr>
          </thead>
          <tbody>
            @foreach($table as $index => $row)
              @if($row->so_indent == 0 && !in_array($row->status, [1, 3]))
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{$row->so_code ?? '-'}}</td>
                <td>{{$row->code ?? '-'}}</td>
                <td>{{$row->brand_name ?? '-'}}</td>
                <td>{{ $row->member->name }} {{ $row->member->text_kota }}</td>
                <td>{{ $row->createdBySuperuser() }}</td>
                <td>{{$row->type_transaction}}</td>
                <td><?= date('d-m-Y',strtotime($row->created_at)); ?></td>
                <td>
                  @if ($step == 2 && $row->status === 2)
                    <button type="button" class="btn btn-primary btn-sm btn-flat" data-toggle="modal" data-target="#myModal{{$row->id}}"><i class="fa fa-eye"></i> View</button>
                    <a href="{{route('superuser.penjualan.sales_order.edit',['id'=>$row->id, 'step'=>2])}}" class="btn btn-success btn-sm btn-flat"><i class="fa fa-check"></i> Kerjakan</a>
                    <!-- <a href="#" class="btn btn-warning btn-sm btn-flat btn-kembali-ke-awal" data-id="{{$row->id}}"><i class="fa fa-times mr-10"></i> Revisi</a> -->
                    <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.sales_order.kembali', $row->id) }}')" class="btn btn-warning btn-sm btn-flat"><i class="fa fa-times mr-10"></i> Revisi</a>
                    <a href="javascript:saveConfirmation2('{{ route('superuser.penjualan.sales_order.delete_lanjutan', ['id' => $row->id]) }}')" class="btn btn-danger btn-sm btn-flat btn-delete-lanjutan"><i class="fa fa-trash mr-10"></i> Delete</a>
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
    
  <!-- Packing Order -->
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
                  @if($row->so->payment_status == 1 OR $row->type_transaction == "TEMPO" OR $row->type_transaction == "COD" OR $row->type_transaction == "MARKETPLACE")
                    <td>{{ $index+1 }}</td>
                    <td>{{$row->code ?? '-'}}</td>
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
                        <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.packing_order.ready', $row->id) }}')" class="btn btn-success btn-sm btn-flat" data-id="{{$row->id}}"><i class="fa fa-send"></i> Naik Ke DO</a>
                        <a href="{{route('superuser.penjualan.delivery_order.print_manifest', $row->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$row->id}}" target="_blank">
                          <i class="fas fa-clipboard-list"></i> Print Manifest
                        </a>
                      @if($row->type_transaction == 'TEMPO' OR $row->type_transaction == "COD" OR $row->type_transaction == "MARKETPLACE")
                        <!-- <a href="#" class="btn btn-danger btn-sm btn-flat btn-frmedit" data-id="{{$row->id}}"><i class="fa fa-edit"></i> Revisi</a> -->
                        <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.packing_order.revisi', $row->id) }}')" class="btn btn-danger btn-sm btn-flat" data-id="{{$row->id}}"><i class="fa fa-edit"></i> Revisi</a>
                      @endif
                      @if($superuser->division == "Developer" && $row->type_transaction == "CASH")
                      <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.packing_order.revisi', $row->id) }}')" class="btn btn-info btn-sm btn-flat" data-id="{{$row->id}}"><i class="fa fa-edit"></i> Revisi</a>
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
              <!-- <th>Action</th> -->
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
                          <span class="badge badge-pill badge-info"><b>Submit DO</bb></span>
                        @endif
                        @if($row->status == 3)
                          <span class="badge badge-pill badge-primary"><b>Packing Proses</b></span>
                        @endif
                        @if($row->status == 4)
                          <span class="badge badge-pill badge-primary"><b>Cetak SJ / DO</b></span>
                        @endif
                        @if($row->status == 5)
                          <span class="badge badge-pill badge-warning"><b>Delivering</b></span>
                        @endif
                        @if($row->status == 6)
                          <span class="badge badge-pill badge-success"><b>Delivered</b></span>
                        @endif
                        @if($row->status == 7)
                          <span class="badge badge-pill badge-danger"><b>Revisi</b></span>
                        @endif
                      </td>
                      <!-- <td>
                        @if($row->type_transaction == 2 && $row->status < 5)
                          <a href="#" class="btn btn-danger btn-sm btn-flat btn-frmedit" data-id="{{$row->id}}"><i class="fa fa-edit"></i> Revisi</a>
                        @endif
                      </td> -->
                  </tr>
                  @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </section>  

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
                      @if($row->status == 5 OR $row->status == 6 OR $row->status == 7)
                        <tr>
                            <td>{{ $index+1 }}</td>
                            <td>{{$row->do_code}}</td>
                            <td>{{$row->so->code}}</td>
                            <td>{{$row->member->name}}</td>
                            <td><?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?></td>
                            <td>{{$row->so->type_transaction}}</td>
                            
                            <td>
                              @if(in_array($row->status, [5, 6]) OR $superuser->division == "Management" OR $superuser->division == "Developer")
                                <a href="javascript:void(0)" type="button" class="btn btn-danger opneModalDoCancel" data-id="{{$row->id}}">Cancel DO</a> 
                              @endif
                              @if($row->status == 7 AND $row->count_cancel == 1 OR $superuser->division == "Admin")
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
</main>
@endif

<form method="post" action="{{route('superuser.penjualan.sales_order.destroy')}}" id="frmDestroyItem">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.penjualan.delivery_order.do_edit')}}" id="frmDoEdit">
    @csrf
    <input type="hidden" name="id">
</form>

<!-- modal indent show-->
@foreach($table as $key)
<div class="modal fade bd-example-modal-lg" id="myModalIndent{{$key->id}}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">View Indent #{{$key->so_code}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col">
                  <div class="block">
                    <div class="block-header block-header-default">
                      <h3 class="block-title">#Detail Nota Indent</h3>
                    </div>
                    <div class="block-content">
                      <div class="form-row">
                        <div class="form-group col-md-6">
                          <label for="invoice_date">Tanggal Indent</label>
                          <input type="text" name="invoice_date" class="form-control" value="{{ date('d-m-Y',strtotime($key->created_at)) }}" readonly>
                        </div>
                        <div class="form-group col-md-6">
                          <label for="invoice_code">Nomer Indent</label>
                          <input type="text" class="form-control" id="invoice_code" value="{{ $key->so_code }}" readonly>
                        </div>
                      </div>

                      <div class="form-row">
                        <!-- <div class="form-group col-md-6">
                          <label for="type_transaction">Type Transaksi</label>
                          <input type="text" name="type_transaction" class="form-control" value="{{$key->type_transaction}}" readonly>
                        </div> -->
                        <div class="form-group col-md-12">
                          <label for="note">Catatan</label>
                          <input type="text" class="form-control" value="{{ $key->note ?? '-' }}" readonly>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="block">
                    <div class="block-header block-header-default">
                      <h3 class="block-title">#Customer</h3>
                    </div>
                    <div class="block-content">
                      <div class="form-row">
                        <div class="form-group col-md-6">
                          <label for="type_transaction">Customer</label>
                          <input type="text" name="customer_name" class="form-control" value="{{ $key->member->name }} {{$key->member->text_kota}}" readonly>
                        </div>
                        <div class="form-group col-md-6">
                          <label for="note">Alamat Kirim</label>
                          <textarea class="form-control" rows="1" readonly>{{ $key->member->address }}</textarea>
                        </div>
                      </div>

                      <div class="form-row">
                        <div class="form-group col-md-6">
                          <label for="customer_city">Kota</label>
                          <input type="text" name="customer_city" class="form-control" value="{{$key->member->text_kota}}" readonly>
                        </div>
                        <div class="form-group col-md-6">
                          <label for="customer_area">Provinsi</label>
                          <input type="text" name="customer_area" class="form-control" value="{{ $key->member->text_provinsi }} " readonly>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
            </div>

            <div class="row">
              <div class="col">
                <table class="table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Product</th>
                      <th>Qty</th>
                      <th>Kemasan</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($key->so_detail as $index => $detail)
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $detail->product_pack->code }} - {{ $detail->product_pack->name }}</td>
                        <td>{{ $detail->qty }}</td>
                        <td>{{ $detail->product_pack->kemasan()->pack_name ?? '-' }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
        </div>
        <div class="modal-footer">
            <!-- <a class="btn btn-success" href="#" role="button"><i class="fa fa-check" aria-hidden="true"></i> Proses</a> -->
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
    </div>
  </div>
</div>
@endforeach

<!-- Modal view & detail -->
@foreach($table as $row)
<div class="modal fade bd-example-modal-xl" id="myModal{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">View SO #{{$row->code}}</h5>
            </div>
            <div class="modal-body">
              <div class="row">
                  <div class="col">
                    <div class="block">
                      <div class="block-header block-header-default">
                        <h3 class="block-title">#Detail Nota</h3>
                      </div>
                      <div class="block-content">
                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="invoice_date">Tanggal Nota</label>
                            <input type="text" name="invoice_date" class="form-control" value="{{ date('d-m-Y',strtotime($row->created_at)) }}" readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="invoice_code">Code</label>
                            <input type="text" class="form-control" id="invoice_code" value="{{$row->so_code}} || {{$row->code}}" readonly>
                          </div>
                        </div>

                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="invoice_date">Sales Senior</label>
                            <input type="text" name="sales_senior_id" class="form-control" value="{{ $row->sales_senior() }}"  readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="invoice_code">Sales</label>
                            <input type="text" class="form-control" id="sales_id"  value="{{ $row->sales() }}" readonly>
                          </div>
                        </div>

                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="type_transaction">Type Transaksi</label>
                            <input type="text" name="type_transaction" class="form-control" value="{{ $row->type_transaction }}"  readonly>
                          </div>
                          @if($row->status == 2 || $row->status == 4)
                          <div class="form-group col-md-6">
                            <label for="note">Note</label>
                            <input type="text" class="form-control" value="{{ $row->note }}"  readonly>
                          </div>
                          @endif
                          @if($row->status == 5)
                          <div class="form-group col-md-6">
                            <label for="catatan">Catatan</label>
                            <textarea class="form-control" id="exampleFormControlTextarea1" rows="1" readonly>{{ $row->catatan }}</textarea>
                          </div>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="block">
                      <div class="block-header block-header-default">
                        <h3 class="block-title">#Customer</h3>
                      </div>
                      <div class="block-content">
                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="type_transaction">Customer</label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control" value="{{ $row->member->name }} {{$row->member->text_kota}}"  readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="note">Alamat Kirim</label>
                            <textarea class="form-control" rows="1" readonly>{{$row->member->address}}</textarea>
                          </div>
                        </div>

                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="customer_city">Kota</label>
                            <input type="text" name="customer_city" class="form-control" value="{{$row->member->text_kota}}" readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="customer_area">Provinsi</label>
                            <input type="text" name="customer_area" class="form-control" value="{{$row->member->text_provinsi}}"  readonly>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
              </div>

              <div class="row">
                <div class="col">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Kemasan</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Free</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($row->so_detail as $value => $key)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $key->product_pack->code ?? '-' }} - <b>{{$key->product_pack->name ?? '-'}}</b></td>
                          <td>{{ $key->product_pack->packaging->pack_name ?? '-' }}</td>
                          <td>{{ $key->qty }}</td>
                          <td>{{ $key->price }}</td>
                          <td>
                            @if($key->free_product == 1)
                            YES
                            @elseif($key->free_product == 0)
                            NO
                            @endif
                        </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              @if($row->status == 5)
                <a href="" class="btn btn-success btn-sm btn-flat btn-lanjutan" data-id="{{$row->id}}"><i class="fa fa-check mr-10"></i>Lanjutan</a>
              @endif
              <button type="button" class="btn btn-danger btn-sm btn-flat" data-dismiss="modal"><i class="fa fa-close mr-10"></i>Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Modal add SO -->
<div class="modal fade" id="exampleModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog " role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">#Add SO {{$step_txt}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          @csrf
          <div class="row">
            <div class="col">
              <div class="form-group">
                <span class="form-label"><b>Customer </b> <span class="text-danger">*</span></span>
                <select class="js-select2 form-control account_member" id="account_member" name="member_name" style="width:100%;" data-placeholder="Cari Customer">
                  <option value="">Pilih Customer</option>
                  @foreach($other_address as $row)
                  <option value="{{$row->id}}">{{$row->name}}  {{$row->text_kota}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col">
              <div class="form-group">
                <span class="form-label"><b>Brand </b> <span class="text-danger">*</span></span>
                <select class="js-select2 form-control" id="merek_ppi" name="brand_name" style="width:100%;" data-placeholder="Pilih Brand">
                  <option value="">Pilih Brand</option>
                  @foreach($brand as $row)
                  <option value="{{$row->brand_name}}">{{$row->brand_name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group">
                <span class="form-label"><b>Type Transaksi </b> <span class="text-danger">*</span></span>
                <select class="form-control js-select2" name="so_type" id="so_type" style="width:100%;">
                  <option value="">Pilih Transaksi Type </option>
                  @foreach(App\Entities\Penjualan\SalesOrder::TYPE_TRANSACTION as $row => $value)
                  <option value="{{$value}}">{{$value}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col">
              <div class="form-group">
                <span class="form-label"><b>Indent </b> <span class="text-danger">*</span></span>
                <select class="form-control js-select2" name="so_indent" id="indent_so" style="width:100%;">
                  <option value="">Pilih status indent</option>
                  <option value="0">NO</option>
                  <option value="1">YES</option>
                </select>
              </div>
            </div>
          </div>
          
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <a href="#" id="addSO" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Add</a>
      </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalDoCancel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">verify auth token</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="myFormDoCancel" method="POST" role="form" enctype="multipart/form-data" novalidate>
          @csrf
          <div class="mb-3">
            <label>TOKEN :</label>
            <input type="password" class="form-control" name="secreatCode">
          </div>
          <input type="hidden" id="doID" />
          <button type="submit" class="btn btn-info">Auth</button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection
@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@section('modal')



@endsection

@push('scripts')
<!-- <script src="{{ asset('utility/superuser/js/form.js') }}"></script> -->
<script type="text/javascript">
  let datatableUrl = '{{ route('superuser.master.customer_other_address.json') }}';
  let firstDatatableUrl = datatableUrl +
        '?member_name=all';

  $(function(){
    $('#so_awal').DataTable( {
      paging:   true,
        orderin: true,
        info:     false,
        searching : true,
        order: [
          [8, 'asc'],
        ],
        pageLength: 10,
        lengthMenu: [
          [10, 30, 100, -1],
          [10, 30, 100, 'All']
        ], 
    });

    $('#so_lanjutan').DataTable( {
        paging:   true,
        ordering: true,
        info:     false,
        searching : true,
        order: [
          [2, 'asc'],
        ],
        pageLength: 10,
        lengthMenu: [
          [10, 30, 100, -1],
          [10, 30, 100, 'All']
        ], 
    });

    $('#packing_order').DataTable( {
        "paging":   true,
        "ordering": true,
        "info":     true,
        "searching" : true,
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
    });

    $('#so_progress').DataTable( {
        "paging":   true,
        "ordering": true,
        "info":     false,
        "searching" : true,
        order: [
          [2, 'desc']
        ],
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
    });
       
    $('#do_cancel').DataTable( {
        "paging":   true,
        "ordering": true,
        "info":     false,
        "searching" : true,
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

      // $(document).on('click','.btn-lanjutan',function(){
      //   if(confirm("Apakah anda yakin ingin mengajukan sales order ke Lanjutan?")){
      //     let id = $(this).data('id');
      //     $('#frmLanjutkan').find('input[name="id"]').val(id);
      //     $('#frmLanjutkan').submit();
      //   }
      // })

      $(document).on('click','.btn-kembali-ke-awal',function(){
        if(confirm("Apakah anda yakin ingin mengembalikan sales order ini?")){
          let id = $(this).data('id');
          $('#frmKembali').find('input[name="id"]').val(id);
          $('#frmKembali').submit();
        }
      })

      // $(document).on('click','.btn-ready',function(e){
      //   e.preventDefault();
      //   if(confirm("Apakah anda yakin ingin mengubah status SO ke DO Ready?")){
      //     let id = $(this).data('id');
      //     alert(id);
      //     $('#frmReady').find('input[name="id"]').val(id);
      //     $('#frmReady').submit();
      //   }
      // });

      $(document).on('click','.btn-frmedit',function(){
        if(confirm("Apakah anda yakin melakukan Edit?")){
          let id = $(this).data('id');
          $('#frmRevisi').find('input[name="id"]').val(id);
          $('#frmRevisi').submit();
        }
      })

      $(document).on('click','.btn_cancel',function(){
        if(confirm("Apakah anda yakin melakukan Cancel DO?")){
          let id = $(this).data('id');
          $('#frmCancel').find('input[name="id"]').val(id);
          $('#frmCancel').submit();
        }
      })

      $(document).on('click','.btn-frmdoedit',function(){
        if(confirm("Apakah anda yakin melakukan Edit DO?")){
          let id = $(this).data('id');
          $('#frmDoEdit').find('input[name="id"]').val(id);
          $('#frmDoEdit').submit();
        }
      })

      $('#addSO').on('click', function() {
        var customer = $('#account_member').val();
        var merek = $('#merek_ppi').val();
        var type_so = $('#so_type').val();
        var indent_so = $('#indent_so').val();
        var step_so = 1;

        var url = '{{ route('superuser.penjualan.sales_order.create',  [":step", ":member", ":brand", ":type", ":indent"]) }}';
        url = url.replace(':member', customer); 
        url = url.replace(':brand', merek); 
        url = url.replace(':type', type_so);
        url = url.replace(':indent', indent_so);
        url = url.replace(':step', step_so);

        $.ajax({
            url: url,
            type: 'GET',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success:function(data)
            {
              window.location.href = url;
            }
        });
      });

      // $('#exampleModal').on('hidden.bs.modal', function () {
      //   // Reset the select element
      //   // $('#account_member').prop('selectedIndex', 0);
      //   // $('#merek_ppi').prop('selectedIndex', 0);
      //   // $('#so_type').prop('selectedIndex', 0);
      //   // $('#indent_so').prop('selectedIndex', 0);
      //   $(this).find('account_member').trigger('reset');
      // });
      $('#exampleModal').on('hidden.bs.modal', function (e) {
          // Remove any data associated with the modal
          $(this).removeData();

          // Reset the form fields
          $(this).find('form')[0].reset();

          // Clear and reinitialize the Select2 elements
          $(this).find('.js-select2').val(null).trigger('change');
      });

      $(document).on('click', '.opneModalDoCancel', function () {
        var id = $(this).data('id');
        $('#doID').val(id);
        $('#modalDoCancel').modal('show');

        // alert(id);
      })

      

      $('#myFormDoCancel').on('submit', function (e) {
          e.preventDefault(); // Prevent the form submission
          var id = $('#doID').val();
          var url = "{{ route('superuser.penjualan.delivery_order.cancel_proses', ":id") }}";
          url = url.replace(':id', id);
          var alertMsg = $('div[role="alert"]');
          var formData = new FormData(this);

          // AJAX request
          $.ajax({
              url: url,
              type: 'POST',
              headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
              data: formData,
              contentType: false,
              processData: false,
              success: function (response) {
                  $(alertMsg).text('Cancellation successful!').show();
                  setTimeout(function () {
                      $('#modalDoCancel').modal('show');
                      setTimeout(function () {
                          window.location.reload();
                      }, 800);
                  }, 800);
              },
              error: function (xhr, status, error) {
                  $(alertMsg).text('Cancellation failed: ' + error).show();
              }
          });
      });

      $('a[href^="#"]').on('click', function(event) {
        var target = $( $(this).attr('href') );
        target.fadeToggle(100);
      });
    });
</script>
@endpush