<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk &mdash; {{ setting('website.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('icon.png') }}">
    @include('superuser.asset.css')
    @stack('plugin-styles')
    <style>
      /* ===== Modern login (scoped: mlogin-) ===== */
      body.mlogin{min-height:100vh;margin:0;font-family:Muli,-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;background:linear-gradient(135deg,#0f2027 0%,#203a43 45%,#2c5364 100%);display:flex;align-items:center;justify-content:center;padding:32px 16px;position:relative;overflow-x:hidden}
      .mlogin-bg{position:fixed;inset:0;pointer-events:none;overflow:hidden}
      .mlogin-bg::before,.mlogin-bg::after{content:"";position:absolute;border-radius:50%;filter:blur(90px);opacity:.45}
      .mlogin-bg::before{width:480px;height:480px;left:-140px;top:-140px;background:#3b82f6}
      .mlogin-bg::after{width:520px;height:520px;right:-160px;bottom:-160px;background:#06b6d4}
      .mlogin-orb{position:absolute;border-radius:50%;border:1px solid rgba(255,255,255,.14)}
      .mlogin-orb.o1{width:220px;height:220px;left:8%;bottom:12%}
      .mlogin-orb.o2{width:140px;height:140px;right:10%;top:14%}
      .mlogin-orb.o3{width:80px;height:80px;right:22%;bottom:20%}
      .mlogin-wrap{position:relative;width:100%;max-width:460px;animation:mlogin-up .5s ease both}
      @keyframes mlogin-up{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
      .mlogin-card{display:grid;grid-template-columns:1fr;background:#fff;border-radius:22px;overflow:hidden;box-shadow:0 30px 80px rgba(0,0,0,.45)}
      /* Brand panel */
      .mlogin-brand{position:relative;padding:40px 36px;color:#fff;background:linear-gradient(160deg,#1d4ed8 0%,#2563eb 45%,#0891b2 100%);overflow:hidden;display:flex;flex-direction:column}
      .mlogin-brand::before{content:"";position:absolute;width:340px;height:340px;right:-120px;top:-120px;border-radius:50%;background:rgba(255,255,255,.12)}
      .mlogin-brand::after{content:"";position:absolute;width:220px;height:220px;right:60px;bottom:-110px;border-radius:50%;background:rgba(255,255,255,.08)}
      .mlogin-logo{display:flex;align-items:center;gap:12px;position:relative;z-index:1}
      .mlogin-logo img{width:48px;height:48px;object-fit:contain;background:#fff;border-radius:14px;padding:6px;box-shadow:0 6px 16px rgba(0,0,0,.25)}
      .mlogin-logo span{font-weight:800;font-size:17px;line-height:1.3}
      .mlogin-brand h2{position:relative;z-index:1;margin:34px 0 10px;font-size:26px;font-weight:800;line-height:1.35;color:#fff}
      .mlogin-brand p{position:relative;z-index:1;margin:0;color:rgba(255,255,255,.82);font-size:14px;line-height:1.7}
      .mlogin-feats{position:relative;z-index:1;list-style:none;margin:26px 0 0;padding:0;display:grid;gap:12px;font-size:13.5px}
      .mlogin-feats li{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.92)}
      .mlogin-feats i{width:28px;height:28px;flex:0 0 28px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;background:rgba(255,255,255,.18);font-size:13px}
      .mlogin-brandfoot{position:relative;z-index:1;margin-top:auto;padding-top:28px;font-size:12px;color:rgba(255,255,255,.65)}
      /* Form panel */
      .mlogin-form{padding:40px 42px}
      .mlogin-form h1{margin:0;font-size:23px;font-weight:800;color:#0f172a}
      .mlogin-form .mlogin-sub{margin:6px 0 22px;color:#64748b;font-size:13.5px}
      .mlogin-field{margin-bottom:16px}
      /* Floating label: terlihat seperti placeholder, naik ke atas saat diisi/fokus */
      .mlogin-control.mlogin-float .fl{position:absolute;left:42px;top:50%;transform:translateY(-50%);font-size:14px;color:#94a3b8;pointer-events:none;transition:all .15s ease;white-space:nowrap}
      .mlogin-control.mlogin-float input.form-control{height:58px;padding:22px 44px 8px 42px}
      .mlogin-control.mlogin-float input.form-control:focus + .fl,
      .mlogin-control.mlogin-float input.form-control:not(:placeholder-shown) + .fl{top:12px;transform:none;font-size:11px;font-weight:800;letter-spacing:.05em;color:#2563eb}
      .mlogin-control{position:relative}
      .mlogin-control>i{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:15px;pointer-events:none}
      .mlogin-control input.form-control{height:50px;border-radius:12px;border:1.5px solid #e2e8f0;background:#f8fafc;padding-left:42px;font-size:14px;color:#0f172a;box-shadow:none;transition:border-color .15s,box-shadow .15s,background .15s}
      .mlogin-control input.form-control:focus{border-color:#2563eb;background:#fff;box-shadow:0 0 0 4px rgba(37,99,235,.12)}
      .mlogin-control.has-eye input.form-control{padding-right:48px}
      .mlogin-eye{position:absolute;right:8px;top:50%;transform:translateY(-50%);width:36px;height:36px;border:none;border-radius:10px;background:transparent;color:#94a3b8;cursor:pointer;font-size:15px}
      .mlogin-eye:hover{background:#eef2ff;color:#2563eb}
      .mlogin-row{display:flex;align-items:center;justify-content:space-between;margin:4px 0 20px;font-size:13px;color:#64748b}
      .mlogin-check{display:inline-flex;align-items:center;gap:8px;cursor:pointer;user-select:none}
      .mlogin-check input{width:17px;height:17px;accent-color:#2563eb;cursor:pointer;margin:0}
      .mlogin-secure{display:inline-flex;align-items:center;gap:6px;color:#94a3b8;font-size:12.5px}
      .mlogin-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;height:50px;border:none;border-radius:12px;font-size:15px;font-weight:800;color:#fff;cursor:pointer;background:linear-gradient(135deg,#2563eb,#0891b2);box-shadow:0 10px 24px rgba(37,99,235,.35);transition:transform .12s,box-shadow .12s,filter .12s}
      .mlogin-btn:hover:not(:disabled){transform:translateY(-1px);filter:brightness(1.05);box-shadow:0 14px 28px rgba(37,99,235,.42)}
      .mlogin-btn:disabled{opacity:.7;cursor:wait;transform:none}
      .mlogin-foot{margin:22px 0 0;text-align:center;font-size:12px;color:#94a3b8}
      /* Maintenance notice */
      .mlogin-maint{display:flex;gap:13px;background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1px solid #fcd34d;border-radius:14px;padding:15px 16px;margin-bottom:20px}
      .mlogin-maint .mi{flex:0 0 40px;width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px;background:linear-gradient(135deg,#f59e0b,#f97316);box-shadow:0 6px 14px rgba(245,158,11,.4)}
      .mlogin-maint strong{display:flex;align-items:center;gap:8px;font-size:13.5px;color:#92400e}
      .mlogin-maint p{margin:5px 0 0;font-size:13px;line-height:1.6;color:#78500a}
      .mlogin-maint small{display:block;margin-top:8px;padding-top:8px;border-top:1px dashed #f3d38a;font-size:12px;color:#a16207}
      .mm-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#dc2626;animation:mm-blink 1.2s ease-in-out infinite}
      @keyframes mm-blink{0%,100%{opacity:1}50%{opacity:.25}}
      /* Error box */
      .mlogin-error{display:flex;gap:12px;background:#fef2f2;border:1px solid #fca5a5;border-radius:14px;padding:13px 15px;margin-bottom:20px}
      .mlogin-error .mi{flex:0 0 36px;width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;background:#ef4444;font-size:15px}
      .mlogin-error div{font-size:13px;color:#991b1b;line-height:1.6}
      @media (max-width:860px){
        .mlogin-card{grid-template-columns:1fr}
        .mlogin-brand{padding:26px 24px}
        .mlogin-brand h2{margin:18px 0 6px;font-size:20px}
        .mlogin-feats,.mlogin-brandfoot{display:none}
        .mlogin-form{padding:28px 22px}
      }
    </style>
  </head>
  <body class="mlogin">
    <div class="mlogin-bg" aria-hidden="true">
      <span class="mlogin-orb o1"></span>
      <span class="mlogin-orb o2"></span>
      <span class="mlogin-orb o3"></span>
    </div>

    {{-- Loader Codebase (dipakai form.js saat submit) --}}
    <div id="page-container" style="display:none">
      <header id="page-header">
        <div id="page-header-loader" class="overlay-header bg-primary"></div>
      </header>
    </div>

    <div class="mlogin-wrap">
      <div class="mlogin-card">

        {{--<div class="mlogin-brand">
          <!-- <div class="mlogin-logo">
            <img src="{{ asset('superuser_assets/media/logo_ppi.png') }}" alt="Logo">
            <span>{{ setting('website.name') }}</span>
          </div> -->
          <h2>Seluruh operasional dalam satu sistem.</h2>
          <p>Receiving, QC, stok, penjualan, hingga keuangan — terpantau rapi dari satu dasbor.</p>
          <ul class="mlogin-feats">
            <li><i class="fa fa-cubes"></i> Stok gudang &amp; showroom real-time</li>
            <li><i class="fa fa-truck"></i> Distribusi &amp; penjualan terpantau</li>
            <li><i class="fa fa-line-chart"></i> Laporan &amp; keuangan terpusat</li>
          </ul>
          <div class="mlogin-brandfoot">Copyright &copy; {{ now()->year }} <b>Premium Parfume Indonesia</b>. All rights reserved.</div>
        </div>--}}

        <div class="mlogin-form">
          <h1>Selamat datang kembali</h1>
          <p class="mlogin-sub">Masuk ke akun Anda untuk melanjutkan.</p>

          @if(!empty($maintenanceDown))
            <div class="mlogin-maint" role="alert">
              <span class="mi"><i class="fa fa-wrench"></i></span>
              <div>
                <strong>Mode Pemeliharaan <span class="mm-dot"></span></strong>
                <p>{{ !empty($maintenanceMessage) ? $maintenanceMessage : 'Mohon coba login kembali nanti.' }}</p>
                <small><i class="fa fa-shield mr-5"></i>Akun admin tetap bisa login untuk mematikan maintenance.</small>
              </div>
            </div>
          @endif

          @if($errors->any())
            <div class="mlogin-error" role="alert">
              <span class="mi"><i class="fa fa-exclamation-triangle"></i></span>
              <div>
                @foreach($errors->all() as $error)
                  <div>{{ $error }}</div>
                @endforeach
              </div>
            </div>
          @endif

          <form class="ajax js-validation-signin" data-action="{{ route('auth.superuser.login') }}" data-type="POST" autocomplete="off">
            <div class="mlogin-field">
              <div class="mlogin-control mlogin-float">
                <i class="fa fa-user"></i>
                <input type="text" id="mlogin-user" class="form-control" name="account_name" placeholder=" " autofocus>
                <span class="fl">Username</span>
              </div>
            </div>
            <div class="mlogin-field">
              <div class="mlogin-control has-eye mlogin-float">
                <i class="fa fa-lock"></i>
                <input type="password" id="mlogin-pass" class="form-control" name="password" placeholder=" ">
                <span class="fl">Password</span>
                <button type="button" class="mlogin-eye" id="toggle_password" data-toggle="false" tabindex="-1" title="Tampilkan password">
                  <i class="fa fa-eye"></i>
                </button>
              </div>
            </div>
            <div class="mlogin-row">
              <label class="mlogin-check">
                <input type="checkbox" name="remember"> Ingat saya
              </label>
              <span class="mlogin-secure"><i class="fa fa-lock"></i> Koneksi aman</span>
            </div>
            <button type="submit" class="mlogin-btn">
              <i class="si si-login"></i> Masuk
            </button>
          </form>

          <div style="display:flex;align-items:center;gap:12px;margin:20px 0 16px;color:#94a3b8;font-size:12px;font-weight:800;letter-spacing:.06em">
            <span style="flex:1;height:1px;background:#e2e8f0"></span> ATAU <span style="flex:1;height:1px;background:#e2e8f0"></span>
          </div>
          <a href="{{ route('auth.superuser.sso.redirect') }}" style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;height:50px;border-radius:12px;border:1.5px solid #e2e8f0;background:#fff;font-size:14px;font-weight:800;color:#0f172a;text-decoration:none">
            <svg width="20" height="20" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.3 6.1 29.4 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.3 6.1 29.4 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C36.9 39.2 44 34 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>
            Masuk dengan Google
          </a>
          <p style="margin:12px 0 0;text-align:center;font-size:12.5px;color:#64748b">Kandidat baru? <a href="{{ route('auth.superuser.sso') }}" style="color:#2563eb;font-weight:700">Daftar di halaman SSO</a> — akun lama dengan email sama otomatis terhubung.</p>

          <div id="alert-block"></div>
          <p class="mlogin-foot">Butuh bantuan? Hubungi administrator sistem Anda.</p>
        </div>

      </div>
    </div>

    @include('superuser.asset.js')
    @include('superuser.asset.plugin.notify')
    <script src="{{ asset('utility/superuser/js/form.js') }}"></script>
    @stack('scripts')
    <script>
      $(document).ready(function () {
        $('#toggle_password').on('click', function () {
          let val = $(this).data('toggle')
          let pw = $('#mlogin-pass')

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
