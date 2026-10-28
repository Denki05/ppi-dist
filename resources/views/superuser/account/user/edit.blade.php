@extends('superuser.app')

@push('styles')
<style>
  .acc-form-page { --ink:#0f172a; --muted:#64748b; --line:#e9eef5; --soft:#f8fafc; max-width:1080px; margin:0 auto; padding:0 8px 24px; }
  .acc-form-page .breadcrumb { background:transparent; padding:0 2px 10px; margin:0; font-size:12px; }
  .acc-form-page .breadcrumb-item, .acc-form-page .breadcrumb-item a { color:#94a3b8; }
  .acc-form-page .breadcrumb-item.active { color:#475569; font-weight:600; }
  .acc-form-page .breadcrumb-item + .breadcrumb-item::before { color:#cbd5e1; }

  .acc-card { background:#fff; border:1px solid var(--line); border-radius:16px; box-shadow:0 1px 2px rgba(15,23,42,.04), 0 12px 32px -20px rgba(15,23,42,.18); overflow:hidden; }
  .acc-card-head { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; padding:18px 20px; border-bottom:1px solid var(--line); }
  .acc-title { font-size:18px; font-weight:700; color:var(--ink); letter-spacing:-.01em; }
  .acc-sub { color:var(--muted); font-size:12.5px; margin-top:2px; }
  .acc-card-body { padding:18px 20px 20px; }

  .acc-summary { display:flex; align-items:center; gap:14px; padding:16px 18px; border-radius:14px; background:var(--soft); border:1px solid var(--line); }
  .acc-avatar { width:46px; height:46px; flex:0 0 46px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#e0e7ff,#dbeafe); color:#3730a3; font-weight:700; font-size:17px; }
  .acc-summary-name { font-weight:700; color:var(--ink); font-size:15px; line-height:1.25; }
  .acc-summary-meta { color:var(--muted); font-size:12px; margin-top:2px; }
  .acc-pill { display:inline-flex; align-items:center; gap:5px; height:21px; padding:0 8px; margin:6px 4px 0 0; border-radius:999px; font-size:10.5px; font-weight:600; }
  .acc-pill-ok { background:#ecfdf5; color:#047857; }
  .acc-pill-off { background:#f1f5f9; color:#64748b; }
  .acc-pill-warn { background:#fffbeb; color:#b45309; }
  .acc-pill-info { background:#eef2ff; color:#4338ca; }
  .acc-pill i { font-size:10px; }

  .acc-section-title { font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#94a3b8; margin:4px 0 12px; }
  .acc-sep-line { border-top:1px solid #f1f5f9; margin:20px 0; }

  .acc-form-page label { font-size:12px; font-weight:600; color:#475569; margin-bottom:5px; }
  .acc-form-page .form-control { height:38px; border-radius:10px; border-color:#e2e8f0; font-size:13px; color:var(--ink); }
  .acc-form-page textarea.form-control { height:auto; }
  .acc-form-page .form-control:focus { border-color:#93c5fd; box-shadow:0 0 0 3px rgba(59,130,246,.15); }
  .acc-form-page .form-text { font-size:11.5px; color:var(--muted); }
  .acc-form-page .form-control[readonly] { background:var(--soft); color:#64748b; }
  .acc-file { display:block; width:100%; padding:8px 10px; border:1px dashed #cbd5e1; border-radius:10px; background:var(--soft); font-size:12.5px; color:#475569; }
  .acc-file::file-selector-button { margin-right:10px; padding:5px 10px; border:0; border-radius:7px; background:#eef2ff; color:#4338ca; font-size:11.5px; font-weight:600; cursor:pointer; }
  .acc-file::-webkit-file-upload-button { margin-right:10px; padding:5px 10px; border:0; border-radius:7px; background:#eef2ff; color:#4338ca; font-size:11.5px; font-weight:600; cursor:pointer; }

  .acc-options { display:flex; flex-wrap:wrap; gap:8px; padding:10px 12px; border:1px solid #dbe3ee; border-radius:10px; background:var(--soft); }
  .acc-option { display:flex; align-items:center; gap:7px; margin:0; font-size:12.5px; color:#334155; cursor:pointer; font-weight:400; }
  .acc-option input { width:15px; height:15px; margin:0; }
  .acc-option b { color:var(--ink); font-weight:700; }
  .acc-option span { color:#64748b; }
  .acc-option.is-disabled { cursor:default; opacity:.8; }

  .acc-footer { display:flex; flex-wrap:wrap; gap:8px; align-items:center; justify-content:space-between; padding:14px 20px; background:var(--soft); border-top:1px solid var(--line); }
  .acc-btn { display:inline-flex; align-items:center; gap:6px; height:36px; padding:0 14px; border-radius:10px; font-size:12.5px; font-weight:600; border:1px solid transparent; transition:.15s ease; }
  .acc-btn:hover { text-decoration:none; }
  .acc-btn-ghost { background:#fff; border-color:#e2e8f0; color:#475569; }
  .acc-btn-ghost:hover { border-color:#cbd5e1; background:#f8fafc; color:var(--ink); }
  .acc-btn-primary { background:#2563eb; color:#fff; box-shadow:0 6px 14px -8px rgba(37,99,235,.8); }
  .acc-btn-primary:hover { background:#1d4ed8; color:#fff; }
  .acc-btn[disabled] { opacity:.6; cursor:not-allowed; }

  .acc-fold { border-top:1px solid var(--line); }
  .acc-fold > summary { list-style:none; cursor:pointer; display:flex; flex-wrap:wrap; gap:4px 10px; align-items:baseline; padding:16px 20px; }
  .acc-fold > summary::-webkit-details-marker { display:none; }
  .acc-fold > summary span { font-size:14px; font-weight:700; color:var(--ink); }
  .acc-fold > summary small { font-size:12px; color:var(--muted); }
  .acc-fold > summary::after { content:'\f078'; font-family:'Font Awesome 5 Free'; font-weight:900; font-size:10px; color:#94a3b8; margin-left:auto; }
  .acc-fold[open] > summary::after { transform:rotate(180deg); }
  .acc-fold-body { padding:0 20px 20px; }

  .acc-role-wrap { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px; }
  .acc-role { display:inline-flex; align-items:center; gap:7px; height:28px; padding:0 10px; border-radius:999px; background:#eef2ff; color:#4338ca; font-size:11.5px; font-weight:600; }
  .acc-role:hover { background:#e0e7ff; color:#3730a3; text-decoration:none; }
  .acc-role i { font-size:10px; opacity:.7; }
  .acc-role-add { display:inline-flex; align-items:center; gap:6px; height:28px; padding:0 10px; border-radius:999px; border:1px dashed #cbd5e1; color:#64748b; font-size:11.5px; font-weight:600; }
  .acc-role-add:hover { border-color:#93c5fd; color:#2563eb; text-decoration:none; }

  .acc-fold-head { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; margin-bottom:10px; }
  .acc-perm-group { border:1px solid var(--line); border-radius:10px; margin-bottom:8px; overflow:hidden; }
  .acc-perm-toggle { display:flex; align-items:center; justify-content:space-between; padding:10px 12px; background:#fbfcfe; color:#334155; font-size:12.5px; font-weight:700; }
  .acc-perm-toggle:hover { text-decoration:none; color:var(--ink); }
  .acc-perm-toggle i { font-size:9px; color:#94a3b8; }
  .acc-perm-table { margin:0; font-size:12px; }
  .acc-perm-table thead th { font-size:10px; text-transform:uppercase; letter-spacing:.05em; color:#94a3b8; border-top:0; background:#fff; padding:8px 10px; }
  .acc-perm-table tbody td { vertical-align:middle; padding:7px 10px; }
  .acc-perm-table tbody td:first-child { color:#94a3b8; width:44px; }

  @media (max-width: 575.98px) {
    .acc-card-head, .acc-card-body, .acc-footer, .acc-fold > summary, .acc-fold-body { padding-left:14px; padding-right:14px; }
  }
</style>
@endpush

@section('content')
<div class="acc-form-page">
<nav class="breadcrumb">
  <span class="breadcrumb-item">Account</span>
  <a class="breadcrumb-item" href="{{ $backUrl }}">Akun</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>

<div id="alert-block"></div>

@php
  $isActive = (int) $result->is_active === 1;
  $isPendingAccount = !$isSuperuser && !$isActive && !empty($result->google_id);
@endphp

<div class="acc-card mb-3">
  <div class="acc-card-body">
    <div class="acc-summary">
      <div class="acc-avatar">{{ strtoupper(mb_substr($result->name ?: $result->username, 0, 1)) }}</div>
      <div>
        <div class="acc-summary-name">{{ $result->name ?: $result->username }}</div>
        <div class="acc-summary-meta">{{ $result->email }} &middot; @ {{ $result->username }}</div>
        <div>
          @if($isSuperuser)
            <span class="acc-pill acc-pill-info"><i class="fa fa-user-shield"></i> Superuser</span>
          @else
            <span class="acc-pill acc-pill-info"><i class="fa fa-user"></i> Staff</span>
          @endif
          @if($isActive)
            <span class="acc-pill acc-pill-ok">Aktif</span>
          @elseif($isPendingAccount)
            <span class="acc-pill acc-pill-warn">Menunggu approval</span>
          @else
            <span class="acc-pill acc-pill-off">Nonaktif</span>
          @endif
          @if(!empty($result->google_id))
            <span class="acc-pill acc-pill-ok"><i class="fa fa-google"></i> Google</span>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

@if(!$isSuperuser && $linkedCustomers > 0)
<div class="alert alert-warning" style="border-radius:12px">
  <i class="fa fa-link mr-1"></i>
  User ini tertaut dengan <b>{{ $linkedCustomers }} customer</b>. Jangan hapus permanen; gunakan <b>Nonaktifkan</b> agar data customer tetap aman.
</div>
@endif

<div class="acc-card">
  <div class="acc-card-head">
    <div>
      <div class="acc-title">Edit {{ $isSuperuser ? 'superuser' : 'akun' }}</div>
      <div class="acc-sub">{{ $isSuperuser ? 'Perbarui data akun dan modul yang boleh dibuka.' : 'Perbarui data akun, penempatan, dan akses modul.' }}</div>
    </div>
  </div>

  <form id="frmEditAccount" method="POST" action="{{ $updateUrl }}" @if($formHasFile) enctype="multipart/form-data" @endif>
    @csrf
    @if($formMethod !== 'POST')
      <input type="hidden" name="_method" value="{{ $formMethod }}">
    @endif
    <input type="hidden" name="user_id" value="{{ $result->id }}">

    <div class="acc-card-body">
      <div class="acc-section-title">Data akun</div>
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label>Nama <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ $result->name }}" required>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Email <span class="text-danger">*</span></label>
            <input type="text" name="email" class="form-control" value="{{ $result->email }}" {{ $canEditIdentity ? '' : 'readonly' }} required>
            <small class="form-text">Gunakan Gmail asli agar user bisa login lewat Google.</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Username <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" value="{{ $result->username }}" {{ $canEditIdentity ? '' : 'readonly' }} required>
          </div>
        </div>
      </div>

      @if($isSuperuser)
        <div class="form-group mb-0">
          <label>Foto</label>
          <input type="file" name="image" class="acc-file" accept="image/png, image/jpeg">
          <small class="form-text">Kosongkan jika tidak diganti. Maksimal 2 MB (JPG/PNG).</small>
        </div>
        @if(!$canEditIdentity)
          <small class="form-text d-block mt-2">Email, username, dan password hanya dapat diubah oleh SuperAdmin.</small>
        @endif
      @endif

      @if(!$isSuperuser || $canEditIdentity)
        <div class="acc-sep-line"></div>
        <div class="acc-section-title">Ganti password</div>
        <div class="row">
          <div class="col-md-4">
            <div class="form-group mb-md-0">
              <label>Password baru</label>
              <input type="password" name="password" class="form-control" autocomplete="new-password">
              <small class="form-text">Kosongkan jika tidak diubah.</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group mb-md-0">
              <label>Konfirmasi password</label>
              <input type="password" name="{{ $passwordConfirmName }}" class="form-control" autocomplete="new-password">
              <small class="form-text">Isi ulang password baru.</small>
            </div>
          </div>
        </div>
      @endif

      @if(!$isSuperuser)
        <div class="acc-sep-line"></div>
        <div class="acc-section-title">Penempatan &amp; akses</div>
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>Jabatan <span class="text-danger">*</span></label>
              <input type="text" name="jabatan" class="form-control" value="{{ $result->jabatan }}" placeholder="Contoh: Sales Manager" required>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Divisi <span class="text-danger">*</span></label>
              <select name="division" class="form-control js-select2" required>
                @foreach($divisions as $division)
                  <option value="{{ $division }}" {{ $result->division === $division ? 'selected' : '' }}>{{ $division }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Cabang</label>
              <input type="text" name="cabang" class="form-control" value="{{ $result->cabang ?: 'Jakarta' }}">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Profil akses <span class="text-danger">*</span></label>
              <select name="access_profile" class="form-control js-select2" required>
                <option value="">Pilih profil...</option>
                @foreach($profiles as $key => $label)
                  <option value="{{ $key }}" {{ $currentProfile === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="form-group mb-0">
          <label>Add-on akses</label>
          <div class="acc-options">
            @foreach($addons as $key => $addon)
              <label class="acc-option" title="{{ $addon['description'] ?? '' }}">
                <input type="checkbox" name="access_addons[]" value="{{ $key }}" {{ in_array($key, $currentAddons, true) ? 'checked' : '' }}>
                <span><b>{{ $addon['label'] ?? $key }}</b></span>
              </label>
            @endforeach
          </div>
          <small class="form-text">Opsional. Pilih maksimal 3 add-on sesuai kebutuhan.</small>
        </div>
      @endif

      <div class="acc-sep-line"></div>
      <div class="acc-section-title">Akses modul</div>
      @if($canEditModules)
        <div class="acc-options">
          @foreach($modules as $code => $item)
            <label class="acc-option" title="{{ $item['description'] ?? '' }}">
              <input type="checkbox" name="modules[]" value="{{ $code }}" {{ in_array($code, $currentModules, true) ? 'checked' : '' }}>
              <span><b>{{ $code }}</b> {{ $item['name'] ?? $code }}</span>
            </label>
          @endforeach
        </div>
        @if($isSuperuser)
          <input type="hidden" name="modules_present" value="1">
        @endif
        <small class="form-text">{{ $isSuperuser ? 'Modul yang boleh dibuka setelah login. Semua tercentang = akses penuh.' : 'Menentukan modul yang bisa dibuka user setelah login.' }}</small>
      @else
        <div class="acc-options">
          @forelse($currentModules as $mod)
            <label class="acc-option is-disabled">
              <input type="checkbox" checked disabled>
              <span><b>{{ $mod }}</b></span>
            </label>
          @empty
            <span class="acc-sub">Belum ada akses modul.</span>
          @endforelse
        </div>
        <small class="form-text">Hanya Developer/SuperAdmin yang dapat mengubah akses modul.</small>
      @endif
    </div>

    <div class="acc-footer">
      <a href="{{ $backUrl }}" class="acc-btn acc-btn-ghost">
        <i class="fa fa-arrow-left"></i> Kembali
      </a>
      <button class="acc-btn acc-btn-primary" type="submit">
        <i class="fa fa-save"></i> Simpan Perubahan
      </button>
    </div>
  </form>

  @if($isSuperuser && $canManageRoles)
  <details class="acc-fold">
    <summary>
      <span>Role &amp; permission</span>
      <small>Atur role dan hak akses menu superuser ini</small>
    </summary>
    <div class="acc-fold-body">
      <div class="acc-role-wrap">
        @foreach($result->roles as $role)
          <a href="javascript:void(0)" class="acc-role remove-role" data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}" title="Hapus role {{ $role->name }}">
            {{ $role->name }} <i class="fa fa-times"></i>
          </a>
        @endforeach
        @if(count($result->dontHaveRoles()) > 0)
          <a href="javascript:void(0)" class="acc-role-add" data-toggle="modal" data-target="#modal-assign-role">
            <i class="fa fa-plus"></i> Tambah role
          </a>
        @endif
      </div>

      <form class="ajax" data-action="{{ route('superuser.account.superuser.permission.sync', $result->id) }}" data-type="POST" enctype="multipart/form-data">
        <div class="acc-fold-head">
          <div class="acc-section-title mb-0">Permission</div>
          <button type="submit" class="acc-btn acc-btn-ghost">Simpan permission</button>
        </div>
        <div id="accordion" role="tablist" aria-multiselectable="true">
          @foreach(\App\Helper\PermissionHelper::MODULES as $name => $permModules)
            @if(!$result->hasRole('Developer') && $name == 'DEVELOPER')
              @continue
            @endif
            <div class="acc-perm-group">
              <a class="acc-perm-toggle" data-toggle="collapse" href="#perm-{{ $loop->iteration }}" role="button" aria-expanded="false" aria-controls="perm-{{ $loop->iteration }}">
                {{ $name }} <i class="fa fa-chevron-down"></i>
              </a>
              <div id="perm-{{ $loop->iteration }}" class="collapse" role="tabpanel">
                <table class="table acc-perm-table">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Menu</th>
                      @foreach(\App\Helper\PermissionHelper::ACTIONS as $action)
                        <th scope="col">{{ $action }}</th>
                      @endforeach
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($permModules as $module)
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ ucwords($module) }}</td>
                        @foreach(\App\Helper\PermissionHelper::ACTIONS as $action)
                          <td>
                            @if(\App\Helper\PermissionHelper::isPermissionExists($module . '-' . $action, 'superuser'))
                              <label class="css-control css-control-success css-checkbox">
                                <input type="checkbox" class="css-control-input" name="permissions[]" value="{{ $module . '-' . $action }}" @if($result->can($module . '-' . $action)) checked @endif>
                                <span class="css-control-indicator"></span>
                              </label>
                            @else
                              <i class="si si-info text-warning"></i>
                            @endif
                          </td>
                        @endforeach
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @endforeach
        </div>
      </form>
    </div>
  </details>
  @endif
</div>
</div>
@endsection

@section('modal')
@if($isSuperuser && $canManageRoles)
  @if(count($result->dontHaveRoles()) > 0)
  <div class="modal fade" id="modal-assign-role" tabindex="-1" role="dialog" aria-labelledby="modal-assign-role-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form class="ajax" data-action="{{ route('superuser.account.superuser.role.assign', $result->id) }}" data-type="POST" enctype="multipart/form-data">
          <div class="block block-themed block-transparent mb-0">
            <div class="block-header bg-primary-dark">
              <h3 class="block-title" id="modal-assign-role-title">Assign Role</h3>
              <div class="block-options">
                <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                  <i class="si si-close"></i>
                </button>
              </div>
            </div>
            <div id="alert-block"></div>
            <div class="block-content">
              <div class="form-group">
                <select class="form-control" name="role">
                  @foreach($result->dontHaveRoles() as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-alt-success">
              <i class="fa fa-check"></i> Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endif

  <div class="modal fade" id="modal-remove-role" tabindex="-1" role="dialog" aria-labelledby="modal-remove-role-title" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="block block-themed block-transparent mb-0">
          <div class="block-header bg-primary-dark">
            <h3 class="block-title" id="modal-remove-role-title">Remove Role</h3>
            <div class="block-options">
              <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                <i class="si si-close"></i>
              </button>
            </div>
          </div>
          <div class="block-content block-content-full text-center">
            <span id="role-name"></span>
          </div>
        </div>
        <div class="modal-footer justify-content-center">
          <form class="ajax" data-action="{{ route('superuser.account.superuser.role.remove', $result->id) }}" data-type="POST" enctype="multipart/form-data">
            <button type="submit" class="btn btn-alt-danger">
              <i class="fa fa-trash"></i> Remove
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endif
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>

@push('scripts')
<script>
  var RETURN_URL = '{{ $backUrl }}';

  $(function(){
    $('.js-select2').select2({ width: '100%' });

    $('#frmEditAccount').on('submit', function(e){
      e.preventDefault();

      var formEl = this;

      if(!confirm("Simpan perubahan akun ini?")){
        return;
      }

      var formData = new FormData(formEl);

      $.ajax({
        url: '{{ $updateUrl }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'JSON',
        beforeSend: function(){
          $(formEl).find('button[type="submit"]').prop('disabled', true).html('Menyimpan...');
        },
        success: function(resp){
          if(resp.IsError == true){
            showToast('danger', resp.Message);
          } else {
            Swal.fire('Berhasil!', resp.Message || 'Perubahan disimpan', 'success').then(function(){
              document.location.href = RETURN_URL;
            });
          }
        },
        error: function(){
          alert("Cek Koneksi Internet");
        },
        complete: function(){
          $(formEl).find('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Perubahan');
        }
      });
    });

    $('.remove-role').on('click', function (e) {
      e.preventDefault();

      $('#modal-remove-role').modal('show');

      var role_id = $(this).data('role-id');
      var role_name = $(this).data('role-name');

      $('#modal-remove-role #role-name').text(role_name);
      $('#modal-remove-role form').append('<input type="hidden" name="role" value="'+ role_id +'">');
    });

    $('#modal-remove-role').on('hidden.bs.modal', function () {
      $('#modal-remove-role #role-name').text('');
      $('#modal-remove-role form').children('input[name=role]').remove();
    });
  });
</script>
@endpush
