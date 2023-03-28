<!DOCTYPE html>
<html lang="en">
  <head>
  <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ setting('website.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" href="{{ asset('superuser_assets/media/logo_ppi.png') }}">
    @stack('plugin-styles')
    @include('superuser.asset.css')
    @stack('styles')
  </head>
  <body>
    <div class="container-scroller">
      <!-- partial:partials/_horizontal-navbar.html -->
      <div class="horizontal-menu">
        @include('superuser.component.menu')
      </div>
      <main id="container">
        <div class="content">
          @yield('content')
        </div>
      </main>
      <footer id="page-footer" class="footer">
        <div class="content font-size-xs clearfix">
          <div class="float-left">
            <p>This page took {{ round(microtime(true) - LARAVEL_START, 3) }} seconds to render</p>
          </div>
          <div class="float-right">
            <a class="font-w600" href="https://willek.github.io" target="_blank">Copyright &copy; 2022 <b>Premium Parfume Indonesia</b>. All rights reserved.</span>
          </div>
        </div>
      </footer>
      <!-- <footer class="footer">
            <div class="container">
              <div class="d-sm-flex justify-content-center justify-content-sm-between">
                <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright &copy; 2022 <b>Premium Parfume Indonesia</b>. All rights reserved.</span>
                <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">This template is made with <i class="mdi mdi-heart text-danger"></i>Developer</span>
              </div>
            </div>
      </footer> -->
      <!-- partial -->
      <!-- page-body-wrapper ends -->
    </div>
    @yield('modal')
    <script>
      var base_url = "{{ url('/') }}";
    </script>
    @include('superuser.asset.js')
    @stack('scripts')
    <script src="{{ asset('utility/superuser/js/common.js') }}"></script>
    @include('superuser.asset.prevent_direct_access')
  </body>
</html>