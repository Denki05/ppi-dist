@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.product_category.index') }}">Product Category</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Product Category</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.product_category.store') }}" data-type="POST" enctype="multipart/form-data">
      {{-- <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)">
        </div>
      </div> --}}
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name">
        </div>
      </div>
      {{-- <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="type">Type <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="type" name="type" data-placeholder="Select Type">
            <option></option>
            @foreach($product_types as $type)
            <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
          </select>
        </div>
      </div> --}}
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="description">Description</label>
        <div class="col-md-7">
          <textarea class="form-control" id="description" name="description"></textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="image_header_list">Image Header List</label>
        <div class="col-md-7">
          <input type="file" class="form-control" id="image_header_list" name="image_header_list" accept="image/*">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="image_header_price">Image Header Price</label>
        <div class="col-md-7">
          <input type="file" class="form-control" id="image_header_price" name="image_header_price" accept="image/*">
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.product_category.index') }}">
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