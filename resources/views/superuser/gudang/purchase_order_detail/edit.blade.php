@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item">Purchase Order (PPB)</span>
  <a class="breadcrumb-item" href="{{ route('superuser.gudang.purchase_order.step', $purchase_order->id) }}">{{ $purchase_order->code }}</a>
  <span class="breadcrumb-item active">Edit Product</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Product</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.gudang.purchase_order.detail.update', [$purchase_order->id, $purchase_order_detail->id]) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Product <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control product_pack" id="product_packaging_id" name="product_packaging_id" data-placeholder="Select SKU">
            <option value="{{ $purchase_order_detail->product_packaging_id }}" selected>{{ $purchase_order_detail->product_pack->code }} - {{$purchase_order_detail->product_pack->name}}</option>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="quantity">Qty</label>
        <div class="col-md-4">
          <input type="number" class="form-control" id="quantity" name="quantity" min="0" value="{{ $purchase_order_detail->quantity }}" step="1">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="packaging_id">Kemasan</label>
        <div class="col-md-4">
          <select class="js-select2 form-control kemasan" id="packaging_id" name="packaging_id" data-placeholder="Pilih Kemasan"> 
            <option value="{{ $purchase_order_detail->product_pack->packaging->id }}" selected>{{ $purchase_order_detail->product_pack->packaging->pack_name}}</option>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="note_produksi">Note</label>
        <div class="col-md-4">
          <input type="text" class="form-control" id="note_produksi" name="note_produksi" value="{{ $purchase_order_detail->note_produksi }}" step="any">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="komisi">Customer</label>
        <div class="col-md-4">
          <input type="text" class="form-control" id="note_repack" name="note_repack" value="{{ $purchase_order_detail->note_repack ?? '-'}}" step="any">
        </div>
      </div>
      

      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="javascript:history.back()">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white">
            Submit <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.fileinput')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {

    $(".js-select2").select2({})
    
    $(".product_pack").select2({
      ajax: {
        url: '{{ route('superuser.gudang.purchase_order.search_sku') }}',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term,
            _token: "{{csrf_token()}}"
          };
        },
        cache: true
      },
      minimumInputLength: 3
    });

    $(".kemasan").select2({
      ajax: {
        url: '{{ route('superuser.gudang.purchase_order.search_kemasan') }}',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term,
            _token: "{{csrf_token()}}"
          };
        },
        cache: true
      },
      minimumInputLength: 3
    });

  })
</script>
@endpush
