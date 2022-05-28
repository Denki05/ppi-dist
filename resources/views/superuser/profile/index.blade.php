@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item active">Profile</span>
</nav>
<div id="alert-block"></div>
<form class="ajax" data-action="{{ route('superuser.profile.update') }}" data-type="POST" enctype="multipart/form-data">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">
        <i class="fa fa-pencil fa-fw mr-5 text-muted"></i> Personal Details
      </h3>
    </div>
    <div class="block-content">
      <div class="row items-push">
        <div class="col-lg-3">
          <p class="text-muted">
            Your personal information is never shown to other users.
          </p>
        </div>
        <div class="col-lg-7 offset-lg-1">
          <div class="form-group">
            <div class="form-material">
              <label for="name">Name</label>
              <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name.." value="{{ $superuser->name }}">
            </div>
          </div>
          <div class="form-group">
            <div class="form-material">
              <input type="file" id="image" name="image" data-max-file-size="2000" accept="image/png, image/jpeg" data-src="{{ $superuser->img }}">
              <label for="image">Image</label>
            </div>
          </div>
          <div class="form-group">
            <div class="form-material">
              <label for="email">Username</label>
              <input type="text" class="form-control" value="{{ $superuser->username }}" disabled>
            </div>
          </div>
          <div class="form-group">
            <div class="form-material">
              <label for="email">Email Address</label>
              <input type="email" class="form-control" value="{{ $superuser->email }}" disabled>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">
        <i class="fa fa-pencil fa-fw mr-5 text-muted"></i> Password
      </h3>
    </div>
    <div class="block-content">
      <div class="row items-push">
        <div class="col-lg-3 text-muted">
          <p>
            Changing your sign in password is an easy way to keep your account secure.
          </p>
          <p class="mb-0">Rules for new password :</p>
          <ul>
            <li>minimum eight characters</li>
            <li>maximum sixteen characters</li>
            {{-- <li>at least one letter and one number</li> --}}
          </ul>  
        </div>
        <div class="col-lg-7 offset-lg-1">
          <div class="form-group">
            <div class="form-material">
              <label for="current_password">Current Password</label>
              <input type="password" class="form-control form-control-lg" id="current_password" name="current_password">
            </div>
          </div>
          <div class="form-group">
            <div class="form-material">
              <label for="new_password">New Password</label>
              <input type="password" class="form-control form-control-lg" id="new_password" name="new_password">
            </div>
          </div>
          <div class="form-group">
            <div class="form-material">
              <label for="new_password_confirmation">Confirm New Password</label>
              <input type="password" class="form-control form-control-lg" id="new_password_confirmation" name="new_password_confirmation">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-md-6">
      <a href="{{ route('superuser.index') }}">
        <button type="button" class="btn bg-gd-cherry border-0 text-white">
          <i class="fa fa-arrow-left mr-10"></i> Back
        </button>
      </a>
    </div>
    <div class="col-md-6 text-right">
      <button type="submit" class="btn bg-gd-corporate border-0 text-white">
        Update 
      </button>
    </div>
  </div>
</form>
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
    initialPreviewConfig: [
      {
          caption: '{{ $superuser->name ?? $superuser->username }}'
      }
    ]
  });
})
</script>
@endpush