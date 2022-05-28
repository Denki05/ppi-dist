@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item active">Settings</span>
</nav>
<div id="alert-block"></div>
<div class="row">
  <div class="col-md-6">
    <form class="ajax" data-action="{{ route('superuser.utility.settings.website') }}" data-type="POST">
      <div class="block">
        <div class="block-header block-header-default">
          <h3 class="block-title">Website</h3>
          <div class="block-options">
            <button type="submit" class="btn-block-option">
              <i class="fa fa-check"></i> Save
            </button>
            {{-- <button type="reset" class="btn-block-option">
              <i class="fa fa-repeat"></i> Reset
            </button> --}}
          </div>
        </div>
        <div class="block-content">
          <div class="form-group row">
            <label class="col-lg-4 col-form-label">Name</label>
            <div class="col-lg-7">
              <input type="text" class="form-control" name="name" placeholder="Website Name" value="{{ setting('website.name') }}">
            </div>
          </div>
          {{-- <div class="form-group row">
            <label class="col-lg-4 col-form-label">Maintenance</label>
            <div class="col-lg-7">
              <label class="css-control css-control-sm css-control-danger css-switch">
                <input type="checkbox" class="css-control-input" name="maintenance" {{ setting('website.maintenance') ? 'checked' : '' }}>
                <span class="css-control-indicator"></span>
              </label>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-lg-4 col-form-label">Maintenance Message</label>
            <div class="col-lg-7">
              <textarea class="form-control" name="maintenance_message" rows="4">{{ setting('website.maintenance_message') }}</textarea>
            </div>
          </div> --}}
          <div class="form-group row">
            <label class="col-lg-4 col-form-label">Color Themes</label>
            <input type="hidden" name="color_themes" value="{{ setting('website.color_themes') }}">
            <div class="col-lg-7">
              <div class="row text-center mb-5">
                <div class="col-2 mb-5">
                  <a class="text-default {{ setting('website.color_themes') == 'default' ? 'border-primary border-b-3' : '' }}" data-toggle="theme" data-theme="default" data-theme-url="default"><i class="fa fa-2x fa-circle"></i></a>
                </div>
                <div class="col-2 mb-5">
                  <a class="text-elegance {{ setting('website.color_themes') == 'superuser_assets/css/themes/elegance.min.css' ? 'border-primary border-b-3' : '' }}" data-toggle="theme" data-theme="{{ asset('superuser_assets/css/themes/elegance.min.css') }}" data-theme-url="superuser_assets/css/themes/elegance.min.css"><i class="fa fa-2x fa-circle"></i></a>
                </div>
                <div class="col-2 mb-5">
                  <a class="text-pulse {{ setting('website.color_themes') == 'superuser_assets/css/themes/pulse.min.css' ? 'border-primary border-b-3' : '' }}" data-toggle="theme" data-theme="{{ asset('superuser_assets/css/themes/pulse.min.css') }}" data-theme-url="superuser_assets/css/themes/pulse.min.css"><i class="fa fa-2x fa-circle"></i></a>
                </div>
                <div class="col-2 mb-5">
                  <a class="text-flat {{ setting('website.color_themes') == 'superuser_assets/css/themes/flat.min.css' ? 'border-primary border-b-3' : '' }}" data-toggle="theme" data-theme="{{ asset('superuser_assets/css/themes/flat.min.css') }}" data-theme-url="superuser_assets/css/themes/flat.min.css"><i class="fa fa-2x fa-circle"></i></a>
                </div>
                <div class="col-2 mb-5">
                  <a class="text-corporate {{ setting('website.color_themes') == 'superuser_assets/css/themes/corporate.min.css' ? 'border-primary border-b-3' : '' }}" data-toggle="theme" data-theme="{{ asset('superuser_assets/css/themes/corporate.min.css') }}" data-theme-url="superuser_assets/css/themes/corporate.min.css"><i class="fa fa-2x fa-circle"></i></a>
                </div>
                <div class="col-2 mb-5">
                  <a class="text-earth {{ setting('website.color_themes') == 'superuser_assets/css/themes/earth.min.css' ? 'border-primary border-b-3' : '' }}" data-toggle="theme" data-theme="{{ asset('superuser_assets/css/themes/earth.min.css') }}" data-theme-url="superuser_assets/css/themes/earth.min.css"><i class="fa fa-2x fa-circle"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
$(document).ready(function () {
  $('[data-toggle=theme]').on('click', function (e) {
    e.preventDefault();

    let color_themes = $(this).data('theme-url')
    $('input[name=color_themes]').val(color_themes)
    $(this).parent().siblings().children().removeClass('border-primary border-b-3')
    $(this).addClass('border-primary border-b-3')
  })
})
</script>
@endpush