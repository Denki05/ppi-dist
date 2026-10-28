@extends('superuser.app')

@push('styles')
<style>
  .acc-page { --ink:#0f172a; --muted:#64748b; --line:#e9eef5; --soft:#f8fafc; max-width:1320px; margin:0 auto; padding:0 8px 24px; }
  .acc-page .breadcrumb { background:transparent; padding:0 2px 10px; margin:0; font-size:12px; }
  .acc-page .breadcrumb-item, .acc-page .breadcrumb-item a { color:#94a3b8; }
  .acc-page .breadcrumb-item.active { color:#475569; font-weight:600; }
  .acc-page .breadcrumb-item + .breadcrumb-item::before { color:#cbd5e1; }

  .acc-card { background:#fff; border:1px solid var(--line); border-radius:16px; box-shadow:0 1px 2px rgba(15,23,42,.04), 0 12px 32px -20px rgba(15,23,42,.18); overflow:hidden; }
  .acc-head { display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:space-between; padding:18px 20px 14px; }
  .acc-title { font-size:19px; font-weight:700; color:var(--ink); letter-spacing:-.01em; }
  .acc-sub { color:var(--muted); font-size:12.5px; margin-top:2px; }
  .acc-head-actions { display:flex; flex-wrap:wrap; gap:8px; }

  .acc-btn { display:inline-flex; align-items:center; gap:6px; height:34px; padding:0 13px; border-radius:10px; font-size:12.5px; font-weight:600; border:1px solid transparent; transition:.15s ease; }
  .acc-btn:hover { text-decoration:none; }
  .acc-btn i { font-size:12px; }
  .acc-btn-ghost { background:#fff; border-color:#e2e8f0; color:#475569; }
  .acc-btn-ghost:hover { border-color:#cbd5e1; background:var(--soft); color:var(--ink); }
  .acc-btn-primary { background:#2563eb; color:#fff; box-shadow:0 6px 14px -8px rgba(37,99,235,.8); }
  .acc-btn-primary:hover { background:#1d4ed8; color:#fff; }
  .acc-btn-dark { background:var(--ink); color:#fff; }
  .acc-btn-dark:hover { background:#1e293b; color:#fff; }

  .acc-toolbar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; padding:0 20px 14px; }
  .acc-tabs { display:inline-flex; gap:2px; padding:3px; background:#f1f5f9; border-radius:11px; }
  .acc-tab { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:8px; font-size:12.5px; font-weight:600; color:#64748b; white-space:nowrap; }
  .acc-tab:hover { color:var(--ink); text-decoration:none; }
  .acc-tab.is-active { background:#fff; color:var(--ink); box-shadow:0 1px 3px rgba(15,23,42,.12); }
  .acc-count { min-width:18px; padding:1px 6px; border-radius:999px; background:#e2e8f0; color:#475569; font-size:10.5px; text-align:center; }
  .acc-tab.is-active .acc-count { background:#2563eb; color:#fff; }
  .acc-count-warn { background:#fef3c7; color:#b45309; }

  .acc-filters { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
  .acc-search { position:relative; }
  .acc-search > i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:12px; pointer-events:none; }
  .acc-search input { width:250px; height:34px; padding:0 12px 0 33px; border:1px solid #e2e8f0; border-radius:10px; background:var(--soft); font-size:12.5px; color:var(--ink); }
  .acc-search input:focus { outline:0; background:#fff; border-color:#93c5fd; box-shadow:0 0 0 3px rgba(59,130,246,.15); }
  .acc-select { height:34px; padding:0 30px 0 11px; border:1px solid #e2e8f0; border-radius:10px; background:#fff; font-size:12.5px; font-weight:600; color:#475569; }
  .acc-select:focus { outline:0; border-color:#93c5fd; box-shadow:0 0 0 3px rgba(59,130,246,.15); }
  .acc-sr { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0; }

  .acc-note { display:flex; gap:10px; margin:0 20px 14px; padding:10px 12px; border-radius:10px; background:#fffbeb; border:1px solid #fde68a; color:#92400e; font-size:12.5px; }
  .acc-note i { margin-top:2px; }

  .acc-table-wrap { border-top:1px solid var(--line); }
  .acc-page table.acc-table { width:100%; min-width:980px; margin:0; }
  .acc-page .acc-table thead th { padding:9px 14px; background:#fbfcfe; border:0; border-bottom:1px solid var(--line); color:#94a3b8; font-size:10.5px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; text-align:left; white-space:nowrap; }
  .acc-page .acc-table thead th.text-right { text-align:right; }
  .acc-page .acc-table tbody td { padding:11px 14px; border:0; border-top:1px solid #f1f5f9; vertical-align:middle; text-align:left; }
  .acc-page .acc-table tbody td.acc-actions { text-align:right; }
  .acc-page .acc-table tbody tr:first-child td { border-top:0; }
  .acc-page .acc-table tbody tr:hover { background:var(--soft); }
  .acc-num { width:46px; color:#94a3b8; font-size:12px; font-weight:600; }

  .acc-user { display:flex; align-items:center; gap:10px; }
  .acc-avatar { flex:0 0 36px; width:36px; height:36px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#e0e7ff,#dbeafe); color:#3730a3; font-size:12px; font-weight:700; letter-spacing:.02em; }
  .acc-name { font-size:13.5px; font-weight:600; color:var(--ink); line-height:1.25; }
  .acc-user-meta { font-size:11.5px; color:var(--muted); margin-top:2px; }

  .acc-type { display:inline-flex; align-items:center; gap:5px; height:22px; padding:0 8px; border-radius:6px; background:#f1f5f9; color:#475569; font-size:11px; font-weight:600; white-space:nowrap; }
  .acc-type i { font-size:10px; }
  .acc-type-su { background:#eef2ff; color:#4338ca; }

  .acc-email { font-size:12.5px; color:#334155; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .acc-flags { display:flex; flex-wrap:wrap; gap:10px; margin-top:4px; }
  .acc-flag { display:inline-flex; align-items:center; gap:5px; font-size:10.5px; font-weight:600; color:#94a3b8; white-space:nowrap; }
  .acc-flag::before { content:''; width:6px; height:6px; border-radius:50%; background:#94a3b8; }
  .acc-flag-ok { color:#059669; }
  .acc-flag-ok::before { background:#10b981; }
  .acc-flag-bad { color:#dc2626; }
  .acc-flag-bad::before { background:#ef4444; }

  .acc-div { font-size:13px; font-weight:600; color:var(--ink); line-height:1.3; }
  .acc-sep { color:#cbd5e1; margin:0 4px; font-weight:400; }
  .acc-jabatan { font-size:11.5px; color:var(--muted); margin-top:1px; }
  .acc-chips { display:flex; flex-wrap:wrap; gap:4px; margin-top:6px; }
  .acc-chip { display:inline-flex; align-items:center; gap:4px; height:21px; padding:0 8px; border-radius:6px; background:#f1f5f9; color:#475569; font-size:10.5px; font-weight:600; white-space:nowrap; }
  .acc-chip i { font-size:9px; }
  .acc-chip-profile { background:#fff; border:1px solid #cbd5e1; color:#334155; }
  .acc-chip-mod { background:#eff6ff; color:#1d4ed8; }
  .acc-chip-warn { background:#fffbeb; color:#b45309; }

  .acc-status { display:inline-flex; align-items:center; gap:6px; padding:3px 9px; border-radius:999px; font-size:11.5px; font-weight:600; white-space:nowrap; }
  .acc-status-dot { width:6px; height:6px; border-radius:50%; background:currentColor; }
  .acc-status.is-active { background:#ecfdf5; color:#047857; }
  .acc-status.is-off { background:#f1f5f9; color:#64748b; }
  .acc-status.is-pending { background:#fffbeb; color:#b45309; }

  .acc-actions { width:112px; }
  .acc-action-group { display:inline-flex; gap:6px; justify-content:flex-end; }
  .acc-act { width:30px; height:30px; padding:0; display:inline-flex; align-items:center; justify-content:center; border-radius:9px; border:1px solid #e2e8f0; background:#fff; color:#475569; font-size:12.5px; transition:.15s ease; }
  .acc-act:hover { border-color:#cbd5e1; background:var(--soft); color:var(--ink); text-decoration:none; }
  .acc-act:focus { outline:0; box-shadow:0 0 0 3px rgba(59,130,246,.18); }
  .acc-act-ok:hover { color:#047857; border-color:#a7f3d0; background:#ecfdf5; }
  .acc-act-warn:hover { color:#b45309; border-color:#fde68a; background:#fffbeb; }
  .acc-act-danger:hover { color:#dc2626; border-color:#fecaca; background:#fef2f2; }
  .acc-act-dark:hover { color:#4338ca; border-color:#c7d2fe; background:#eef2ff; }
  .acc-act[disabled] { opacity:.4; cursor:not-allowed; }
  .acc-act[disabled]:hover { border-color:#e2e8f0; background:#fff; color:#64748b; }

  .acc-empty { padding:44px 16px; text-align:center; color:var(--muted); }
  .acc-empty-icon { width:42px; height:42px; margin:0 auto 10px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:#f1f5f9; color:#94a3b8; font-size:16px; }

  .acc-pager { padding:12px 20px 16px; }
  .acc-page .pagination { margin:0; }
  .acc-page .pagination .page-link { border:1px solid #e2e8f0; color:#475569; border-radius:8px; margin:0 2px; font-size:12px; padding:5px 10px; }
  .acc-page .pagination .page-item.active .page-link { background:#2563eb; border-color:#2563eb; color:#fff; }
  .acc-page .pagination .page-item.disabled .page-link { color:#cbd5e1; }

  @media (max-width: 767.98px) {
    .acc-head { padding:16px 14px 12px; }
    .acc-toolbar, .acc-filters { padding-left:0; padding-right:0; }
    .acc-toolbar { padding:0 14px 12px; }
    .acc-search input { width:100%; }
    .acc-search { flex:1 1 100%; }
    .acc-tabs { overflow-x:auto; max-width:100%; }
  }
</style>
@endpush

@section('content')
<div class="acc-page">
<nav class="breadcrumb">
  <span class="breadcrumb-item">Account</span>
  <span class="breadcrumb-item active">Akun</span>
</nav>

@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert" style="border-radius:12px">
    @if (session('error'))
    <strong>Error!</strong> {!! session('error') !!}
    @elseif (session('success'))
    <strong>Berhasil!</strong> {!! session('success') !!}
    @endif
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div class="acc-card">
  <div class="acc-head">
    <div>
      <div class="acc-title">Manajemen Akun</div>
      <div class="acc-sub">Kelola staff, pendaftar, superuser, akses modul, dan status akun dari satu tempat.</div>
    </div>
    <div class="acc-head-actions">
      <a href="{{route('superuser.account.module_access.index')}}" class="acc-btn acc-btn-ghost">
        <i class="fa fa-table"></i> Checklist Modul
      </a>
      <a href="{{route('superuser.account.user.create')}}" class="acc-btn acc-btn-primary">
        <i class="fa fa-plus"></i> Tambah Staff
      </a>
      <a href="{{route('superuser.account.superuser.create')}}" class="acc-btn acc-btn-dark">
        <i class="fa fa-user-shield"></i> Tambah Superuser
      </a>
    </div>
  </div>

  <div class="acc-toolbar">
    <div class="acc-tabs" role="tablist" aria-label="Filter status akun">
      <a href="{{ route('superuser.account.user.index', ['tab' => 'pending', 'type' => $type]) }}" class="acc-tab {{ $tab==='pending' ? 'is-active' : '' }}">
        Perlu Approval <span class="acc-count {{ $counts['pending'] > 0 ? 'acc-count-warn' : '' }}">{{ $counts['pending'] }}</span>
      </a>
      <a href="{{ route('superuser.account.user.index', ['tab' => 'active', 'type' => $type]) }}" class="acc-tab {{ $tab==='active' ? 'is-active' : '' }}">
        Aktif <span class="acc-count">{{ $counts['active'] }}</span>
      </a>
      <a href="{{ route('superuser.account.user.index', ['tab' => 'inactive', 'type' => $type]) }}" class="acc-tab {{ $tab==='inactive' ? 'is-active' : '' }}">
        Nonaktif <span class="acc-count">{{ $counts['inactive'] }}</span>
      </a>
      <a href="{{ route('superuser.account.user.index', ['tab' => 'all', 'type' => $type]) }}" class="acc-tab {{ $tab==='all' ? 'is-active' : '' }}">
        Semua <span class="acc-count">{{ $counts['active'] + $counts['inactive'] + $counts['pending'] }}</span>
      </a>
    </div>

    <form method="get" action="{{ route('superuser.account.user.index') }}" class="acc-filters">
      <input type="hidden" name="tab" value="{{ $tab }}">
      <div class="acc-search">
        <i class="fa fa-search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, username, email, divisi, jabatan" aria-label="Cari akun">
      </div>
      <select name="type" class="acc-select" aria-label="Filter tipe akun" onchange="this.form.querySelector('input[name=tab]').value='all'; this.form.submit();">
        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Semua tipe</option>
        <option value="staff" {{ $type === 'staff' ? 'selected' : '' }}>Staff ({{ $counts['staff'] }})</option>
        <option value="superuser" {{ $type === 'superuser' ? 'selected' : '' }}>Superuser ({{ $counts['superuser'] }})</option>
      </select>
      @if(request('search'))
        <a href="{{ route('superuser.account.user.index', ['tab' => $tab, 'type' => $type]) }}" class="acc-btn acc-btn-ghost">
          <i class="fa fa-times"></i> Reset
        </a>
      @endif
      <button type="submit" class="acc-sr">Cari</button>
    </form>
  </div>

  @if($tab === 'pending')
  <div class="acc-note">
    <i class="fa fa-info-circle"></i>
    <div><b>Alur pendaftar baru:</b> klik tombol <b>Proses</b> untuk melengkapi data sekaligus mengaktifkan akun. Tidak perlu Edit terlebih dahulu.</div>
  </div>
  @endif

  <div class="table-responsive acc-table-wrap">
    <table class="table acc-table" id="datatables">
      <thead>
        <tr>
          <th class="acc-num" scope="col">#</th>
          <th scope="col">User</th>
          <th scope="col">Tipe</th>
          <th scope="col">Email</th>
          <th scope="col">Posisi &amp; Akses</th>
          <th scope="col">Status</th>
          <th scope="col" class="text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($table as $index => $row)
          @php
            $em = strtolower(trim($row->email ?? ''));
            $dom = substr(strrchr($em, '@') ?: '', 1);
            $emailOk = $em !== '' && filter_var($em, FILTER_VALIDATE_EMAIL) && in_array($dom, config('services.google.allowed_domains', ['gmail.com','googlemail.com']), true);
            $isActive = (int) $row->is_active === 1;
            $isSuperuser = (int) $row->is_superuser === 1;
            $isPendingUser = !$isSuperuser && !$isActive && !empty($row->google_id);
            $rawModules = \App\Services\ModuleAccessService::normalize($row->modul_akses);
            $displayModules = $isActive ? \App\Services\ModuleAccessService::forUser($row) : $rawModules;
            $fullAccess = $isSuperuser && ($row->modul_akses === null || $row->modul_akses === '');
            $linked = $linkedByUser[$row->id] ?? 0;
            $profileKey = $row->access_profile ?: \App\Services\MenuAccessService::profileKeyForUser($row);
            $profileLabel = $profileKey ? config('access_matrix.profile_labels.' . $profileKey, $profileKey) : null;
            $addonKeys = \App\Services\MenuAccessService::addonKeys($row);
            $nameWords = preg_split('/\s+/', trim($row->name ?: $row->username ?: '?')) ?: [];
            $initials = '';
            foreach (array_slice($nameWords, 0, 2) as $word) { $initials .= mb_substr($word, 0, 1); }
            $initials = strtoupper($initials !== '' ? $initials : '?');
            $createdAt = $row->created_at ? \Illuminate\Support\Carbon::parse($row->created_at)->format('d M Y') : '-';
          @endphp
          <tr>
            <td class="acc-num">{{$table->firstItem() + $index}}</td>
            <td>
              <div class="acc-user">
                <span class="acc-avatar" aria-hidden="true">{{ $initials }}</span>
                <div>
                  <div class="acc-name">{{ $row->name ?: $row->username }}</div>
                  <div class="acc-user-meta">@ {{ $row->username }}@if($createdAt !== '-')<span class="acc-sep">&middot;</span>{{ $createdAt }}@endif</div>
                </div>
              </div>
            </td>
            <td>
              @if($isSuperuser)
                <span class="acc-type acc-type-su"><i class="fa fa-user-shield"></i> Superuser</span>
              @else
                <span class="acc-type"><i class="fa fa-user"></i> Staff</span>
              @endif
            </td>
            <td>
              <div class="acc-email" title="{{ $row->email }}">{{ $row->email ?: '—' }}</div>
              <div class="acc-flags">
                @if(!empty($row->google_id))
                  <span class="acc-flag acc-flag-ok">Google</span>
                @else
                  <span class="acc-flag">Belum link</span>
                @endif
                @if(!$emailOk)
                  <span class="acc-flag acc-flag-bad">Email tidak valid</span>
                @endif
              </div>
            </td>
            <td>
              <div class="acc-div">
                {{ $row->division ?: 'Divisi belum diisi' }}@if(!empty($row->cabang))<span class="acc-sep">&middot;</span>{{ $row->cabang }}@endif
              </div>
              <div class="acc-jabatan">{{ $row->jabatan ?: 'Jabatan belum diisi' }}</div>
              <div class="acc-chips">
                @if($fullAccess)
                  <span class="acc-chip acc-chip-profile"><i class="fa fa-key"></i> Semua modul</span>
                @else
                  @if(!$isSuperuser && $profileLabel)
                    <span class="acc-chip acc-chip-profile">{{ $profileLabel }}</span>
                  @endif
                  @if(!$isSuperuser)
                    @foreach($addonKeys as $addonKey)
                      <span class="acc-chip">{{ config('access_matrix.addons.' . $addonKey . '.label', $addonKey) }}</span>
                    @endforeach
                  @endif
                  @forelse($displayModules as $mod)
                    <span class="acc-chip acc-chip-mod">{{ $mod }}</span>
                  @empty
                    <span class="acc-chip acc-chip-warn">Belum ada akses modul</span>
                  @endforelse
                @endif
              </div>
            </td>
            <td>
              @if($isPendingUser)
                <span class="acc-status is-pending"><span class="acc-status-dot"></span> Menunggu approval</span>
              @elseif($isActive)
                <span class="acc-status is-active"><span class="acc-status-dot"></span> Aktif</span>
              @else
                <span class="acc-status is-off"><span class="acc-status-dot"></span> Nonaktif</span>
              @endif
            </td>
            <td class="acc-actions">
              <div class="acc-action-group">
                @if($isSuperuser)
                  <a href="{{route('superuser.account.superuser.show', $row->id)}}" class="acc-act acc-act-dark" title="Kelola" aria-label="Kelola">
                    <i class="fa fa-user-cog"></i>
                  </a>
                  <a href="{{route('superuser.account.superuser.edit', $row->id)}}" class="acc-act" title="Edit" aria-label="Edit">
                    <i class="fa fa-edit"></i>
                  </a>
                @elseif($isPendingUser)
                  <a href="{{route('superuser.account.user.approve.form',$row->id)}}" class="acc-act acc-act-ok" title="Proses & aktifkan" aria-label="Proses">
                    <i class="fa fa-check"></i>
                  </a>
                  <button type="button" class="acc-act acc-act-danger btn-force-delete" data-id="{{$row->id}}" data-name="{{ $row->name ?: $row->username }}" title="Hapus permanen" aria-label="Hapus permanen">
                    <i class="fa fa-trash"></i>
                  </button>
                @elseif($isActive)
                  <a href="{{route('superuser.account.user.edit',['id' => $row->id, 'tab' => $tab])}}" class="acc-act" title="Edit" aria-label="Edit">
                    <i class="fa fa-edit"></i>
                  </a>
                  <button type="button" class="acc-act acc-act-warn btn-deactivate" data-id="{{$row->id}}" data-name="{{ $row->name ?: $row->username }}" title="Nonaktifkan" aria-label="Nonaktifkan">
                    <i class="fa fa-ban"></i>
                  </button>
                @else
                  <a href="{{route('superuser.account.user.edit',['id' => $row->id, 'tab' => $tab])}}" class="acc-act" title="Edit" aria-label="Edit">
                    <i class="fa fa-edit"></i>
                  </a>
                  <button type="button" class="acc-act acc-act-ok btn-restore" data-id="{{$row->id}}" data-name="{{ $row->name ?: $row->username }}" title="Aktifkan" aria-label="Aktifkan">
                    <i class="fa fa-check"></i>
                  </button>
                  <button type="button" class="acc-act acc-act-danger btn-force-delete" data-id="{{$row->id}}" data-name="{{ $row->name ?: $row->username }}" {{ $linked > 0 ? 'disabled' : '' }} title="{{ $linked > 0 ? 'Tertaut dengan '.$linked.' customer. Nonaktifkan saja agar data customer aman.' : 'Hapus permanen' }}" aria-label="Hapus permanen">
                    <i class="fa fa-trash"></i>
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7">
              <div class="acc-empty">
                <div class="acc-empty-icon"><i class="fa fa-users"></i></div>
                <div>{{ $tab === 'pending' ? 'Tidak ada pendaftar yang menunggu approval.' : 'Belum ada akun yang cocok dengan filter ini.' }}</div>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="acc-pager">
    {{$table->links()}}
  </div>
</div>
</div>

<form method="post" action="{{route('superuser.account.user.destroy')}}" id="frmDestroy">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.account.user.force_delete')}}" id="frmForceDelete">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.account.user.restore')}}" id="frmRestore">
    @csrf
    <input type="hidden" name="id">
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(function(){
    $('#datatables').DataTable({
      "paging": false,
      "ordering": false,
      "info": false,
      "searching": false
    });

    $('.js-select2').select2();

    $(document).on('click','.btn-deactivate',function(){
      var name = $(this).data('name') || 'user ini';
      if(confirm("Nonaktifkan " + name + "?\n\nLogin password dan Google akan ditolak, tetapi data user dan tautan customer tetap aman.")){
        $('#frmDestroy').find('input[name="id"]').val($(this).data('id'));
        $('#frmDestroy').submit();
      }
    });

    $(document).on('click','.btn-force-delete',function(){
      if($(this).prop('disabled')) return;
      var name = $(this).data('name') || 'user ini';
      if(confirm("Hapus permanen " + name + "?\n\nTindakan ini tidak bisa dibatalkan. Gunakan Nonaktifkan jika user masih tertaut dengan customer.")){
        $('#frmForceDelete').find('input[name="id"]').val($(this).data('id'));
        $('#frmForceDelete').submit();
      }
    });

    $(document).on('click','.btn-restore',function(){
      var name = $(this).data('name') || 'user ini';
      if(confirm("Aktifkan kembali " + name + "?")){
        $('#frmRestore').find('input[name="id"]').val($(this).data('id'));
        $('#frmRestore').submit();
      }
    });
  });
</script>
@endpush
