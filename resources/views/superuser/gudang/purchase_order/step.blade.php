@extends('superuser.app')

@section('content')

@if ( $purchase_order->status() == 'DRAFT' )
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Gudang</span>
    <span class="breadcrumb-item">Purchase Order (PO)</span>
    <span class="breadcrumb-item">New</span>
    <span class="breadcrumb-item active">Add Product</span>
  </nav>
@else
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Gudang</span>
    <span class="breadcrumb-item">Purchase Order (PO)</span>
    <span class="breadcrumb-item">{{ $purchase_order->code }}</span>
    <span class="breadcrumb-item active">Edit Product</span>
  </nav>
@endif

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
  <div class="block-header block-header-default">
    <h3 class="block-title">New Purchase Order (PO)</h3>
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
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->status() }}</div>
      </div>
    </div>

    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        @if ($purchase_order->status != $purchase_order::STATUS['DRAFT'])
        <a href="{{ route('superuser.gudang.purchase_order.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
        @endif
      </div>
      @if ($purchase_order->status == $purchase_order::STATUS['DRAFT'])
      <div class="col-md-6 text-right">
      <a href="{{ route('superuser.gudang.purchase_order.edit', $purchase_order->id) }}">
          <button type="button" class="btn bg-gd-sea border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.gudang.purchase_order.publish', $purchase_order->id) }}" class="btn bg-gd-leaf border-0 text-white" title="Publish">
          Publish <i class="fa fa-check ml-10"></i>
        </a>
      </div>
      @else
      <div class="col-md-6 text-right">
        @if($purchase_order->edit_marker == 0)
        <a href="{{ route('superuser.gudang.purchase_order.edit', $purchase_order->id) }}">
          <button type="button" class="btn bg-gd-sea border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
        @endif
        @if($purchase_order->edit_marker == 1)
        <a href="{{ route('superuser.gudang.purchase_order.save_modify', [$purchase_order->id, 'save']) }}" class="btn bg-gd-corporate border-0 text-white" title="Save">
          Save <i class="fa fa-check ml-10"></i>
        </a>
        @endif
        <a href="{{ route('superuser.gudang.purchase_order.save_modify', [$purchase_order->id, 'save-acc']) }}" class="btn bg-gd-leaf border-0 text-white" title="Acc">
          ACC <i class="fa fa-check ml-10"></i>
        </a>
      </div>
      @endif
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">{{ ( $purchase_order->status() == 'DRAFT' ? 'Add' : 'Edit' ) }} Product </h3>
    
    <button type="button" class="btn btn-outline-info mr-10 min-width-125 pull-right" data-toggle="modal" data-target="#modal-manage">Import</button>
    
    <a href="#">
      <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Create</button>
    </a>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">SKU</th>
          <th class="text-center">Qty</th>
          <th class="text-center">Unit Price (RMB)</th>
          <th class="text-center">Local Freight Cost (RMB)</th>
          <th class="text-center">Komisi (IDR)</th>
          <th class="text-center">Total Price (RMB)</th>
          <th class="text-center">Kurs (RMB)</th>
          <th class="text-center">Total Price (IDR)</th>
          <th class="text-center">Unit Price (IDR)</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        
      </tbody>
      <!-- <tfoot>
        <tr>
          <th colspan="5" style="text-align:right">Total RMB:</th>
          <th colspan="3"></th>
          <th style="text-align:right">Total IDR:</th>
          <th></th>
        </tr>
      </tfoot> -->
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
    $('#datatable').DataTable()

    $('a.img-lightbox').magnificPopup({
      type: 'image',
      closeOnContentClick: true,
    });
  });
</script>
@endpush
