<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  {{-- Kunci portrait tablet: lebar device, no zoom liar biar tombol nggak kegeser pas disentuh --}}
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Logistik') - Checker Transaksi</title>

  {{-- Bundle CSS yang sama persis dengan app.blade.php (bootstrap/tema, dsb),
       supaya menu horizontal ke-render sama seperti versi desktop. --}}
  @stack('plugin-styles')
  @include('superuser.asset.css')

  <style>
    html, body {
      font-size: 15px;
    }

    /* Menu asli dipakai sama seperti CRM, cuma dirapatkan dikit biar muat di lebar tablet portrait. */
    .tablet-topmenu {
      position: sticky;
      top: 0;
      z-index: 100;
    }

    /* "Canvas" konten di bawah menu, mirip area putih utama di CRM desktop */
    .tablet-canvas-wrap {
      max-width: 960px;
      margin: 0 auto;
      padding: 16px;
    }
    .tablet-canvas {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 1px 6px rgba(0,0,0,.08);
      padding: 18px;
      min-height: 60vh;
    }

    .tablet-canvas .btn {
      font-size: 14px;
      padding: 8px 14px;
      border-radius: 8px;
    }
    .tablet-canvas .form-control,
    .tablet-canvas input[type="checkbox"] {
      font-size: 14px;
    }
    .tablet-canvas input[type="checkbox"] {
      width: 20px;
      height: 20px;
    }
    .tablet-canvas table.table th,
    .tablet-canvas table.table td {
      font-size: 14px;
      padding: 8px 8px;
      vertical-align: middle;
    }
    .tablet-canvas .card {
      border-radius: 10px;
      border: none;
      box-shadow: 0 1px 4px rgba(0,0,0,.08);
    }
  </style>
  @stack('styles')
</head>
<body>
  <div class="container-scroller">

    @php
      $user = Auth::id();
      $notifCount = DB::table('notifications')
        ->where('notifiable_id', $user)
        ->where('read_at', null)
        ->count();
    @endphp
    {{-- Menu yang sama persis dengan CRM (app.blade.php), bukan topbar custom lagi --}}
    <div class="tablet-topmenu horizontal-menu">
      @include('superuser.component.menu')
    </div>

    <div class="tablet-canvas-wrap">
      <div class="tablet-canvas">
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
      </div>
    </div>

  </div>

  @yield('modal')
  <script>
    var base_url = "{{ url('/') }}";
  </script>
  {{-- Bundle JS yang sama persis dengan app.blade.php, supaya dropdown/toggle menu
       dan script existing (konfirmasiBarang(), dsb) tetap jalan --}}
  @include('superuser.asset.js')
  @stack('scripts')
  <script src="{{ asset('utility/superuser/js/common.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  @include('superuser.asset.prevent_direct_access')
</body>
</html>