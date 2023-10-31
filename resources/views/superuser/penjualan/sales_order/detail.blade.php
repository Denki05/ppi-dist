@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}">Sales Order {{ $step_txt }}</a>
  <span class="breadcrumb-item active">Edit Sales Order</span>
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
    <form>
      @csrf
      <input type="hidden" name="id" value="{{$result->id}}">
      <div class="row">
        <div class="col-12">
          <h5>#Data Pesanan</h5>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="name">Sales Senior</label>
            <div class="col-md-8">
              <input type="text" class="form-control" value="{{$result->so_sales_senior()->scalar ?? ''}}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="name">Sales </label>
            <div class="col-md-8">
              <input type="text" class="form-control" value="{{$result->so_sales()->scalar ?? ''}}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Origin warehouse</label>
            <div class="col-md-8">
              <input type="text" class="form-control" value="{{$result->origin_warehouse->name ?? ''}}" readonly>
            </div>
          </div>
          
          @if($result->so_for == 1)
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Store</label>
            <div class="col-md-8">
              <input type="text" class="form-control" value="{{$result->customer->name ?? ''}}" readonly>
            </div>
          </div>
          @else
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Destination warehouse</label>
            <div class="col-md-8">
              <input type="text" class="form-control" value="{{$result->customer_gudang->name ?? ''}}" readonly>
            </div>
          </div>
          @endif
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Address</label>
            <div class="col-md-8">
              @if($result->so_for == 1)
              <textarea type="text" name="address" class="form-control" readonly>{{$result->customer->address ?? ''}}</textarea>
              @else
              <textarea type="text" name="address" class="form-control" readonly>{{$result->warehouse->address ?? ''}}</textarea>
              @endif
            </div>
          </div>
          @if($result->member->member_default == 1)
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Member</label>
              <div class="col-md-8">
                <input type="text" class="form-control" value="{{$result->customer->name ?? ''}} {{ $result->member->text_kota }}" readonly>
              </div>
            </div>
          @else
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Member</label>
              <div class="col-md-8">
                <input type="text" class="form-control" value="{{$result->member->name ?? ''}} {{ $result->member->text_kota }}" readonly>
              </div>
            </div>
          @endif
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Transaction</label>
            <div class="col-md-8">
              <input type="text" class="form-control" value="{{$result->type_transaction ?? ''}}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Ekspedisi</label>
            <div class="col-md-8">
              <input type="text" class="form-control" value="{{$result->ekspedisi->name ?? ''}}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Note</label>
            <div class="col-md-8">
              <textarea class="form-control" readonly rows="3">{{$result->note}}</textarea>
            </div>
          </div>
        </div>
      </div>
    </form>
    
  </div>
</div>
<div class="block">
  <div class="block-content">
    <div class="row">
      <div class="col-12">
        <h5>#Detail Item</h5>
        <div class="tabel-responsive">
          <table class="table table-striped">
            <thead>
              <th>Code</th>
              <th>Produk</th>
              <th>Qty</th>
              <th>Packaging</th>
            </thead>
            <tbody>

              @if(count($result->so_detail) > 0)
                @foreach($result->so_detail as $index => $row)
                  <tr>
                    <td>{{$row->product_pack->code ?? ''}}</td>
                    <td>{{$row->product_pack->name ?? ''}}</td>
                    <td>
                      @if($row->status <> 4)
                        {{$row->qty ?? '0'}}
                      @else
                        {{$row->qty_worked ?? '0'}}
                      @endif
                    </td>
                    <td>{{$row->product_pack->kemasan()->pack_name ?? ''}}</td>
                  </tr>
                @endforeach
              @else
              <tr>
                <td colspan="5" class="text-center">Data tidak ditemukan</td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <a href="javascript:history.go(-1)">
            <button type="button" class="btn btn-warning  btn-md text-white">
              <i class="fa fa-arrow-left"></i> Back
            </button>
        </a>
      </div>
    </div>
  </div>
</div>


@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
@endpush