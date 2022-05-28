@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <a class="breadcrumb-item" href="{{ route('superuser.account.sales_person.index') }}">Sales Person</a>
  <a class="breadcrumb-item" href="{{ route('superuser.account.sales_person.show', $sales_person->id) }}">{{ $sales_person->id }}</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Sales Person</h3>
  </div>
  <form class="ajax" data-action="{{ route('superuser.account.sales_person.update', $sales_person->id) }}" data-type="POST" enctype="multipart/form-data">
    <input type="hidden" name="_method" value="PUT">
    <div class="block-content block-content-full">
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Username <span class="text-danger">*</span></label>
        <div class="col-lg-7">
          @role('SuperAdmin', 'superuser')
          <input type="text" class="form-control" name="username" value="{{ $sales_person->username }}">
          @else
          <div class="form-control-plaintext">{{ $sales_person->username }}</div>
          @endrole
        </div>
      </div>
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Email <span class="text-danger">*</span></label>
        <div class="col-lg-7">
          @role('SuperAdmin', 'superuser')
          <input type="text" class="form-control" name="email" value="{{ $sales_person->email }}">
          @else
          <div class="form-control-plaintext">{{ $sales_person->email }}</div>
          @endrole
        </div>
      </div>
      @role('SuperAdmin', 'superuser')
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Password <span class="text-danger">*</span></label>
        <div class="col-lg-7">
          <input type="password" class="form-control" name="password">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Password Confirmation <span class="text-danger">*</span></label>
        <div class="col-lg-7">
          <input type="password" class="form-control" name="password_confirmation">
        </div>
      </div>
      @endrole
      <hr class="my-20">
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Name <span class="text-danger">*</span></label>
        <div class="col-lg-7">
          <input type="text" class="form-control" name="name" value="{{ $sales_person->name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Phone</label>
        <div class="col-lg-7">
          <input type="text" class="form-control" name="phone" value="{{ $sales_person->phone }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Address <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <textarea class="form-control" id="address" name="address">{{ $sales_person->address }}</textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Provinsi</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="provinsi" name="provinsi" data-placeholder="Select Provinsi" data-value="{{ $sales_person->provinsi }}">
            <option></option>
          </select>
          <input type="hidden" name="text_provinsi">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kota</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kota" name="kota" data-placeholder="Select Kota" data-value="{{ $sales_person->kota }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kota">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kecamatan</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kecamatan" name="kecamatan" data-placeholder="Select Kecamatan" data-value="{{ $sales_person->kecamatan }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kecamatan">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kelurahan</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kelurahan" name="kelurahan" data-placeholder="Select Kelurahan" data-value="{{ $sales_person->kelurahan }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kelurahan">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="zipcode">Zipcode</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="zipcode" name="zipcode" value="{{ $sales_person->zipcode }}">
        </div>
      </div>
    </div>
    <div class="block-content block-content-full block-content-sm bg-body-light font-size-sm text-right">
      <a href="{{ route('superuser.account.sales_person.show', $sales_person->id) }}"">
        <button type="button" class="btn bg-gd-cherry border-0 text-white">
          <i class="fa fa-arrow-left mr-10"></i> Back
        </button>
      </a>
      <button type="submit" class="btn bg-gd-corporate border-0 text-white">
        Submit <i class="fa fa-arrow-right ml-10"></i>
      </button>
    </div>
  </form>
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