@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sale_return.index') }}">Retur</a>
  <span class="breadcrumb-item active">Show</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Retur</h3>
  </div>
  <div class="block-content">
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="code">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sale_return->code }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="delivery_order">DO</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sale_return->invoice->code }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="warehouse_reparation">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sale_return->warehouse->name ?? '-' }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="retur_date">Return Date</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sale_return->retur_date ? date('d/m/Y', strtotime($sale_return->retur_date)) : '-' }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="status">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sale_return->status() }}</div>
      </div>
    </div>
    <div class="form-group row pt-30">
      <div class="col-md-6">
        <a href="{{ route('superuser.penjualan.sale_return.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
    </div>
  </div>
</div>
<div class="block">
  <div class="block-header">
    <h3 class="block-title">Product</h3>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
            <th class="text-center">#</th>
            <th class="text-center">Product</th> <!-- gabungan code - name -->
            <th class="text-center">Packaging</th> <!-- kemasan -->
            <th class="text-center">Quantity</th>
            @if ($sale_return->status() == 'DRAFT')
            <th class="text-center">Price</th>
            @endif
            <th class="text-center">Note</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($sale_return->sale_return_details as $detail)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $detail->product->code ?? '-' }} - {{ $detail->product->name ?? '-' }}</td>
            <td>{{ $detail->product->packaging->pack_name ?? '-' }}</td>
            <td>{{ $detail->qty }}</td>
            @if ($sale_return->status() == 'DRAFT')
            <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
            @endif
            <td>{{ $detail->note }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.datatables')

@push('scripts')
<script>
  $(document).ready(function () {
    $('#datatable').DataTable({})

  })
</script>
@endpush
