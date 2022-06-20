@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Customer</a>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.show', $customer->id) }}">{{ $customer->id }}</a>
  <span class="breadcrumb-item">Other Address</span>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Other Address</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.customer.other_address.update', [$customer->id, $other_address->id]) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="label">Label <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="label" name="label" value="{{ $other_address->label }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="contact_person">Contact Person</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ $other_address->contact_person }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="phone">Phone</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="phone" name="phone" value="{{ $other_address->phone }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Address <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <textarea class="form-control" id="address" name="address">{{ $other_address->address }}</textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">GPS Coordinate</label>
        <div class="col-md-3">
          <input type="text" class="form-control" id="gps_latitude" name="gps_latitude" placeholder="Latitude" value="{{ $other_address->gps_latitude }}">
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-3">
          <input type="text" class="form-control" id="gps_longitude" name="gps_longitude" placeholder="Longitude" value="{{ $other_address->gps_longitude }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Provinsi</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="provinsi" name="provinsi" data-placeholder="Select Provinsi" data-value="{{ $other_address->provinsi }}">
            <option></option>
          </select>
          <input type="hidden" name="text_provinsi">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kota</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kota" name="kota" data-placeholder="Select Kota" data-value="{{ $other_address->kota }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kota">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kecamatan</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kecamatan" name="kecamatan" data-placeholder="Select Kecamatan" data-value="{{ $other_address->kecamatan }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kecamatan">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kelurahan</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kelurahan" name="kelurahan" data-placeholder="Select Kelurahan" data-value="{{ $other_address->kelurahan }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kelurahan">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="zipcode">Zipcode</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="zipcode" name="zipcode" value="{{ $other_address->zipcode }}">
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.customer.show', $customer->id) }}">
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
<div id="alert-block"></div>
@endsection

@include('superuser.asset.plugin.fileinput')
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
