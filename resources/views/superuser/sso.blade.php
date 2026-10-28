<!DOCTYPE html>
<html lang="id" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pendaftaran / Masuk &mdash; {{ setting('website.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('icon.png') }}">
    @include('superuser.asset.css')
    <style>
      body.mlogin{min-height:100vh;margin:0;font-family:Muli,-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;background:linear-gradient(135deg,#0f2027 0%,#203a43 45%,#2c5364 100%);display:flex;align-items:center;justify-content:center;padding:32px 16px}
      .mlogin-wrap{width:100%;max-width:520px}
      .mlogin-card{background:#fff;border-radius:22px;overflow:hidden;box-shadow:0 30px 80px rgba(0,0,0,.45);padding:40px 38px;text-align:center}
      .mlogin-card h1{margin:0;font-size:22px;font-weight:800;color:#0f172a}
      .mlogin-card p.sub{margin:8px 0 22px;color:#64748b;font-size:13.5px;line-height:1.7}
      .gbtn{display:flex;align-items:center;justify-content:center;gap:12px;width:100%;height:52px;border-radius:12px;border:1.5px solid #e2e8f0;background:#fff;font-size:15px;font-weight:800;color:#0f172a;text-decoration:none;transition:box-shadow .15s,transform .12s}
      .gbtn:hover{box-shadow:0 10px 24px rgba(0,0,0,.12);transform:translateY(-1px)}
      .gbtn svg{width:22px;height:22px}
      .mlogin-error{display:flex;gap:12px;background:#fef2f2;border:1px solid #fca5a5;border-radius:14px;padding:13px 15px;margin-bottom:20px;text-align:left}
      .mlogin-error div{font-size:13px;color:#991b1b;line-height:1.6}
      .mlogin-links{margin-top:22px;font-size:13px;color:#64748b}
      .mlogin-links a{color:#2563eb;text-decoration:none;font-weight:700}
      .mlogin-steps{margin:22px 0 0;padding:0;list-style:none;text-align:left;font-size:13px;color:#475569;line-height:1.7}
    </style>
  </head>
  <body class="mlogin">
    <div class="mlogin-wrap">
      <div class="mlogin-card">
        <h1>Pendaftaran / Masuk Karyawan</h1>
        <p class="sub">Klik tombol di bawah pakai akun Google kamu.<br>Akun baru otomatis <b>menunggu verifikasi admin</b> sebelum aktif.</p>

        @if($errors->any())
          <div class="mlogin-error" role="alert">
            <div>
              @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
              @endforeach
            </div>
          </div>
        @endif

        <a class="gbtn" href="{{ route('auth.superuser.sso.redirect') }}">
          <svg viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.3 6.1 29.4 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.3 6.1 29.4 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C36.9 39.2 44 34 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>
          Lanjut dengan Google
        </a>

        <ul class="mlogin-steps">
          <li><b>1.</b> Kandidat baru: daftar → status menunggu → admin aktifkan.</li>
          <li><b>2.</b> Akun lama: login password dulu, lalu hubungkan Google dari halaman Profile (butuh password).</li>
          <li><b>3.</b> Email beda: hubungi admin untuk mapping manual sekali.</li>
        </ul>

        <div class="mlogin-links">
          Staff lama pakai password? <a href="{{ route('auth.superuser.index') }}">Login biasa</a>
        </div>
      </div>
    </div>
  </body>
</html>
