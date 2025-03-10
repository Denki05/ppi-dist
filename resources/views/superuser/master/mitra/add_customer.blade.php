@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.mitra.index') }}">Mitra</a>
  <span class="breadcrumb-item active">Add Customer</span>
</nav>
<div id="alert-block"></div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Add Customer Mitra</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.mitra.store_customer') }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="mitra_id" value="{{ $mitra->id }}">
      @csrf
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="customer">Customer <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="customer" name="customer[]" multiple data-placeholder="Select Customers">
            @foreach($customers as $customer)
              <option value="{{ $customer->id }}" 
                @if($mitra->mitra_detail->contains('customer_other_address_id', $customer->id)) selected @endif>
                {{ $customer->name }} {{ $customer->text_kota }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
      
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.mitra.index') }}">
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
<script>
  $(document).ready(function () {
    $('.js-select2').select2({
        placeholder: "Select Customers",
        allowClear: true
    });
  });
</script>
@endpush