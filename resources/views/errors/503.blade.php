<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Maintenance &mdash; {{ config('app.name', 'Laravel') }}</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        html,body{height:100%}
        body{font-family:Muli,-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;background:linear-gradient(135deg,#0f2027 0%,#203a43 45%,#2c5364 100%);color:#fff;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px 16px;position:relative;overflow:hidden}
        .orb{position:fixed;border-radius:50%;filter:blur(90px);opacity:.45;pointer-events:none}
        .orb.o1{width:480px;height:480px;left:-140px;top:-140px;background:#3b82f6}
        .orb.o2{width:520px;height:520px;right:-160px;bottom:-160px;background:#06b6d4}
        .orb.o3{width:220px;height:220px;left:12%;bottom:8%;background:#f59e0b;opacity:.25}
        .ring{position:fixed;border-radius:50%;border:1px solid rgba(255,255,255,.12);pointer-events:none}
        .ring.r1{width:200px;height:200px;right:8%;top:10%}
        .ring.r2{width:110px;height:110px;left:6%;top:16%}
        .card{position:relative;width:100%;max-width:560px;text-align:center;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.16);border-radius:24px;padding:44px 40px 36px;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);box-shadow:0 30px 80px rgba(0,0,0,.45);animation:rise .5s ease both}
        @keyframes rise{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
        .badge{display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:800;letter-spacing:.14em;color:#fde68a;background:rgba(245,158,11,.15);border:1px solid rgba(245,158,11,.5);border-radius:999px;padding:7px 18px;margin-bottom:22px}
        .dot{width:8px;height:8px;border-radius:50%;background:#f87171;animation:blink 1.2s ease-in-out infinite}
        @keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}
        .gears{display:flex;align-items:center;justify-content:center;margin-bottom:18px}
        .gears svg{overflow:visible}
        .spin-a{animation:spin 9s linear infinite;transform-origin:center}
        .spin-b{animation:spin-rev 6s linear infinite;transform-origin:center}
        @keyframes spin{to{transform:rotate(360deg)}}
        @keyframes spin-rev{to{transform:rotate(-360deg)}}
        h1{font-size:30px;font-weight:800;line-height:1.3;margin-bottom:10px}
        .sub{color:rgba(255,255,255,.75);font-size:15px;line-height:1.7;margin-bottom:6px}
        .msg{display:inline-block;margin:12px 0 4px;font-size:14px;color:#fde68a;background:rgba(245,158,11,.12);border:1px dashed rgba(245,158,11,.55);border-radius:12px;padding:10px 18px;line-height:1.6;max-width:100%}
        .divider{height:1px;background:rgba(255,255,255,.14);margin:22px 0}
        .btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
        .btn{display:inline-flex;align-items:center;gap:9px;padding:13px 28px;border-radius:12px;font-size:14px;font-weight:800;text-decoration:none;cursor:pointer;border:none;transition:transform .12s,box-shadow .12s,filter .12s}
        .btn:hover{transform:translateY(-1px);filter:brightness(1.05)}
        .btn-solid{background:#fff;color:#0f172a;box-shadow:0 10px 24px rgba(0,0,0,.3)}
        .btn-ghost{background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.45)}
        .auto{margin-top:20px;font-size:12.5px;color:rgba(255,255,255,.55)}
        .auto b{color:#fde68a}
        .foot{margin-top:14px;font-size:12px;color:rgba(255,255,255,.4)}
        @media (max-width:520px){.card{padding:34px 24px 28px}h1{font-size:24px}}
    </style>
</head>
<body>
    <span class="orb o1"></span>
    <span class="orb o2"></span>
    <span class="orb o3"></span>
    <span class="ring r1"></span>
    <span class="ring r2"></span>

    <div class="card">
        <span class="badge"><span class="dot"></span>503 &bull; MAINTENANCE</span>

        <div class="gears" aria-hidden="true">
            <svg width="120" height="96" viewBox="0 0 120 96" fill="none">
                <g class="spin-a">
                    <g fill="#f59e0b">
                        <rect x="26" y="2" width="8" height="16" rx="2"/>
                        <rect x="26" y="2" width="8" height="16" rx="2" transform="rotate(45 30 30)"/>
                        <rect x="26" y="2" width="8" height="16" rx="2" transform="rotate(90 30 30)"/>
                        <rect x="26" y="2" width="8" height="16" rx="2" transform="rotate(135 30 30)"/>
                        <rect x="26" y="2" width="8" height="16" rx="2" transform="rotate(180 30 30)"/>
                        <rect x="26" y="2" width="8" height="16" rx="2" transform="rotate(225 30 30)"/>
                        <rect x="26" y="2" width="8" height="16" rx="2" transform="rotate(270 30 30)"/>
                        <rect x="26" y="2" width="8" height="16" rx="2" transform="rotate(315 30 30)"/>
                    </g>
                    <circle cx="30" cy="30" r="17" fill="#f59e0b"/>
                    <circle cx="30" cy="30" r="7" fill="#203a43"/>
                </g>
                <g class="spin-b">
                    <g fill="#38bdf8">
                        <rect x="82" y="52" width="7" height="14" rx="2"/>
                        <rect x="82" y="52" width="7" height="14" rx="2" transform="rotate(60 85 78)"/>
                        <rect x="82" y="52" width="7" height="14" rx="2" transform="rotate(120 85 78)"/>
                        <rect x="82" y="52" width="7" height="14" rx="2" transform="rotate(180 85 78)"/>
                        <rect x="82" y="52" width="7" height="14" rx="2" transform="rotate(240 85 78)"/>
                        <rect x="82" y="52" width="7" height="14" rx="2" transform="rotate(300 85 78)"/>
                    </g>
                    <circle cx="85" cy="78" r="14" fill="#38bdf8"/>
                    <circle cx="85" cy="78" r="6" fill="#203a43"/>
                </g>
            </svg>
        </div>

        <h1>Sistem Sedang Maintenance</h1>
        <p class="sub">Mohon maaf atas ketidaknyamanannya.<br>Program saat ini sedang menjalani pemeliharaan.</p>

        @if(!empty($exception) && trim($exception->getMessage()) !== '')
            <div><span class="msg">{{ $exception->getMessage() }}</span></div>
        @endif

        <div class="divider"></div>

        <div class="btns">
            <button class="btn btn-solid" type="button" onclick="location.reload()">&#8635;&nbsp; Muat Ulang</button>
            <a class="btn btn-ghost" href="{{ url('auth/superuser') }}">Login sebagai Admin</a>
        </div>

        <p class="auto">Mengecek status otomatis dalam <b><span id="mm-retry">60</span> detik</b> &hellip;</p>
        <p class="foot">Terima kasih atas pengertian Anda.</p>
    </div>

    <script>
        (function () {
            var s = 60, el = document.getElementById('mm-retry');
            setInterval(function () {
                s -= 1;
                if (s <= 0) { location.reload(); return; }
                if (el) el.textContent = s;
            }, 1000);
        })();
    </script>
</body>
</html>