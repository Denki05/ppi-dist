<!DOCTYPE html>
<html lang="en">
  <head>
  <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <!-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> -->
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
      @include('superuser.component.menu')
      <main id="container">
        <div class="content">
          @yield('content')
        </div>
      </main>
      <footer class="footer">
          <div class="footer-wrap">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © <a href="https://www.bootstrapdash.com/" target="_blank">bootstrapdash.com </a>2021</span>
              <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Only the best <a href="https://www.bootstrapdash.com/" target="_blank"> Bootstrap dashboard </a> templates</span>
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