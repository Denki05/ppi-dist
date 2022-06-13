@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.store.index') }}">Store</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Store</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.store.update', $store) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      {{--<div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $store->code }}">
        </div>
      </div>--}}
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Store Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name" value="{{ $store->name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="customer">Customer <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="customer" name="customer" data-placeholder="Select Customer">
            <option></option>
            @foreach($customers as $customer)
            <option value="{{ $customer->id }}" {{ ($customer->id == $customer->customer_id) ? 'selected' : '' }}>{{ $customer->name }}</option>
            @endforeach
          </select>
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

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
@endpush
