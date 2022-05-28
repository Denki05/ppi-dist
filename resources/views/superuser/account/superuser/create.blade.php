@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <a class="breadcrumb-item" href="{{ route('superuser.account.superuser.index') }}">Superuser</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Superuser</h3>
  </div>
  <form class="ajax" data-action="{{ route('superuser.account.superuser.store') }}" data-type="POST" enctype="multipart/form-data">
    <div class="block-content block-content-full">
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Username <span class="text-danger">*</span></label>
        <div class="col-lg-7">
          <input type="text" class="form-control" name="username">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Email <span class="text-danger">*</span></label>
        <div class="col-lg-7">
          <input type="text" class="form-control" name="email">
        </div>
      </div>
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
      <hr class="my-20">
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Name</label>
        <div class="col-lg-7">
          <input type="text" class="form-control" name="name">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Image</label>
        <div class="col-lg-7">
          <input type="file" id="image" name="image" data-max-file-size="2000" accept="image/png, image/jpeg">
        </div>
      </div>
    </div>
    <div class="block-content block-content-full block-content-sm bg-body-light font-size-sm text-right">
      <a href="{{ route('superuser.account.superuser.index') }}">
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

@include('superuser.asset.plugin.fileinput')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
$(document).ready(function () {
  $('#image').fileinput({
    theme: 'explorer-fa',
    browseOnZoneClick: true,
    showCancel: false,
    showClose: false,
    showUpload: false,
    browseLabel: '',
    removeLabel: '',
    initialPreview: $('#image').data('src'),
    initialPreviewAsData: true,
    fileActionSettings: {
      showDrag: false,
      showRemove: false
    },
  });
})
</script>
@endpush