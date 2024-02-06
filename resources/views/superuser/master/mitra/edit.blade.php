@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.mitra.index') }}">Mitra</a>
  <a class="breadcrumb-item" href="{{ route('superuser.master.mitra.show', $mitra->id) }}">{{ $mitra->id }}</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Vendor</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.mitra.update', $mitra) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name" value="{{ $mitra->name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Alamat</label>
        <div class="col-md-7">
          <textarea class="form-control" id="address" name="address">{{ $mitra->alamat }}</textarea>
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
    $('.js-select2').select2()
  })
</script>
@endpush
