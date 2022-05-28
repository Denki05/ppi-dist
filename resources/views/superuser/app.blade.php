<!doctype html>
<html lang="en" class="no-focus">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ setting('website.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" href="{{ asset('icon.png') }}">
    @stack('plugin-styles')
    @include('superuser.asset.css')
    @stack('styles')
  </head>
  <body>
    <div id="page-container" class="sidebar-o sidebar-inverse enable-page-overlay side-scroll page-header-modern main-content-boxed">
      <nav id="sidebar">
        <div class="sidebar-content">
          <div class="content-header content-header-fullrow px-15">
            <div class="content-header-section text-center align-parent sidebar-mini-hidden">
              <button type="button" class="btn btn-circle btn-dual-secondary d-lg-none align-v-r" data-toggle="layout" data-action="sidebar_close">
                <i class="fa fa-times"></i>
              </button>
              <div class="content-header-item">
                <a class="link-effect font-w700" href="{{ route('superuser.index') }}">
                  <span class="font-size-xl text-dual-primary-dark">{{ setting('website.name') }}</span>
                </a>
              </div>
            </div>
          </div>
          <div class="content-side content-side-full content-side-user px-10 align-parent">
            <div class="sidebar-mini-visible-b align-v animated fadeIn">
              <img class="img-avatar img-avatar32" src="{{ $superuser->img }}">
            </div>
            <div class="sidebar-mini-hidden-b text-center">
              <a class="img-link" href="{{ route('superuser.profile.index') }}">
                <img class="img-avatar" src="{{ $superuser->img }}">
              </a>
              <ul class="list-inline mt-10">
                <li class="list-inline-item">
                  <a class="link-effect text-dual-primary-dark font-size-xs font-w600 text-uppercase" href="{{ route('superuser.profile.index') }}">{{ $superuser->name ?? $superuser->username }}</a>
                </li>
                {{-- <li class="list-inline-item">
                  <a class="link-effect text-dual-primary-dark" href="{{ route('superuser.logout') }}">
                    <i class="si si-logout"></i>
                  </a>
                </li> --}}
              </ul>
            </div>
          </div>
          <div class="content-side content-side-full">
            @include('superuser.component.menu')
          </div>
        </div>
      </nav>
      <header id="page-header">
        <div class="content-header">
          <div class="content-header-section">
            <button type="button" class="btn btn-circle btn-dual-secondary" data-toggle="layout" data-action="sidebar_toggle">
              <i class="fa fa-navicon"></i>
            </button>
          </div>
          <div class="content-header-section">
            @include('superuser.component.dropdown-menu')
            {{-- @include('superuser.component.dropdown-notification') --}}
          </div>
        </div>
        <div id="page-header-loader" class="overlay-header bg-primary">
          <div class="content-header content-header-fullrow text-center">
            <div class="content-header-item">
              <i class="fa fa-sun-o fa-spin text-white"></i>
            </div>
          </div>
        </div>
      </header>
      <main id="main-container">
        <div class="content">
          @yield('content')
        </div>
      </main>
      <footer id="page-footer" class="opacity-0">
        <div class="content font-size-xs clearfix">
          <div class="float-left">
            <p>This page took {{ round(microtime(true) - LARAVEL_START, 3) }} seconds to render</p>
          </div>
          <div class="float-right">
            <a class="font-w600" href="https://willek.github.io" target="_blank">Developer</a> &copy; <span class="js-year-copy"></span>
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
