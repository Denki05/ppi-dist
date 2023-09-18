@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item">Receiving</span>
  <span class="breadcrumb-item">{{ $receiving->code }}</span>
  <span class="breadcrumb-item active">Edit Detail</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Detail</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.gudang.receiving.detail.update', [$receiving->id, $receiving_detail->id]) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="ppb">PPB <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" value="{{ $receiving_detail->purchase_order->code }}" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="ppb">SKU <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" value="{{ $receiving_detail->product_pack->code }}" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="ppb">SKU <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" value="{{ $receiving_detail->product_pack->name }}" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="ppb">Qty</label>
        <div class="col-md-4">
          <input type="text" class="form-control" value="{{ $receiving_detail->quantity }}" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="delivery_cost">Delivery Cost</label>
        <div class="col-md-4">
          <input type="number" class="form-control" id="delivery_cost" name="delivery_cost" min="0" value="{{ $receiving_detail->delivery_cost }}" step="any">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="no_container">No Container</label>
        <div class="col-md-4">
        <input type="text" class="form-control" id="no_container" name="no_container" value="{{ $receiving_detail->no_container }}" step="any">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="description">Note</label>
        <div class="col-md-4">
          <textarea class="form-control" id="description" name="description">{{ $receiving_detail->description }}</textarea>
        </div>
      </div>

      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.gudang.receiving.step', $receiving->id) }}">
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

@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
@endpush