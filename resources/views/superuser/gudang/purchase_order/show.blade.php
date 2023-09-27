@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item">Purchase Order (PO)</span>
  <span class="breadcrumb-item">{{ $purchase_order->code }}</span>
</nav>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Purchase Order (PPB)</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">PO Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->warehouse->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">ETD</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ \Carbon\Carbon::parse($purchase_order->etd)->format('d-m-Y')}}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Note</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->note }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->status() }}</div>
      </div>
    </div>
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="javascript:history.back()">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Product ({{ $purchase_order->purchase_order_detail->count() }})</h3>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped ">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Nama Varian</th>
          <th class="text-center">Kode</th>
          <th class="text-center">Qty (KG)</th>
          <th class="text-center">Packaging</th>
          <th class="text-center">Notes</th>
          <th class="text-center">Customer</th>
        </tr>
      </thead>
      <tbody>
        @foreach($purchase_order->purchase_order_detail as $detail)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $detail->product_pack->name }}</td>
            <td class="text-center">{{ $detail->product_pack->code }}</td>
            <td class="text-center">{{ $detail->quantity }}</td>
            <td class="text-center">{{ $detail->product_pack->kemasan()->pack_name }}</td>
            <td class="text-center">{{ $detail->note_produksi ?? '-' }}</td>
            <td class="text-center">{{ $detail->note_repack ?? '-' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable({
      
    })

    $('a.img-lightbox').magnificPopup({
    type: 'image',
    closeOnContentClick: true,
  });
  })
</script>
@endpush
