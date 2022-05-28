@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <a class="breadcrumb-item" href="{{ route('superuser.boilerplate.index') }}">Boilerplate</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Boilerplate</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.boilerplate.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="form-group row">
        <div class="col-md-12">
          <div class="form-material">
            <input type="text" class="form-control" id="text" name="text" placeholder="Lorem ipsum dolor sit amet.">
            <label for="text">Text</label>
          </div>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-12">
          <div class="form-material">
            <textarea class="form-control" id="textarea" name="textarea" rows="3" placeholder="Lorem ipsum dolor sit amet."></textarea>
            <label for="textarea">Textarea</label>
          </div>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-6">
          <div class="form-material">
            <select class="js-select2 form-control" id="select2" name="select2" data-placeholder="Select">
              <option></option>
              <option value="html">HTML</option>
              <option value="css">CSS</option>
              <option value="javascript">JavaScript</option>
              <option value="php">PHP</option>
            </select>
            <label for="select2">Select2</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-material">
            <select class="js-select2 form-control" id="select2-multiple" name="select2-multiple[]" data-placeholder="Select multiple" multiple>
              <option></option>
              <option value="html">HTML</option>
              <option value="css">CSS</option>
              <option value="javascript">JavaScript</option>
              <option value="php">PHP</option>
            </select>
            <label for="select2-multiple">Select2 Multiple</label>
          </div>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-6">
          <div class="form-material">
            <input type="file" id="image" name="image" data-max-file-size="2000" accept="image/png, image/jpeg">
            <label for="image">Image</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-material">
            <input type="file" id="image-multiple" name="image-multiple[]" data-max-file-size="2000" accept="image/png, image/jpeg" multiple>
            <label for="image-multiple">Image Multiple</label>
          </div>
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.boilerplate.index') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
          <button type="reset" class="btn bg-gd-sun border-0 text-white">
            <i class="fa fa-repeat mr-10"></i> Reset
          </button>
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
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
$(document).ready(function () {
  $('.js-select2').select2();

  $('#image').fileinput({
    theme: 'explorer-fa',
    browseOnZoneClick: true,
    showCancel: false,
    showClose: false,
    showUpload: false,
    browseLabel: '',
    removeLabel: '',
  });

  $('#image-multiple').fileinput({
    theme: 'explorer-fa',
    browseOnZoneClick: true,
    showCancel: false,
    showClose: false,
    showUpload: false,
    browseLabel: '',
    removeLabel: '',
  });

  $('button[type="reset"]').click(function () {
    $('.js-select2').val('').trigger('change');
  });
})
</script>
@endpush
