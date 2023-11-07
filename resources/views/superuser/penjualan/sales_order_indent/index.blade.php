@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <span class="breadcrumb-item">Sales Order</span>
  <span class="breadcrumb-item active">Indent</span>
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
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Created at</th>
          <th class="text-center">Nota SO</th>
          <th class="text-center">Customer</th>
          <th class="text-center">Type</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($sales_order as $key)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{ $key->created_at }}</td>
                <td>{{ $key->code }}</td>
                <td>{{ $key->member->name }} {{ $key->member->text_kota }}</td>
                <td>{{ $key->type_transaction }}</td>
                <td>
                    <!-- <button type="button" class="btn btn-info" data-toggle="modal" data-target=".bd-example-modal-lg"><i class="fa fa-eye" aria-hidden="true"></i> show</button> -->
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#myModal{{$key->id}}"><i class="fa fa-eye" aria-hidden="true"></i> View</button>
                    <a class="btn btn-danger" href="javascript:deleteConfirmation('{{ route('superuser.penjualan.sales_order_indent.destroy', $key->id) }}')" role="button"><i class="fa fa-trash" aria-hidden="true"></i> Hapus</a>
                </td>
            </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- modal show -->
@foreach($sales_order as $key)
<div class="modal fade bd-example-modal-lg" id="myModal{{$key->id}}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">New message</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
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
                          <input type="text" name="invoice_date" class="form-control" value="{{ date('d-m-Y',strtotime($key->created_at)) }}" readonly>
                        </div>
                        <div class="form-group col-md-6">
                          <label for="invoice_code">Nomer Nota</label>
                          <input type="text" class="form-control" id="invoice_code" value="{{ $key->so_code }}" readonly>
                        </div>
                      </div>

                      <div class="form-row">
                        <div class="form-group col-md-6">
                          <label for="type_transaction">Type Transaksi</label>
                          <input type="text" name="type_transaction" class="form-control" value="{{$key->type_transaction}}" readonly>
                        </div>
                        <div class="form-group col-md-6">
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
                      <th>Harga</th>
                      <th>Free</th>
                      <th>Kemasan</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($key->so_detail as $index => $detail)
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $detail->product_pack->code }} - {{ $detail->product_pack->name }}</td>
                        <td>{{ $detail->qty }}</td>
                        <td>{{ $detail->product_pack->price }}</td>
                        <td>
                          @if($detail->free_product == 1)
                          YES
                          @elseif($detail->free_product == 0)
                          NO
                          @endif
                        </td>
                        <td>{{ $detail->product_pack->kemasan()->pack_name }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
        </div>
        <div class="modal-footer">
            <a class="btn btn-success" href="#" role="button"><i class="fa fa-check" aria-hidden="true"></i> Proses</a>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
    </div>
  </div>
</div>
@endforeach

@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
    var table = $('#datatable').DataTable({});
</script>
@endpush