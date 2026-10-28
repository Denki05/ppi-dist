@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <a class="breadcrumb-item" href="{{ route('superuser.account.user.index') }}">User</a>
  <span class="breadcrumb-item active">Checklist Modul</span>
</nav>

@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
    <strong>{{ session('error') ? 'Error!' : 'Berhasil!' }}</strong> {{ session('error') ?: session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div class="block">
  <div class="block-content block-content-full">
    <div class="row align-items-center">
      <div class="col-md-7 mb-2 mb-md-0">
        <a href="{{ route('superuser.account.user.index') }}" class="btn btn-sm btn-alt-primary mr-1">
          <i class="fa fa-users"></i> Daftar User
        </a>
        <span class="text-muted ml-2">Total user aktif: <b>{{ $totalActive }}</b></span>
      </div>
      <div class="col-md-5 text-md-right">
        <a href="{{ route('superuser.account.user.create') }}" class="btn btn-primary btn-sm">
          <i class="fa fa-plus"></i> Add User
        </a>
      </div>
    </div>
  </div>
</div>

<div class="block">
  <div class="block-content block-content-full">
    <form method="get" action="{{ route('superuser.account.module_access.index') }}">
      <div class="row align-items-end mb-3">
        <div class="col-md-3 mb-2">
          <label class="mb-1">Status</label>
          <select name="tab" class="form-control">
            <option value="active" {{ $tab === 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="inactive" {{ $tab === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            <option value="all" {{ $tab === 'all' ? 'selected' : '' }}>Semua</option>
          </select>
        </div>
        <div class="col-md-3 mb-2">
          <label class="mb-1">Modul</label>
          <select name="module" class="form-control">
            <option value="">Semua modul</option>
            @foreach($codes as $code)
              <option value="{{ $code }}" {{ $module === $code ? 'selected' : '' }}>
                {{ $code }} &mdash; {{ $items[$code]['name'] ?? $code }} ({{ $counts[$code] ?? 0 }})
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4 mb-2">
          <label class="mb-1">Cari</label>
          <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Nama, username, email, divisi">
        </div>
        <div class="col-md-2 mb-2">
          <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Filter</button>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-striped table-vcenter">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Divisi</th>
            <th>Status</th>
            @foreach($codes as $code)
              <th class="text-center" title="{{ $items[$code]['name'] ?? $code }}">{{ $code }}</th>
            @endforeach
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $index => $user)
            <tr>
              <td>{{ $users->firstItem() + $index }}</td>
              <td>
                <div class="font-w600">{{ $user->name ?: '-' }}</div>
                <small class="text-muted">@ {{ $user->username }}{{ $user->email ? ' &middot; ' . $user->email : '' }}</small>
              </td>
              <td><small>{{ $user->division ?: '-' }}</small></td>
              <td>
                @if($user->is_active == 1)
                  <span class="badge badge-success">Aktif</span>
                @else
                  <span class="badge badge-warning">Nonaktif</span>
                @endif
              </td>
              @foreach($codes as $code)
                <td class="text-center">
                  @if(in_array($code, $user->modules, true))
                    <span class="badge badge-success" title="Boleh akses {{ $items[$code]['name'] ?? $code }}">&check;</span>
                  @else
                    <span class="text-muted">&mdash;</span>
                  @endif
                </td>
              @endforeach
              <td>
                <a href="{{ route('superuser.account.user.edit', $user->id) }}" class="btn btn-primary btn-sm">
                  <i class="fa fa-edit"></i> Edit
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="{{ 5 + count($codes) }}" class="text-center text-muted">Tidak ada user yang cocok dengan filter.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="row">
      <div class="col-12">{{ $users->links() }}</div>
    </div>

    <p class="text-muted mt-3 mb-0" style="font-size:12.5px">
      Centang di tabel adalah akses efektif untuk akun staff. Akun superuser diatur dari menu <b>Edit Superuser</b>;
      selama daftar modulnya kosong, superuser tetap otomatis punya semua modul.
      Akun lama tanpa data modul dianggap punya akses <b>TRS</b> agar tidak kehilangan akses transaksi.
    </p>
  </div>
</div>
@endsection
