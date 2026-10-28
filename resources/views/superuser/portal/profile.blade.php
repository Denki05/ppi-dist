<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profil Saya</title>
    <link rel="shortcut icon" href="{{ asset('icon.png') }}">
    <link rel="stylesheet" href="{{ asset('superuser_assets/plus-admin/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;font-family:Muli,-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#081216;color:#f8fafc;display:flex;justify-content:center;padding:28px 18px 28px}
        body::before{content:"";position:fixed;inset:0;z-index:-1;background:
            radial-gradient(900px 520px at 18% -10%,rgba(37,99,235,.22),transparent 60%),
            radial-gradient(760px 460px at 88% 8%,rgba(8,145,178,.18),transparent 62%),
            radial-gradient(680px 420px at 50% 110%,rgba(22,163,74,.14),transparent 60%),
            linear-gradient(160deg,#071116 0%,#0b1a21 52%,#0a151b 100%)}
        .wrap{width:100%;max-width:780px}
        .top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
        .top .out{display:inline-flex;align-items:center;gap:7px;color:rgba(255,255,255,.72);text-decoration:none;font-size:12.5px;font-weight:700;padding:8px 12px;border:1px solid rgba(255,255,255,.14);border-radius:999px;transition:background .18s ease,color .18s ease,border-color .18s ease}
        .top .out:hover{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.3);color:#fff;text-decoration:none}
        .hero{display:flex;align-items:center;gap:14px;padding:16px 18px;border-radius:16px;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.1);margin-bottom:12px;backdrop-filter:blur(10px)}
        .ava{width:56px;height:56px;flex:0 0 56px;border-radius:16px;overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;color:#fff;background:linear-gradient(135deg,#2563eb,#0891b2 55%,#16a34a);box-shadow:0 0 0 1px rgba(255,255,255,.14)}
        .ava img{width:100%;height:100%;object-fit:cover}
        .hero h1{margin:0 0 3px;font-size:18px;font-weight:800;letter-spacing:-.02em}
        .hero .sub{font-size:12.5px;color:rgba(255,255,255,.56)}
        .pills{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
        .pill{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:800;padding:3px 9px;border-radius:999px;text-decoration:none;border:1px solid rgba(255,255,255,.25);background:rgba(255,255,255,.14);color:#fff}
        .pill:hover{color:#fff;text-decoration:none;background:rgba(255,255,255,.24)}
        .pill.ok{background:#16a34a;border-color:#16a34a}
        .pill.warn{background:#f59e0b;border-color:#f59e0b}
        .pill.bad{background:#ef4444;border-color:#ef4444}
        .banner{display:flex;gap:10px;align-items:flex-start;border-radius:12px;padding:10px 13px;margin-bottom:12px;font-size:12.5px;line-height:1.6}
        .banner.warn{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.4);color:#fcd34d}
        .banner.info{background:rgba(37,99,235,.14);border:1px solid rgba(59,130,246,.4);color:#bfdbfe}
        .banner.ok{background:rgba(22,163,74,.14);border:1px solid rgba(34,197,94,.4);color:#bbf7d0}
        .banner.bad{background:rgba(239,68,68,.14);border:1px solid rgba(248,113,113,.4);color:#fecaca}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .card{border-radius:14px;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.1);overflow:hidden;backdrop-filter:blur(10px)}
        .card-h{display:flex;align-items:center;gap:8px;padding:9px 13px;border-bottom:1px solid rgba(255,255,255,.08);font-size:12px;font-weight:800;color:#e2e8f0}
        .card-h i{color:#38bdf8}
        .card-b{padding:13px}
        .label{display:block;font-size:11.5px;font-weight:700;color:rgba(255,255,255,.72);margin-bottom:5px}
        .input{width:100%;border-radius:9px;border:1.5px solid rgba(255,255,255,.14);background:rgba(0,0,0,.25);color:#f8fafc;font-size:13px;padding:8px 10px;outline:none;transition:border-color .18s ease,box-shadow .18s ease}
        .input:focus{border-color:#38bdf8;box-shadow:0 0 0 3px rgba(56,189,248,.16)}
        .input[type=file]{padding:6px;font-size:12px}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .hint{font-size:11px;color:rgba(255,255,255,.38);margin-top:5px;line-height:1.5}
        .gbtn{display:inline-flex;align-items:center;justify-content:center;gap:7px;font-size:12.5px;font-weight:800;border-radius:9px;padding:8px 13px;border:1.5px solid rgba(255,255,255,.16);background:rgba(255,255,255,.08);color:#f8fafc;cursor:pointer;white-space:nowrap;transition:background .18s ease}
        .gbtn:hover{background:rgba(255,255,255,.16)}
        .gbtn.primary{background:linear-gradient(135deg,#2563eb,#0891b2);border:none;color:#fff}
        .gbtn.danger{background:rgba(239,68,68,.18);border-color:rgba(248,113,113,.45);color:#fecaca}
        .gbtn.warn{background:rgba(245,158,11,.16);border-color:rgba(245,158,11,.45);color:#fcd34d}
        .gbtn:disabled{opacity:.6;cursor:not-allowed}
        .inline{display:flex;align-items:center;gap:8px}
        .inline .input{flex:1}
        .actions{display:flex;justify-content:flex-end;gap:8px;margin-top:12px}
        .foot{margin-top:18px;text-align:center;color:rgba(255,255,255,.34);font-size:11px}
        @media(max-width:640px){.grid{grid-template-columns:1fr}.row{grid-template-columns:1fr}body{padding:20px 14px}.hero{flex-wrap:wrap}}
    </style>
</head>
<body>
@php
    $displayName = $superuser->name ?: $superuser->username;
    $initial = strtoupper(substr($displayName, 0, 1)) ?: 'U';
    $emailRaw = strtolower(trim((string) ($superuser->email ?? '')));
    $allowedDomains = config('services.google.allowed_domains', ['gmail.com', 'googlemail.com']);
    $emailDomain = substr(strrchr($emailRaw, '@') ?: '', 1);
    $emailOk = $emailRaw !== '' && filter_var($emailRaw, FILTER_VALIDATE_EMAIL) && in_array($emailDomain, $allowedDomains, true);
    $linked = !empty($superuser->google_id);
    $hasPhoto = $superuser->image && file_exists(\App\Entities\Account\Superuser::$directory_image.$superuser->image);
@endphp
<div class="wrap">
    <div class="top">
        <a class="out" href="{{ route('portal.modules') }}"><i class="fa fa-th-large"></i> Portal</a>
        <a class="out" href="{{ route('superuser.logout') }}"><i class="fa fa-sign-out"></i> Keluar</a>
    </div>

    <div class="hero">
        <div class="ava">
            @if($hasPhoto)
                <img src="{{ $superuser->img }}" alt="Foto">
            @else
                {{ $initial }}
            @endif
        </div>
        <div style="min-width:0">
            <h1>{{ $displayName }}</h1>
            <div class="sub">@ {{ $superuser->username }}@if(!empty($superuser->division)) &middot; {{ $superuser->division }}@endif</div>
            <div class="pills">
                @if($linked)
                    <span class="pill ok"><i class="fa fa-google"></i> Google terhubung</span>
                @else
                    <span class="pill warn"><i class="fa fa-google"></i> Belum terhubung</span>
                @endif
                @if($emailOk)
                    <span class="pill"><i class="fa fa-envelope"></i> Email valid</span>
                @else
                    <span class="pill bad"><i class="fa fa-exclamation-triangle"></i> Perbaiki email</span>
                @endif
            </div>
        </div>
    </div>

    @if(!$emailOk)
        <div class="banner warn"><i class="fa fa-exclamation-triangle"></i><div>Email <b>{{ $superuser->email }}</b> belum memakai domain yang diizinkan. Ganti ke Gmail/domain kantor di kartu <b>Data Diri</b>, simpan, lalu klik <b>Hubungkan Google</b>.</div></div>
    @elseif(!$linked)
        <div class="banner info"><i class="fa fa-google"></i><div>Email sudah oke. Klik <b>Hubungkan</b> di kartu Login Google agar ke depan bisa masuk 1 klik tanpa password.</div></div>
    @else
        <div class="banner ok"><i class="fa fa-check-circle"></i><div>Akun siap login Google. Terhubung sejak {{ $superuser->google_linked_at ? $superuser->google_linked_at->format('d M Y H:i') : '-' }}.</div></div>
    @endif

    @if(session('status'))
        <div class="banner ok"><i class="fa fa-check"></i><div>{{ session('status') }}</div></div>
    @endif
    @if($errors->any())
        <div class="banner warn"><i class="fa fa-exclamation-triangle"></i><div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div></div>
    @endif

    <div id="pfx-alert"></div>

    <form id="profile-form" autocomplete="off">
        <div class="grid">
            <div class="card">
                <div class="card-h"><i class="fa fa-user"></i> Data Diri</div>
                <div class="card-b">
                    <div class="row">
                        <div>
                            <label class="label" for="name">Nama lengkap</label>
                            <input class="input" type="text" id="name" name="name" value="{{ $superuser->name }}" placeholder="Nama lengkap">
                        </div>
                        <div>
                            <label class="label" for="email">Email SSO</label>
                            <input class="input" type="email" id="email" name="email" value="{{ $superuser->email }}" placeholder="nama@gmail.com">
                        </div>
                    </div>
                    <div class="hint">Username <b>{{ $superuser->username }}</b> tidak bisa diubah. Ganti email = hubungkan ulang Google.</div>
                    <div style="margin-top:10px">
                        <label class="label" for="image">Foto profil</label>
                        <input class="input" type="file" id="image" name="image" accept="image/png, image/jpeg">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-h"><i class="fa fa-google"></i> Login Google</div>
                <div class="card-b">
                    <div style="font-size:12px;color:rgba(255,255,255,.62);line-height:1.6">
                        @if($linked)
                            <b style="color:#f8fafc">Aktif.</b> Login Google terakhir: {{ $superuser->google_last_login_at ? $superuser->google_last_login_at->format('d M Y H:i') : 'belum pernah' }}.
                        @else
                            <b style="color:#f8fafc">Belum aktif.</b> Pastikan email Gmail/domain kantor, lalu hubungkan.
                        @endif
                    </div>
                    <div class="inline" style="margin-top:10px">
                        <input class="input" type="password" id="google_password" placeholder="Password saat ini" autocomplete="new-password">
                        @if($linked)
                            <button type="button" class="gbtn danger" id="btn-unlink-google">Lepas</button>
                        @else
                            <button type="button" class="gbtn primary" id="btn-link-google"><i class="fa fa-google"></i> Hubungkan</button>
                        @endif
                    </div>
                    <div class="hint">Wajib isi password akun untuk hubungkan/lepas Google.</div>
                </div>
            </div>

            <div class="card">
                <div class="card-h"><i class="fa fa-lock"></i> Password</div>
                <div class="card-b">
                    <label class="label" for="current_password">Password saat ini</label>
                    <input class="input" type="password" id="current_password" name="current_password" autocomplete="new-password">
                    <div class="row" style="margin-top:9px">
                        <div>
                            <label class="label" for="new_password">Baru</label>
                            <input class="input" type="password" id="new_password" name="new_password" autocomplete="new-password">
                        </div>
                        <div>
                            <label class="label" for="new_password_confirmation">Konfirmasi</label>
                            <input class="input" type="password" id="new_password_confirmation" name="new_password_confirmation" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="hint">8–16 karakter. Kosongkan kalau tidak ganti.</div>
                </div>
            </div>

            <div class="card">
                <div class="card-h"><i class="fa fa-shield"></i> Keamanan</div>
                <div class="card-b">
                    <div style="font-size:12px;color:rgba(255,255,255,.62);line-height:1.6">Matikan auto-login ("Ingat saya") di semua perangkat lain. Perangkat ini tetap aktif.</div>
                    <button type="button" class="gbtn warn" id="btn-logout-all" style="margin-top:10px;width:100%"><i class="fa fa-sign-out"></i> Logout semua perangkat</button>
                </div>
            </div>
        </div>

        <div class="actions">
            <a class="gbtn" href="{{ route('portal.modules') }}"><i class="fa fa-arrow-left"></i> Kembali ke portal</a>
            <button type="submit" class="gbtn primary"><i class="fa fa-check"></i> Simpan Perubahan</button>
        </div>
    </form>

    <div class="foot">Copyright &copy; {{ now()->year }}.</div>
</div>

<script>
(function () {
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var alertBox = document.getElementById('pfx-alert');

    function showAlert(type, message) {
        alertBox.innerHTML = '';
        var box = document.createElement('div');
        box.className = 'banner ' + type;
        var icon = document.createElement('i');
        icon.className = 'fa ' + (type === 'ok' ? 'fa-check-circle' : (type === 'info' ? 'fa-info-circle' : 'fa-exclamation-circle'));
        var text = document.createElement('div');
        text.textContent = message;
        box.appendChild(icon);
        box.appendChild(text);
        alertBox.appendChild(box);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function post(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: data,
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json().catch(function () { return null; }).then(function (body) {
                return { ok: res.ok, body: body };
            });
        });
    }

    function handle(res, fallback) {
        var data = (res.body && res.body.data) ? res.body.data : {};
        var n = data.notification;

        if (n && n.content) {
            var message = Array.isArray(n.content) ? n.content.join(' ') : String(n.content);
            var type = String(n.type || '');
            var cls = 'warn';

            if (type === 'success' || type === 'notify') {
                cls = res.ok ? 'ok' : 'warn';
            } else if (type === 'info') {
                cls = 'info';
            } else if (type === 'danger' || type === 'error' || type.indexOf('alert-danger') === 0) {
                cls = 'bad';
            }

            showAlert(cls, message);
        } else if (!res.ok) {
            showAlert('bad', fallback);
        }

        return res.ok ? data.redirect_to : null;
    }

    function disable(btn, state) {
        if (btn) btn.disabled = state;
    }

    document.getElementById('profile-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = e.target.querySelector('button[type="submit"]');
        disable(btn, true);

        post(@json(route('portal.profile.update')), new FormData(e.target)).then(function (res) {
            var redirect = handle(res, 'Gagal menyimpan perubahan.');
            if (redirect === 'reload()') {
                setTimeout(function () { location.reload(); }, 900);
            } else if (redirect) {
                setTimeout(function () { location.href = redirect; }, 900);
            } else {
                disable(btn, false);
            }
        }).catch(function () {
            showAlert('bad', 'Tidak bisa menghubungi server. Coba lagi.');
            disable(btn, false);
        });
    });

    var linkBtn = document.getElementById('btn-link-google');
    if (linkBtn) {
        linkBtn.addEventListener('click', function () {
            var pwd = document.getElementById('google_password').value.trim();
            if (!pwd) {
                showAlert('warn', 'Isi password saat ini dulu untuk menghubungkan Google.');
                return;
            }

            var data = new FormData();
            data.append('current_password', pwd);
            disable(linkBtn, true);

            post(@json(route('portal.profile.google-link')), data).then(function (res) {
                var redirect = handle(res, 'Gagal menghubungkan Google. Coba lagi.');
                if (redirect) {
                    setTimeout(function () { location.href = redirect; }, 900);
                } else {
                    disable(linkBtn, false);
                }
            }).catch(function () {
                showAlert('bad', 'Tidak bisa menghubungi server. Coba lagi.');
                disable(linkBtn, false);
            });
        });
    }

    var unlinkBtn = document.getElementById('btn-unlink-google');
    if (unlinkBtn) {
        unlinkBtn.addEventListener('click', function () {
            var pwd = document.getElementById('google_password').value.trim();
            if (!pwd) {
                showAlert('warn', 'Isi password saat ini dulu untuk melepas Google.');
                return;
            }
            if (!confirm('Lepas hubungan Google? Login password tetap bisa.')) return;

            var data = new FormData();
            data.append('current_password', pwd);
            disable(unlinkBtn, true);

            post(@json(route('portal.profile.unlink-google')), data).then(function (res) {
                handle(res, 'Gagal melepas Google. Coba lagi.');
                if (res.ok) setTimeout(function () { location.reload(); }, 900);
                else disable(unlinkBtn, false);
            }).catch(function () {
                showAlert('bad', 'Tidak bisa menghubungi server. Coba lagi.');
                disable(unlinkBtn, false);
            });
        });
    }

    document.getElementById('btn-logout-all').addEventListener('click', function () {
        if (!confirm('Logout dari semua perangkat lain? Auto-login akan dimatikan.')) return;
        var btn = this;
        disable(btn, true);

        post(@json(route('portal.profile.logout-all-devices')), new FormData()).then(function (res) {
            handle(res, 'Gagal. Coba lagi.');
            if (res.ok) setTimeout(function () { location.reload(); }, 900);
            else disable(btn, false);
        }).catch(function () {
            showAlert('bad', 'Tidak bisa menghubungi server. Coba lagi.');
            disable(btn, false);
        });
    });
})();
</script>
</body>
</html>
