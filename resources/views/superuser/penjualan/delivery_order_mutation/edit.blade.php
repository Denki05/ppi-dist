<?php
  $sub_total = 0;
?>
@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.delivery_order_mutation.index') }}">Delivery Order Mutation</a>
  <span class="breadcrumb-item active">Edit Delivery Order Mutation</span>
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
    <div class="row">
      <div class="col-12">
        <h5>#Data Pesanan</h5>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right" for="name">Origin Warehouse</label>
          <div class="col-md-8">
            <input type="text" class="form-control" readonly value="{{$result->origin_warehouse->name ?? ''}}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right" for="name">Destination Warehouse </label>
          <div class="col-md-8">
            <input type="text" class="form-control" readonly value="{{$result->destination_warehouse->name ?? ''}}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Address</label>
          <div class="col-md-8">
            <input type="text" name="address" class="form-control" value="{{$result->address}}" readonly>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="block">
  <div class="block-content">
    <div class="row mb-20">
      <div class="col-12 text-center">
        <a href="{{route('superuser.penjualan.delivery_order_mutation.select_so',$result->id)}}" class="btn btn-info">Select SO</a>
      </div>
    </div>
  </div>
</div>
<div class="block">
  <div class="block-content">
    <div class="row">
      <div class="col-12">
        <h5>#Item</h5>
        <div class="table table-responsive">
          <table class="table table-striped table-bordered">
            <thead>
              <th>Code</th>
              <th>Product</th>
              <th>Packaging</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Sub Total</th>
              <th>Note</th>
              <th>Aksi</th>
            </thead>
            @if(count($result->do_mutation_item) == 0)
              <tr><td colspan="8" align="center">Data tidak ditemukan</td></tr>
            @endif
            @foreach($result->do_mutation_item as $index => $row)
              <?php
                $sub_total += floatval($row->qty * $row->price) ?? 0; 
              ?>
              <tr>
                <td>{{$row->product_pack->code ?? ''}}</td>
                <td>{{$row->product_pack->name ?? ''}}</td>
                <td>{{$row->packaging->pack_name ?? ''}}</td>
                <td>{{$row->qty ?? ''}}</td>
                <td>{{$row->price ?? ''}}</td>
                <td>{{$sub_total}}</td>
                <td>{{$row->note ?? ''}}</td>
                <td>
                  <button class="btn btn-danger btn-sm btn-flat btn-delete" data-id="{{$row->id}}"><i class="fa fa-trash"></i></button>
                </td>
              </tr>
            @endforeach
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="block">
  <div class="block-content">
    <div class="row mb-20">
      <div class="col-12">
        <a href="{{route('superuser.penjualan.delivery_order_mutation.index')}}" class="btn btn-warning" ><i class="fa fa-arrow-left"></i> Back</a>
        <a href="#" class="btn btn-success btn-acc" data-id="{{$result->id}}"><i class="fa fa-send"></i> Sent</a>
      </div>
    </div>
  </div>
</div>
<form id="frmDestroyItem" action="{{route('superuser.penjualan.delivery_order_mutation.destroy_item')}}" method="post">
  @csrf
  <input type="hidden" name="id">
</form>
<form id="frmAcc" action="{{route('superuser.penjualan.delivery_order_mutation.sent')}}" method="post">
  @csrf
  <input type="hidden" name="id">
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script>
  $(function(){
    $('button[type="submit"]').removeAttr('disabled');

    $('.js-select2').select2();

    $(document).on('click','.btn-delete',function(){
      let id = $(this).data('id');
      $('#frmDestroyItem').find('input[name="id"]').val(id);
      if(confirm("Yakin ?")){
        $('#frmDestroyItem').submit();
      }
    })

    $(document).on('click','.btn-acc',function(){
      let id = $(this).data('id');
      $('#frmAcc').find('input[name="id"]').val(id);
      if(confirm("Yakin ?")){
        $('#frmAcc').submit();
      }
    })

  })
</script>
@endpush