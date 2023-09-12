@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item">Purchase Order (PPB)</span>
  <a class="breadcrumb-item" href="{{ route('superuser.purchasing.purchase_order.step', $purchase_order->id) }}">{{ $purchase_order->code }}</a>
  <span class="breadcrumb-item active">Edit Product</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Product</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.purchasing.purchase_order.detail.update', [$purchase_order->id, $purchase_order_detail->id]) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="order_date">Order Date</label>
        <div class="col-md-4">
          <input type="date" class="form-control" id="order_date" name="order_date" value="{{ $purchase_order_detail->order_date ? date('Y-m-d', strtotime($purchase_order_detail->order_date)) : '' }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">SKU <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="sku" name="sku" data-placeholder="Select SKU">
            <option value="{{ $purchase_order_detail->product_id }}" selected>{{ $purchase_order_detail->product->code }}</option>
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
        <label class="col-md-3 col-form-label text-right" for="unit_price">Unit Price (RMB)</label>
        <div class="col-md-4">
          <input type="number" class="form-control" id="unit_price" name="unit_price" min="0" value="{{ $purchase_order_detail->unit_price }}" step="any">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="local_freight_cost">Local Freight Cost (RMB)</label>
        <div class="col-md-4">
          <input type="number" class="form-control" id="local_freight_cost" name="local_freight_cost" min="0" value="{{ $purchase_order_detail->local_freight_cost }}" step="any">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="komisi">Komisi (RMB)</label>
        <div class="col-md-4">
          <input type="number" class="form-control" id="komisi" name="komisi" min="0" value="{{ $purchase_order_detail->komisi }}" step="any">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="kurs">Kurs (IDR)</label>
        <div class="col-md-4">
          <input type="number" class="form-control" id="kurs" name="kurs" min="0" value="{{ $purchase_order_detail->kurs }}" step="any">
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
    
    $(".js-select2").select2({
      ajax: {
        url: '{{ route('superuser.sale.sales_order.search_sku') }}',
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
      minimumInputLength: 3,
      
    });

  })
</script>
@endpush
