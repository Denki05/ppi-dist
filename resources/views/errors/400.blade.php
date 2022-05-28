<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ setting('website.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" href="{{ asset('icon.png') }}">
    @include('superuser.asset.css')
  </head>
  <body>
    <div id="page-container" class="main-content-boxed">
      <main id="main-container">
        <div class="hero bg-white">
          <div class="hero-inner">
            <div class="content content-full">
              <div class="py-30 text-center">
                <div class="display-3 text-warning">400</div>
                <h1 class="h2 font-w700 mt-30 mb-10">Oops.. You just found an error page..</h1>
                <h2 class="h3 font-w400 text-muted mb-50">We are sorry but your request contains bad syntax and cannot be fulfilled..</h2>
                <a class="btn btn-hero btn-rounded btn-alt-secondary" href="{{ route('superuser.index') }}">
                  <i class="fa fa-arrow-left mr-10"></i> Back
                </a>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </body>
</html>