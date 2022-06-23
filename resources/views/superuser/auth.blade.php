<!DOCTYPE html>
<html lang="en" class="no-focus" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ setting('website.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('icon.png') }}">
    @include('superuser.asset.css')
    @stack('plugin-styles')
  </head>
  <body>
                <form class="ajax js-validation-signin" data-action="{{ route('auth.superuser.login') }}" data-type="POST">
                  <div class="wrapper">
                      <div class="logo">
                          <img src="https://www.premiumparfum.com/images/logo%20ppi%202016%20-%20variant-02-u26873_2x.png" alt="">
                      </div>
                      <div class="text-center mt-4 name">
                          
                      </div>
                      <form class="p-3 mt-3">
                          <div class="form-field d-flex align-items-center">
                              <span class="far fa-user"></span>
                              <input type="text" name="account_name" id="account_name" placeholder="Username / Email">
                          </div>
                          <div class="form-field d-flex align-items-center">
                              <span class="fas fa-key"></span>
                              <input type="password" name="password" id="password" placeholder="Password">
                          </div>
                          <button class="btn mt-3">Login</button>
                      </form>
                      {{-- <div class="text-center fs-6">
                          <a href="#">Forget password?</a> or <a href="#">Sign up</a>
                      </div>--}}
                  </div>
                </form>
    </div>
    @include('superuser.asset.js')
    @include('superuser.asset.plugin.notify')
    <script src="{{ asset('utility/superuser/js/form.js') }}"></script>
    @stack('scripts')
    <script>
      $(document).ready(function () {
        $('#toggle_password').on('click', function () {
          let val = $(this).data('toggle')
          let pw = $(this).parent().siblings()

          if (val) {
            $(this).children().attr('class', 'fa fa-eye')
            pw.attr('type', 'password')
          } else {
            $(this).children().attr('class', 'fa fa-eye-slash')
            pw.attr('type', 'text')
          }

          $(this).data('toggle', !val)
        })
      })
    </script>
  </body>
</html>
