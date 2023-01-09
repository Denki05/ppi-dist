@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.brand_lokal.index') }}">Brand PPI</a>
  <a class="breadcrumb-item" href="{{ route('superuser.master.brand_lokal.show', $brand_lokal->id) }}">{{ $brand_lokal->id }}</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Brand PPI</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.brand_lokal.update', $brand_lokal) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="brand_name">Brand Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="brand_name" name="brand_name" value="{{ $brand_lokal->brand_name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="category">Category</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="category" name="category" value="{{ $brand_lokal->category }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="type">Type</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="type" name="type" value="{{ $brand_lokal->type }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="packaging">Packaging</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="packaging" name="packaging" data-placeholder="Select Packaging" value="{{ $brand_lokal->packaging }}">
            <option value="">==Select Packaging==</option>
            <option value="100gr">100 gr</option>
            <option value="500gr">500 gr</option>
            <option value="2500gr">2.5 kg</option>
            <option value="5000gr">5000 gr / 5 kg</option>
            <option value="25kg">25 kg</option>
          </select>
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.brand_lokal.show', $brand_lokal->id) }}">
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
