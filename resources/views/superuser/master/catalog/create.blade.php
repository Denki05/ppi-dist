@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.catalog.index') }}">Catalog</a>
  <span class="breadcrumb-item active">New</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">New Catalog Product</h3>
  </div>
  <div class="block-content">
  <form class="ajax" data-action="{{ route('superuser.master.catalog.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Catalog Code<span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="note">Brand</label>
        <div class="col-md-7">
          <select class="form-control js-select2" name="brand">
            <option value="">Select Brand</option>
            @foreach($brand as $key)
            <option value="{{$key->brand_name}}">{{$key->brand_name}}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="note">Note</label>
        <div class="col-md-7">
          <textarea class="form-control" id="note" name="note"></textarea>
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
            Save <i class="fa fa-arrow-right ml-10"></i>
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
