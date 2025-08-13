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

    <div class="block">
      <div class="block-content block-content-full">
        <div class="row">
            <div class="col-lg-2 pt-2">
                <h5>#SO PPN {{ $step_txt }}</h5>
            </div>
            <br>
            <br>
            <br>
            <div class="col-12">
              <table class="table table-hover" id="so_lanjutan">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">code</th>
                    <th scope="col">Nota</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Created By</th>
                    <th scope="col">Transaksi Type</th>
                    <th scope="col">Tanggal Buat</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($table as $row)
                  <tr>
                    <th scope="row">{{ $loop->iteration }}</th>
                    <td>{{ $row->so_code }}</td>
                    <td>{{ $row->code ?? '-' }}</td>
                    <td>{{ $row->member->name }} {{ $row->member->text_kota }}</td>
                    <td>{{ $row->createdBySuperuser() }}</td>
                    <td>{{$row->type_transaction}}</td>
                    <td><?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?></td>
                    <td>
                      @if ($step == 2 && $row->status === 2)
                      <button type="button" class="btn btn-primary btn-sm btn-flat" data-toggle="modal" data-target="#myModal{{$row->id}}"><i class="fa fa-eye"></i> View</button>
                      <a href="{{ route('superuser.penjualan.sales_order_ppn.edit_ppn', ['id' => $row->id, 'step' => 2]) }}" class="btn btn-success btn-sm btn-flat"><i class="fa fa-check"></i> Kerjakan</a>
                      <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.sales_order.kembali', $row->id) }}')" class="btn btn-warning btn-sm btn-flat"><i class="fa fa-times mr-10"></i> Revisi</a>
                      <a href="javascript:saveConfirmation2('{{ route('superuser.penjualan.sales_order.destroy_lanjutan', ['id' => $row->id]) }}')" class="btn btn-danger btn-sm btn-flat btn-delete-lanjutan"><i class="fa fa-trash mr-10"></i> Delete</a>
                      @endif
                      @if ($row->status === 4)
                      <a href="{{route('superuser.penjualan.sales_order_ppn.detail' ,$row->id)}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-eye"></i> Detail</a>
                        <a href="javascript:saveConfirmation('{{ route('superuser.penjualan.sales_order_ppn.cancel_approve', ['id' => $row->id]) }}')" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times-circle" aria-hidden="true"></i> Cancel</a>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            
        </div>
      </div>
    </div>

<!-- Modal view & detail -->
@foreach($table as $row)
<div class="modal fade bd-example-modal-xl" id="myModal{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">View #{{$row->so_code}}</h5>
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
                            <input type="text" name="sales_senior_id" class="form-control" value="{{ $row->so_sales_senior() }}"  readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="invoice_code">Sales</label>
                            <input type="text" class="form-control" id="sales_id"  value="{{ $row->so_sales() }}" readonly>
                          </div>
                        </div>

                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="type_transaction">Type Transaksi</label>
                            <input type="text" name="type_transaction" class="form-control" value="{{ $row->type_transaction }}"  readonly>
                          </div>
                          @if($row->status == 2 || $row->status == 4)
                          <div class="form-group col-md-6">
                            <label for="note">No Document</label>
                            <input type="text" class="form-control" value="{{ $row->no_ducument_ppn }}"  readonly>
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
                          <td>{{ $key->product_pack->price }}</td>
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
              <button type="button" class="btn btn-danger btn-sm btn-flat" data-dismiss="modal"><i class="fa fa-close mr-10"></i>Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(function(){
    $('#so_lanjutan').DataTable( {
        paging:   true,
        orderin: true,
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

    $('.js-select2').select2();
  });
</script>
@endpush