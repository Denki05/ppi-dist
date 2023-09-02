@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.sub_brand_reference.index') }}">Searah</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Searah</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.sub_brand_reference.store') }}" data-type="POST" enctype="multipart/form-data">
      {{-- <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)">
        </div>
      </div> --}}
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="brand_reference">Brand Fragrantica <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="brand_reference" name="brand_reference" data-placeholder="Select Brand Reference">
            <option></option>
            @foreach($brand_references as $brand_reference)
            <option value="{{ $brand_reference->id }}">{{$brand_reference->code}} - {{ $brand_reference->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="link">Link URL Fragrantica</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="link" name="link">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="description">Description (Top, Body, Base)</label>
        <div class="col-md-7">
          <textarea class="form-control" id="description" name="description"></textarea>
        </div>
      </div>
      
      {{--<div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="description">Image Bottel</label>
        <div class="col-md-7">
          <input type="file" id="image_botol" name="image_botol" data-max-file-size="2000" accept="image/png, image/jpeg">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="image_table_botol">Image Main Accords</label>
        <div class="col-md-7">
          <input type="file" id="image_table_botol" name="image_table_botol" data-max-file-size="2000" accept="image/png, image/jpeg">
        </div>
      </div>--}}

      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.sub_brand_reference.index') }}">
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
@include('superuser.asset.plugin.fileinput')

@push('scripts')
<script src="{{ asset('public/utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()

    $('#image_botol').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
    });

    $('#image_table_botol').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
    });
  })
</script>
@endpush
