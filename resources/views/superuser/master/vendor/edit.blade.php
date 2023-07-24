@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.vendor.index') }}">Vendor</a>
  <a class="breadcrumb-item" href="{{ route('superuser.master.vendor.show', $vendor->id) }}">{{ $vendor->id }}</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Vendor</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.vendor.update', $vendor) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $vendor->code }}" disabled>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name" value="{{ $vendor->name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Alamat</label>
        <div class="col-md-7">
          <textarea class="form-control" id="address" name="address">{{ $vendor->address }}</textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="email">Email</label>
        <div class="col-md-7">
          <input type="email" class="form-control" id="email" name="email" value="{{ $vendor->email }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="phone">Telepon</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="phone" name="phone" value="{{ $vendor->phone }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="owner_name">PIC</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="owner_name" name="owner_name" value="{{ $vendor->owner_name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="website">Website</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="website" name="website" value="{{ $vendor->website }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="description">Keterangan</label>
        <div class="col-md-7">
          <textarea class="form-control" id="description" name="description">{{ $vendor->description }}</textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="type">Type Vendor <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <div class="col-md-2 form-check form-check-inline">
            <input class="form-check-input" type="radio" name="type" id="type" value="1" @if($vendor->type == 1) checked @endif>
            <label class="form-check-label" for="type">Ekspedisi</label>
          </div>
          <div class="col-md-3 form-check form-check-inline">
            <input class="form-check-input" type="radio" name="type" id="type" value="0" @if($vendor->type == 0) checked @endif>
            <label class="form-check-label" for="type">Non Ekspedisi</label>
          </div>
          <div class="col-md-2 form-check form-check-inline">
            <input class="form-check-input" type="radio" name="type" id="type" value="2" @if($vendor->type == 2) checked @endif>
            <label class="form-check-label" for="type">Factory</label>
          </div>
          <div class="col-md-2 form-check form-check-inline">
            <input class="form-check-input" type="radio" name="type" id="type" value="3" @if($vendor->type == 3) checked @endif>
            <label class="form-check-label" for="type">Mitra</label>
          </div>
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.vendor.show', $vendor->id) }}">
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
@include('superuser.asset.plugin.select2-chain-indonesian-teritory')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()
  })
</script>
@endpush
