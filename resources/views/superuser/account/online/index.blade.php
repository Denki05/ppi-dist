@extends('superuser.app')

@push('styles')
<style>
  .onl-page { --ink:#0f172a; --muted:#64748b; --line:#e9eef5; --soft:#f8fafc; max-width:1320px; margin:0 auto; padding:0 8px 24px; }
  .onl-page .breadcrumb { background:transparent; padding:0 2px 10px; margin:0; font-size:12px; }
  .onl-page .breadcrumb-item, .onl-page .breadcrumb-item a { color:#94a3b8; }
  .onl-page .breadcrumb-item.active { color:#475569; font-weight:600; }
  .onl-page .breadcrumb-item + .breadcrumb-item::before { color:#cbd5e1; }

  .onl-card { background:#fff; border:1px solid var(--line); border-radius:16px; box-shadow:0 1px 2px rgba(15,23,42,.04), 0 12px 32px -20px rgba(15,23,42,.18); overflow:hidden; }
  .onl-head { display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:space-between; padding:18px 20px 14px; }
  .onl-title { font-size:19px; font-weight:700; color:var(--ink); letter-spacing:-.01em; }
  .onl-sub { color:var(--muted); font-size:12.5px; margin-top:2px; }
  .onl-actions { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
  .onl-chip { display:inline-flex; align-items:center; gap:6px; height:30px; padding:0 11px; border-radius:999px; background:#ecfdf5; color:#047857; font-size:12px; font-weight:700; }
  .onl-chip.is-muted { background:#f1f5f9; color:#475569; }
  .onl-btn { display:inline-flex; align-items:center; gap:6px; height:32px; padding:0 12px; border-radius:10px; border:1px solid #e2e8f0; background:#fff; color:#475569; font-size:12.5px; font-weight:600; }
  .onl-btn:hover { border-color:#cbd5e1; background:var(--soft); color:var(--ink); text-decoration:none; }

  .onl-table-wrap { border-top:1px solid var(--line); }
  .onl-page table.onl-table { width:100%; margin:0; }
  .onl-page .onl-table thead th { padding:9px 16px; background:#fbfcfe; border:0; border-bottom:1px solid var(--line); color:#94a3b8; font-size:10.5px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; text-align:left; white-space:nowrap; }
  .onl-page .onl-table tbody td { padding:11px 16px; border:0; border-top:1px solid #f1f5f9; vertical-align:middle; }
  .onl-page .onl-table tbody tr:first-child td { border-top:0; }
  .onl-page .onl-table tbody tr:hover { background:var(--soft); }

  .onl-user { display:flex; align-items:center; gap:10px; }
  .onl-avatar { flex:0 0 34px; width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#dcfce7,#d1fae5); color:#047857; font-size:12px; font-weight:700; }
  .onl-name { display:flex; align-items:center; gap:6px; font-size:13.5px; font-weight:600; color:var(--ink); line-height:1.25; }
  .onl-username { font-size:11.5px; color:var(--muted); margin-top:2px; }
  .onl-you { display:inline-flex; align-items:center; height:18px; padding:0 6px; border-radius:5px; background:#eff6ff; color:#1d4ed8; font-size:10px; font-weight:700; }
  .onl-online { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; color:#047857; }
  .onl-dot { width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.15); }
  .onl-email { font-size:12.5px; color:#334155; }
  .onl-ip { font-size:12px; color:#475569; }
  .onl-time { font-size:12.5px; font-weight:600; color:#334155; }
  .onl-time small { display:block; font-size:11px; font-weight:400; color:#94a3b8; margin-top:1px; }

  .onl-empty { padding:44px 16px; text-align:center; color:var(--muted); }
  .onl-empty-icon { width:42px; height:42px; margin:0 auto 10px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:#f1f5f9; color:#94a3b8; font-size:16px; }

  @media (max-width: 767.98px) {
    .onl-head { padding:16px 14px 12px; }
    .onl-page .onl-table thead th, .onl-page .onl-table tbody td { padding-left:12px; padding-right:12px; }
  }
</style>
@endpush

@section('content')
<div class="onl-page">
  <nav class="breadcrumb">
    <span class="breadcrumb-item">Account</span>
    <span class="breadcrumb-item active">Pengguna Online</span>
  </nav>

  <div class="onl-card">
    <div class="onl-head">
      <div>
        <div class="onl-title">Pengguna Online</div>
        <div class="onl-sub">Akun yang aktif dalam {{ $windowMinutes }} menit terakhir. Data diperbarui otomatis tiap 60 detik.</div>
      </div>
      <div class="onl-actions">
        <span class="onl-chip"><i class="fa fa-circle"></i> {{ $users->count() }} akun</span>
        <span class="onl-chip is-muted">{{ $totalSessions }} sesi aktif</span>
        <a href="{{ route('superuser.account.online.index') }}" class="onl-btn"><i class="fa fa-refresh"></i> Muat ulang</a>
      </div>
    </div>

    <div class="onl-table-wrap">
      @if($users->count())
      <table class="onl-table">
        <thead>
          <tr>
            <th>Pengguna</th>
            <th>Email</th>
            <th>Sesi</th>
            <th>IP terakhir</th>
            <th>Terakhir aktif</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $user)
          @php
            $label = trim((string) ($user->name ?: $user->username));
            $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($label, 0, 1));
            $seen = \Carbon\Carbon::parse($user->last_seen_at);
            $seconds = max(0, (int) $seen->diffInSeconds(now()));
            if ($seconds < 60) {
                $relative = $seconds . ' detik lalu';
            } elseif ($seconds < 3600) {
                $relative = floor($seconds / 60) . ' menit lalu';
            } else {
                $relative = floor($seconds / 3600) . ' jam lalu';
            }
          @endphp
          <tr>
            <td>
              <div class="onl-user">
                <span class="onl-avatar">{{ $initial !== '' ? $initial : '?' }}</span>
                <div>
                  <div class="onl-name">
                    {{ $label !== '' ? $label : '-' }}
                    @if((int) $user->id === $currentUserId)
                    <span class="onl-you">Anda</span>
                    @endif
                  </div>
                  <div class="onl-username">{{ $user->username }}</div>
                </div>
              </div>
            </td>
            <td><span class="onl-email">{{ $user->email }}</span></td>
            <td><span class="onl-online"><span class="onl-dot"></span>{{ (int) $user->session_count }} sesi</span></td>
            <td><span class="onl-ip">{{ $user->last_ip ?: '-' }}</span></td>
            <td>
              <span class="onl-time">
                {{ $relative }}
                <small>{{ $seen->format('d M Y H:i:s') }}</small>
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @else
      <div class="onl-empty">
        <div class="onl-empty-icon"><i class="fa fa-user-clock"></i></div>
        Tidak ada pengguna aktif dalam {{ $windowMinutes }} menit terakhir.
      </div>
      @endif
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
  window.setInterval(function () {
    if (document.visibilityState === 'visible') {
      window.location.reload();
    }
  }, 60000);
</script>
@endpush
