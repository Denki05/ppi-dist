<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pilih Modul</title>
    <link rel="shortcut icon" href="{{ asset('icon.png') }}">
    <link rel="stylesheet" href="{{ asset('superuser_assets/plus-admin/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <style>
        *{box-sizing:border-box}
        html{scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.22) transparent}
        body{margin:0;min-height:100vh;font-family:Muli,-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#081216;color:#f8fafc;display:flex;justify-content:center;padding:18px 16px 16px;overflow-x:hidden}
        body::before{content:"";position:fixed;inset:0;z-index:-1;background:
            radial-gradient(900px 520px at 18% -10%,rgba(37,99,235,.22),transparent 60%),
            radial-gradient(760px 460px at 88% 8%,rgba(8,145,178,.18),transparent 62%),
            radial-gradient(680px 420px at 50% 110%,rgba(22,163,74,.14),transparent 60%),
            linear-gradient(160deg,#071116 0%,#0b1a21 52%,#0a151b 100%)}
        .wrap{width:100%;max-width:640px;margin:auto}
        .top{display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-bottom:12px}
        .top .out{display:inline-flex;align-items:center;gap:6px;color:rgba(255,255,255,.72);text-decoration:none;font-size:11.5px;font-weight:700;padding:6px 10px;border:1px solid rgba(255,255,255,.14);border-radius:999px;transition:background .18s ease,color .18s ease,border-color .18s ease}
        .top .out:hover{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.3);color:#fff}
        .profile{text-align:center;margin-bottom:12px}
        .avatar{width:44px;height:44px;margin:0 auto 8px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;letter-spacing:.04em;color:#fff;background:linear-gradient(135deg,#2563eb,#0891b2 55%,#16a34a);box-shadow:0 0 0 1px rgba(255,255,255,.14),0 10px 22px rgba(8,145,178,.28)}
        .profile h1{margin:0 0 2px;font-size:18px;font-weight:800;letter-spacing:-.02em}
        .profile p{margin:0;color:rgba(255,255,255,.56);font-size:12.5px;line-height:1.5}
        .pills{display:flex;flex-wrap:wrap;gap:5px;justify-content:center;margin-top:7px}
        .pill{display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:800;padding:3px 9px;border-radius:999px;text-decoration:none;border:1px solid rgba(255,255,255,.25);background:rgba(255,255,255,.14);color:#fff;transition:background .18s ease}
        .pill:hover{background:rgba(255,255,255,.26);color:#fff;text-decoration:none}
        .pill.ok{background:#16a34a;border-color:#16a34a}
        .pill.warn{background:#f59e0b;border-color:#f59e0b}
        .pill.bad{background:#ef4444;border-color:#ef4444}
        .alert{display:flex;gap:8px;align-items:flex-start;background:rgba(239,68,68,.12);border:1px solid rgba(248,113,113,.35);color:#fecaca;padding:9px 12px;border-radius:10px;margin-bottom:10px;font-size:12px;line-height:1.5}
        /* ==== 1 KOLOM KE BAWAH, DIPADATKAN ==== */
        .links{display:flex;flex-direction:column;gap:7px}
        .link{position:relative;display:flex;align-items:center;gap:11px;padding:9px 12px;border-radius:12px;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.1);color:#f8fafc;text-decoration:none;overflow:hidden;opacity:0;transform:translateY(5px);animation:fadeUp .35s ease forwards;animation-delay:calc(var(--i) * 35ms);transition:background .18s ease,border-color .18s ease,transform .18s ease,box-shadow .18s ease;backdrop-filter:blur(10px);min-height:56px}
        .link::before{content:"";position:absolute;left:0;top:9px;bottom:9px;width:3px;border-radius:0 3px 3px 0;background:var(--accent);opacity:.85}
        .link:hover{background:rgba(255,255,255,.095);border-color:var(--accent);transform:translateX(3px);box-shadow:0 10px 22px -14px var(--accent);color:#fff;text-decoration:none}
        .link:focus-visible{outline:2px solid var(--accent);outline-offset:2px}
        .link-icon{flex:0 0 36px;width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;background:var(--accent);box-shadow:inset 0 1px 0 rgba(255,255,255,.28);transition:transform .18s ease}
        .link:hover .link-icon{transform:scale(1.05)}
        .link-text{min-width:0;flex:1;display:flex;flex-direction:column;gap:1px}
        .link-code{font-size:9px;font-weight:800;letter-spacing:.1em;color:var(--accent);text-transform:uppercase}
        .link-name{font-size:14px;font-weight:800;line-height:1.3}
        .link-desc{font-size:11.5px;color:rgba(255,255,255,.58);line-height:1.4;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .link-arrow{flex:0 0 26px;width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.5);border:1px solid rgba(255,255,255,.14);font-size:10px;transition:background .18s ease,color .18s ease,border-color .18s ease,transform .18s ease}
        .link:hover .link-arrow{background:var(--accent);border-color:var(--accent);color:#fff;transform:translateX(1px)}
        .empty{padding:22px 18px;text-align:center;border-radius:14px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1)}
        .empty i{font-size:20px;color:rgba(255,255,255,.35);margin-bottom:8px}
        .empty h2{margin:0 0 5px;font-size:15px}
        .empty p{margin:0;color:rgba(255,255,255,.58);font-size:12px;line-height:1.6}
        .foot{margin-top:10px;text-align:center;color:rgba(255,255,255,.34);font-size:10.5px}
        @keyframes fadeUp{to{opacity:1;transform:translateY(0)}}
        @media(max-width:560px){body{padding:14px 12px 12px}.profile h1{font-size:17px}.link{padding:8px 11px;min-height:52px}.link-icon{flex-basis:33px;width:33px;height:33px;font-size:13px}.link-desc{white-space:normal}.link-arrow{display:none}}
        @media(prefers-reduced-motion:reduce){.link{animation:none;opacity:1;transform:none;transition:none}.link:hover{transform:none}}
    </style>
</head>
<body>
@php
    $displayName = $superuser->name ?: $superuser->username;
    $nameParts = preg_split('/\s+/', trim($displayName));
    $initials = '';
    foreach (array_slice($nameParts, 0, 2) as $part) {
        $initials .= substr($part, 0, 1);
    }
    $initials = strtoupper($initials) ?: 'U';

    $emailRaw = strtolower(trim((string) ($superuser->email ?? '')));
    $allowedDomains = config('services.google.allowed_domains', ['gmail.com', 'googlemail.com']);
    $emailDomain = substr(strrchr($emailRaw, '@') ?: '', 1);
    $emailOk = $emailRaw !== '' && filter_var($emailRaw, FILTER_VALIDATE_EMAIL) && in_array($emailDomain, $allowedDomains, true);
    $googleLinked = !empty($superuser->google_id);
@endphp
<div class="wrap">
    <div class="top">
        <a class="out" href="{{ route('portal.profile') }}"><i class="fa fa-user"></i> Profil</a>
        <a class="out" href="{{ route('superuser.logout') }}"><i class="fa fa-sign-out"></i> Keluar</a>
    </div>

    <div class="profile">
        <div class="avatar">{{ $initials }}</div>
        <h1>Halo, {{ $displayName }}</h1>
        <p>{{ count($items) > 0 ? count($items) . ' modul siap dibuka' : 'Belum ada modul yang bisa dibuka' }}</p>
        <div class="pills">
            @if($googleLinked)
                <span class="pill ok"><i class="fa fa-google"></i> Google terhubung</span>
            @else
                <a class="pill warn" href="{{ route('portal.profile') }}"><i class="fa fa-google"></i> Hubungkan Google</a>
            @endif
            @if(!$emailOk)
                <a class="pill bad" href="{{ route('portal.profile') }}"><i class="fa fa-exclamation-triangle"></i> Perbaiki email</a>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="alert"><i class="fa fa-exclamation-circle"></i><span>{{ session('error') }}</span></div>
    @endif

    @if(count($items) === 0)
        <div class="empty">
            <i class="fa fa-lock"></i>
            <h2>Belum ada akses modul</h2>
            <p>Akun Anda aktif, tetapi belum diberi akses ke modul mana pun.<br>Hubungi admin untuk mengatur akses melalui menu <b>Checklist Modul</b>.</p>
        </div>
    @else
        <div class="links">
            @foreach($items as $code => $item)
                <a class="link" href="{{ route('portal.modules.go', $code) }}" style="--accent:{{ $item['color'] ?? '#2563eb' }};--i:{{ $loop->index }}">
                    <span class="link-icon"><i class="fa {{ $item['icon'] ?? 'fa-cube' }}"></i></span>
                    <span class="link-text">
                        <span class="link-code">{{ $item['label'] ?? $code }}</span>
                        <span class="link-name">{{ $item['name'] ?? $code }}</span>
                        <span class="link-desc">{{ $item['description'] ?? '' }}</span>
                    </span>
                    <span class="link-arrow"><i class="fa fa-arrow-right"></i></span>
                </a>
            @endforeach
        </div>
    @endif

    <div class="foot">Copyright &copy; {{ now()->year }}.</div>
</div>
</body>
</html>