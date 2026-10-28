@extends('superuser.app')

@push('styles')
<style>
  .approve-summary { display: flex; align-items: center; gap: 14px; padding: 18px; border-radius: 14px; background: #f8fafc; border: 1px solid #e2e8f0; }
  .approve-avatar { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #2563eb; color: #fff; font-weight: 700; font-size: 18px; flex: 0 0 48px; }
  .approve-summary-title { font-weight: 700; color: #1e293b; }
  .approve-summary-sub { color: #64748b; font-size: 12px; }
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
  <a class="breadcrumb-item" href="{{ route('superuser.account.user.index', ['tab' => 'pending']) }}">Akun</a>
  <span class="breadcrumb-item active">Proses Pendaftar</span>
</nav>

<div id="alert-block"></div>

<div class="block block-rounded mb-3">
  <div class="block-content">
    <div class="approve-summary">
      <div class="approve-avatar">{{ strtoupper(substr($result->name ?: $result->username, 0, 1)) }}</div>
      <div>
        <div class="approve-summary-title">{{ $result->name ?: $result->username }}</div>
        <div class="approve-summary-sub">
          {{ $result->email }} &middot; mendaftar {{ $result->created_at }}
        </div>
        <div class="approve-summary-sub mt-1">
          <span class="badge badge-warning">Menunggu approval</span>
          <span class="badge badge-success"><i class="fa fa-google mr-1"></i>Google</span>
        </div>
      </div>
    </div>

    <div class="alert alert-info mt-3 mb-0" style="border-radius:10px;font-size:13px">
      Lengkapi data di bawah, lalu klik <b>Aktifkan User</b>. Tidak perlu Edit terlebih dahulu; data yang tersimpan sebelumnya akan otomatis muncul di form ini.
    </div>
  </div>
</div>

<div class="block">
  <div class="block-content block-content-full">
    <form id="frmApprove" action="#">
    @csrf
    <input type="hidden" name="user_id" value="{{$result->id}}">
    <div class="row">
      <div class="col-lg-6">
        <div class="form-section-title">Data akun</div>
        <div class="form-group">
          <label>Nama <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" value="{{ $result->name }}" required>
        </div>
        <div class="form-group">
          <label>Username <span class="text-danger">*</span></label>
          <input type="text" name="username" class="form-control" value="{{ $result->username }}" required>
          <small class="form-text text-muted">Username ini yang dipakai kandidat untuk login manual.</small>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="text" class="form-control" value="{{ $result->email }}" disabled>
          <small class="form-text text-muted">Email mengikuti akun Google dan tidak diubah dari halaman ini.</small>
        </div>
        <div class="form-group">
          <label>Password awal <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="text" name="password" id="password" class="form-control" placeholder="Wajib diisi, 8-16 karakter" autocomplete="new-password" required minlength="8" maxlength="16">
            <div class="input-group-append">
              <button type="button" class="btn btn-alt-primary" id="btn-generate"><i class="fa fa-random mr-1"></i> Generate</button>
            </div>
          </div>
          <small class="form-text text-muted">Tampil sekali setelah aktivasi. Sampaikan ke kandidat dan minta diganti di Profile.</small>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="form-section-title">Penempatan &amp; akses</div>
        <div class="form-group">
          <label>Jabatan <span class="text-danger">*</span></label>
          <input type="text" name="jabatan" class="form-control" value="{{ $result->jabatan }}" placeholder="Contoh: Sales Manager, Finance Staff" required>
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Divisi</label>
            <select name="division" class="form-control js-select2">
              @foreach($divisions as $division)
                <option value="{{ $division }}" {{ ($result->division ?: 'Marketing') === $division ? 'selected' : '' }}>{{ $division }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-6">
            <label>Cabang</label>
            <input type="text" name="cabang" class="form-control" value="{{ $result->cabang ?: 'Jakarta' }}">
          </div>
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
          <small class="form-text text-muted">Jika tidak ada modul yang dipilih, user bisa login ke portal tetapi belum bisa membuka modul apa pun.</small>
        </div>
      </div>
    </div>

    <hr>
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center">
      <a href="{{route('superuser.account.user.index', ['tab' => 'pending'])}}" class="btn btn-alt-secondary mb-2 mb-sm-0">
        <i class="fa fa-arrow-left mr-1"></i> Kembali
      </a>
      <button class="btn btn-success" type="submit">
        <i class="fa fa-check mr-1"></i> Aktifkan User
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

    $('#btn-generate').on('click', function(){
      var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
      var out = '';
      for (var i = 0; i < 12; i++) out += chars.charAt(Math.floor(Math.random() * chars.length));
      $('#password').val(out);
    });

    $(document).on('submit','#frmApprove',function(e){
      e.preventDefault();
      var pass = $.trim($('#password').val());
      if(pass.length < 8 || pass.length > 16){
        showToast('danger','Password wajib diisi (8-16 karakter) sebelum approve');
        $('#password').focus();
        return;
      }
      if(confirm("Aktifkan user ini dengan jabatan, divisi, dan akses modul yang dipilih?")){
        var _form = $('#frmApprove');
        $.ajax({
          url : '{{route('superuser.account.user.approve')}}',
          method : "POST",
          data : getFormData(_form),
          dataType : "JSON",
          beforeSend : function(){
            $('button[type="submit"]').prop('disabled', true).html('Memproses...');
          },
          success : function(resp){
            if(resp.IsError == true){
              showToast('danger',resp.Message);
            }
            else{
              var cred = resp.Credentials || {};
              Swal.fire({
                title: 'Berhasil diaktifkan!',
                html: 'Sampaikan ke kandidat:<br><b>Username:</b> ' + (cred.username || '-') +
                      '<br><b>Password:</b> ' + (cred.password || '-') +
                      '<br><small>Minta diganti di Profile saat login pertama. Kredensial ini tidak ditampilkan lagi.</small>',
                icon: 'success',
                confirmButtonText: 'Kembali ke daftar'
              }).then(function(){
                document.location.href = '{{route('superuser.account.user.index', ['tab' => 'active'])}}';
              });
            }
          },
          error : function(){
            alert("Cek Koneksi Internet");
          },
          complete : function(){
            $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Aktifkan User');
          }
        });
      }
    });
  });
</script>
@endpush
