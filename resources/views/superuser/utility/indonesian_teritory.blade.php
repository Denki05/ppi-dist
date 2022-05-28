@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item active">Indonesian Teritory</span>
</nav>
<div class="block">
  <div class="block-content">
    <span>Using External API</span>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right">Provinsi</label>
      <div class="col-md-7">
        <select class="js-select2 form-control" id="provinsi" name="provinsi" data-placeholder="Select Provinsi">
          <option></option>
        </select>
        <input type="hidden" name="text_provinsi">
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right">Kota</label>
      <div class="col-md-7">
        <select class="js-select2 form-control" id="kota" name="kota" data-placeholder="Select Kota">
          <option></option>
        </select>
        <input type="hidden" name="text_kota">
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right">Kecamatan</label>
      <div class="col-md-7">
        <select class="js-select2 form-control" id="kecamatan" name="kecamatan" data-placeholder="Select Kecamatan">
          <option></option>
        </select>
        <input type="hidden" name="text_kecamatan">
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right">Kelurahan</label>
      <div class="col-md-7">
        <select class="js-select2 form-control" id="kelurahan" name="kelurahan" data-placeholder="Select Kelurahan">
          <option></option>
        </select>
        <input type="hidden" name="text_kelurahan">
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.select2-chain-indonesian-teritory')

@push('scripts')
<script>
  $(document).ready(function () {
    $('.js-select2').select2()
  })
</script>
@endpush