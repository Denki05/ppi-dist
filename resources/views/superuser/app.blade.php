<!DOCTYPE html>
<html lang="en" class="no-focus">
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
      <div class="horizontal-menu">
        @include('superuser.component.menu')
      </div>
      <main id="main-container">
        <div class="content">
          @yield('content')
        </div>
      </main>
      <!-- <footer id="page-footer" class="footer">
        <div class="content font-size-xs clearfix">
          <div class="float-left">
            <p>This page took {{ round(microtime(true) - LARAVEL_START, 3) }} seconds to render</p>
          </div>
          <div class="float-right">
            <a class="font-w600" href="#" target="_blank">Copyright &copy; 2022 <b>Premium Parfume Indonesia</b>. All rights reserved.</span>
          </div>
        </div>
      </footer> -->
      <footer id="page-footer" class="opacity-1">
        <div class="content font-size-xs clearfix">
          <div class="float-left">
            <p>This page took {{ round(microtime(true) - LARAVEL_START, 3) }} seconds to render</p>
          </div>
          <div class="float-right">
            <a class="font-w600" href="#" target="_blank">Copyright &copy; 2022 <b>Premium Parfume Indonesia</b>. All rights reserved.</span>
          </div>
        </div>
      </footer>
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