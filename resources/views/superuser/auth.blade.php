<!DOCTYPE html>
<html lang="en" class="no-focus" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ setting('website.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('public/icon.png') }}">
    @include('superuser.asset.css')
    @stack('plugin-styles')
  </head>
  <body>
    <div id="page-container" class="main-content-boxed">
      <header id="page-header">
        <div id="page-header-loader" class="overlay-header bg-primary">
          <div class="content-header content-header-fullrow text-center">
            <div class="content-header-item">
              <i class="fa fa-sun-o fa-spin text-white"></i>
            </div>
          </div>
        </div>
      </header>
      <main id="main-container">
        <div class="bg-body-dark">
          <div class="row mx-0 justify-content-center">
            <div class="hero-static col-lg-6 col-xl-4">
              <div class="content content-full overflow-hidden">
                {{-- <div class="py-30 text-center">
                  <h1 class="h4 font-w700 mt-30 mb-10">Welcome to Your Dashboard</h1>
                  <h2 class="h5 font-w400 text-muted mb-0">It’s a great day today!</h2>
                </div> --}}
                <form class="ajax js-validation-signin pt-150" data-action="{{ route('auth.superuser.login') }}" data-type="POST">
                  <div class="block block-themed block-rounded block-shadow">
                    <div class="block-header bg-gd-primary">
                      <h3 class="block-title">Sign In</h3>
                    </div>
                    <div class="block-content">
                      <div class="form-group">
                        <label>Username / Email</label>
                        <input type="text" class="form-control" name="account_name">
                      </div>
                      <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                          <input type="password" class="form-control" name="password">
                          <div class="input-group-append">
                            <button type="button" class="input-group-text" id="toggle_password" data-toggle="false">
                              <i class="fa fa-eye"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                      <div class="form-group row mb-0">
                        <div class="col-6 d-sm-flex align-items-center push">
                          <div class="mr-auto ml-0 mb-0">
                            <label class="css-control css-control-secondary css-checkbox">
                              <input type="checkbox" class="css-control-input" name="remember">
                              <span class="css-control-indicator"></span> Remember me
                            </label>
                          </div>
                        </div>
                        <div class="col-6 text-right push">
                          <button type="submit" class="btn btn-outline-secondary">
                            <i class="si si-login mr-10"></i> Sign In
                          </button>
                        </div>
                      </div>
                    </div>
                    {{-- <div class="block-content bg-body-light">
                      <div class="form-group text-center">
                        <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="op_auth_reminder3.html">
                          <i class="fa fa-warning mr-5"></i> Forgot Password
                        </a>
                      </div>
                    </div> --}}
                  </div>
                </form>
                <div id="alert-block"></div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    @include('superuser.asset.js')
    @include('superuser.asset.plugin.notify')
    <script src="{{ asset('public/utility/superuser/js/form.js') }}"></script>
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
