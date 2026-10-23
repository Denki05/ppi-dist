<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak &mdash; {{ config('app.name', 'Aplikasi') }}</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:linear-gradient(135deg,#0f2027,#203a43 55%,#2c5364);font-family:Muli,-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;color:#0f172a}
        .box{width:100%;max-width:520px;background:#fff;border-radius:18px;padding:32px;box-shadow:0 24px 60px rgba(0,0,0,.35);text-align:center}
        .icon{width:64px;height:64px;margin:0 auto 18px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#fef2f2;color:#dc2626;font-size:28px;font-weight:800}
        h1{margin:0 0 10px;font-size:23px}
        p{margin:0 0 22px;color:#475569;font-size:14px;line-height:1.7}
        .actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
        a{display:inline-block;padding:11px 18px;border-radius:10px;text-decoration:none;font-size:13.5px;font-weight:800}
        .primary{background:#2563eb;color:#fff}
        .secondary{background:#e2e8f0;color:#0f172a}
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">!</div>
        <h1>Akses ditolak</h1>
        <p>{{ isset($exception) && $exception->getMessage() ? $exception->getMessage() : 'Akun Anda tidak memiliki akses ke halaman ini.' }}</p>
        <div class="actions">
            @if(Auth::guard('superuser')->check())
                <a class="primary" href="{{ route('portal.modules', ['choose' => 1]) }}">Pilih Modul</a>
                <a class="secondary" href="{{ route('superuser.logout') }}">Keluar</a>
            @else
                <a class="primary" href="{{ route('auth.superuser.index') }}">Kembali ke Login</a>
            @endif
        </div>
    </div>
</body>
</html>
