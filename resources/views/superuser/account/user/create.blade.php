@extends('superuser.app')

@push('styles')
<style>
  .form-section-title { font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #64748b; margin: 0 0 12px; }
  .module-list { display: flex; flex-wrap: wrap; gap: 8px 18px; padding: 10px 12px; border: 1px solid #dbe3ee; border-radius: 10px; background: #f8fafc; }
  .module-item { display: flex; align-items: center; gap: 7px; margin: 0; cursor: pointer; font-size: 13px; color: #334155; }
  .module-item input { width: 15px; height: 15px; margin: 0; }
  .module-item b { color: #0f172a; }
  .user-form-page { max-width: 1080px; margin: 0 auto; padding: 0 8px 16px; }
</style>
@endpush

@section('content')
<div class="user-form-page">
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <a class="breadcrumb-item" href="{{ route('superuser.account.user.index') }}">Akun</a>
  <span class="breadcrumb-item active">Tambah Staff</span>
</nav>

<div id="alert-block"></div>

<div class="block">
  <div class="block-content block-content-full">
    <form id="frmCreate" action="#">
    @csrf
    <div class="row">
      <div class="col-lg-6">
        <div class="form-section-title">Data akun</div>
        <div class="form-group">
          <label>Nama <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Email <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" required>
          <small class="form-text text-muted">Gunakan Gmail asli agar user bisa login lewat Google.</small>
        </div>
        <div class="form-group">
          <label>Username <span class="text-danger">*</span></label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Password <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" autocomplete="new-password" required minlength="8" maxlength="16">
          </div>
          <div class="form-group col-md-6">
            <label>Konfirmasi Password <span class="text-danger">*</span></label>
            <input type="password" name="password_confirm" class="form-control" autocomplete="new-password" required minlength="8" maxlength="16">
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="form-section-title">Penempatan &amp; akses</div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Jabatan <span class="text-danger">*</span></label>
            <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Sales Manager, Finance Staff" required>
          </div>
          <div class="form-group col-md-6">
            <label>Divisi <span class="text-danger">*</span></label>
            <select name="division" class="form-control js-select2" required>
              @foreach($divisions as $division)
                <option value="{{ $division }}">{{ $division }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Cabang</label>
          <input type="text" name="cabang" class="form-control" value="Jakarta">
        </div>
        <div class="form-group">
          <label>Profil akses <span class="text-danger">*</span></label>
          <select name="access_profile" class="form-control js-select2" required>
            <option value="">Pilih profil...</option>
            @foreach($profiles as $key => $label)
              <option value="{{ $key }}" {{ $currentProfile === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <small class="form-text text-muted">Profil menentukan paket menu dasar yang diberikan ke user.</small>
        </div>
        <div class="form-group">
          <label>Add-on akses</label>
          <div class="module-list">
            @foreach($addons as $key => $addon)
              <label class="module-item" title="{{ $addon['description'] ?? '' }}">
                <input type="checkbox" name="access_addons[]" value="{{ $key }}" {{ in_array($key, $currentAddons, true) ? 'checked' : '' }}>
                <span><b>{{ $addon['label'] ?? $key }}</b></span>
              </label>
            @endforeach
          </div>
          <small class="form-text text-muted">Opsional. Pilih maksimal 3 add-on sesuai kebutuhan.</small>
        </div>
        <div class="form-group mb-1">
          <label>Akses modul</label>
          <div class="module-list">
            @foreach($modules as $code => $item)
              <label class="module-item" title="{{ $item['description'] ?? '' }}">
                <input type="checkbox" name="modules[]" value="{{ $code }}" {{ in_array($code, $currentModules, true) ? 'checked' : '' }}>
                <span><b>{{ $code }}</b> {{ $item['name'] ?? $code }}</span>
              </label>
            @endforeach
          </div>
          <small class="form-text text-muted">Modul menentukan aplikasi yang bisa dibuka user; profil mengatur menu di dalam aplikasi ini.</small>
        </div>
      </div>
    </div>

    <hr>
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center">
      <a href="{{ route('superuser.account.user.index') }}" class="btn btn-alt-secondary mb-2 mb-sm-0">
        <i class="fa fa-arrow-left mr-1"></i> Kembali
      </a>
      <button class="btn btn-primary" type="submit">
        <i class="fa fa-save mr-1"></i> Simpan User
      </button>
    </div>
    </form>
  </div>
</div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script>
  $(function(){
    $('.js-select2').select2({ width: '100%' });

    $(document).on('submit','#frmCreate',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin menyimpan user ini?")){
        var _form = $('#frmCreate');
        $.ajax({
          url : '{{ route('superuser.account.user.store') }}',
          method : "POST",
          data : getFormData(_form),
          dataType : "JSON",
          beforeSend : function(){
            $('button[type="submit"]').prop('disabled', true).html('Menyimpan...');
          },
          success : function(resp){
            if(resp.IsError == true){
              showToast('danger',resp.Message);
            }
            else{
              Swal.fire('Berhasil!', resp.Message, 'success').then(function(){
                document.location.href = '{{ route('superuser.account.user.index') }}';
              });
            }
          },
          error : function(){
            alert("Cek Koneksi Internet");
          },
          complete : function(){
            $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Simpan User');
          }
        });
      }
    });
  });
</script>
@endpush
