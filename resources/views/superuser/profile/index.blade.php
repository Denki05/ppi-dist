@extends('superuser.app')

@section('content')
@php
  $email = strtolower(trim($superuser->email ?? ''));
  $allowedDomains = ['gmail.com', 'googlemail.com', 'lssoft88.xyz', 'lsfragrance.id'];
  $domain = substr(strrchr($email, '@') ?: '', 1);
  $emailOk = $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && in_array($domain, $allowedDomains, true);
  $linked = !empty($superuser->google_id);
  $initial = strtoupper(substr($superuser->name ?: $superuser->username, 0, 1));
@endphp

<style>
  .pfx-hero{position:relative;border-radius:18px;overflow:hidden;background:linear-gradient(120deg,#1d4ed8 0%,#2563eb 45%,#0891b2 100%);box-shadow:0 14px 34px rgba(37,99,235,.28);margin-bottom:14px}
  .pfx-hero::before{content:"";position:absolute;width:340px;height:340px;right:-110px;top:-130px;border-radius:50%;background:rgba(255,255,255,.12)}
  .pfx-hero::after{content:"";position:absolute;width:200px;height:200px;right:90px;bottom:-110px;border-radius:50%;background:rgba(255,255,255,.08)}
  .pfx-hero-in{position:relative;z-index:1;display:flex;align-items:center;gap:16px;padding:20px 22px;color:#fff}
  .pfx-ava{width:64px;height:64px;flex:0 0 64px;border-radius:20px;overflow:hidden;background:#fff;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:800;color:#1d4ed8;box-shadow:0 8px 18px rgba(0,0,0,.25)}
  .pfx-ava img{width:100%;height:100%;object-fit:cover}
  .pfx-name{font-size:17px;font-weight:800;line-height:1.25}
  .pfx-sub{font-size:12.5px;color:rgba(255,255,255,.82)}
  .pfx-pills{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
  .pfx-pill{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:800;padding:4px 10px;border-radius:999px;background:rgba(255,255,255,.16);color:#fff;border:1px solid rgba(255,255,255,.25)}
  .pfx-pill.ok{background:#16a34a;border-color:#16a34a}
  .pfx-pill.warn{background:#f59e0b;border-color:#f59e0b;color:#fff}
  .pfx-pill.bad{background:#ef4444;border-color:#ef4444}
  .pfx-hero .btn{box-shadow:0 6px 14px rgba(0,0,0,.2)}
  .pfx-card{background:#fff;border:1px solid #e8edf3;border-radius:16px;box-shadow:0 6px 18px rgba(15,23,42,.05);margin-bottom:14px;overflow:hidden}
  .pfx-card-h{display:flex;align-items:center;gap:8px;padding:11px 16px;border-bottom:1px solid #eef2f7;font-size:13px;font-weight:800;color:#0f172a}
  .pfx-card-h i{color:#2563eb}
  .pfx-card-h small{margin-left:auto;font-weight:600;color:#94a3b8;font-size:11.5px}
  .pfx-card-b{padding:16px}
  .pfx-label{display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:6px}
  .pfx-input{border-radius:10px !important;border:1.5px solid #e2e8f0 !important;background:#f8fafc !important;font-size:13.5px !important}
  .pfx-input:focus{border-color:#2563eb !important;background:#fff !important;box-shadow:0 0 0 3px rgba(37,99,235,.12) !important}
  .pfx-hint{font-size:11.5px;color:#94a3b8;margin-top:6px}
  .pfx-banner{display:flex;gap:10px;align-items:flex-start;border-radius:14px;padding:11px 14px;margin-bottom:14px;font-size:13px;line-height:1.6}
  .pfx-banner.warn{background:#fffbeb;border:1px solid #fcd34d;color:#92400e}
  .pfx-banner.info{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af}
  .pfx-banner.ok{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
  .pfx-gbtn{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:800;border-radius:10px;padding:8px 14px;text-decoration:none;border:1.5px solid #e2e8f0;background:#fff;color:#0f172a;white-space:nowrap}
  .pfx-gbtn:hover{box-shadow:0 8px 18px rgba(0,0,0,.1);text-decoration:none;color:#0f172a}
  .pfx-gbtn.primary{background:linear-gradient(135deg,#2563eb,#0891b2);border:none;color:#fff}
  /* Padatkan plugin fileinput bawaan */
  .pfx-card .file-drop-zone{min-height:90px;padding:10px}
  .pfx-card .krajee-default.file-preview-frame{margin:4px}
  @media (max-width:767px){.pfx-hero-in{flex-wrap:wrap}}
</style>

<div id="alert-block"></div>

{{-- HERO --}}
<div class="pfx-hero">
  <div class="pfx-hero-in">
    <div class="pfx-ava">
      @if($superuser->image && file_exists(\App\Entities\Account\Superuser::$directory_image.$superuser->image))
        <img src="{{ $superuser->img }}" alt="Foto">
      @else
        {{ $initial }}
      @endif
    </div>
    <div class="flex-fill">
      <div class="pfx-name">{{ $superuser->name ?: $superuser->username }}</div>
      <div class="pfx-sub">@ {{ $superuser->username }}@if(!empty($superuser->division)) &middot; {{ $superuser->division }}@endif</div>
      <div class="pfx-pills">
        @if($linked)
          <span class="pfx-pill ok"><i class="fa fa-google"></i>Google terhubung</span>
        @else
          <span class="pfx-pill warn"><i class="fa fa-google"></i>Belum terhubung</span>
        @endif
        @if($emailOk)
          <span class="pfx-pill"><i class="fa fa-envelope"></i>Email valid</span>
        @else
          <span class="pfx-pill bad"><i class="fa fa-exclamation-triangle"></i>Perbaiki email</span>
        @endif
        @if(empty($superuser->is_active))
          <span class="pfx-pill bad">Nonaktif</span>
        @endif
      </div>
    </div>
    <a href="{{ route('superuser.index') }}" class="btn btn-sm btn-light"><i class="fa fa-arrow-left mr-1"></i>Dasbor</a>
  </div>
</div>

@if(!$emailOk)
  <div class="pfx-banner warn"><i class="fa fa-exclamation-triangle mt-1"></i><div>Email <b>{{ $superuser->email }}</b> bukan Gmail asli. Ganti ke Gmail kamu di bawah → Simpan → klik <b>Hubungkan Google</b>. Ganti email otomatis melepas hubungan lama.</div></div>
@elseif(!$linked)
  <div class="pfx-banner info"><i class="fa fa-google mt-1"></i><div>Email sudah oke. Tinggal klik <b>Hubungkan Google</b> di kartu Login Google agar bisa masuk 1 klik.</div></div>
@else
  <div class="pfx-banner ok"><i class="fa fa-check-circle mt-1"></i><div>Akun siap SSO. Terhubung sejak {{ optional($superuser->email_verified_at ? \Carbon\Carbon::parse($superuser->email_verified_at) : null)->format('d M Y') ?: '-' }}.</div></div>
@endif

@if(session('status'))
  <div class="pfx-banner ok"><i class="fa fa-check mt-1"></i><div>{{ session('status') }}</div></div>
@endif
@if($errors->any())
  <div class="pfx-banner warn"><i class="fa fa-exclamation-triangle mt-1"></i><div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div></div>
@endif

<form class="ajax" data-action="{{ route('superuser.profile.update') }}" data-type="POST" enctype="multipart/form-data">
  <div class="row gutters-tiny">
    <div class="col-md-7">
      <div class="pfx-card">
        <div class="pfx-card-h"><i class="fa fa-user"></i>Data Diri<small>Nama, email SSO & foto</small></div>
        <div class="pfx-card-b">
          <div class="row gutters-tiny">
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label class="pfx-label" for="name">Nama lengkap</label>
                <input type="text" class="form-control form-control-sm pfx-input" id="name" name="name" value="{{ $superuser->name }}" placeholder="Nama lengkap">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label class="pfx-label" for="email">Email SSO (Gmail asli)</label>
                <input type="email" class="form-control form-control-sm pfx-input" id="email" name="email" value="{{ $superuser->email }}" placeholder="nama@gmail.com">
              </div>
            </div>
          </div>
          <div class="pfx-hint mb-3">Username <b>{{ $superuser->username }}</b> tidak bisa diubah. Ganti email = hubungkan ulang Google.</div>
          <div class="form-group mb-0">
            <label class="pfx-label" for="image">Foto profil</label>
            <input type="file" id="image" name="image" data-max-file-size="2000" accept="image/png, image/jpeg" data-src="{{ $superuser->img }}">
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="pfx-card">
        <div class="pfx-card-h"><i class="fa fa-google"></i>Login Google<small>1 klik</small></div>
        <div class="pfx-card-b d-flex align-items-center">
          <div class="flex-fill" style="font-size:12.5px;color:#475569">
            @if($linked)
              <b style="color:#0f172a">Aktif.</b> Tidak perlu password tiap hari.
            @else
              <b style="color:#0f172a">Belum aktif.</b> Pastikan email Gmail, lalu hubungkan.
            @endif
          </div>
          @if($linked)
            <button type="button" class="btn btn-sm btn-alt-danger ml-2" id="btn-unlink-google">Lepas</button>
          @else
            <a href="{{ route('auth.superuser.sso.redirect') }}" class="pfx-gbtn primary ml-2"><i class="fa fa-google"></i>Hubungkan</a>
          @endif
        </div>
      </div>

      <div class="pfx-card">
        <div class="pfx-card-h"><i class="fa fa-lock"></i>Password<small>opsional</small></div>
        <div class="pfx-card-b">
          <div class="form-group mb-2">
            <label class="pfx-label" for="current_password">Password saat ini</label>
            <input type="password" class="form-control form-control-sm pfx-input" id="current_password" name="current_password" autocomplete="new-password">
          </div>
          <div class="row gutters-tiny">
            <div class="col-6">
              <div class="form-group mb-0">
                <label class="pfx-label" for="new_password">Baru</label>
                <input type="password" class="form-control form-control-sm pfx-input" id="new_password" name="new_password" autocomplete="new-password">
              </div>
            </div>
            <div class="col-6">
              <div class="form-group mb-0">
                <label class="pfx-label" for="new_password_confirmation">Konfirmasi</label>
                <input type="password" class="form-control form-control-sm pfx-input" id="new_password_confirmation" name="new_password_confirmation" autocomplete="new-password">
              </div>
            </div>
          </div>
          <div class="pfx-hint">8–16 karakter. Kosongkan kalau tidak ganti.</div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" style="border-radius:12px;font-weight:800"><i class="fa fa-check mr-1"></i>Simpan Perubahan</button>
      <div class="pfx-hint text-center mt-2">Kandidat baru daftar via halaman SSO, bukan dari sini.</div>
    </div>
  </div>
</form>
@endsection

@include('superuser.asset.plugin.fileinput')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
$(document).ready(function () {
  $('#image').fileinput({
    theme: 'explorer-fa',
    browseOnZoneClick: true,
    showCancel: false,
    showClose: false,
    showUpload: false,
    browseLabel: '',
    removeLabel: '',
    initialPreview: $('#image').data('src'),
    initialPreviewAsData: true,
    fileActionSettings: { showDrag: false, showRemove: false },
    initialPreviewConfig: [{ caption: '{{ $superuser->name ?? $superuser->username }}' }]
  });

  $('#btn-unlink-google').on('click', function () {
    if (!confirm('Lepas hubungan Google? Login password tetap bisa.')) return;
    var btn = $(this);
    btn.prop('disabled', true);
    $.ajax({
      url: '{{ route('superuser.profile.unlink-google') }}',
      type: 'POST',
      data: { _token: '{{ csrf_token() }}' },
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    }).done(function (response) {
      if (response && response.data && response.data.notification) responded(response.data.notification);
      if (response && response.data && response.data.redirect_to === 'reload()') redirect('reload()', 1500);
      else location.reload();
    }).fail(function (xhr) {
      var msg = (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.notification && xhr.responseJSON.data.notification.content) || 'Gagal melepas. Coba lagi.';
      responded({ alert: 'block', type: 'alert-danger', header: 'Error', content: msg });
      btn.prop('disabled', false);
    });
  });
});
</script>
@endpush
