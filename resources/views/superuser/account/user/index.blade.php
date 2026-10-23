@extends('superuser.app')

@push('styles')
<style>
  .user-hero-title { font-size: 20px; font-weight: 700; color: #1e293b; }
  .user-hero-sub { color: #64748b; font-size: 13px; }
  .user-tabs .btn { border-radius: 999px; }
  .user-table > tbody > tr > td { vertical-align: middle; }
  .user-name { font-weight: 600; color: #1e293b; }
  .user-meta { color: #64748b; font-size: 12px; }
  .user-module-chip { display: inline-block; margin: 2px 3px 0 0; padding: 2px 7px; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 600; }
  .user-actions { white-space: nowrap; }
  .user-actions .btn { margin-bottom: 2px; }
  .user-page { max-width: 1320px; margin: 0 auto; padding: 0 8px 16px; }
  @media (max-width: 767px) {
    .user-hero-actions { margin-top: 12px; }
  }
</style>
@endpush

@section('content')
<div class="user-page">
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <span class="breadcrumb-item active">Akun</span>
</nav>

@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
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

<div class="block block-rounded mb-3">
  <div class="block-content py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
    <div>
      <div class="user-hero-title">Manajemen Akun</div>
      <div class="user-hero-sub">Kelola staff, pendaftar, superuser, akses modul, dan status akun dari satu tempat.</div>
    </div>
    <div class="user-hero-actions">
      <a href="{{route('superuser.account.module_access.index')}}" class="btn btn-alt-primary btn-sm">
        <i class="fa fa-table mr-1"></i> Checklist Modul
      </a>
      <a href="{{route('superuser.account.user.create')}}" class="btn btn-primary btn-sm">
        <i class="fa fa-plus mr-1"></i> Tambah Staff
      </a>
      <a href="{{route('superuser.account.superuser.create')}}" class="btn btn-dark btn-sm">
        <i class="fa fa-user-shield mr-1"></i> Tambah Superuser
      </a>
    </div>
  </div>
</div>

<div class="block mb-0">
  <div class="block-header">
    <div class="btn-group btn-group-sm user-tabs flex-wrap" role="group">
      <a href="{{ route('superuser.account.user.index', ['tab' => 'pending', 'type' => $type]) }}" class="btn {{ $tab==='pending' ? 'btn-warning' : 'btn-alt-warning' }}">
        Perlu Approval <span class="badge badge-light ml-1">{{ $counts['pending'] }}</span>
      </a>
      <a href="{{ route('superuser.account.user.index', ['tab' => 'active', 'type' => $type]) }}" class="btn {{ $tab==='active' ? 'btn-success' : 'btn-alt-success' }}">
        Aktif <span class="badge badge-light ml-1">{{ $counts['active'] }}</span>
      </a>
      <a href="{{ route('superuser.account.user.index', ['tab' => 'inactive', 'type' => $type]) }}" class="btn {{ $tab==='inactive' ? 'btn-danger' : 'btn-alt-danger' }}">
        Nonaktif <span class="badge badge-light ml-1">{{ $counts['inactive'] }}</span>
      </a>
      <a href="{{ route('superuser.account.user.index', ['tab' => 'all', 'type' => $type]) }}" class="btn {{ $tab==='all' ? 'btn-primary' : 'btn-alt-primary' }}">
        Semua
      </a>
    </div>
  </div>
  <div class="block-content">
    <form class="mb-3" method="get" action="{{ route('superuser.account.user.index') }}">
      <input type="hidden" name="tab" value="{{ $tab }}">
      <div class="row align-items-center">
        <div class="col-md-4">
          <div class="input-group input-group-sm">
            <input type="text" class="form-control" placeholder="Cari nama, username, email, divisi, atau jabatan" name="search" value="{{ request('search') }}">
            <div class="input-group-append">
              <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </div>
          </div>
        </div>
        <div class="col-md-3 mt-2 mt-md-0">
          <select name="type" class="form-control form-control-sm" onchange="this.form.querySelector('input[name=tab]').value='all'; this.form.submit();">
            <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Semua tipe akun</option>
            <option value="staff" {{ $type === 'staff' ? 'selected' : '' }}>Staff ({{ $counts['staff'] }})</option>
            <option value="superuser" {{ $type === 'superuser' ? 'selected' : '' }}>Superuser ({{ $counts['superuser'] }})</option>
          </select>
        </div>
        @if(request('search'))
        <div class="col-md-5 text-md-right mt-2 mt-md-0">
          <a href="{{ route('superuser.account.user.index', ['tab' => $tab, 'type' => $type]) }}" class="btn btn-alt-secondary btn-sm">
            <i class="fa fa-times mr-1"></i> Reset pencarian
          </a>
        </div>
        @endif
      </div>
    </form>

    @if($tab === 'pending')
    <div class="alert alert-warning d-flex align-items-start" style="border-radius:10px">
      <i class="fa fa-info-circle mr-2 mt-1"></i>
      <div>
        <b>Alur pendaftar baru:</b> klik <b>Proses</b> untuk melengkapi data sekaligus mengaktifkan akun.
        Tidak perlu Edit terlebih dahulu.
      </div>
    </div>
    @endif

    <div class="table-responsive">
      <table class="table table-striped table-vcenter user-table" id="datatables">
        <thead>
          <tr>
            <th style="width:42px">#</th>
            <th>User</th>
            <th>Tipe</th>
            <th>Email</th>
            <th>Posisi &amp; Akses</th>
            <th>Status</th>
            <th style="width:220px">Aksi</th>
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
              $linked = $linkedByUser[$row->id] ?? 0;
              $profileKey = $row->access_profile ?: \App\Services\MenuAccessService::profileKeyForUser($row);
              $profileLabel = $profileKey ? config('access_matrix.profile_labels.' . $profileKey, $profileKey) : null;
              $addonKeys = \App\Services\MenuAccessService::addonKeys($row);
            @endphp
            <tr>
              <td>{{$table->firstItem() + $index}}</td>
              <td>
                <div class="user-name">{{ $row->name ?: '-' }}</div>
                <div class="user-meta">@ {{ $row->username }}</div>
                <div class="user-meta">Terdaftar {{ $row->created_at }}</div>
              </td>
              <td>
                @if($isSuperuser)
                  <span class="badge badge-dark"><i class="fa fa-user-shield mr-1"></i>Superuser</span>
                @else
                  <span class="badge badge-info">Staff</span>
                @endif
              </td>
              <td>
                <div>{{ $row->email }}</div>
                @if(!empty($row->google_id))
                  <span class="badge badge-success mt-1"><i class="fa fa-google mr-1"></i>Google</span>
                @else
                  <span class="badge badge-secondary mt-1">Belum link</span>
                @endif
                @if(!$emailOk)
                  <span class="badge badge-danger mt-1">Email tidak valid</span>
                @endif
              </td>
              <td>
                <div>{{ $row->division ?: 'Divisi belum diisi' }}@if(!empty($row->cabang)) &middot; {{ $row->cabang }}@endif</div>
                <div class="mt-1">
                  @if($row->jabatan)
                    <span class="badge badge-primary">{{ $row->jabatan }}</span>
                  @else
                    <span class="badge badge-secondary">Jabatan belum diisi</span>
                  @endif
                </div>
                <div class="mt-1">
                  @if($isSuperuser)
                    <span class="badge badge-dark">Akses penuh semua menu</span>
                  @else
                    @if($profileLabel)
                      <span class="badge badge-dark">{{ $profileLabel }}</span>
                    @endif
                    @foreach($addonKeys as $addonKey)
                      <span class="badge badge-secondary">{{ config('access_matrix.addons.' . $addonKey . '.label', $addonKey) }}</span>
                    @endforeach
                    @forelse($displayModules as $mod)
                      <span class="user-module-chip">{{ $mod }}</span>
                    @empty
                      <span class="badge badge-warning">Belum ada akses modul</span>
                    @endforelse
                  @endif
                </div>
              </td>
              <td>
                @if($isPendingUser)
                  <span class="badge badge-warning">Menunggu approval</span>
                @elseif($isActive)
                  <span class="badge badge-success">Aktif</span>
                @else
                  <span class="badge badge-danger">Nonaktif</span>
                @endif
              </td>
              <td class="user-actions">
                @if($isSuperuser)
                  <a href="{{route('superuser.account.superuser.show', $row->id)}}" class="btn btn-primary btn-sm">
                    <i class="fa fa-user-cog mr-1"></i> Kelola
                  </a>
                  <a href="{{route('superuser.account.superuser.edit', $row->id)}}" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-edit mr-1"></i> Edit
                  </a>
                @elseif($isPendingUser)
                  <a href="{{route('superuser.account.user.approve.form',$row->id)}}" class="btn btn-success btn-sm">
                    <i class="fa fa-check mr-1"></i> Proses
                  </a>
                  <button type="button" class="btn btn-outline-danger btn-sm btn-force-delete" data-id="{{$row->id}}" data-name="{{ $row->name ?: $row->username }}">
                    <i class="fa fa-trash mr-1"></i> Hapus
                  </button>
                @elseif($isActive)
                  <a href="{{route('superuser.account.user.edit',['id' => $row->id, 'tab' => $tab])}}" class="btn btn-primary btn-sm">
                    <i class="fa fa-edit mr-1"></i> Edit
                  </a>
                  <button type="button" class="btn btn-outline-warning btn-sm btn-deactivate" data-id="{{$row->id}}" data-name="{{ $row->name ?: $row->username }}">
                    <i class="fa fa-ban mr-1"></i> Nonaktifkan
                  </button>
                @else
                  <a href="{{route('superuser.account.user.edit',['id' => $row->id, 'tab' => $tab])}}" class="btn btn-primary btn-sm">
                    <i class="fa fa-edit mr-1"></i> Edit
                  </a>
                  <button type="button" class="btn btn-outline-success btn-sm btn-restore" data-id="{{$row->id}}" data-name="{{ $row->name ?: $row->username }}">
                    <i class="fa fa-check mr-1"></i> Aktifkan
                  </button>
                  <button type="button" class="btn btn-outline-danger btn-sm btn-force-delete" data-id="{{$row->id}}" data-name="{{ $row->name ?: $row->username }}" {{ $linked > 0 ? 'disabled' : '' }} title="{{ $linked > 0 ? 'Tertaut dengan '.$linked.' customer. Nonaktifkan saja agar data customer aman.' : 'Hapus permanen' }}">
                    <i class="fa fa-trash mr-1"></i> Hapus
                  </button>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">
              @if($tab === 'pending') Tidak ada pendaftar yang menunggu approval. @else Tidak ada data. @endif
            </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="row mt-3">
      <div class="col-12">
        {{$table->links()}}
      </div>
    </div>
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
      "ordering": true,
      "info": false,
      "searching": false,
      "columnDefs": [{
        "targets": 0,
        "orderable": false
      }]
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
